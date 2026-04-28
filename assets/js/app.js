const spinner = document.getElementById('loadingSpinner');
const SIDEBAR_COLLAPSED_KEY = 'itmarkaz_sidebar_collapsed';

let studentPage = 1;
let directionsCache = [];
let statusesCache = [];
let roomsCache = [];
let coursesCache = [];
let weekDaysCache = [];
let residentStudentsCache = [];
let studentOptionsCache = [];
let dashboardChartInstance = null;
let analyticsChartInstance = null;

let scheduleFilterType = '';
let scheduleViewMode = 'list';
let notifyStudentSelectorManager = null;
let teamStudentSelectorManager = null;

function qs(id) {
  return document.getElementById(id);
}

function parseJsonArray(value) {
  if (Array.isArray(value)) return value;
  try {
    return JSON.parse(value || '[]');
  } catch {
    return [];
  }
}

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function parseDataItem(item) {
  try {
    return JSON.parse(item || '{}');
  } catch {
    return null;
  }
}

function formatDate(value) {
  if (!value) return '-';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return escapeHtml(value);
  return date.toLocaleDateString('uz-UZ', { year: 'numeric', month: '2-digit', day: '2-digit' });
}

function truncateText(value, max = 100) {
  const text = String(value ?? '').trim();
  if (!text) return '';
  if (text.length <= max) return text;
  return `${text.slice(0, max).trim()}...`;
}

const LOADING_SHOW_DELAY_MS = 140;
const LOADING_MIN_VISIBLE_MS = 220;
let loadingRequestCount = 0;
let loadingShowTimer = null;
let loadingVisibleSince = 0;

function setLoadingVisible(show) {
  if (!spinner) return;
  spinner.classList.toggle('hidden', !show);
  spinner.classList.toggle('flex', show);
  if (show) {
    loadingVisibleSince = Date.now();
  }
}

function startLoading() {
  loadingRequestCount += 1;
  if (loadingRequestCount > 1) return;

  if (loadingShowTimer) {
    clearTimeout(loadingShowTimer);
  }
  loadingShowTimer = setTimeout(() => {
    setLoadingVisible(true);
    loadingShowTimer = null;
  }, LOADING_SHOW_DELAY_MS);
}

function stopLoading() {
  loadingRequestCount = Math.max(0, loadingRequestCount - 1);
  if (loadingRequestCount > 0) return;

  if (loadingShowTimer) {
    clearTimeout(loadingShowTimer);
    loadingShowTimer = null;
    return;
  }

  const elapsed = Date.now() - loadingVisibleSince;
  const waitMore = Math.max(0, LOADING_MIN_VISIBLE_MS - elapsed);
  setTimeout(() => {
    if (loadingRequestCount === 0) {
      setLoadingVisible(false);
    }
  }, waitMore);
}

async function apiFetch(url, options = {}) {
  startLoading();
  try {
    const res = await fetch(url, options);
    const text = await res.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch (_) {
      data = { success: false, message: `Server JSON javobi xato (${res.status})`, data: [] };
    }
    if (!res.ok) {
      return { success: false, message: data.message || `Server xatolik: ${res.status}`, data: data.data || [] };
    }
    return data;
  } finally {
    stopLoading();
  }
}

function toast(icon, title) {
  Swal.fire({ toast: true, position: 'top-end', timer: 2000, showConfirmButton: false, icon, title });
}

function confirmAction(text = "Rostdan ham o'chirasizmi?") {
  return Swal.fire({
    title: 'Tasdiqlang',
    text,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ha',
    cancelButtonText: "Yo'q",
  });
}

function setupSidebar() {
  const sidebar = qs('sidebar');
  const overlay = qs('mobileSidebarOverlay');
  const mainShell = qs('mainShell');
  if (!sidebar || !mainShell) return;

  const settingsToggle = qs('settingsMenuToggle');
  const settingsSubmenu = qs('settingsSubmenu');
  const settingsChevron = qs('settingsMenuChevron');

  const setSettingsMenu = (isOpen) => {
    if (!settingsSubmenu || !settingsToggle) return;
    settingsSubmenu.classList.toggle('hidden', !isOpen);
    settingsChevron?.classList.toggle('rotate-180', isOpen);
    settingsToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  };

  if (settingsToggle && settingsSubmenu) {
    settingsToggle.addEventListener('click', () => {
      const isOpen = settingsToggle.getAttribute('aria-expanded') === 'true';
      setSettingsMenu(!isOpen);
    });
  }

  const setCollapsed = (isCollapsed) => {
    sidebar.classList.toggle('md:w-24', isCollapsed);
    sidebar.classList.toggle('md:w-72', !isCollapsed);
    mainShell.classList.toggle('md:pl-24', isCollapsed);
    mainShell.classList.toggle('md:pl-72', !isCollapsed);
    document.querySelectorAll('.sidebar-label').forEach((el) => el.classList.toggle('hidden', isCollapsed));
    localStorage.setItem(SIDEBAR_COLLAPSED_KEY, isCollapsed ? '1' : '0');
  };

  setCollapsed(localStorage.getItem(SIDEBAR_COLLAPSED_KEY) === '1');
  qs('sidebarCollapseBtn')?.addEventListener('click', () => setCollapsed(!sidebar.classList.contains('md:w-24')));
  qs('sidebarToggle')?.addEventListener('click', () => {
    sidebar.classList.toggle('-translate-x-full');
    overlay?.classList.toggle('hidden');
  });
  overlay?.addEventListener('click', () => {
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
  });
}

function setupProfileDropdown() {
  const btn = qs('profileMenuBtn');
  const dropdown = qs('profileDropdown');
  if (!btn || !dropdown) return;

  let hideTimer = null;
  const closeDropdown = () => {
    dropdown.classList.remove('opacity-100', 'translate-y-0');
    dropdown.classList.add('opacity-0', 'translate-y-1', 'pointer-events-none');
    hideTimer = setTimeout(() => dropdown.classList.add('hidden'), 180);
  };
  const openDropdown = () => {
    if (hideTimer) clearTimeout(hideTimer);
    dropdown.classList.remove('hidden');
    requestAnimationFrame(() => {
      dropdown.classList.remove('opacity-0', 'translate-y-1', 'pointer-events-none');
      dropdown.classList.add('opacity-100', 'translate-y-0');
    });
  };

  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = !dropdown.classList.contains('hidden') && dropdown.classList.contains('opacity-100');
    if (isOpen) {
      closeDropdown();
    } else {
      openDropdown();
    }
  });

  document.addEventListener('click', (e) => {
    if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
      closeDropdown();
    }
  });
}

function setupGlobalSearch() {
  const input = qs('globalSearch');
  const results = qs('globalSearchResults');
  if (!input || !results) return;

  let timer;
  input.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(async () => {
      const q = input.value.trim();
      if (q.length < 2) {
        results.classList.add('hidden');
        return;
      }
      const res = await apiFetch(`../get/global_search.php?q=${encodeURIComponent(q)}`);
      if (!res.success) return;
      const list = res.data || [];
      results.innerHTML = !list.length
        ? '<p class="px-3 py-2 text-sm text-slate-500">Topilmadi</p>'
        : list.slice(0, 8).map((item) => `
          <div class="px-3 py-2 rounded hover:bg-slate-100">
            <p class="text-sm font-medium">${escapeHtml(item.title || item.fio || 'Natija')}</p>
            <p class="text-xs text-slate-500">${escapeHtml(item.type || '')}</p>
          </div>
        `).join('');
      results.classList.remove('hidden');
    }, 250);
  });
}

function setupCalendar() {
  const calendarBtn = qs('calendarBtn');
  const calendarPopover = qs('calendarPopover');
  if (!calendarBtn || !calendarPopover) return;
  if (typeof flatpickr !== 'undefined') {
    flatpickr('#calendarInput', { inline: true });
  }
  calendarBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    calendarPopover.classList.toggle('hidden');
  });
  document.addEventListener('click', (e) => {
    if (!calendarPopover.contains(e.target) && !calendarBtn.contains(e.target)) {
      calendarPopover.classList.add('hidden');
    }
  });
}

function applyUiEnhancements() {
  document.querySelectorAll('button').forEach((btn) => btn.classList.add('transition-all', 'duration-200'));
}

async function loadDirections() {
  const res = await apiFetch('../get/directions.php');
  if (res.success) directionsCache = res.data.items || [];
}

async function loadStatuses() {
  const res = await apiFetch('../get/statuses.php');
  if (res.success) statusesCache = res.data.items || [];
}

async function loadRoomsCache() {
  const res = await apiFetch('../get/rooms.php');
  if (res.success) roomsCache = res.data.items || [];
}

