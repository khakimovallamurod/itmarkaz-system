<?php
class Database
{
    private string $host = 'localhost';
    private string $user = 'root';
    private string $pass = '';
    private string $name = 'itmarkazdb';
    private ?mysqli $conn = null;

    public function connect(): mysqli
    {
        if ($this->conn instanceof mysqli) {
            return $this->conn;
        }

        $persistentHost = str_starts_with($this->host, 'p:') ? $this->host : 'p:' . $this->host;
        $this->conn = @new mysqli($persistentHost, $this->user, $this->pass, $this->name);
        if ($this->conn->connect_error) {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->name);
        }
        if ($this->conn->connect_error) {
            http_response_code(500);
            die('Database ulanishda xatolik: ' . $this->conn->connect_error);
        }

        $this->conn->set_charset('utf8mb4');
        return $this->conn;
    }
}

function clean_input(?string $value): string
{
    return trim(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

function normalize_uz_phone(?string $value): ?string
{
    $digits = preg_replace('/\D+/', '', (string) $value);
    if ($digits === null || $digits === '') {
        return null;
    }

    if (str_starts_with($digits, '998')) {
        $local = substr($digits, 3);
    } elseif (strlen($digits) === 9) {
        $local = $digits;
    } else {
        return null;
    }

    if (!preg_match('/^\d{9}$/', $local)) {
        return null;
    }

    return sprintf('+998 %s %s %s %s', substr($local, 0, 2), substr($local, 2, 3), substr($local, 5, 2), substr($local, 7, 2));
}

function ensure_mentor_module_schema(mysqli $db): void
{
    static $booted = false;
    if ($booted) {
        return;
    }
    $booted = true;

    $tableExists = static function (string $table) use ($db): bool {
        $sql = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('s', $table);
        $stmt->execute();
        return (int) ($stmt->get_result()->fetch_assoc()['cnt'] ?? 0) > 0;
    };
    $columnExists = static function (string $table, string $column) use ($db): bool {
        $sql = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ss', $table, $column);
        $stmt->execute();
        return (int) ($stmt->get_result()->fetch_assoc()['cnt'] ?? 0) > 0;
    };
    $indexExists = static function (string $table, string $index) use ($db): bool {
        $sql = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ss', $table, $index);
        $stmt->execute();
        return (int) ($stmt->get_result()->fetch_assoc()['cnt'] ?? 0) > 0;
    };
    $fkExists = static function (string $table, string $fkName) use ($db): bool {
        $sql = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ss', $table, $fkName);
        $stmt->execute();
        return (int) ($stmt->get_result()->fetch_assoc()['cnt'] ?? 0) > 0;
    };

    $db->query('
      CREATE TABLE IF NOT EXISTS mentors (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        student_id INT UNSIGNED NOT NULL,
        course_id INT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_mentor_student_course (student_id, course_id),
        CONSTRAINT fk_mentors_student FOREIGN KEY (student_id)
          REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_mentors_course FOREIGN KEY (course_id)
          REFERENCES courses(id) ON DELETE CASCADE ON UPDATE CASCADE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ');

    if (!$columnExists('mentors', 'student_id')) {
        $db->query('ALTER TABLE mentors ADD COLUMN student_id INT UNSIGNED NULL AFTER id');
    }
    if (!$columnExists('mentors', 'course_id')) {
        $db->query('ALTER TABLE mentors ADD COLUMN course_id INT UNSIGNED NULL AFTER student_id');
    }
    if ($columnExists('mentors', 'phone')) {
        $db->query('ALTER TABLE mentors MODIFY COLUMN phone VARCHAR(30) NULL');
    }
    if ($columnExists('mentors', 'telegram_chat_id')) {
        $db->query('ALTER TABLE mentors MODIFY COLUMN telegram_chat_id VARCHAR(80) NULL');
    }
    if ($columnExists('mentors', 'fio')) {
        $db->query('UPDATE mentors m JOIN students s ON s.fio = m.fio SET m.student_id = s.id WHERE m.student_id IS NULL');
    }

    if ($tableExists('mentor_courses')) {
        $db->query('
          UPDATE mentors m
          JOIN mentor_courses mc ON mc.mentor_id = m.id
          SET m.course_id = mc.course_id
          WHERE m.course_id IS NULL
        ');
        $db->query('
          INSERT IGNORE INTO mentors (student_id, course_id, created_at)
          SELECT m.student_id, mc.course_id, COALESCE(m.created_at, CURRENT_TIMESTAMP)
          FROM mentors m
          JOIN mentor_courses mc ON mc.mentor_id = m.id
          WHERE m.student_id IS NOT NULL AND mc.course_id IS NOT NULL
        ');
    }

    $db->query('DELETE FROM mentors WHERE student_id IS NULL OR course_id IS NULL');

    if ($indexExists('mentors', 'uq_mentor_student')) {
        $db->query('ALTER TABLE mentors DROP INDEX uq_mentor_student');
    }
    if (!$indexExists('mentors', 'uq_mentor_student_course')) {
        $db->query('ALTER TABLE mentors ADD UNIQUE KEY uq_mentor_student_course (student_id, course_id)');
    }
    if (!$fkExists('mentors', 'fk_mentors_student')) {
        $db->query('ALTER TABLE mentors ADD CONSTRAINT fk_mentors_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }
    if (!$fkExists('mentors', 'fk_mentors_course')) {
        $db->query('ALTER TABLE mentors ADD CONSTRAINT fk_mentors_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    $db->query('ALTER TABLE mentors MODIFY COLUMN student_id INT UNSIGNED NOT NULL');
    $db->query('ALTER TABLE mentors MODIFY COLUMN course_id INT UNSIGNED NOT NULL');
}

function ensure_system_schema(mysqli $db): void
{
    static $booted = false;
    if ($booted) {
        return;
    }
    $booted = true;
    mysqli_report(MYSQLI_REPORT_OFF);
    try {

    $tableExists = static function (string $table) use ($db): bool {
        $sql = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('s', $table);
        $stmt->execute();
        return (int) ($stmt->get_result()->fetch_assoc()['cnt'] ?? 0) > 0;
    };
    $columnExists = static function (string $table, string $column) use ($db): bool {
        $sql = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ss', $table, $column);
        $stmt->execute();
        return (int) ($stmt->get_result()->fetch_assoc()['cnt'] ?? 0) > 0;
    };
    $indexExists = static function (string $table, string $index) use ($db): bool {
        $sql = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ss', $table, $index);
        $stmt->execute();
        return (int) ($stmt->get_result()->fetch_assoc()['cnt'] ?? 0) > 0;
    };
    $fkExists = static function (string $table, string $fkName) use ($db): bool {
        $sql = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ss', $table, $fkName);
        $stmt->execute();
        return (int) ($stmt->get_result()->fetch_assoc()['cnt'] ?? 0) > 0;
    };

    $db->query("
      CREATE TABLE IF NOT EXISTS competitions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(180) NOT NULL,
        description TEXT DEFAULT NULL,
        registration_deadline DATE NOT NULL,
        competition_date DATE NOT NULL,
        location VARCHAR(200) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_competitions_date (competition_date)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    if (!$columnExists('competitions', 'location')) {
        $db->query('ALTER TABLE competitions ADD COLUMN location VARCHAR(200) DEFAULT NULL AFTER competition_date');
    }
    if (!$columnExists('competitions', 'created_at')) {
        $db->query('ALTER TABLE competitions ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
    }
    if (!$indexExists('competitions', 'idx_competitions_date')) {
        $db->query('ALTER TABLE competitions ADD INDEX idx_competitions_date (competition_date)');
    }

    $db->query("
      CREATE TABLE IF NOT EXISTS competition_participants (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        competition_id INT UNSIGNED NOT NULL,
        student_id INT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_competition_participant (competition_id, student_id),
        INDEX idx_competition_participants_student (student_id),
        CONSTRAINT fk_competition_participants_competition FOREIGN KEY (competition_id)
          REFERENCES competitions(id) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_competition_participants_student FOREIGN KEY (student_id)
          REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    if (!$indexExists('competition_participants', 'uq_competition_participant')) {
        $db->query('ALTER TABLE competition_participants ADD UNIQUE KEY uq_competition_participant (competition_id, student_id)');
    }
    if (!$fkExists('competition_participants', 'fk_competition_participants_competition')) {
        $db->query('ALTER TABLE competition_participants ADD CONSTRAINT fk_competition_participants_competition FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }
    if (!$fkExists('competition_participants', 'fk_competition_participants_student')) {
        $db->query('ALTER TABLE competition_participants ADD CONSTRAINT fk_competition_participants_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    $db->query("
      CREATE TABLE IF NOT EXISTS competition_result_types (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL UNIQUE,
        sort_order TINYINT UNSIGNED NOT NULL DEFAULT 1
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $db->query("
      INSERT INTO competition_result_types (code, name, sort_order) VALUES
      ('certificate', 'Sertifikat', 1),
      ('diploma', 'Diplom', 2),
      ('winner', 'Sovrindor bo''ldi', 3),
      ('cash', 'Pul miqdori', 4)
      ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        sort_order = VALUES(sort_order)
    ");

    $db->query("
      CREATE TABLE IF NOT EXISTS competition_results (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        competition_id INT UNSIGNED NOT NULL,
        student_id INT UNSIGNED NOT NULL,
        award_type_id INT UNSIGNED NOT NULL,
        cash_amount DECIMAL(12,2) DEFAULT NULL,
        position TINYINT UNSIGNED NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_competition_student_result (competition_id, student_id),
        INDEX idx_competition_results_student (student_id),
        CONSTRAINT fk_competition_results_competition FOREIGN KEY (competition_id)
          REFERENCES competitions(id) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_competition_results_student FOREIGN KEY (student_id)
          REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_competition_results_type FOREIGN KEY (award_type_id)
          REFERENCES competition_result_types(id) ON DELETE RESTRICT ON UPDATE CASCADE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    if (!$columnExists('competition_results', 'award_type_id')) {
        $db->query('ALTER TABLE competition_results ADD COLUMN award_type_id INT UNSIGNED NULL AFTER student_id');
    }
    if (!$columnExists('competition_results', 'cash_amount')) {
        $db->query('ALTER TABLE competition_results ADD COLUMN cash_amount DECIMAL(12,2) DEFAULT NULL AFTER award_type_id');
    }
    if (!$columnExists('competition_results', 'position')) {
        $db->query('ALTER TABLE competition_results ADD COLUMN position TINYINT UNSIGNED NULL AFTER student_id');
    }
    $db->query('UPDATE competition_results SET position = NULL WHERE position IS NOT NULL AND (position < 1 OR position > 5)');
    if ($columnExists('competition_results', 'result')) {
        $db->query("
          UPDATE competition_results
          SET position = CASE
            WHEN position IS NOT NULL THEN position
            WHEN TRIM(result) REGEXP '(^|[^0-9])1([^0-9]|$)' THEN 1
            WHEN TRIM(result) REGEXP '(^|[^0-9])2([^0-9]|$)' THEN 2
            WHEN TRIM(result) REGEXP '(^|[^0-9])3([^0-9]|$)' THEN 3
            ELSE NULL
          END
          WHERE position IS NULL
        ");
    }
    $db->query("
      UPDATE competition_results cr
      JOIN competition_result_types rt ON rt.code = 'winner'
      SET cr.award_type_id = rt.id
      WHERE cr.award_type_id IS NULL
    ");
    $db->query('ALTER TABLE competition_results MODIFY COLUMN award_type_id INT UNSIGNED NOT NULL');
    $db->query('ALTER TABLE competition_results MODIFY COLUMN position TINYINT UNSIGNED NULL');
    if (!$columnExists('competition_results', 'created_at')) {
        $db->query('ALTER TABLE competition_results ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
    }
    if (!$indexExists('competition_results', 'uq_competition_student_result')) {
        $db->query('ALTER TABLE competition_results ADD UNIQUE KEY uq_competition_student_result (competition_id, student_id)');
    }
    if ($indexExists('competition_results', 'uq_competition_position_result')) {
        $db->query('ALTER TABLE competition_results DROP INDEX uq_competition_position_result');
    }
    if (!$fkExists('competition_results', 'fk_competition_results_competition')) {
        $db->query('ALTER TABLE competition_results ADD CONSTRAINT fk_competition_results_competition FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }
    if (!$fkExists('competition_results', 'fk_competition_results_student')) {
        $db->query('ALTER TABLE competition_results ADD CONSTRAINT fk_competition_results_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }
    if (!$fkExists('competition_results', 'fk_competition_results_type')) {
        $db->query('ALTER TABLE competition_results ADD CONSTRAINT fk_competition_results_type FOREIGN KEY (award_type_id) REFERENCES competition_result_types(id) ON DELETE RESTRICT ON UPDATE CASCADE');
    }

    $db->query("
      CREATE TABLE IF NOT EXISTS schedule (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(180) NOT NULL,
        type ENUM('daily', 'weekly') NOT NULL,
        date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_schedule_type_date (type, date)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    if (!$columnExists('schedule', 'created_at')) {
        $db->query('ALTER TABLE schedule ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
    }
    if (!$indexExists('schedule', 'idx_schedule_type_date')) {
        $db->query('ALTER TABLE schedule ADD INDEX idx_schedule_type_date (type, date)');
    }

    $db->query("
      CREATE TABLE IF NOT EXISTS teams (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        team_name VARCHAR(150) NOT NULL,
        level ENUM('junior', 'middle', 'senior') NOT NULL DEFAULT 'middle',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    if (!$columnExists('teams', 'level')) {
        $db->query("ALTER TABLE teams ADD COLUMN level ENUM('junior', 'middle', 'senior') NOT NULL DEFAULT 'middle' AFTER team_name");
    } else {
        $db->query("ALTER TABLE teams MODIFY COLUMN level ENUM('junior', 'middle', 'senior') NOT NULL DEFAULT 'middle'");
    }

    $db->query("
      CREATE TABLE IF NOT EXISTS team_members (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        team_id INT UNSIGNED NOT NULL,
        student_id INT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_team_student (team_id, student_id),
        INDEX idx_team_members_student (student_id),
        CONSTRAINT fk_team_members_team FOREIGN KEY (team_id)
          REFERENCES teams(id) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_team_members_student FOREIGN KEY (student_id)
          REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    if (!$indexExists('team_members', 'uq_team_student')) {
        $db->query('ALTER TABLE team_members ADD UNIQUE KEY uq_team_student (team_id, student_id)');
    }
    if (!$fkExists('team_members', 'fk_team_members_team')) {
        $db->query('ALTER TABLE team_members ADD CONSTRAINT fk_team_members_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }
    if (!$fkExists('team_members', 'fk_team_members_student')) {
        $db->query('ALTER TABLE team_members ADD CONSTRAINT fk_team_members_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    $db->query("
      CREATE TABLE IF NOT EXISTS projects (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        project_name VARCHAR(180) NOT NULL,
        status ENUM('boshlanish', 'qurish', 'testlash', 'tugallash') NOT NULL DEFAULT 'boshlanish',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    if (!$columnExists('projects', 'status')) {
        $db->query("ALTER TABLE projects ADD COLUMN status ENUM('boshlanish', 'qurish', 'testlash', 'tugallash') NOT NULL DEFAULT 'boshlanish' AFTER project_name");
    } else {
        $db->query("ALTER TABLE projects MODIFY COLUMN status ENUM('boshlanish', 'qurish', 'testlash', 'tugallash') NOT NULL DEFAULT 'boshlanish'");
    }
    if (!$columnExists('projects', 'created_at')) {
        $db->query('ALTER TABLE projects ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
    }

    $db->query("
      CREATE TABLE IF NOT EXISTS project_members (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        project_id INT UNSIGNED NOT NULL,
        student_id INT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_project_student (project_id, student_id),
        INDEX idx_project_members_student (student_id),
        CONSTRAINT fk_project_members_project FOREIGN KEY (project_id)
          REFERENCES projects(id) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_project_members_student FOREIGN KEY (student_id)
          REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    if (!$indexExists('project_members', 'uq_project_student')) {
        $db->query('ALTER TABLE project_members ADD UNIQUE KEY uq_project_student (project_id, student_id)');
    }
    if (!$indexExists('project_members', 'idx_project_members_student')) {
        $db->query('ALTER TABLE project_members ADD INDEX idx_project_members_student (student_id)');
    }
    if (!$fkExists('project_members', 'fk_project_members_project')) {
        $db->query('ALTER TABLE project_members ADD CONSTRAINT fk_project_members_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }
    if (!$fkExists('project_members', 'fk_project_members_student')) {
        $db->query('ALTER TABLE project_members ADD CONSTRAINT fk_project_members_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    if (!$columnExists('course_students', 'status')) {
        $db->query("ALTER TABLE course_students ADD COLUMN status ENUM('active', 'completed') NOT NULL DEFAULT 'active' AFTER room_id");
    } else {
        $db->query("ALTER TABLE course_students MODIFY COLUMN status ENUM('active', 'completed') NOT NULL DEFAULT 'active'");
    }

    if (!$indexExists('student_status', 'idx_student_status_student')) {
        $db->query('ALTER TABLE student_status ADD INDEX idx_student_status_student (student_id)');
    }
    if (!$indexExists('student_status', 'idx_student_status_status')) {
        $db->query('ALTER TABLE student_status ADD INDEX idx_student_status_status (status_id)');
    }
    if (!$indexExists('course_students', 'idx_course_students_student')) {
        $db->query('ALTER TABLE course_students ADD INDEX idx_course_students_student (student_id)');
    }
    if (!$indexExists('course_students', 'idx_course_students_course')) {
        $db->query('ALTER TABLE course_students ADD INDEX idx_course_students_course (course_id)');
    }
    if (!$indexExists('competition_participants', 'idx_competition_participants_competition')) {
        $db->query('ALTER TABLE competition_participants ADD INDEX idx_competition_participants_competition (competition_id)');
    }
    if (!$indexExists('competition_results', 'idx_competition_results_competition')) {
        $db->query('ALTER TABLE competition_results ADD INDEX idx_competition_results_competition (competition_id)');
    }
    $db->query("INSERT INTO statuses (name) VALUES ('Rezident'), ('Kurs o''quvchi'), ('Talaba') ON DUPLICATE KEY UPDATE name = VALUES(name)");
    } finally {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }
}

function json_response(bool $success, string $message, array $data = []): void
{
    $normalize = static function ($value) use (&$normalize) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $normalize($v);
            }
            return $value;
        }
        if (is_string($value)) {
            return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        return $value;
    };

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => html_entity_decode($message, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'data' => $normalize($data),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
