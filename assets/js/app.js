const PAGE_OPTIONS = window.PAGE_OPTIONS || {};
const PAGE_DATA = window.PAGE_DATA || {};
const SIDEBAR_COLLAPSED_KEY = 'itmarkaz_sidebar_collapsed';

function qs(id) {
  return document.getElementById(id);
}

function parseDataItem(item) {
  try {
    return JSON.parse(item || '{}');
  } catch {
    return null;
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

async function apiFetch(url, options = {}) {
  const res = await fetch(url, options);
  const text = await res.text();
  let data;
  try {
    data = JSON.parse(text);
  } catch {
    data = { success: false, message: `Server JSON xatolik (${res.status})`, data: {} };
  }
  if (!res.ok) {
    return { success: false, message: data.message || `Server xatolik: ${res.status}`, data: data.data || {} };
  }
  return data;
}

function toast(icon, title) {
  Swal.fire({ toast: true, position: 'top-end', timer: 1800, showConfirmButton: false, icon, title });
}

function confirmAction(text = "Rostdan ham o'chirasizmi?") {
  return Swal.fire({ title: 'Tasdiqlang', text, icon: 'warning', showCancelButton: true, confirmButtonText: 'Ha', cancelButtonText: "Yo'q" });
}

function canUseSelect2() {
  return typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn?.select2 === 'function';
}

function initSelect2ForElement(selectEl, placeholder = 'Tanlang', modalId = '') {
  if (!selectEl || !canUseSelect2()) return;
  const $ = window.jQuery;
  const $select = $(selectEl);
  if ($select.data('select2')) $select.select2('destroy');
  const options = { width: '100%', placeholder, allowClear: true };
  if (modalId && qs(modalId)) options.dropdownParent = $(qs(modalId));
  $select.select2(options);
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
    const isMobile = window.innerWidth < 768;
    sidebar.classList.toggle('md:w-24', isCollapsed);
    sidebar.classList.toggle('md:w-72', !isCollapsed);
    mainShell.classList.toggle('md:pl-24', isCollapsed);
    mainShell.classList.toggle('md:pl-72', !isCollapsed);
    
    document.querySelectorAll('.sidebar-label').forEach((el) => {
      if (isMobile) {
        el.classList.remove('hidden');
      } else {
        el.classList.toggle('hidden', isCollapsed);
      }
    });
    localStorage.setItem(SIDEBAR_COLLAPSED_KEY, isCollapsed ? '1' : '0');
  };

  setCollapsed(localStorage.getItem(SIDEBAR_COLLAPSED_KEY) === '1');
  qs('sidebarCollapseBtn')?.addEventListener('click', () => setCollapsed(!sidebar.classList.contains('md:w-24')));
  qs('sidebarToggle')?.addEventListener('click', () => { sidebar.classList.toggle('-translate-x-full'); overlay?.classList.toggle('hidden'); });
  overlay?.addEventListener('click', () => { sidebar.classList.add('-translate-x-full'); overlay?.classList.add('hidden'); });

  // Auto-hide sidebar on mobile after clicking a link
  sidebar.querySelectorAll('a.sidebar-link').forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth < 768) {
        sidebar.classList.add('-translate-x-full');
        overlay?.classList.add('hidden');
      }
    });
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
    if (isOpen) closeDropdown(); else openDropdown();
  });
  document.addEventListener('click', (e) => {
    if (!btn.contains(e.target) && !dropdown.contains(e.target)) closeDropdown();
  });
}

function setupCalendar() {
  const calendarBtn = qs('calendarBtn');
  const calendarPopover = qs('calendarPopover');
  if (!calendarBtn || !calendarPopover) return;
  if (typeof flatpickr !== 'undefined') flatpickr('#calendarInput', { inline: true });
  calendarBtn.addEventListener('click', (e) => { e.stopPropagation(); calendarPopover.classList.toggle('hidden'); });
  document.addEventListener('click', (e) => {
    if (!calendarPopover.contains(e.target) && !calendarBtn.contains(e.target)) calendarPopover.classList.add('hidden');
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
      if (q.length < 2) return results.classList.add('hidden');
      const res = await apiFetch(`../get/global_search.php?q=${encodeURIComponent(q)}`);
      if (!res.success) return;
      const list = res.data || [];
      results.innerHTML = !list.length
        ? '<p class="px-3 py-2 text-sm text-slate-500">Topilmadi</p>'
        : list.slice(0, 8).map((item) => `<div class="px-3 py-2 rounded hover:bg-slate-100"><p class="text-sm font-medium">${escapeHtml(item.title || '')}</p><p class="text-xs text-slate-500">${escapeHtml(item.type || '')}</p></div>`).join('');
      results.classList.remove('hidden');
    }, 250);
  });
}

function formatUzPhoneInput(value) {
  const digitsRaw = String(value ?? '').replace(/\D/g, '');
  let digits = digitsRaw;
  if (!digits.startsWith('998')) digits = `998${digits}`;
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
    if (!input.value.trim()) input.value = '+998';
    input.addEventListener('input', () => { input.value = formatUzPhoneInput(input.value); });
  });
}