async function loadCoursesCache() {
  const res = await apiFetch('../get/courses.php');
  if (res.success) coursesCache = res.data.items || [];
}

async function loadResidentStudentsCache() {
  const res = await apiFetch('../get/resident_students.php');
  if (res.success) residentStudentsCache = res.data.items || [];
}

async function loadWeekDays() {
  const res = await apiFetch('../get/week_days.php');
  if (res.success) weekDaysCache = res.data.items || [];
}

async function loadStudentOptions() {
  const res = await apiFetch('../get/student_options.php');
  if (res.success) studentOptionsCache = res.data.items || [];
}

function canUseSelect2() {
  return typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn?.select2 === 'function';
}

function initSelect2ForElement(selectEl, placeholder = 'Talaba tanlang', modalId = '') {
  if (!selectEl || !canUseSelect2()) return;
  const $ = window.jQuery;
  const $select = $(selectEl);
  if ($select.data('select2')) {
    $select.select2('destroy');
  }
  const options = {
    width: '100%',
    placeholder,
    allowClear: true,
  };
  if (modalId && qs(modalId)) {
    options.dropdownParent = $(qs(modalId));
  }
  $select.select2(options);
}

function buildStudentOptionsHtml(withEmpty = true) {
  const empty = withEmpty ? '<option value=""></option>' : '';
  return empty + studentOptionsCache.map((s) => `<option value="${s.id}">${escapeHtml(s.fio)}</option>`).join('');
}

function createDynamicStudentSelectorManager({
  containerId,
  addButtonId,
  modalId,
  inputName = 'student_ids[]',
  minRows = 1,
  placeholder = 'Talaba tanlang',
}) {
  const container = qs(containerId);
  const addBtn = qs(addButtonId);
  if (!container) return null;

  let rowCounter = 0;

  const getRows = () => Array.from(container.querySelectorAll('[data-student-row]'));
  const getSelects = () => getRows().map((row) => row.querySelector('select')).filter(Boolean);

  const getSelectedValues = () => getSelects()
    .map((select) => String(select.value || '').trim())
    .filter((value) => value !== '');

  const hasDuplicate = () => {
    const values = getSelectedValues();
    return new Set(values).size !== values.length;
  };

  const refreshRemoveButtons = () => {
    const rows = getRows();
    rows.forEach((row) => {
      const removeBtn = row.querySelector('[data-remove-student-row]');
      if (!removeBtn) return;
      removeBtn.classList.toggle('hidden', rows.length <= minRows);
    });
  };

  const syncDisabledOptions = () => {
    const selected = getSelectedValues();
    getSelects().forEach((select) => {
      const current = String(select.value || '');
      Array.from(select.options).forEach((option) => {
        if (!option.value) return;
        option.disabled = selected.includes(option.value) && option.value !== current;
      });
      if (canUseSelect2()) {
        window.jQuery(select).trigger('change.select2');
      }
    });
  };

  const attachRowEvents = (row) => {
    const select = row.querySelector('select');
    const removeBtn = row.querySelector('[data-remove-student-row]');
    if (select) {
      select.addEventListener('change', () => syncDisabledOptions());
      initSelect2ForElement(select, placeholder, modalId);
    }
    removeBtn?.addEventListener('click', () => {
      const rows = getRows();
      if (rows.length <= minRows) {
        if (select) {
          select.value = '';
          if (canUseSelect2()) window.jQuery(select).trigger('change');
        }
      } else {
        if (select && canUseSelect2() && window.jQuery(select).data('select2')) {
          window.jQuery(select).select2('destroy');
        }
        row.remove();
      }
      refreshRemoveButtons();
      syncDisabledOptions();
    });
  };

  const addRow = (selectedValue = '') => {
    rowCounter += 1;
    const row = document.createElement('div');
    row.setAttribute('data-student-row', '1');
    row.className = 'flex items-center gap-2';
    row.innerHTML = `
      <select name="${inputName}" class="form-input flex-1" data-student-select required>
        ${buildStudentOptionsHtml(true)}
      </select>
      <button
        type="button"
        data-remove-student-row
        class="h-10 w-10 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 flex items-center justify-center"
        title="Qatorni o'chirish">×</button>
    `;
    container.appendChild(row);
    const select = row.querySelector('select');
    if (select) {
      select.value = selectedValue ? String(selectedValue) : '';
    }
    attachRowEvents(row);
    refreshRemoveButtons();
    syncDisabledOptions();
  };

  const reset = (values = []) => {
    getSelects().forEach((select) => {
      if (canUseSelect2() && window.jQuery(select).data('select2')) {
        window.jQuery(select).select2('destroy');
      }
    });
    container.innerHTML = '';
    if (!values.length) {
      addRow('');
    } else {
      values.forEach((value) => addRow(value));
    }
    refreshRemoveButtons();
    syncDisabledOptions();
  };

  addBtn?.addEventListener('click', () => addRow(''));

  return {
    reset,
    addRow,
    getSelectedValues,
    hasDuplicate,
    syncDisabledOptions,
  };
}

function fillDirectionSelect() {
  const select = qs('studentDirectionSelect');
  if (!select) return;
  select.innerHTML = '<option value="">Yo\'nalish tanlang</option>' + directionsCache.map((d) => `<option value="${d.id}">${escapeHtml(d.name)}</option>`).join('');
}

function fillStudentDirectionFilter() {
  const select = qs('studentDirectionFilter');
  if (!select) return;
  const selectedValue = select.value;
  select.innerHTML = '<option value="">Barcha yo\'nalishlar</option>' + directionsCache.map((d) => `<option value="${d.id}">${escapeHtml(d.name)}</option>`).join('');
  select.value = selectedValue;
}

function calculateCourseFromEntryYear(entryYear) {
  const year = Number(entryYear);
  if (!Number.isInteger(year) || year < 1900) return '';
  const currentYear = new Date().getFullYear();
  const elapsedYears = currentYear - year;
  if (elapsedYears < 0) return 'Hali boshlanmagan';
  if (elapsedYears > 4) return 'Bitirgan';
  const course = elapsedYears === 0 ? 1 : elapsedYears;
  return `${course}-kurs`;
}

function updateStudentAutoCourse() {
  const yearInput = qs('studentEntryYear');
  const courseInput = qs('studentAutoCourse');
  if (!yearInput || !courseInput) return;
  courseInput.value = calculateCourseFromEntryYear(yearInput.value) || '-';
}

function fillStatusCheckboxes(selected = []) {
  const wrap = qs('studentStatusCheckboxes');
  if (!wrap) return;
  const selectedSet = new Set(selected.map((id) => Number(id)));
  wrap.innerHTML = statusesCache.map((s) => `
    <label class="flex items-center gap-2 cursor-pointer select-none rounded-md px-2 py-1.5 hover:bg-white">
      <input
        type="checkbox"
        name="status[]"
        value="${s.id}"
        class="h-4 w-4 accent-green-600"
        ${selectedSet.has(Number(s.id)) ? 'checked' : ''}>
      <span class="text-sm text-slate-700">${escapeHtml(s.name)}</span>
    </label>
  `).join('');
}

function formatUzPhoneInput(value) {
  const digitsRaw = String(value ?? '').replace(/\D/g, '');
  let digits = digitsRaw;
  if (!digits.startsWith('998')) {
    digits = `998${digits}`;
  }
  digits = digits.slice(0, 12);
  const local = digits.slice(3);
  let formatted = '+998';
  if (local.length > 0) formatted += ` ${local.slice(0, 2)}`;
  if (local.length > 2) formatted += ` ${local.slice(2, 5)}`;
  if (local.length > 5) formatted += ` ${local.slice(5, 7)}`;
  if (local.length > 7) formatted += ` ${local.slice(7, 9)}`;
  return formatted.trim();
}

function setupPhoneInputs() {
  document.querySelectorAll('input[data-phone-input]').forEach((input) => {
    if (!input.value || !input.value.trim()) input.value = '+998';
    input.addEventListener('focus', () => {
      if (!input.value.trim()) input.value = '+998';
      if (input.value.trim() === '+998') input.setSelectionRange(input.value.length, input.value.length);
    });
    input.addEventListener('input', () => {
      input.value = formatUzPhoneInput(input.value);
    });
  });
}

function renderDashboardChart(distribution) {
  const canvas = qs('dashboardChart');
  if (!canvas || typeof Chart === 'undefined') return;

  if (dashboardChartInstance) {
    dashboardChartInstance.destroy();
  }

  dashboardChartInstance = new Chart(canvas, {
    type: 'bar',
    data: {
      labels: ["1-o'rin", "2-o'rin", "3-o'rin"],
      datasets: [{
        label: 'Natijalar soni',
        data: [distribution.first || 0, distribution.second || 0, distribution.third || 0],
        backgroundColor: ['#047857', '#0ea5e9', '#f59e0b'],
        borderRadius: 8,
      }],
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
    },
  });
}

