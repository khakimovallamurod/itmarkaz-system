CREATE DATABASE IF NOT EXISTS itmarkazdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE itmarkazdb;

CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- default admin password: admin123 (stored as md5)
INSERT INTO admins (username, password_hash)
VALUES ('admin', '0192023a7bbd73250516f069df18b500')
ON DUPLICATE KEY UPDATE username = username;

CREATE TABLE IF NOT EXISTS directions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS statuses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS students (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  fio VARCHAR(150) NOT NULL,
  yonalish_id INT UNSIGNED NOT NULL,
  guruh VARCHAR(80) NOT NULL,
  kirgan_yili YEAR NOT NULL,
  telefon VARCHAR(30) NOT NULL,
  telegram_chat_id VARCHAR(80) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_students_fio (fio),
  INDEX idx_students_yonalish_id (yonalish_id),
  CONSTRAINT fk_students_direction FOREIGN KEY (yonalish_id)
    REFERENCES directions(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_status (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  status_id INT UNSIGNED NOT NULL,
  UNIQUE KEY uq_student_status (student_id, status_id),
  INDEX idx_student_status_student (student_id),
  INDEX idx_student_status_status (status_id),
  CONSTRAINT fk_student_status_student FOREIGN KEY (student_id)
    REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_student_status_status FOREIGN KEY (status_id)
    REFERENCES statuses(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rooms (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  room_number VARCHAR(30) NOT NULL UNIQUE,
  capacity INT UNSIGNED NOT NULL DEFAULT 0,
  computers_count INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS residents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL UNIQUE,
  room_id INT UNSIGNED DEFAULT NULL,
  computer_number VARCHAR(20) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_residents_student FOREIGN KEY (student_id)
    REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_residents_room FOREIGN KEY (room_id)
    REFERENCES rooms(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS courses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(140) NOT NULL,
  description TEXT,
  days JSON NOT NULL,
  time VARCHAR(20) NOT NULL,
  duration VARCHAR(60) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS course_students (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NOT NULL,
  room_id INT UNSIGNED DEFAULT NULL,
  status ENUM('active', 'completed') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_course_student (student_id, course_id),
  INDEX idx_course_students_student (student_id),
  INDEX idx_course_students_course (course_id),
  CONSTRAINT fk_course_students_student FOREIGN KEY (student_id)
    REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_course_students_course FOREIGN KEY (course_id)
    REFERENCES courses(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_course_students_room FOREIGN KEY (room_id)
    REFERENCES rooms(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS competitions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  description TEXT DEFAULT NULL,
  registration_deadline DATE NOT NULL,
  competition_date DATE NOT NULL,
  location VARCHAR(200) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_competitions_date (competition_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS competition_participants (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  competition_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_competition_participant (competition_id, student_id),
  INDEX idx_competition_participants_competition (competition_id),
  INDEX idx_competition_participants_student (student_id),
  CONSTRAINT fk_competition_participants_competition FOREIGN KEY (competition_id)
    REFERENCES competitions(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_competition_participants_student FOREIGN KEY (student_id)
    REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS competition_result_types (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL UNIQUE,
  sort_order TINYINT UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS competition_results (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  competition_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  award_type_id INT UNSIGNED NOT NULL,
  cash_amount DECIMAL(12,2) DEFAULT NULL,
  position TINYINT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_competition_student_result (competition_id, student_id),
  INDEX idx_competition_results_competition (competition_id),
  INDEX idx_competition_results_student (student_id),
  CONSTRAINT fk_competition_results_competition FOREIGN KEY (competition_id)
    REFERENCES competitions(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_competition_results_student FOREIGN KEY (student_id)
    REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_competition_results_type FOREIGN KEY (award_type_id)
    REFERENCES competition_result_types(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS schedule (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  type ENUM('daily', 'weekly') NOT NULL,
  date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_schedule_type_date (type, date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS task_schedule (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  deadline DATE NOT NULL,
  file_path VARCHAR(255) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  target_groups JSON DEFAULT NULL,
  student_ids JSON DEFAULT NULL,
  course_student_ids JSON DEFAULT NULL,
  mentor_ids JSON DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_task_schedule_deadline (deadline)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teams (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  team_name VARCHAR(150) NOT NULL,
  level ENUM('junior', 'middle', 'senior') NOT NULL DEFAULT 'middle',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_name VARCHAR(180) NOT NULL,
  status ENUM('boshlanish', 'qurish', 'testlash', 'tugallash') NOT NULL DEFAULT 'boshlanish',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS week_days (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(10) NOT NULL UNIQUE,
  name VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO directions (name) VALUES ('Frontend'), ('Backend'), ('Mobile')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO statuses (name) VALUES ('Rezident'), ('Kurs o\'quvchi'), ('Talaba')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO competition_result_types (code, name, sort_order) VALUES
('certificate', 'Sertifikat', 1),
('diploma', 'Diplom', 2),
('winner', 'Sovrindor bo\'ldi', 3),
('cash', 'Pul miqdori', 4)
ON DUPLICATE KEY UPDATE name = VALUES(name), sort_order = VALUES(sort_order);

INSERT INTO week_days (code, name) VALUES
('Mon', 'Dushanba'),
('Tue', 'Seshanba'),
('Wed', 'Chorshanba'),
('Thu', 'Payshanba'),
('Fri', 'Juma'),
('Sat', 'Shanba'),
('Sun', 'Yakshanba')
ON DUPLICATE KEY UPDATE name = VALUES(name);