function formatNumberWithSpaces(value) {
  const digits = String(value ?? '').replace(/\D/g, '');
  return digits.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

function setupNumberInputs() {
  document.querySelectorAll('input[data-number-format]').forEach((input) => {
    input.value = formatNumberWithSpaces(input.value);
    input.addEventListener('input', () => {
      const start = input.selectionStart;
      const oldLen = input.value.length;
      input.value = formatNumberWithSpaces(input.value);
      const newLen = input.value.length;
      input.setSelectionRange(start + (newLen - oldLen), start + (newLen - oldLen));
    });
  });
}

function calculateCourseFromEntryYear(entryYear) {
  const year = Number(entryYear);
  if (!Number.isInteger(year) || year < 1900) return '';
  const currentYear = new Date().getFullYear();
  const elapsedYears = currentYear - year;
  if (elapsedYears < 0) return 'Hali boshlanmagan';
  if (elapsedYears > 4) return 'Bitirgan';
  return `${elapsedYears === 0 ? 1 : elapsedYears}-kurs`;
}

function updateStudentAutoCourse() {
  const yearInput = qs('studentEntryYear');
  const courseInput = qs('studentAutoCourse');
  if (!yearInput || !courseInput) return;
  courseInput.value = calculateCourseFromEntryYear(yearInput.value) || '-';
}

function setDefaultStudentStatus() {
  const form = qs('studentForm');
  if (!form) return;
  const statusInputs = Array.from(form.querySelectorAll('input[name="status[]"]'));
  if (!statusInputs.length) return;
  if (statusInputs.some((input) => input.checked)) return;
  const talabaInput = statusInputs.find((input) => {
    const label = input.closest('label');
    const text = label?.querySelector('span')?.textContent || '';
    return text.trim().toLowerCase() === 'talaba';
  });
  if (talabaInput) talabaInput.checked = true;
}

function reloadPage() {
  window.location.reload();
}

function createDynamicStudentSelectorManager({ containerId, addButtonId, modalId, inputName = 'student_ids[]', minRows = 1, optionsList = null }) {
  const container = qs(containerId);
  const addBtn = qs(addButtonId);
  if (!container) return null;
  const options = Array.isArray(optionsList) ? optionsList : (PAGE_OPTIONS.student_options || []);

  const optionHtml = () => '<option value=""></option>' + options.map((s) => {
    const disabled = Boolean(Number(s.is_assigned || 0)) || Boolean(s.disabled);
    const label = `${escapeHtml(s.fio)}${disabled ? ' (mavjud)' : ''}`;
    return `<option value="${s.id}" ${disabled ? 'disabled' : ''}>${label}</option>`;
  }).join('');

  const getRows = () => Array.from(container.querySelectorAll('[data-student-row]'));
  const getSelects = () => getRows().map((row) => row.querySelector('select')).filter(Boolean);
  const selectedValues = () => getSelects().map((s) => (s.value || '').trim()).filter(Boolean);

  const syncDisabled = () => {
    const selected = selectedValues();
    getSelects().forEach((select) => {
      const current = select.value || '';
      Array.from(select.options).forEach((opt) => {
        if (!opt.value) return;
        const baseDisabled = opt.dataset.baseDisabled === '1';
        opt.disabled = baseDisabled || (selected.includes(opt.value) && opt.value !== current);
      });
      if (canUseSelect2()) window.jQuery(select).trigger('change.select2');
    });
  };

  const refreshRemoveButtons = () => {
    const rows = getRows();
    rows.forEach((row) => {
      const btn = row.querySelector('[data-remove-student-row]');
      if (!btn) return;
      btn.classList.toggle('hidden', rows.length <= minRows);
    });
  };

  const addRow = () => {
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2';
    row.setAttribute('data-student-row', '1');
    row.innerHTML = `<select name="${inputName}" class="form-input flex-1" required>${optionHtml()}</select><button type="button" data-remove-student-row class="h-10 w-10 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 flex items-center justify-center">×</button>`;
    container.appendChild(row);
    const select = row.querySelector('select');
    Array.from(select.options).forEach((opt) => {
      if (!opt.value) return;
      opt.dataset.baseDisabled = opt.disabled ? '1' : '0';
    });
    initSelect2ForElement(select, 'Talabani tanlang', modalId);
    select.addEventListener('change', syncDisabled);
    row.querySelector('[data-remove-student-row]')?.addEventListener('click', () => {
      if (getRows().length <= minRows) {
        select.value = '';
        if (canUseSelect2()) window.jQuery(select).trigger('change');
      } else {
        if (canUseSelect2() && window.jQuery(select).data('select2')) window.jQuery(select).select2('destroy');
        row.remove();
      }
      refreshRemoveButtons();
      syncDisabled();
    });
    refreshRemoveButtons();
    syncDisabled();
  };

  const reset = () => {
    getSelects().forEach((select) => {
      if (canUseSelect2() && window.jQuery(select).data('select2')) window.jQuery(select).select2('destroy');
    });
    container.innerHTML = '';
    addRow();
  };

  const hasDuplicate = () => {
    const vals = selectedValues();
    return vals.length !== new Set(vals).size;
  };

  addBtn?.addEventListener('click', addRow);
  return { reset, hasDuplicate, selectedValues };
}

let notifyManager = null;
let teamManager = null;
let projectManager = null;
let residentManager = null;
let courseStudentManager = null;
let competitionParticipantManager = null;
let competitionResultManager = null;
let competitionReportPicker = null;

function openModal(id) {
  const el = qs(id);
  if (!el) return;
  el.classList.remove('hidden');
  el.classList.add('flex');
}

function closeModal(id) {
  const el = qs(id);
  if (!el) return;
  el.classList.add('hidden');
  el.classList.remove('flex');
}

function openStudentModal() {
  openModal('studentModal');
  const form = qs('studentForm');
  form?.reset();
  if (qs('studentId')) qs('studentId').value = '';
  setDefaultStudentStatus();
  updateStudentAutoCourse();
}
function closeStudentModal() { closeModal('studentModal'); }
function editStudent(item) {
  openStudentModal();
  const form = qs('studentForm');
  if (!form) return;
  qs('studentId').value = item.id;
  form.fio.value = item.fio || '';
  form.yonalish_id.value = item.yonalish_id || '';
  form.guruh.value = item.guruh || '';
  form.kirgan_yili.value = item.kirgan_yili || '';
  form.telefon.value = item.telefon || '+998';
  form.telegram_chat_id.value = item.telegram_chat_id || '';
  updateStudentAutoCourse();
  const selected = new Set((item.status_ids || []).map((v) => Number(v)));
  form.querySelectorAll('input[name="status[]"]').forEach((cb) => { cb.checked = selected.has(Number(cb.value)); });
}

function openResidentModal(item) {
  openModal('residentModal');
  qs('residentStudentId').value = item.student_id || '';
  qs('residentStudentName').value = item.fio || '';
  qs('residentRoomSelect').value = item.room_id || '';
  qs('residentForm').computer_number.value = item.computer_number || '';
}
function closeResidentModal() { closeModal('residentModal'); }

function openResidentBulkModal() {
  openModal('residentBulkModal');
  qs('residentBulkForm')?.reset();
  if (!residentManager && qs('residentStudentSelectors')) {
    residentManager = createDynamicStudentSelectorManager({
      containerId: 'residentStudentSelectors',
      addButtonId: 'addResidentStudentSelectBtn',
      modalId: 'residentBulkModal',
      inputName: 'student_ids[]',
      optionsList: PAGE_OPTIONS.resident_students || [],
    });
  }
  residentManager?.reset();
}
function closeResidentBulkModal() { closeModal('residentBulkModal'); }

function openCourseStudentModal(item) {
  openModal('courseStudentModal');
  qs('courseStudentId').value = item.student_id || '';
  qs('courseStudentName').value = item.fio || '';
  qs('courseSelect').value = item.course_id || '';
  qs('courseRoomSelect').value = item.room_id || '';
}
function closeCourseStudentModal() { closeModal('courseStudentModal'); }

function openCourseStudentBulkModal() {
  openModal('courseStudentBulkModal');
  qs('courseStudentBulkForm')?.reset();
  if (!courseStudentManager && qs('courseStudentSelectors')) {
    courseStudentManager = createDynamicStudentSelectorManager({
      containerId: 'courseStudentSelectors',
      addButtonId: 'addCourseStudentSelectBtn',
      modalId: 'courseStudentBulkModal',
      inputName: 'student_ids[]',
      optionsList: PAGE_OPTIONS.course_students_options || [],
    });
  }
  courseStudentManager?.reset();
}
function closeCourseStudentBulkModal() { closeModal('courseStudentBulkModal'); }

function openRoomModal() { openModal('roomModal'); qs('roomForm')?.reset(); }
function closeRoomModal() { closeModal('roomModal'); }
function fillRoom(room) {
  openRoomModal();
  const f = qs('roomForm');
  f.querySelector('[name="id"]').value = room.id;
  f.room_number.value = room.room_number;
  f.capacity.value = room.capacity;
  f.computers_count.value = room.computers_count;
}

function openCourseModal() { openModal('courseModal'); qs('courseForm')?.reset(); }
function closeCourseModal() { closeModal('courseModal'); }
function fillCourse(course) {
  openCourseModal();
  const f = qs('courseForm');
  f.querySelector('[name="id"]').value = course.id;
  f.name.value = course.name || '';
  f.description.value = course.description || '';
  f.time.value = course.time || '';
  f.duration.value = course.duration || '';
  const selected = Array.isArray(course.days) ? course.days : [];
  f.querySelectorAll('input[name="days[]"]').forEach((cb) => { cb.checked = selected.includes(cb.value); });
}

function openMentorModal() { openModal('mentorModal'); qs('mentorForm')?.reset(); qs('mentorForm')?.querySelector('[name="id"]') && (qs('mentorForm').querySelector('[name="id"]').value = ''); }
function closeMentorModal() { closeModal('mentorModal'); }
function fillMentor(mentor) {
  openMentorModal();
  const f = qs('mentorForm');
  f.querySelector('[name="id"]').value = mentor.id || '';
  f.student_id.value = mentor.student_id || '';
  f.course_id.value = mentor.course_id || '';
}

async function loadProjectMembers(projectId, targetSelect, selectedId = null) {
  if (!projectId) {
    targetSelect.innerHTML = '<option value="">Loyihani tanlang...</option>';
    targetSelect.disabled = true;
    return;
  }
  targetSelect.innerHTML = '<option value="">Yuklanmoqda...</option>';
  targetSelect.disabled = true;
  const res = await apiFetch(`../get/project_members.php?project_id=${projectId}`);
  if (res.success) {
    const members = res.data || [];
    targetSelect.innerHTML = members.length 
      ? '<option value="">Talabani tanlang</option>' + members.map(m => `<option value="${m.id}" ${Number(m.id) === Number(selectedId) ? 'selected' : ''}>${escapeHtml(m.fio)}</option>`).join('')
      : '<option value="">Ushbu loyihada talabalar yo\'q</option>';
    targetSelect.disabled = !members.length;
  }
}

function openPaymentModal() { 
  openModal('paymentModal'); 
  qs('paymentForm')?.reset(); 
  qs('paymentId').value = '';
  qs('paymentModalTitle').textContent = 'To\'lov kiritish';
  qs('paymentStudentSelect').disabled = true;
  qs('paymentStudentSelect').innerHTML = '<option value="">Loyihani tanlang...</option>';
}
function closePaymentModal() { closeModal('paymentModal'); }
async function fillPayment(item) {
  openPaymentModal();
  qs('paymentModalTitle').textContent = 'To\'lovni tahrirlash';
  const f = qs('paymentForm');
  qs('paymentId').value = item.id;
  f.project_id.value = item.project_id;
  f.amount.value = item.amount;
  f.payment_type_id.value = item.payment_type_id;
  // Load members and select the student
  await loadProjectMembers(item.project_id, qs('paymentStudentSelect'), item.student_id);
}

function openDirectionModal() { openModal('directionModal'); qs('directionForm')?.reset(); }
function closeDirectionModal() { closeModal('directionModal'); }
function fillDirection(d) { openDirectionModal(); qs('directionId').value = d.id; qs('directionName').value = d.name || ''; }

function openStatusModal() { openModal('statusModal'); qs('statusForm')?.reset(); }
function closeStatusModal() { closeModal('statusModal'); }
function fillStatus(s) { openStatusModal(); qs('statusId').value = s.id; qs('statusName').value = s.name || ''; }

function openCompetitionModal() { openModal('competitionModal'); qs('competitionForm')?.reset(); const id = qs('competitionForm')?.querySelector('[name="id"]'); if (id) id.value = ''; }
function closeCompetitionModal() { closeModal('competitionModal'); }
function fillCompetition(item) {
  openCompetitionModal();
  const form = qs('competitionForm');
  form.id.value = item.id || '';
  form.name.value = item.name || '';
  form.description.value = item.description || '';
  form.registration_deadline.value = item.registration_deadline || '';
  form.competition_date.value = item.competition_date || '';
  form.location.value = item.location || '';
}

function openCompetitionNotifyModal() { openModal('competitionNotifyModal'); notifyManager?.reset(); }
function closeCompetitionNotifyModal() { closeModal('competitionNotifyModal'); }
function openCompetitionResultModal() {
  openModal('competitionResultModal');
  qs('competitionResultForm')?.reset();
  if (!competitionResultManager && qs('competitionResultStudentSelectors')) {
    competitionResultManager = createDynamicStudentSelectorManager({
      containerId: 'competitionResultStudentSelectors',
      addButtonId: 'addCompetitionResultStudentSelectBtn',
      modalId: 'competitionResultModal',
      inputName: 'student_ids[]',
      optionsList: PAGE_OPTIONS.student_options || [],
    });
  }
  competitionResultManager?.reset();
  initSelect2ForElement(qs('competitionResultTypeSelect'), 'Natija turini tanlang', 'competitionResultModal');
  toggleCompetitionCashInput();
}
function closeCompetitionResultModal() { closeModal('competitionResultModal'); }
function openCompetitionParticipantModal() {
  openModal('competitionParticipantModal');
  qs('competitionParticipantForm')?.reset();
  const participantIds = new Set((PAGE_DATA.participants || []).map((p) => Number(p.student_id)));
  const options = (PAGE_OPTIONS.student_options || []).map((s) => ({
    ...s,
    disabled: participantIds.has(Number(s.id)),
  }));
  if (!competitionParticipantManager && qs('competitionParticipantStudentSelectors')) {
    competitionParticipantManager = createDynamicStudentSelectorManager({
      containerId: 'competitionParticipantStudentSelectors',
      addButtonId: 'addCompetitionParticipantStudentSelectBtn',
      modalId: 'competitionParticipantModal',
      inputName: 'student_ids[]',
      optionsList: options,
    });
  }
  competitionParticipantManager?.reset();
}
function closeCompetitionParticipantModal() { closeModal('competitionParticipantModal'); }
function formatIsoDate(date) {
  if (!(date instanceof Date)) return '';
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

function renderCompetitionReport(data = {}) {
  const counts = {
    competitions: Number(data.competitions_count || 0),
    participants: Number(data.participants_count || 0),
    winners: Number(data.winners_count || 0),
  };
  if (qs('reportCompetitionsCount')) qs('reportCompetitionsCount').textContent = String(counts.competitions);
  if (qs('reportParticipantsCount')) qs('reportParticipantsCount').textContent = String(counts.participants);
  if (qs('reportWinnersCount')) qs('reportWinnersCount').textContent = String(counts.winners);

  const names = data.period_names || {};
  const renderNames = (targetId, list) => {
    const el = qs(targetId);
    if (!el) return;
    const items = Array.isArray(list) ? list : [];
    el.innerHTML = items.length
      ? items.map((name) => `<p>• ${escapeHtml(name)}</p>`).join('')
      : '<p class="text-slate-500">Mavjud emas.</p>';
  };
  renderNames('reportPastNames', names.past || []);
  renderNames('reportUpcoming15Names', names.upcoming_15 || []);
  renderNames('reportUpcomingAfter15Names', names.upcoming_after_15 || []);
}

async function loadCompetitionReport(dateFrom = '', dateTo = '') {
  const params = new URLSearchParams();
  if (dateFrom) params.set('date_from', dateFrom);
  if (dateTo) params.set('date_to', dateTo);
  const url = `../get/competition_report.php${params.toString() ? `?${params.toString()}` : ''}`;
  const res = await apiFetch(url);
  if (!res.success) {
    toast('error', res.message || 'Hisobotni olishda xatolik.');
    return;
  }
  renderCompetitionReport(res.data || {});
}

function initCompetitionReportDateRange() {
  const input = qs('competitionReportDateRange');
  if (!input || typeof flatpickr === 'undefined') return;
  if (competitionReportPicker) {
    competitionReportPicker.destroy();
    competitionReportPicker = null;
  }
  input.value = '';
  competitionReportPicker = flatpickr(input, {
    mode: 'range',
    dateFormat: 'Y-m-d',
    allowInput: false,
    onClose: (selectedDates) => {
      if (!Array.isArray(selectedDates) || selectedDates.length !== 2) {
        loadCompetitionReport('', '');
        return;
      }
      const [fromDate, toDate] = selectedDates;
      loadCompetitionReport(formatIsoDate(fromDate), formatIsoDate(toDate));
    },
  });
}

function openCompetitionReportModal() {
  openModal('competitionReportModal');
  initCompetitionReportDateRange();
  loadCompetitionReport('', '');
}
function closeCompetitionReportModal() { closeModal('competitionReportModal'); }

function toggleCompetitionCashInput() {
  const select = qs('competitionResultTypeSelect');
  const cashInput = qs('competitionResultCashInput');
  if (!select || !cashInput) return;
  const selected = select.options[select.selectedIndex];
  const code = selected?.dataset?.code || '';
  const isCash = code === 'cash';
  cashInput.disabled = !isCash;
  cashInput.required = isCash;
  if (!isCash) cashInput.value = '';
}

function setupAutoFilters() {
  const forms = Array.from(document.querySelectorAll('form[method="get"]')).filter((form) => form.querySelector('input[name="page"]'));
  forms.forEach((form) => {
    const submitForm = () => {
      if (typeof form.requestSubmit === 'function') form.requestSubmit();
      else form.submit();
    };

    form.querySelectorAll('select').forEach((select) => {
      select.addEventListener('change', submitForm);
    });
    form.querySelectorAll('input[type="date"]').forEach((input) => {
      input.addEventListener('change', submitForm);
    });
    form.querySelectorAll('button').forEach((btn) => {
      const label = (btn.textContent || '').trim().toLowerCase();
      if (label === 'filter') btn.classList.add('hidden');
    });
  });
}

function openScheduleModal() { openModal('scheduleModal'); qs('scheduleForm')?.reset(); const id = qs('scheduleForm')?.querySelector('[name="id"]'); if (id) id.value = ''; }
function closeScheduleModal() { closeModal('scheduleModal'); }
function fillSchedule(item) { openScheduleModal(); const f = qs('scheduleForm'); f.id.value = item.id || ''; f.type.value = item.type || 'weekly'; f.title.value = item.title || ''; f.date.value = item.date || ''; }

function openTeamModal() {
  openModal('teamModal');
  qs('teamForm')?.reset();
  if (!teamManager && qs('teamStudentSelectors')) {
    teamManager = createDynamicStudentSelectorManager({ containerId: 'teamStudentSelectors', addButtonId: 'addTeamStudentSelectBtn', modalId: 'teamModal', inputName: 'student_ids[]' });
  }
  teamManager?.reset();
}
function closeTeamModal() { closeModal('teamModal'); }
function openTeamMemberModal(teamId) { openModal('teamMemberModal'); qs('teamMemberTeamId').value = teamId; initSelect2ForElement(qs('teamMemberStudentSelect'), 'Talabani tanlang', 'teamMemberModal'); }
function closeTeamMemberModal() { closeModal('teamMemberModal'); }

function openProjectModal() {
  openModal('projectModal');
  qs('projectForm')?.reset();
  if (!projectManager && qs('projectStudentSelectors')) {
    projectManager = createDynamicStudentSelectorManager({ containerId: 'projectStudentSelectors', addButtonId: 'addProjectStudentSelectBtn', modalId: 'projectModal', inputName: 'student_ids[]' });
  }
  projectManager?.reset();
}
function closeProjectModal() { closeModal('projectModal'); }
function openProjectMemberModal(projectId) { openModal('projectMemberModal'); qs('projectMemberProjectId').value = projectId; initSelect2ForElement(qs('projectMemberStudentSelect'), 'Talabani tanlang', 'projectMemberModal'); }
function closeProjectMemberModal() { closeModal('projectMemberModal'); }



function setupScheduleViewToggle() {
  const tableWrap = qs('scheduleTableWrap');
  const grid = qs('scheduleGrid');
  const listBtn = qs('scheduleViewListBtn');
  const gridBtn = qs('scheduleViewGridBtn');
  if (!tableWrap || !grid || !listBtn || !gridBtn) return;

  const setMode = (mode) => {
    const isGrid = mode === 'grid';
    tableWrap.classList.toggle('hidden', isGrid);
    grid.classList.toggle('hidden', !isGrid);
    listBtn.classList.toggle('bg-white', !isGrid);
    listBtn.classList.toggle('shadow', !isGrid);
    gridBtn.classList.toggle('bg-white', isGrid);
    gridBtn.classList.toggle('shadow', isGrid);
  };

  listBtn.addEventListener('click', () => setMode('list'));
  gridBtn.addEventListener('click', () => setMode('grid'));
  setMode('list');
}

function setupCharts() {
  const stats = PAGE_DATA.stats || {};
  if (typeof Chart === 'undefined') return;

  const dist = stats.result_distribution || { first: 0, second: 0, third: 0 };

  const dashboardCanvas = qs('dashboardChart');
  if (dashboardCanvas) {
    new Chart(dashboardCanvas, {
      type: 'bar',
      data: {
        labels: ["1-o'rin", "2-o'rin", "3-o'rin"],
        datasets: [{ data: [dist.first || 0, dist.second || 0, dist.third || 0], backgroundColor: ['#047857', '#0ea5e9', '#f59e0b'], borderRadius: 8 }],
      },
      options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
    });
  }

  const analyticsCanvas = qs('analyticsChart');
  if (analyticsCanvas) {
    new Chart(analyticsCanvas, {
      type: 'doughnut',
      data: { labels: ["1-o'rin", "2-o'rin", "3-o'rin"], datasets: [{ data: [dist.first || 0, dist.second || 0, dist.third || 0], backgroundColor: ['#16a34a', '#0284c7', '#f59e0b'] }] },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } },
    });
  }
}