function renderAnalyticsChart(distribution) {
  const canvas = qs('analyticsChart');
  if (!canvas || typeof Chart === 'undefined') return;

  if (analyticsChartInstance) {
    analyticsChartInstance.destroy();
  }

  analyticsChartInstance = new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels: ["1-o'rin", "2-o'rin", "3-o'rin"],
      datasets: [{
        data: [distribution.first || 0, distribution.second || 0, distribution.third || 0],
        backgroundColor: ['#16a34a', '#0284c7', '#f59e0b'],
      }],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom' },
      },
    },
  });
}

function renderRecentActivity(items = []) {
  const list = qs('recentActivity');
  if (!list) return;
  list.innerHTML = items.length ? items.map((item) => `
    <li class="p-3 rounded-lg bg-slate-50">
      <p class="text-sm text-slate-700">${escapeHtml(item.title || '')}</p>
      <p class="text-xs text-slate-500 mt-1">${formatDate(item.created_at)}</p>
    </li>
  `).join('') : '<li class="p-3 rounded-lg bg-slate-50 text-slate-500">Faollik topilmadi</li>';
}

function renderUpcomingCompetitions(items = []) {
  const wrap = qs('upcomingCompetitions');
  if (!wrap) return;
  wrap.innerHTML = items.length ? items.map((item) => `
    <article class="rounded-xl border border-slate-200 p-3 bg-slate-50">
      <p class="font-medium text-slate-900">${escapeHtml(item.name)}</p>
      <p class="text-sm text-slate-600 mt-1">${formatDate(item.competition_date)}</p>
      <p class="text-xs text-slate-500 mt-1">${escapeHtml(item.location || 'Manzil kiritilmagan')}</p>
    </article>
  `).join('') : '<p class="text-sm text-slate-500">Yaqin tanlovlar mavjud emas.</p>';
}

function renderAnalyticsWinners(items = []) {
  const wrap = qs('winnerList');
  if (!wrap) return;
  wrap.innerHTML = items.length ? items.map((item, idx) => `
    <div class="rounded-lg border border-slate-200 px-3 py-2 flex items-center justify-between">
      <span class="text-sm text-slate-700">${idx + 1}. ${escapeHtml(item.fio)}</span>
      <span class="text-xs px-2 py-1 rounded bg-emerald-100 text-emerald-700">${escapeHtml(item.wins)} g'alaba</span>
    </div>
  `).join('') : '<p class="text-sm text-slate-500">Hozircha g\'oliblar yo\'q.</p>';
}

function renderAnalyticsTopStudents(items = []) {
  const wrap = qs('topStudentsList');
  if (!wrap) return;
  wrap.innerHTML = items.length ? items.map((item) => `
    <article class="rounded-xl border border-slate-200 p-3 bg-slate-50">
      <p class="font-medium text-slate-900">${escapeHtml(item.fio)}</p>
      <p class="text-xs text-slate-600 mt-1">Ball: ${escapeHtml(item.points)} | Natijalar: ${escapeHtml(item.total_results)}</p>
    </article>
  `).join('') : '<p class="text-sm text-slate-500">Top talabalar topilmadi.</p>';
}

async function loadStats() {
  const res = await apiFetch('../get/stats.php');
  if (!res.success) return;

  const data = res.data || {};

  qs('statStudents') && (qs('statStudents').textContent = data.students || 0);
  qs('statResidents') && (qs('statResidents').textContent = data.residents || 0);
  qs('statCourseStudents') && (qs('statCourseStudents').textContent = data.course_students || 0);
  qs('statMentors') && (qs('statMentors').textContent = data.mentors || 0);
  qs('statCompetitions') && (qs('statCompetitions').textContent = data.competitions || 0);
  qs('statSchedules') && (qs('statSchedules').textContent = data.schedule || 0);

  qs('analyticsStudents') && (qs('analyticsStudents').textContent = data.students || 0);
  qs('analyticsResidents') && (qs('analyticsResidents').textContent = data.residents || 0);
  qs('analyticsCourseStudents') && (qs('analyticsCourseStudents').textContent = data.course_students || 0);
  qs('analyticsMentors') && (qs('analyticsMentors').textContent = data.mentors || 0);
  qs('analyticsCompetitions') && (qs('analyticsCompetitions').textContent = data.competitions || 0);

  renderDashboardChart(data.result_distribution || {});
  renderRecentActivity(data.recent_activity || []);
  renderUpcomingCompetitions(data.upcoming_competitions || []);

  renderAnalyticsChart(data.result_distribution || {});
  renderAnalyticsWinners(data.winners || []);
  renderAnalyticsTopStudents(data.top_students || []);
}

async function loadStudents(page = 1) {
  studentPage = page;
  const search = qs('studentSearch')?.value || '';
  const directionId = qs('studentDirectionFilter')?.value || '';
  const res = await apiFetch(`../get/students.php?page=${page}&search=${encodeURIComponent(search)}&direction_id=${encodeURIComponent(directionId)}`);
  if (!res.success || !qs('studentsTableBody')) return;

  qs('studentsTableBody').innerHTML = (res.data.items || []).map((item) => {
    const statuses = parseJsonArray(item.statuses).join(', ');
    const courseByYear = calculateCourseFromEntryYear(item.kirgan_yili) || '-';
    const itemJson = escapeHtml(JSON.stringify(item));
    return `<tr>
      <td>${escapeHtml(item.fio)}</td>
      <td>${escapeHtml(item.yonalish)}</td>
      <td>${escapeHtml(item.guruh)}</td>
      <td>${escapeHtml(courseByYear)}</td>
      <td>${escapeHtml(item.telefon)}</td>
      <td>${escapeHtml(statuses)}</td>
      <td>
        <div class="table-actions">
          <button type="button" class="js-student-edit px-2 py-1 text-xs border rounded" data-item="${itemJson}">Edit</button>
          <button type="button" class="js-student-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="${item.id}">Delete</button>
        </div>
      </td>
    </tr>`;
  }).join('');

  const p = res.data.pagination || { page: 1, pages: 1 };
  qs('studentsPagination').innerHTML = Array.from({ length: p.pages || 1 }, (_, i) => i + 1)
    .map((n) => `<button class="px-3 py-1 border rounded ${n === p.page ? 'bg-green-600 text-white' : ''}" onclick="loadStudents(${n})">${n}</button>`)
    .join('');
}

function openStudentModal() {
  qs('studentModal').classList.remove('hidden');
  qs('studentModal').classList.add('flex');
  qs('studentForm').reset();
  qs('studentId').value = '';
  fillDirectionSelect();
  fillStatusCheckboxes([]);
  const phoneInput = qs('studentForm')?.querySelector('input[name="telefon"]');
  if (phoneInput) phoneInput.value = '+998';
  updateStudentAutoCourse();
}

function closeStudentModal() {
  qs('studentModal').classList.add('hidden');
  qs('studentModal').classList.remove('flex');
}

function editStudent(item) {
  openStudentModal();
  const form = qs('studentForm');
  qs('studentId').value = item.id;
  form.fio.value = item.fio;
  form.yonalish_id.value = item.yonalish_id;
  form.guruh.value = item.guruh;
  form.kirgan_yili.value = item.kirgan_yili;
  form.telefon.value = item.telefon;
  form.telegram_chat_id.value = item.telegram_chat_id || '';
  updateStudentAutoCourse();

  const selectedIds = parseJsonArray(item.status_ids)
    .map((value) => parseInt(value, 10))
    .filter((value) => !Number.isNaN(value));
  fillStatusCheckboxes(selectedIds);
}