function setupTableActions() {
  document.addEventListener('click', async (event) => {
    const btn = event.target.closest('button');
    if (btn) {
      if (btn.classList.contains('js-student-edit')) return editStudent(parseDataItem(btn.dataset.item));
      if (btn.classList.contains('js-student-delete')) {
        const ok = await confirmAction(); if (!ok.isConfirmed) return;
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0');
        const res = await apiFetch('../delete/student.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
      if (btn.classList.contains('js-resident-open')) return openResidentModal(parseDataItem(btn.dataset.item));
      if (btn.classList.contains('js-resident-delete')) {
        const ok = await confirmAction(); if (!ok.isConfirmed) return;
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0');
        const res = await apiFetch('../delete/resident.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
      if (btn.classList.contains('js-course-student-open')) return openCourseStudentModal(parseDataItem(btn.dataset.item));
      if (btn.classList.contains('js-course-status-toggle')) {
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0'); fd.append('status', btn.dataset.status || 'active');
        const res = await apiFetch('../update/course_student_status.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
      if (btn.classList.contains('js-course-student-delete')) {
        const ok = await confirmAction(); if (!ok.isConfirmed) return;
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0');
        const res = await apiFetch('../delete/course_student.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
      if (btn.classList.contains('js-room-edit')) return fillRoom(parseDataItem(btn.dataset.item));
      if (btn.classList.contains('js-room-delete')) {
        const ok = await confirmAction(); if (!ok.isConfirmed) return;
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0');
        const res = await apiFetch('../delete/room.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
      if (btn.classList.contains('js-course-edit')) return fillCourse(parseDataItem(btn.dataset.item));
      if (btn.classList.contains('js-course-delete')) {
        const ok = await confirmAction(); if (!ok.isConfirmed) return;
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0');
        const res = await apiFetch('../delete/course.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
      if (btn.classList.contains('js-mentor-edit')) return fillMentor(parseDataItem(btn.dataset.item));
      if (btn.classList.contains('js-mentor-delete')) {
        const ok = await confirmAction(); if (!ok.isConfirmed) return;
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0');
        const res = await apiFetch('../delete/mentor.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
      if (btn.classList.contains('js-direction-edit')) return fillDirection(parseDataItem(btn.dataset.item));
      if (btn.classList.contains('js-direction-delete')) {
        const ok = await confirmAction(); if (!ok.isConfirmed) return;
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0');
        const res = await apiFetch('../delete/direction.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
      if (btn.classList.contains('js-status-edit')) return fillStatus(parseDataItem(btn.dataset.item));
      if (btn.classList.contains('js-status-delete')) {
        const ok = await confirmAction(); if (!ok.isConfirmed) return;
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0');
        const res = await apiFetch('../delete/status.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
      if (btn.classList.contains('js-competition-edit')) return fillCompetition(parseDataItem(btn.dataset.item));
      if (btn.classList.contains('js-competition-delete')) {
        const ok = await confirmAction(); if (!ok.isConfirmed) return;
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0');
        const res = await apiFetch('../delete/competition.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
      if (btn.classList.contains('js-schedule-edit')) return fillSchedule(parseDataItem(btn.dataset.item));
      if (btn.classList.contains('js-schedule-delete')) {
        const ok = await confirmAction(); if (!ok.isConfirmed) return;
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0');
        const res = await apiFetch('../delete/schedule.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
      if (btn.classList.contains('js-participant-delete')) {
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0');
        const res = await apiFetch('../delete/competition_participant.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
      if (btn.classList.contains('js-result-delete')) {
        const ok = await confirmAction(); if (!ok.isConfirmed) return;
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0');
        const res = await apiFetch('../delete/competition_result.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
      if (btn.classList.contains('js-team-delete')) {
        const ok = await confirmAction(); if (!ok.isConfirmed) return;
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0');
        const res = await apiFetch('../delete/team.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
      if (btn.classList.contains('js-team-member-remove')) {
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0');
        const res = await apiFetch('../delete/team_member.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
      if (btn.classList.contains('js-team-member-open')) return openTeamMemberModal(btn.dataset.teamId || '0');
      if (btn.classList.contains('js-project-delete')) {
        const ok = await confirmAction(); if (!ok.isConfirmed) return;
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0');
        const res = await apiFetch('../delete/project.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
      if (btn.classList.contains('js-project-member-remove')) {
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0');
        const res = await apiFetch('../delete/project_member.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
      if (btn.classList.contains('js-project-member-open')) return openProjectMemberModal(btn.dataset.projectId || '0');
      if (btn.classList.contains('js-payment-edit')) {
        return fillPayment(JSON.parse(btn.dataset.item));
      }
      if (btn.classList.contains('js-payment-delete')) {
        const ok = await confirmAction(); if (!ok.isConfirmed) return;
        const fd = new FormData(); fd.append('id', btn.dataset.id || '0');
        const res = await apiFetch('../delete/payment.php', { method: 'POST', body: fd });
        toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
        return;
      }
    }

    const card = event.target.closest('[data-open-competition]');
    if (!card) return;
    if (event.target.closest('[data-competition-actions]')) return;
    window.location.href = `index.php?page=competition_detail&id=${card.dataset.openCompetition}`;
  });

  document.querySelectorAll('th[data-sort]').forEach((th) => {
    th.style.cursor = 'pointer';
    th.addEventListener('click', () => {
      const sortBy = th.dataset.sort;
      const url = new URL(window.location.href);
      const currentSort = url.searchParams.get('sort_by');
      const currentOrder = url.searchParams.get('sort_order') || 'asc';
      const nextOrder = (currentSort === sortBy && currentOrder === 'asc') ? 'desc' : 'asc';
      url.searchParams.set('sort_by', sortBy);
      url.searchParams.set('sort_order', nextOrder);
      window.location.href = url.toString();
    });
  });
}

function setupForms() {
  qs('studentEntryYear')?.addEventListener('input', updateStudentAutoCourse);

  qs('studentForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await apiFetch(fd.get('id') ? '../update/student.php' : '../insert/student.php', { method: 'POST', body: fd });
    toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
  });

  qs('residentForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const res = await apiFetch('../insert/resident.php', { method: 'POST', body: new FormData(e.target) });
    toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
  });

  qs('residentBulkForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (residentManager?.hasDuplicate()) return toast('error', 'Bir xil talaba ikki marta tanlandi.');
    const selected = residentManager?.selectedValues() || [];
    if (!selected.length) return toast('error', 'Kamida bitta talaba tanlang.');

    const options = PAGE_OPTIONS.resident_students || [];
    const nameMap = new Map(options.map((s) => [String(s.id), s.fio || `ID ${s.id}`]));
    const roomId = String(e.target.room_id?.value || '');
    const computerNumber = String(e.target.computer_number?.value || '');

    let successCount = 0;
    const errors = [];
    for (const studentId of selected) {
      const fd = new FormData();
      fd.append('student_id', studentId);
      fd.append('room_id', roomId);
      fd.append('computer_number', computerNumber);
      const res = await apiFetch('../insert/resident.php', { method: 'POST', body: fd });
      if (res.success) {
        successCount += 1;
      } else {
        const label = nameMap.get(String(studentId)) || `ID ${studentId}`;
        errors.push(`${label}: ${res.message}`);
      }
    }

    if (!errors.length) {
      toast('success', `${successCount} ta rezident qo'shildi.`);
      reloadPage();
      return;
    }

    Swal.fire({
      icon: successCount > 0 ? 'warning' : 'error',
      title: 'Qo\'shishda xatolik bor',
      html: `<div class="text-left text-sm">Muvaffaqiyatli: ${successCount}<br>Xatolik: ${errors.length}<br><br>${errors.slice(0, 8).map((msg) => escapeHtml(msg)).join('<br>')}</div>`,
    });
  });

  qs('courseStudentForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const res = await apiFetch('../insert/course_student.php', { method: 'POST', body: new FormData(e.target) });
    toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
  });

  qs('courseStudentBulkForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (courseStudentManager?.hasDuplicate()) return toast('error', 'Bir xil talaba ikki marta tanlandi.');
    const selected = courseStudentManager?.selectedValues() || [];
    if (!selected.length) return toast('error', 'Kamida bitta talaba tanlang.');

    const options = PAGE_OPTIONS.course_students_options || [];
    const nameMap = new Map(options.map((s) => [String(s.id), s.fio || `ID ${s.id}`]));
    const courseId = String(e.target.course_id?.value || '');
    if (!courseId) return toast('error', 'Kursni tanlang.');
    const roomId = String(e.target.room_id?.value || '');

    let successCount = 0;
    const errors = [];
    for (const studentId of selected) {
      const fd = new FormData();
      fd.append('student_id', studentId);
      fd.append('course_id', courseId);
      fd.append('room_id', roomId);
      const res = await apiFetch('../insert/course_student.php', { method: 'POST', body: fd });
      if (res.success) {
        successCount += 1;
      } else {
        const label = nameMap.get(String(studentId)) || `ID ${studentId}`;
        errors.push(`${label}: ${res.message}`);
      }
    }

    if (!errors.length) {
      toast('success', `${successCount} ta kurs o'quvchi qo'shildi.`);
      reloadPage();
      return;
    }

    Swal.fire({
      icon: successCount > 0 ? 'warning' : 'error',
      title: 'Qo\'shishda xatolik bor',
      html: `<div class="text-left text-sm">Muvaffaqiyatli: ${successCount}<br>Xatolik: ${errors.length}<br><br>${errors.slice(0, 8).map((msg) => escapeHtml(msg)).join('<br>')}</div>`,
    });
  });

  qs('directionForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await apiFetch(fd.get('id') ? '../update/direction.php' : '../insert/direction.php', { method: 'POST', body: fd });
    toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
  });

  qs('statusForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await apiFetch(fd.get('id') ? '../update/status.php' : '../insert/status.php', { method: 'POST', body: fd });
    toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
  });

  qs('roomForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await apiFetch(fd.get('id') ? '../update/room.php' : '../insert/room.php', { method: 'POST', body: fd });
    toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
  });

  qs('courseForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!e.target.querySelectorAll('input[name="days[]"]:checked').length) return toast('error', 'Kamida bitta kun tanlang.');
    const fd = new FormData(e.target);
    const res = await apiFetch(fd.get('id') ? '../update/course.php' : '../insert/course.php', { method: 'POST', body: fd });
    toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
  });

  qs('mentorForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await apiFetch(fd.get('id') ? '../update/mentor.php' : '../insert/mentor.php', { method: 'POST', body: fd });
    toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
  });

  qs('competitionForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await apiFetch(fd.get('id') ? '../update/competition.php' : '../insert/competition.php', { method: 'POST', body: fd });
    toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
  });

  qs('competitionNotifyForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (notifyManager?.hasDuplicate()) return toast('error', 'Bir xil talaba takror tanlangan.');
    const fd = new FormData(e.target);
    const selected = notifyManager?.selectedValues() || [];
    if (!selected.length) return toast('error', 'Kamida bitta talaba tanlang.');
    const res = await apiFetch('../insert/competition_message.php', { method: 'POST', body: fd });
    toast(res.success ? 'success' : 'error', res.message);
    if (res.success) {
      const sent = res.data?.sent_count || 0;
      const failed = res.data?.failed_count || 0;
      Swal.fire({ icon: 'info', title: 'Yuborish natijasi', text: `Yuborildi: ${sent}. Xatolik: ${failed}.` });
      reloadPage();
    }
  });

  qs('competitionResultForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (competitionResultManager?.hasDuplicate()) return toast('error', 'Bir xil talaba ikki marta tanlandi.');
    const selected = competitionResultManager?.selectedValues() || [];
    if (!selected.length) return toast('error', 'Kamida bitta talaba tanlang.');
    const fd = new FormData(e.target);
    const cash = fd.get('cash_amount')?.replace(/\s/g, '');
    if (cash) fd.set('cash_amount', cash);
    const res = await apiFetch('../insert/competition_result.php', { method: 'POST', body: fd });
    toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
  });

  qs('competitionParticipantForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (competitionParticipantManager?.hasDuplicate()) return toast('error', 'Bir xil talaba ikki marta tanlandi.');
    const selected = competitionParticipantManager?.selectedValues() || [];
    if (!selected.length) return toast('error', 'Kamida bitta talaba tanlang.');
    const res = await apiFetch('../insert/competition_participant.php', { method: 'POST', body: new FormData(e.target) });
    toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
  });

  qs('scheduleForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await apiFetch(fd.get('id') ? '../update/schedule.php' : '../insert/schedule.php', { method: 'POST', body: fd });
    toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
  });

  qs('teamForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (teamManager?.hasDuplicate()) return toast('error', 'Bir xil talaba ikki marta tanlandi.');
    const selected = teamManager?.selectedValues() || [];
    if (!selected.length) return toast('error', 'Kamida bitta talaba tanlang.');
    const res = await apiFetch('../insert/team.php', { method: 'POST', body: new FormData(e.target) });
    toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
  });

  qs('teamMemberForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const res = await apiFetch('../insert/team_member.php', { method: 'POST', body: new FormData(e.target) });
    toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
  });

  qs('projectForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (projectManager?.hasDuplicate()) return toast('error', 'Bir xil talaba ikki marta tanlandi.');
    const selected = projectManager?.selectedValues() || [];
    if (!selected.length) return toast('error', 'Kamida bitta talaba tanlang.');
    const res = await apiFetch('../insert/project.php', { method: 'POST', body: new FormData(e.target) });
    toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
  });

  qs('projectMemberForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const res = await apiFetch('../insert/project_member.php', { method: 'POST', body: new FormData(e.target) });
    toast(res.success ? 'success' : 'error', res.message); if (res.success) reloadPage();
  });

  qs('logoutBtn')?.addEventListener('click', async () => {
    const res = await apiFetch('../api/auth.php?action=logout', { method: 'POST' });
    if (res.success) window.location.href = '../index.php';
  });

  qs('paymentProjectSelect')?.addEventListener('change', (e) => {
    loadProjectMembers(e.target.value, qs('paymentStudentSelect'));
  });

  qs('paymentForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    // Format amount (remove spaces)
    const amountVal = fd.get('amount');
    if (amountVal) {
      fd.set('amount', String(amountVal).replace(/\s/g, ''));
    }
    const id = fd.get('id');
    const url = id ? '../update/payment.php' : '../insert/payment.php';
    const res = await apiFetch(url, { method: 'POST', body: fd });
    toast(res.success ? 'success' : 'error', res.message);
    if (res.success) { closePaymentModal(); reloadPage(); }
  });
}

function setupPageButtons() {
  qs('openResidentBulkModalBtn')?.addEventListener('click', openResidentBulkModal);
  qs('openCourseStudentBulkModalBtn')?.addEventListener('click', openCourseStudentBulkModal);
  qs('openCompetitionReportBtn')?.addEventListener('click', openCompetitionReportModal);
  qs('competitionResultTypeSelect')?.addEventListener('change', toggleCompetitionCashInput);
  qs('competitionNotifyBtn')?.addEventListener('click', () => {
    if (!notifyManager && qs('notifyStudentSelectors')) {
      notifyManager = createDynamicStudentSelectorManager({ containerId: 'notifyStudentSelectors', addButtonId: 'addNotifyStudentSelectBtn', modalId: 'competitionNotifyModal', inputName: 'student_ids[]' });
    }
    openCompetitionNotifyModal();
  });
  qs('competitionResultBtn')?.addEventListener('click', openCompetitionResultModal);
  qs('competitionParticipantAddBtn')?.addEventListener('click', openCompetitionParticipantModal);
  qs('openTeamCreateModalBtn')?.addEventListener('click', openTeamModal);
  qs('openProjectCreateModalBtn')?.addEventListener('click', openProjectModal);
}

function setupInlineSelectActions() {
  document.addEventListener('change', async (event) => {
    const target = event.target;
    if (!(target instanceof HTMLSelectElement)) return;

    if (target.classList.contains('js-team-level-change')) {
      const fd = new FormData();
      fd.append('id', target.dataset.id || '0');
      fd.append('level', target.value || 'middle');
      const res = await apiFetch('../update/team_level.php', { method: 'POST', body: fd });
      toast(res.success ? 'success' : 'error', res.message);
      return;
    }

    if (target.classList.contains('js-project-status-change')) {
      const fd = new FormData();
      fd.append('id', target.dataset.id || '0');
      fd.append('status', target.value || 'boshlanish');
      const res = await apiFetch('../update/project_status.php', { method: 'POST', body: fd });
      toast(res.success ? 'success' : 'error', res.message);
    }
  });
}

setupSidebar();
setupProfileDropdown();
setupCalendar();
setupGlobalSearch();
setupPhoneInputs();
setupNumberInputs();
updateStudentAutoCourse();
setupTableActions();
setupForms();
setupPageButtons();
setupInlineSelectActions();
setupAutoFilters();
setupScheduleViewToggle();
setupCharts();

// Global click listeners
document.addEventListener('click', (e) => {
  // Close modals on outside click
  if (e.target.classList.contains('admin-modal')) {
    const closeBtn = e.target.querySelector('[id$="CloseBtn"], [id$="-close"]');
    if (closeBtn) closeBtn.click();
    else e.target.classList.add('hidden');
  }

  // Close profile dropdown on outside click
  const profileBtn = qs('profileMenuBtn');
  const profileDropdown = qs('profileDropdown');
  if (profileBtn && profileDropdown && !profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
    profileDropdown.classList.remove('opacity-100', 'translate-y-0');
    profileDropdown.classList.add('opacity-0', 'translate-y-1', 'pointer-events-none');
  }
});

window.openStudentModal = openStudentModal;
window.closeStudentModal = closeStudentModal;
window.openResidentModal = openResidentModal;
window.closeResidentModal = closeResidentModal;
window.closeResidentBulkModal = closeResidentBulkModal;
window.openCourseStudentModal = openCourseStudentModal;
window.closeCourseStudentModal = closeCourseStudentModal;
window.closeCourseStudentBulkModal = closeCourseStudentBulkModal;
window.openRoomModal = openRoomModal;
window.closeRoomModal = closeRoomModal;
window.openCourseModal = openCourseModal;
window.closeCourseModal = closeCourseModal;
window.openMentorModal = openMentorModal;
window.closeMentorModal = closeMentorModal;
window.openDirectionModal = openDirectionModal;
window.closeDirectionModal = closeDirectionModal;
window.openStatusModal = openStatusModal;
window.closeStatusModal = closeStatusModal;
window.openCompetitionModal = openCompetitionModal;
window.closeCompetitionModal = closeCompetitionModal;
window.closeCompetitionNotifyModal = closeCompetitionNotifyModal;
window.closeCompetitionResultModal = closeCompetitionResultModal;
window.closeCompetitionParticipantModal = closeCompetitionParticipantModal;
window.closeCompetitionReportModal = closeCompetitionReportModal;
window.openScheduleModal = openScheduleModal;
window.closeScheduleModal = closeScheduleModal;
window.closeTeamModal = closeTeamModal;
window.closeTeamMemberModal = closeTeamMemberModal;
window.closeProjectModal = closeProjectModal;
window.closeProjectMemberModal = closeProjectMemberModal;
window.openPaymentModal = openPaymentModal;
window.closePaymentModal = closePaymentModal;