async function deleteStudent(id) {
  const ok = await confirmAction();
  if (!ok.isConfirmed) return;
  const fd = new FormData();
  fd.append('id', id);
  const res = await apiFetch('../delete/student.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) loadStudents(studentPage);
}

async function loadResidents() {
  await loadRoomsCache();
  const search = qs('residentSearch')?.value || '';
  const status = qs('residentStatusFilter')?.value || '';
  const res = await apiFetch(`../get/residents.php?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`);
  if (!res.success || !qs('residentsTableBody')) return;

  qs('residentsTableBody').innerHTML = (res.data.items || []).map((r) => `<tr>
    <td>${escapeHtml(r.fio)}</td>
    <td>${escapeHtml(r.room_number || '-')}</td>
    <td>${escapeHtml(r.computer_number || '-')}</td>
    <td>
      <div class="table-actions">
        <button type="button" class="js-resident-open px-2 py-1 text-xs border rounded" data-item="${escapeHtml(JSON.stringify(r))}">Xona berish</button>
        ${r.id ? `<button type="button" class="js-resident-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="${r.id}">Delete</button>` : ''}
      </div>
    </td>
  </tr>`).join('');
}

function openResidentModal(item) {
  qs('residentModal').classList.remove('hidden');
  qs('residentModal').classList.add('flex');
  qs('residentStudentId').value = item.student_id;
  qs('residentStudentName').value = item.fio;
  qs('residentRoomSelect').innerHTML = '<option value="">Xona tanlang</option>' + roomsCache.map((r) => `<option value="${r.id}">${escapeHtml(r.room_number)}</option>`).join('');
  qs('residentRoomSelect').value = item.room_id || '';
  qs('residentForm').computer_number.value = item.computer_number || '';
}

function closeResidentModal() {
  qs('residentModal').classList.add('hidden');
  qs('residentModal').classList.remove('flex');
}

async function deleteResident(id) {
  const ok = await confirmAction();
  if (!ok.isConfirmed) return;
  const fd = new FormData();
  fd.append('id', id);
  const res = await apiFetch('../delete/resident.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) loadResidents();
}

function courseStatusBadge(status) {
  if (status === 'completed') {
    return '<span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-700">Tugatgan</span>';
  }
  return '<span class="px-2 py-1 rounded text-xs bg-emerald-100 text-emerald-700">Kursda</span>';
}

async function loadCourseStudents() {
  await Promise.all([loadCoursesCache(), loadRoomsCache()]);
  const search = qs('courseStudentSearch')?.value || '';
  const status = qs('courseStudentStatusFilter')?.value || '';
  const res = await apiFetch(`../get/course_students.php?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`);
  if (!res.success || !qs('courseStudentsTableBody')) return;

  qs('courseStudentsTableBody').innerHTML = (res.data.items || []).map((row) => {
    const rowStatus = row.status || 'active';
    const nextStatus = rowStatus === 'active' ? 'completed' : 'active';
    const toggleLabel = rowStatus === 'active' ? 'Tugatdi' : 'Faol qil';
    return `<tr>
      <td>${escapeHtml(row.fio)}</td>
      <td>${escapeHtml(row.course_name || '-')}</td>
      <td>${escapeHtml(row.room_number || '-')}</td>
      <td>${courseStatusBadge(rowStatus)}</td>
      <td>
        <div class="table-actions">
          <button type="button" class="js-course-student-open px-2 py-1 text-xs border rounded" data-item="${escapeHtml(JSON.stringify(row))}">Kurs biriktirish</button>
          ${row.id ? `<button type="button" class="js-course-status-toggle px-2 py-1 text-xs bg-indigo-600 text-white rounded" data-id="${row.id}" data-status="${nextStatus}">${toggleLabel}</button>` : ''}
          ${row.id ? `<button type="button" class="js-course-student-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="${row.id}">Delete</button>` : ''}
        </div>
      </td>
    </tr>`;
  }).join('');
}

async function updateCourseStudentStatus(id, status) {
  const fd = new FormData();
  fd.append('id', id);
  fd.append('status', status);
  const res = await apiFetch('../update/course_student_status.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) loadCourseStudents();
}

function openCourseStudentModal(item) {
  qs('courseStudentModal').classList.remove('hidden');
  qs('courseStudentModal').classList.add('flex');
  qs('courseStudentId').value = item.student_id;
  qs('courseStudentName').value = item.fio;
  qs('courseSelect').innerHTML = '<option value="">Kurs tanlang</option>' + coursesCache.map((c) => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
  qs('courseSelect').value = item.course_id || '';
  qs('courseRoomSelect').innerHTML = '<option value="">Xona tanlang</option>' + roomsCache.map((r) => `<option value="${r.id}">${escapeHtml(r.room_number)}</option>`).join('');
  qs('courseRoomSelect').value = item.room_id || '';
}

function closeCourseStudentModal() {
  qs('courseStudentModal').classList.add('hidden');
  qs('courseStudentModal').classList.remove('flex');
}

async function deleteCourseStudent(id) {
  const ok = await confirmAction();
  if (!ok.isConfirmed) return;
  const fd = new FormData();
  fd.append('id', id);
  const res = await apiFetch('../delete/course_student.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) loadCourseStudents();
}

async function loadRooms() {
  const res = await apiFetch('../get/rooms.php');
  if (!res.success || !qs('roomsTableBody')) return;

  qs('roomsTableBody').innerHTML = (res.data.items || []).map((r) => `<tr>
    <td>${escapeHtml(r.room_number)}</td>
    <td>${escapeHtml(r.capacity)}</td>
    <td>${escapeHtml(r.computers_count)}</td>
    <td>
      <div class="table-actions">
        <button type="button" class="js-room-edit px-2 py-1 text-xs border rounded" data-item="${escapeHtml(JSON.stringify(r))}">Edit</button>
        <button type="button" class="js-room-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="${r.id}">Delete</button>
      </div>
    </td>
  </tr>`).join('');
}

function openRoomModal() {
  qs('roomModal').classList.remove('hidden');
  qs('roomModal').classList.add('flex');
  qs('roomForm').reset();
}

function closeRoomModal() {
  qs('roomModal').classList.add('hidden');
  qs('roomModal').classList.remove('flex');
}

function fillRoom(room) {
  openRoomModal();
  const f = qs('roomForm');
  f.querySelector('[name="id"]').value = room.id;
  f.room_number.value = room.room_number;
  f.capacity.value = room.capacity;
  f.computers_count.value = room.computers_count;
}

async function removeRoom(id) {
  const ok = await confirmAction();
  if (!ok.isConfirmed) return;
  const fd = new FormData();
  fd.append('id', id);
  const res = await apiFetch('../delete/room.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) loadRooms();
}

async function loadCourses() {
  await loadWeekDays();
  const res = await apiFetch('../get/courses.php');
  if (!res.success || !qs('coursesTableBody')) return;

  qs('coursesTableBody').innerHTML = (res.data.items || []).map((c) => `<tr>
    <td>${escapeHtml(c.name)}</td>
    <td>${escapeHtml(parseJsonArray(c.days).join(', '))}</td>
    <td>${escapeHtml(c.time)}</td>
    <td>${escapeHtml(c.duration)}</td>
    <td>
      <div class="table-actions">
        <button type="button" class="js-course-edit px-2 py-1 text-xs border rounded" data-item="${escapeHtml(JSON.stringify(c))}">Edit</button>
        <button type="button" class="js-course-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="${c.id}">Delete</button>
      </div>
    </td>
  </tr>`).join('');
}

function renderCourseDaysGrid(selectedCodes = []) {
  const grid = qs('courseDaysGrid');
  if (!grid) return;
  grid.innerHTML = weekDaysCache.map((day) => `
    <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2">
      <input type="checkbox" name="days[]" value="${day.code}" ${selectedCodes.includes(day.code) ? 'checked' : ''} class="h-4 w-4 accent-emerald-700">
      <span class="text-sm">${day.name}</span>
    </label>
  `).join('');
}

function openCourseModal() {
  qs('courseModal').classList.remove('hidden');
  qs('courseModal').classList.add('flex');
  qs('courseForm').reset();
  renderCourseDaysGrid([]);
}

function closeCourseModal() {
  qs('courseModal').classList.add('hidden');
  qs('courseModal').classList.remove('flex');
}

function fillCourse(course) {
  openCourseModal();
  const f = qs('courseForm');
  f.querySelector('[name="id"]').value = course.id;
  f.name.value = course.name;
  f.description.value = course.description || '';
  f.time.value = course.time;
  f.duration.value = course.duration;
  renderCourseDaysGrid(parseJsonArray(course.days));
}

async function removeCourse(id) {
  const ok = await confirmAction();
  if (!ok.isConfirmed) return;
  const fd = new FormData();
  fd.append('id', id);
  const res = await apiFetch('../delete/course.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) loadCourses();
}

function fillMentorStudentSelect(selectedStudentId = '', fallbackItem = null) {
  const select = qs('mentorStudentSelect');
  if (!select) return;
  let list = [...residentStudentsCache];
  if (fallbackItem && fallbackItem.student_id && !list.find((item) => String(item.id) === String(fallbackItem.student_id))) {
    list = [{ id: fallbackItem.student_id, fio: fallbackItem.fio }, ...list];
  }
  select.innerHTML = '<option value="">Rezident tanlang</option>' + list.map((s) => `<option value="${s.id}">${escapeHtml(s.fio)}</option>`).join('');
  select.value = String(selectedStudentId || '');
}

function fillMentorCourseSelect(selectedCourseId = '') {
  const select = qs('mentorCourseSelect');
  if (!select) return;
  select.innerHTML = '<option value="">Kurs tanlang</option>' + coursesCache.map((course) => `<option value="${course.id}">${escapeHtml(course.name)}</option>`).join('');
  select.value = String(selectedCourseId || '');
}

async function loadMentors() {
  await Promise.all([loadCoursesCache(), loadResidentStudentsCache()]);
  const res = await apiFetch('../get/mentors.php');
  if (!res.success || !qs('mentorsTableBody')) return;

  qs('mentorsTableBody').innerHTML = (res.data.items || []).map((m) => `<tr>
    <td>${escapeHtml(m.fio)}</td>
    <td>${escapeHtml(m.course_name)}</td>
    <td>
      <div class="table-actions">
        <button type="button" class="js-mentor-edit px-2 py-1 text-xs border rounded" data-item="${escapeHtml(JSON.stringify(m))}">Edit</button>
        <button type="button" class="js-mentor-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="${m.id}">Delete</button>
      </div>
    </td>
  </tr>`).join('');
}

function openMentorModal() {
  qs('mentorModal').classList.remove('hidden');
  qs('mentorModal').classList.add('flex');
  const mentorForm = qs('mentorForm');
  mentorForm?.reset();
  const hiddenId = mentorForm?.querySelector('[name="id"]');
  if (hiddenId) hiddenId.value = '';
  fillMentorStudentSelect('');
  fillMentorCourseSelect('');
}

function closeMentorModal() {
  qs('mentorModal').classList.add('hidden');
  qs('mentorModal').classList.remove('flex');
}

function fillMentor(mentor) {
  openMentorModal();
  const f = qs('mentorForm');
  f.querySelector('[name="id"]').value = mentor.id;
  fillMentorStudentSelect(mentor.student_id, mentor);
  fillMentorCourseSelect(mentor.course_id);
}

async function removeMentor(id) {
  const ok = await confirmAction();
  if (!ok.isConfirmed) return;
  const fd = new FormData();
  fd.append('id', id);
  const res = await apiFetch('../delete/mentor.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) loadMentors();
}

async function loadSettings() {
  const [directionsRes, statusesRes] = await Promise.all([apiFetch('../get/directions.php'), apiFetch('../get/statuses.php')]);

  if (directionsRes.success && qs('directionsTableBody')) {
    qs('directionsTableBody').innerHTML = directionsRes.data.items.map((d) => `<tr>
      <td>${escapeHtml(d.name)}</td>
      <td>
        <div class="table-actions">
          <button type="button" class="js-direction-edit px-2 py-1 text-xs border rounded" data-item="${escapeHtml(JSON.stringify(d))}">Edit</button>
          <button type="button" class="js-direction-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="${d.id}">Delete</button>
        </div>
      </td>
    </tr>`).join('');
  }

  if (statusesRes.success && qs('statusesTableBody')) {
    qs('statusesTableBody').innerHTML = statusesRes.data.items.map((s) => `<tr>
      <td>${escapeHtml(s.name)}</td>
      <td>
        <div class="table-actions">
          <button type="button" class="js-status-edit px-2 py-1 text-xs border rounded" data-item="${escapeHtml(JSON.stringify(s))}">Edit</button>
          <button type="button" class="js-status-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="${s.id}">Delete</button>
        </div>
      </td>
    </tr>`).join('');
  }
}

function openDirectionModal() {
  qs('directionModal').classList.remove('hidden');
  qs('directionModal').classList.add('flex');
  qs('directionForm').reset();
}

function closeDirectionModal() {
  qs('directionModal').classList.add('hidden');
  qs('directionModal').classList.remove('flex');
}

function fillDirection(d) {
  openDirectionModal();
  qs('directionId').value = d.id;
  qs('directionName').value = d.name;
}

function openStatusModal() {
  qs('statusModal').classList.remove('hidden');
  qs('statusModal').classList.add('flex');
  qs('statusForm').reset();
}

function closeStatusModal() {
  qs('statusModal').classList.add('hidden');
  qs('statusModal').classList.remove('flex');
}

function fillStatus(s) {
  openStatusModal();
  qs('statusId').value = s.id;
  qs('statusName').value = s.name;
}

async function removeDirection(id) {
  const ok = await confirmAction();
  if (!ok.isConfirmed) return;
  const fd = new FormData();
  fd.append('id', id);
  const res = await apiFetch('../delete/direction.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) loadSettings();
}

async function removeStatus(id) {
  const ok = await confirmAction();
  if (!ok.isConfirmed) return;
  const fd = new FormData();
  fd.append('id', id);
  const res = await apiFetch('../delete/status.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) loadSettings();
}

async function loadCompetitions() {
  const res = await apiFetch('../get/competitions.php');
  if (!res.success || !qs('competitionsGrid')) return;

  qs('competitionsGrid').innerHTML = (res.data.items || []).map((item) => {
    const cardData = escapeHtml(JSON.stringify(item));
    return `
      <article class="group rounded-xl bg-white p-4 shadow hover:shadow-lg border border-slate-100 transition-all duration-200 hover:-translate-y-1 cursor-pointer" data-open-competition="${item.id}">
        <div class="flex items-start justify-between gap-2">
          <h3 class="font-semibold text-slate-900 leading-snug">${escapeHtml(item.name)}</h3>
          <span class="text-[11px] px-2 py-1 rounded bg-emerald-100 text-emerald-700">${formatDate(item.competition_date)}</span>
        </div>
        <p class="text-sm text-slate-600 mt-2">${escapeHtml(truncateText(item.description || 'Tavsif mavjud emas', 120))}</p>
        <p class="text-xs text-slate-500 mt-2"><i class="fa-solid fa-location-dot mr-1"></i>${escapeHtml(item.location || 'Manzil kiritilmagan')}</p>
        <div class="flex items-center justify-between mt-3 text-xs text-slate-500">
          <span>Ishtirokchi: ${escapeHtml(item.participant_count || 0)}</span>
          <span>Natija: ${escapeHtml(item.result_count || 0)}</span>
        </div>
        <div class="flex gap-2 mt-4" data-competition-actions>
          <button type="button" class="js-competition-edit px-2 py-1 text-xs border rounded" data-item="${cardData}">Edit</button>
          <button type="button" class="js-competition-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="${item.id}">Delete</button>
        </div>
      </article>
    `;
  }).join('');
}

function openCompetitionModal() {
  qs('competitionModal')?.classList.remove('hidden');
  qs('competitionModal')?.classList.add('flex');
  qs('competitionForm')?.reset();
  const idField = qs('competitionForm')?.querySelector('[name="id"]');
  if (idField) idField.value = '';
}

function closeCompetitionModal() {
  qs('competitionModal')?.classList.add('hidden');
  qs('competitionModal')?.classList.remove('flex');
}

function fillCompetition(item) {
  openCompetitionModal();
  const form = qs('competitionForm');
  if (!form) return;
  form.id.value = item.id;
  form.name.value = item.name || '';
  form.description.value = item.description || '';
  form.registration_deadline.value = item.registration_deadline || '';
  form.competition_date.value = item.competition_date || '';
  form.location.value = item.location || '';
}

async function removeCompetition(id) {
  const ok = await confirmAction('Tanlovni o\'chirmoqchimisiz?');
  if (!ok.isConfirmed) return;
  const fd = new FormData();
  fd.append('id', id);
  const res = await apiFetch('../delete/competition.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) {
    if (window.CURRENT_PAGE === 'competitions') loadCompetitions();
  }
}

async function loadCompetitionDetailPage() {
  const pageEl = qs('competitionDetailPage');
  if (!pageEl) return;

  const competitionId = Number(pageEl.dataset.competitionId || 0);
  if (!competitionId) {
    toast('error', 'Tanlov ID topilmadi');
    return;
  }

  await loadStudentOptions();
  const res = await apiFetch(`../get/competition_detail.php?id=${competitionId}`);
  if (!res.success) {
    toast('error', res.message);
    return;
  }

  const detail = res.data || {};
  const competition = detail.competition || {};
  const participants = detail.participants || [];
  const results = detail.results || [];

  qs('competitionDetailName').textContent = competition.name || 'Tanlov topilmadi';
  qs('competitionDetailMeta').textContent = `${formatDate(competition.competition_date)} | ${competition.location || 'Manzil yo\'q'} | Deadline: ${formatDate(competition.registration_deadline)}`;

  const participantList = qs('competitionParticipantList');
  participantList.innerHTML = participants.length ? participants.map((item) => `
    <li class="rounded-lg border border-slate-200 px-3 py-2 flex items-center justify-between">
      <span class="text-sm">${escapeHtml(item.fio)}</span>
      <button type="button" class="js-participant-delete text-xs px-2 py-1 rounded bg-red-500 text-white" data-id="${item.id}">O'chirish</button>
    </li>
  `).join('') : '<li class="text-sm text-slate-500">Ishtirokchilar hali qo\'shilmagan.</li>';

  const resultWrap = qs('competitionResultCards');
  resultWrap.innerHTML = results.length ? results.map((item) => `
    <article class="rounded-lg border border-slate-200 px-3 py-2 bg-slate-50">
      <p class="text-sm font-medium text-slate-900">${item.position}-o'rin: ${escapeHtml(item.fio)}</p>
    </article>
  `).join('') : '<p class="text-sm text-slate-500">Natijalar kiritilmagan.</p>';

  const optionHtml = '<option value="">Talaba tanlang</option>' + studentOptionsCache.map((s) => `<option value="${s.id}">${escapeHtml(s.fio)}</option>`).join('');
  if (qs('competitionParticipantStudentSelect')) {
    qs('competitionParticipantStudentSelect').innerHTML = optionHtml;
    initSelect2ForElement(qs('competitionParticipantStudentSelect'), 'Talabani tanlang', 'competitionParticipantModal');
  }
  if (qs('competitionResultStudentSelect')) {
    qs('competitionResultStudentSelect').innerHTML = optionHtml;
    initSelect2ForElement(qs('competitionResultStudentSelect'), 'Talabani tanlang', 'competitionResultModal');
  }

  if (!notifyStudentSelectorManager) {
    notifyStudentSelectorManager = createDynamicStudentSelectorManager({
      containerId: 'notifyStudentSelectors',
      addButtonId: 'addNotifyStudentSelectBtn',
      modalId: 'competitionNotifyModal',
      inputName: 'student_ids[]',
      minRows: 1,
      placeholder: 'Talabani tanlang',
    });
  }
  notifyStudentSelectorManager?.reset([]);

  qs('notifyCompetitionId') && (qs('notifyCompetitionId').value = String(competitionId));
}

function openCompetitionNotifyModal() {
  qs('competitionNotifyModal')?.classList.remove('hidden');
  qs('competitionNotifyModal')?.classList.add('flex');
  notifyStudentSelectorManager?.reset([]);
}

function closeCompetitionNotifyModal() {
  qs('competitionNotifyModal')?.classList.add('hidden');
  qs('competitionNotifyModal')?.classList.remove('flex');
}

function openCompetitionResultModal() {
  qs('competitionResultModal')?.classList.remove('hidden');
  qs('competitionResultModal')?.classList.add('flex');
  initSelect2ForElement(qs('competitionResultStudentSelect'), 'Talabani tanlang', 'competitionResultModal');
}

function closeCompetitionResultModal() {
  qs('competitionResultModal')?.classList.add('hidden');
  qs('competitionResultModal')?.classList.remove('flex');
}

function openCompetitionParticipantModal() {
  qs('competitionParticipantModal')?.classList.remove('hidden');
  qs('competitionParticipantModal')?.classList.add('flex');
  initSelect2ForElement(qs('competitionParticipantStudentSelect'), 'Talabani tanlang', 'competitionParticipantModal');
}

function closeCompetitionParticipantModal() {
  qs('competitionParticipantModal')?.classList.add('hidden');
  qs('competitionParticipantModal')?.classList.remove('flex');
}

async function loadSchedules() {
  const res = await apiFetch(`../get/schedules.php?type=${encodeURIComponent(scheduleFilterType)}`);
  if (!res.success) return;

  const items = res.data.items || [];
  const tbody = qs('scheduleTableBody');
  const grid = qs('scheduleGrid');
  if (!tbody || !grid) return;

  tbody.innerHTML = items.map((item) => `<tr>
    <td>${item.type === 'daily' ? 'Kunlik' : 'Haftalik'}</td>
    <td>${escapeHtml(item.title)}</td>
    <td>${formatDate(item.date)}</td>
    <td>
      <div class="table-actions">
        <button type="button" class="js-schedule-edit px-2 py-1 text-xs border rounded" data-item="${escapeHtml(JSON.stringify(item))}">Edit</button>
        <button type="button" class="js-schedule-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="${item.id}">Delete</button>
      </div>
    </td>
  </tr>`).join('');

  grid.innerHTML = items.map((item) => `
    <article class="rounded-xl border border-slate-200 p-3 bg-slate-50">
      <div class="flex items-start justify-between gap-2">
        <p class="font-medium text-slate-900">${escapeHtml(item.title)}</p>
        <span class="text-xs px-2 py-1 rounded ${item.type === 'daily' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700'}">${item.type === 'daily' ? 'Kunlik' : 'Haftalik'}</span>
      </div>
      <p class="text-sm text-slate-600 mt-2">${formatDate(item.date)}</p>
      <div class="flex gap-2 mt-3">
        <button type="button" class="js-schedule-edit px-2 py-1 text-xs border rounded" data-item="${escapeHtml(JSON.stringify(item))}">Edit</button>
        <button type="button" class="js-schedule-delete px-2 py-1 text-xs bg-red-500 text-white rounded" data-id="${item.id}">Delete</button>
      </div>
    </article>
  `).join('');

  applyScheduleView();
}

function applyScheduleView() {
  const tableWrap = qs('scheduleTableWrap');
  const grid = qs('scheduleGrid');
  const listBtn = qs('scheduleViewListBtn');
  const gridBtn = qs('scheduleViewGridBtn');
  if (!tableWrap || !grid || !listBtn || !gridBtn) return;

  const isGrid = scheduleViewMode === 'grid';
  tableWrap.classList.toggle('hidden', isGrid);
  grid.classList.toggle('hidden', !isGrid);

  listBtn.classList.toggle('bg-white', !isGrid);
  listBtn.classList.toggle('shadow', !isGrid);
  gridBtn.classList.toggle('bg-white', isGrid);
  gridBtn.classList.toggle('shadow', isGrid);
}

function setupScheduleFilters() {
  const allBtn = qs('scheduleTypeAll');
  const dailyBtn = qs('scheduleTypeDaily');
  const weeklyBtn = qs('scheduleTypeWeekly');

  const paint = () => {
    const mapping = [
      [allBtn, ''],
      [dailyBtn, 'daily'],
      [weeklyBtn, 'weekly'],
    ];
    mapping.forEach(([btn, val]) => {
      if (!btn) return;
      const active = scheduleFilterType === val;
      btn.classList.toggle('bg-white', active);
      btn.classList.toggle('shadow', active);
    });
  };

  allBtn?.addEventListener('click', () => {
    scheduleFilterType = '';
    paint();
    loadSchedules();
  });
  dailyBtn?.addEventListener('click', () => {
    scheduleFilterType = 'daily';
    paint();
    loadSchedules();
  });
  weeklyBtn?.addEventListener('click', () => {
    scheduleFilterType = 'weekly';
    paint();
    loadSchedules();
  });

  qs('scheduleViewListBtn')?.addEventListener('click', () => {
    scheduleViewMode = 'list';
    applyScheduleView();
  });
  qs('scheduleViewGridBtn')?.addEventListener('click', () => {
    scheduleViewMode = 'grid';
    applyScheduleView();
  });

  paint();
  applyScheduleView();
}

function openScheduleModal() {
  qs('scheduleModal')?.classList.remove('hidden');
  qs('scheduleModal')?.classList.add('flex');
  qs('scheduleForm')?.reset();
  const idField = qs('scheduleForm')?.querySelector('[name="id"]');
  if (idField) idField.value = '';
}

function closeScheduleModal() {
  qs('scheduleModal')?.classList.add('hidden');
  qs('scheduleModal')?.classList.remove('flex');
}

function fillSchedule(item) {
  openScheduleModal();
  const form = qs('scheduleForm');
  if (!form) return;
  form.id.value = item.id;
  form.type.value = item.type;
  form.title.value = item.title;
  form.date.value = item.date;
}

async function removeSchedule(id) {
  const ok = await confirmAction('Jadvalni o\'chirmoqchimisiz?');
  if (!ok.isConfirmed) return;
  const fd = new FormData();
  fd.append('id', id);
  const res = await apiFetch('../delete/schedule.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) loadSchedules();
}

async function loadTeams() {
  const res = await apiFetch('../get/teams.php');
  if (!res.success || !qs('teamsGrid')) return;

  const teams = res.data.items || [];
  qs('teamsGrid').innerHTML = teams.length ? teams.map((team) => {
    const members = team.members || [];
    return `
      <article class="rounded-xl bg-white p-4 shadow border border-slate-100">
        <div class="flex items-start justify-between gap-2">
          <h3 class="font-semibold text-slate-900">${escapeHtml(team.team_name)}</h3>
          <button type="button" class="js-team-delete text-xs px-2 py-1 rounded bg-red-500 text-white" data-id="${team.id}">O'chirish</button>
        </div>
        <p class="text-xs text-slate-500 mt-1">A'zolar: ${members.length}</p>
        <div class="mt-3 space-y-2 max-h-44 overflow-auto">
          ${members.length ? members.map((member) => `
            <div class="rounded-lg border border-slate-200 px-3 py-2 flex items-center justify-between">
              <span class="text-sm text-slate-700">${escapeHtml(member.fio)}</span>
              <button type="button" class="js-team-member-remove text-xs px-2 py-1 rounded bg-slate-800 text-white" data-id="${member.id}">Chiqarish</button>
            </div>
          `).join('') : '<p class="text-sm text-slate-500">Hali a\'zo qo\'shilmagan</p>'}
        </div>
        <button type="button" class="js-team-member-open mt-3 px-3 py-1.5 text-xs rounded bg-emerald-700 text-white" data-team-id="${team.id}">+ A'zo qo'shish</button>
      </article>
    `;
  }).join('') : '<p class="text-sm text-slate-500">Jamoalar mavjud emas.</p>';
}

function openTeamModal() {
  qs('teamModal')?.classList.remove('hidden');
  qs('teamModal')?.classList.add('flex');
  const form = qs('teamForm');
  form?.reset();

  if (!teamStudentSelectorManager) {
    teamStudentSelectorManager = createDynamicStudentSelectorManager({
      containerId: 'teamStudentSelectors',
      addButtonId: 'addTeamStudentSelectBtn',
      modalId: 'teamModal',
      inputName: 'student_ids[]',
      minRows: 1,
      placeholder: 'Talabani tanlang',
    });
  }
  teamStudentSelectorManager?.reset([]);
}

function closeTeamModal() {
  qs('teamModal')?.classList.add('hidden');
  qs('teamModal')?.classList.remove('flex');
}

function openTeamMemberModal(teamId) {
  qs('teamMemberModal')?.classList.remove('hidden');
  qs('teamMemberModal')?.classList.add('flex');
  qs('teamMemberTeamId').value = String(teamId);
  const select = qs('teamMemberStudentSelect');
  if (select) {
    select.innerHTML = '<option value="">Talaba tanlang</option>' + studentOptionsCache.map((s) => `<option value="${s.id}">${escapeHtml(s.fio)}</option>`).join('');
    initSelect2ForElement(select, 'Talabani tanlang', 'teamMemberModal');
  }
}

function closeTeamMemberModal() {
  qs('teamMemberModal')?.classList.add('hidden');
  qs('teamMemberModal')?.classList.remove('flex');
}

async function removeTeam(id) {
  const ok = await confirmAction('Jamoani o\'chirasizmi?');
  if (!ok.isConfirmed) return;
  const fd = new FormData();
  fd.append('id', id);
  const res = await apiFetch('../delete/team.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) loadTeams();
}

async function removeTeamMember(id) {
  const fd = new FormData();
  fd.append('id', id);
  const res = await apiFetch('../delete/team_member.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) loadTeams();
}

function setupTableActions() {
  document.addEventListener('click', async (event) => {
    const btn = event.target.closest('button');
    if (btn) {
      if (btn.classList.contains('js-student-edit')) {
        const data = parseDataItem(btn.dataset.item);
        if (data) editStudent(data);
        return;
      }
      if (btn.classList.contains('js-student-delete')) {
        deleteStudent(Number(btn.dataset.id || 0));
        return;
      }
      if (btn.classList.contains('js-resident-open')) {
        const data = parseDataItem(btn.dataset.item);
        if (data) openResidentModal(data);
        return;
      }
      if (btn.classList.contains('js-resident-delete')) {
        deleteResident(Number(btn.dataset.id || 0));
        return;
      }
      if (btn.classList.contains('js-course-student-open')) {
        const data = parseDataItem(btn.dataset.item);
        if (data) openCourseStudentModal(data);
        return;
      }
      if (btn.classList.contains('js-course-status-toggle')) {
        updateCourseStudentStatus(Number(btn.dataset.id || 0), btn.dataset.status || 'active');
        return;
      }
      if (btn.classList.contains('js-course-student-delete')) {
        deleteCourseStudent(Number(btn.dataset.id || 0));
        return;
      }
      if (btn.classList.contains('js-room-edit')) {
        const data = parseDataItem(btn.dataset.item);
        if (data) fillRoom(data);
        return;
      }
      if (btn.classList.contains('js-room-delete')) {
        removeRoom(Number(btn.dataset.id || 0));
        return;
      }
      if (btn.classList.contains('js-course-edit')) {
        const data = parseDataItem(btn.dataset.item);
        if (data) fillCourse(data);
        return;
      }
      if (btn.classList.contains('js-course-delete')) {
        removeCourse(Number(btn.dataset.id || 0));
        return;
      }
      if (btn.classList.contains('js-mentor-edit')) {
        const data = parseDataItem(btn.dataset.item);
        if (data) fillMentor(data);
        return;
      }
      if (btn.classList.contains('js-mentor-delete')) {
        removeMentor(Number(btn.dataset.id || 0));
        return;
      }
      if (btn.classList.contains('js-direction-edit')) {
        const data = parseDataItem(btn.dataset.item);
        if (data) fillDirection(data);
        return;
      }
      if (btn.classList.contains('js-direction-delete')) {
        removeDirection(Number(btn.dataset.id || 0));
        return;
      }
      if (btn.classList.contains('js-status-edit')) {
        const data = parseDataItem(btn.dataset.item);
        if (data) fillStatus(data);
        return;
      }
      if (btn.classList.contains('js-status-delete')) {
        removeStatus(Number(btn.dataset.id || 0));
        return;
      }
      if (btn.classList.contains('js-competition-edit')) {
        const data = parseDataItem(btn.dataset.item);
        if (data) fillCompetition(data);
        return;
      }
      if (btn.classList.contains('js-competition-delete')) {
        removeCompetition(Number(btn.dataset.id || 0));
        return;
      }
      if (btn.classList.contains('js-schedule-edit')) {
        const data = parseDataItem(btn.dataset.item);
        if (data) fillSchedule(data);
        return;
      }
      if (btn.classList.contains('js-schedule-delete')) {
        removeSchedule(Number(btn.dataset.id || 0));
        return;
      }
      if (btn.classList.contains('js-participant-delete')) {
        const fd = new FormData();
        fd.append('id', Number(btn.dataset.id || 0));
        const res = await apiFetch('../delete/competition_participant.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message);
        if (res.success) loadCompetitionDetailPage();
        return;
      }
      if (btn.classList.contains('js-team-delete')) {
        removeTeam(Number(btn.dataset.id || 0));
        return;
      }
      if (btn.classList.contains('js-team-member-remove')) {
        removeTeamMember(Number(btn.dataset.id || 0));
        return;
      }
      if (btn.classList.contains('js-team-member-open')) {
        openTeamMemberModal(Number(btn.dataset.teamId || 0));
      }
    }

    const card = event.target.closest('[data-open-competition]');
    if (!card) return;

    if (event.target.closest('[data-competition-actions]')) {
      return;
    }

    const competitionId = Number(card.dataset.openCompetition || 0);
    if (competitionId > 0) {
      window.location.href = `index.php?page=competition_detail&id=${competitionId}`;
    }
  });
}

function setupPageSpecificButtons() {
  qs('competitionNotifyBtn')?.addEventListener('click', openCompetitionNotifyModal);
  qs('competitionResultBtn')?.addEventListener('click', openCompetitionResultModal);
  qs('competitionParticipantAddBtn')?.addEventListener('click', openCompetitionParticipantModal);
  qs('openTeamCreateModalBtn')?.addEventListener('click', openTeamModal);

  if (qs('notifyStudentSelectors') && !notifyStudentSelectorManager) {
    notifyStudentSelectorManager = createDynamicStudentSelectorManager({
      containerId: 'notifyStudentSelectors',
      addButtonId: 'addNotifyStudentSelectBtn',
      modalId: 'competitionNotifyModal',
      inputName: 'student_ids[]',
      minRows: 1,
      placeholder: 'Talabani tanlang',
    });
    notifyStudentSelectorManager?.reset([]);
  }

  if (qs('teamStudentSelectors') && !teamStudentSelectorManager) {
    teamStudentSelectorManager = createDynamicStudentSelectorManager({
      containerId: 'teamStudentSelectors',
      addButtonId: 'addTeamStudentSelectBtn',
      modalId: 'teamModal',
      inputName: 'student_ids[]',
      minRows: 1,
      placeholder: 'Talabani tanlang',
    });
    teamStudentSelectorManager?.reset([]);
  }
}

qs('logoutBtn')?.addEventListener('click', async () => {
  const res = await apiFetch('../api/auth.php?action=logout', { method: 'POST' });
  if (res.success) window.location.href = '../index.php';
});

qs('studentForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  if (!fd.getAll('status[]').length) return toast('error', 'Kamida bitta status tanlang.');
  const url = fd.get('id') ? '../update/student.php' : '../insert/student.php';
  const res = await apiFetch(url, { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) {
    closeStudentModal();
    loadStudents(studentPage);
  }
});

qs('studentSearch')?.addEventListener('input', () => loadStudents(1));
qs('studentDirectionFilter')?.addEventListener('change', () => loadStudents(1));
qs('studentEntryYear')?.addEventListener('input', updateStudentAutoCourse);

qs('residentSearch')?.addEventListener('input', () => loadResidents());
qs('residentStatusFilter')?.addEventListener('change', () => loadResidents());

qs('courseStudentSearch')?.addEventListener('input', () => loadCourseStudents());
qs('courseStudentStatusFilter')?.addEventListener('change', () => loadCourseStudents());

qs('residentForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const res = await apiFetch('../insert/resident.php', { method: 'POST', body: new FormData(e.target) });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) {
    closeResidentModal();
    loadResidents();
  }
});

qs('courseStudentForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const res = await apiFetch('../insert/course_student.php', { method: 'POST', body: new FormData(e.target) });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) {
    closeCourseStudentModal();
    loadCourseStudents();
  }
});

qs('directionForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await apiFetch(fd.get('id') ? '../update/direction.php' : '../insert/direction.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) {
    closeDirectionModal();
    loadSettings();
  }
});

qs('statusForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await apiFetch(fd.get('id') ? '../update/status.php' : '../insert/status.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) {
    closeStatusModal();
    loadSettings();
  }
});

qs('roomForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await apiFetch(fd.get('id') ? '../update/room.php' : '../insert/room.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) {
    closeRoomModal();
    loadRooms();
  }
});

qs('courseForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const selectedDays = e.target.querySelectorAll('input[name="days[]"]:checked');
  if (!selectedDays.length) return toast('error', 'Kamida bitta kun tanlang.');
  const fd = new FormData(e.target);
  const res = await apiFetch(fd.get('id') ? '../update/course.php' : '../insert/course.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) {
    closeCourseModal();
    loadCourses();
  }
});

qs('mentorForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  if (!fd.get('student_id')) return toast('error', 'Rezident talaba tanlang.');
  if (!fd.get('course_id')) return toast('error', 'Kurs tanlang.');
  const res = await apiFetch(fd.get('id') ? '../update/mentor.php' : '../insert/mentor.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) {
    closeMentorModal();
    loadMentors();
  }
});

qs('competitionForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await apiFetch(fd.get('id') ? '../update/competition.php' : '../insert/competition.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) {
    closeCompetitionModal();
    loadCompetitions();
  }
});

qs('competitionNotifyForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  if (notifyStudentSelectorManager?.hasDuplicate()) {
    toast('error', 'Bir xil talaba qayta tanlandi. Iltimos, takrorlarni olib tashlang.');
    return;
  }
  const selected = fd.getAll('student_ids[]');
  if (!selected.length || selected.some((value) => String(value).trim() === '')) {
    toast('error', 'Kamida bitta talaba tanlang.');
    return;
  }
  const res = await apiFetch('../insert/competition_message.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) {
    closeCompetitionNotifyModal();
    const sent = res.data?.sent_count || 0;
    const failed = res.data?.failed_count || 0;
    Swal.fire({ icon: 'info', title: 'Yuborish natijasi', text: `Yuborildi: ${sent}. Xatolik: ${failed}.` });
  }
});

qs('competitionResultForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await apiFetch('../insert/competition_result.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) {
    closeCompetitionResultModal();
    loadCompetitionDetailPage();
  }
});

qs('competitionParticipantForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await apiFetch('../insert/competition_participant.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) {
    closeCompetitionParticipantModal();
    loadCompetitionDetailPage();
  }
});

qs('scheduleForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await apiFetch(fd.get('id') ? '../update/schedule.php' : '../insert/schedule.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) {
    closeScheduleModal();
    loadSchedules();
  }
});

qs('teamForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  if (teamStudentSelectorManager?.hasDuplicate()) {
    toast('error', 'Jamoa uchun bir xil talaba ikki marta tanlandi.');
    return;
  }
  const fd = new FormData(e.target);
  const selected = fd.getAll('student_ids[]').filter((value) => String(value).trim() !== '');
  if (selected.length === 0) {
    toast('error', 'Kamida bitta talaba tanlang.');
    return;
  }
  const res = await apiFetch('../insert/team.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) {
    closeTeamModal();
    loadTeams();
  }
});

qs('teamMemberForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await apiFetch('../insert/team_member.php', { method: 'POST', body: fd });
  toast(res.success ? 'success' : 'error', res.message);
  if (res.success) {
    closeTeamMemberModal();
    loadTeams();
  }
});

setupSidebar();
setupProfileDropdown();
setupGlobalSearch();
setupCalendar();
applyUiEnhancements();
setupTableActions();
setupPhoneInputs();
setupPageSpecificButtons();

Promise.all([loadDirections(), loadStatuses(), loadWeekDays(), loadStudentOptions()]).then(async () => {
  fillStudentDirectionFilter();

  if (window.CURRENT_PAGE === 'dashboard') {
    await loadStats();
  }
  if (window.CURRENT_PAGE === 'statistics') {
    await loadStats();
  }
  if (window.CURRENT_PAGE === 'students') {
    loadStudents();
  }
  if (window.CURRENT_PAGE === 'residents') {
    loadResidents();
  }
  if (window.CURRENT_PAGE === 'course_students') {
    loadCourseStudents();
  }
  if (window.CURRENT_PAGE === 'directions' || window.CURRENT_PAGE === 'statuses') {
    loadSettings();
  }
  if (window.CURRENT_PAGE === 'rooms') {
    loadRooms();
  }
  if (window.CURRENT_PAGE === 'courses') {
    loadCourses();
  }
  if (window.CURRENT_PAGE === 'mentors') {
    loadMentors();
  }
  if (window.CURRENT_PAGE === 'competitions') {
    loadCompetitions();
  }
  if (window.CURRENT_PAGE === 'competition_detail') {
    loadCompetitionDetailPage();
  }
  if (window.CURRENT_PAGE === 'schedule') {
    setupScheduleFilters();
    loadSchedules();
  }
  if (window.CURRENT_PAGE === 'teams') {
    loadTeams();
  }
});

window.openStudentModal = openStudentModal;
window.closeStudentModal = closeStudentModal;
window.editStudent = editStudent;
window.deleteStudent = deleteStudent;
window.openResidentModal = openResidentModal;
window.closeResidentModal = closeResidentModal;
window.deleteResident = deleteResident;
window.openCourseStudentModal = openCourseStudentModal;
window.closeCourseStudentModal = closeCourseStudentModal;
window.deleteCourseStudent = deleteCourseStudent;
window.openDirectionModal = openDirectionModal;
window.closeDirectionModal = closeDirectionModal;
window.fillDirection = fillDirection;
window.openStatusModal = openStatusModal;
window.closeStatusModal = closeStatusModal;
window.fillStatus = fillStatus;
window.removeDirection = removeDirection;
window.removeStatus = removeStatus;
window.openRoomModal = openRoomModal;
window.closeRoomModal = closeRoomModal;
window.fillRoom = fillRoom;
window.removeRoom = removeRoom;
window.openCourseModal = openCourseModal;
window.closeCourseModal = closeCourseModal;
window.fillCourse = fillCourse;
window.removeCourse = removeCourse;
window.openMentorModal = openMentorModal;
window.closeMentorModal = closeMentorModal;
window.fillMentor = fillMentor;
window.removeMentor = removeMentor;
window.loadStudents = loadStudents;
window.openCompetitionModal = openCompetitionModal;
window.closeCompetitionModal = closeCompetitionModal;
window.openScheduleModal = openScheduleModal;
window.closeScheduleModal = closeScheduleModal;
window.closeCompetitionNotifyModal = closeCompetitionNotifyModal;
window.closeCompetitionResultModal = closeCompetitionResultModal;
window.closeCompetitionParticipantModal = closeCompetitionParticipantModal;
window.closeTeamModal = closeTeamModal;
window.closeTeamMemberModal = closeTeamMemberModal;
