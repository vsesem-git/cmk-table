(() => {
  'use strict';

  const API = 'api.php';
  const COLUMNS_API = 'columns.php';
  const TAXONOMY_API = 'taxonomies.php';
  const SETTINGS_API = 'settings.php';
  const LANDING_API = 'landing_pages.php';
  const LANDING_UPLOAD_API = 'landing_upload.php';

  const MONTHS_RU = ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];

  // Базовые столбцы. Пользовательские (добавленные через "+ Столбец") подмешиваются в COLUMNS во время init().
  const BASE_COLUMNS = [
    { key: 'price',             label: 'Цена',          type: 'money', filterable: true,  sortable: true,  width: '100px' },
    { key: 'organizer',         label: 'Организатор',  type: 'badge', filterable: true,  sortable: false, width: '110px' },
    { key: 'date',              label: 'Дата',          type: 'date',  filterable: true,  sortable: true,  width: '190px' },
    { key: 'speaker',           label: 'Спикер',        type: 'text',  filterable: true,  sortable: false, width: '150px' },
    { key: 'title',             label: 'Вебинар',       type: 'text',  filterable: false, sortable: false, minWidth: '280px' },
    { key: 'link_participant',  label: 'Участникам',    type: 'link',  filterable: false, sortable: false, width: '130px' },
    { key: 'link_host',         label: 'Организатору',  type: 'link',  filterable: false, sortable: false, width: '130px' },
    { key: 'link_materials',    label: 'Материалы',     type: 'link',  filterable: false, sortable: false, width: '120px' },
    { key: 'link_recording',    label: 'Запись',        type: 'link',  filterable: false, sortable: false, width: '120px' },
    { key: 'moderator_code',    label: 'Код модератора', type: 'text', filterable: false, sortable: false, width: '130px' },
    { key: 'doc_official_letter', label: 'Официальное письмо', type: 'link', filterable: false, sortable: false, width: '150px' },
    { key: 'doc_program',         label: 'Программа',          type: 'link', filterable: false, sortable: false, width: '130px' },
    { key: 'doc_invitation',      label: 'Приглашение',        type: 'link', filterable: false, sortable: false, width: '130px' },
    { key: 'published_on_site',   label: 'Размещён на сайте',  type: 'badge', filterable: true, sortable: false, width: '150px' },
    { key: 'mailing_list',        label: 'Рассылка',           type: 'tags', filterable: true, sortable: false, width: '150px' },
    { key: 'direction',           label: 'Направление',        type: 'badge', filterable: true, sortable: false, width: '140px' },
    { key: 'subscription',     label: 'Входит в подписку',  type: 'badge', filterable: true, sortable: false, width: '150px' },
    { key: 'actions',           label: 'Действия',          type: 'actions', filterable: false, sortable: false, width: '110px' },
  ];

  let COLUMNS = BASE_COLUMNS.slice();
  let customColumns = [];
  let organizersList = [];
  let mailingListsList = [];
  let directionsList = [];
  let speakersList = [];
  let publishedOnSiteList = [];
  let subscriptionList = [];

  let rows = [];
  let filters = {};
  let search = '';
  let sortState = { col: 'date', dir: 'asc' };
  let openPopoverCol = null;
  let selectedIds = new Set();
  let settings = defaultSettings();

  const $ = sel => document.querySelector(sel);
  const $$ = sel => Array.from(document.querySelectorAll(sel));

  function defaultSettings() {
    return {
      hidden: [], linkAsText: [], colWidths: {}, tableWidthMode: 'scroll', landingOutputBase: '',
      landingLinkLength: 6,
      landingPortsText: 'Откройте порты TCP:443 UDP:16384 - 32768',
      landingSupportText: 'Техническая поддержка',
      landingSupportPhone: '+7 910 154 76 86',
      landingSupportSchedule: '(гарантируется только в день вебинара с 10:00 до 15:00)',
      landingBrowserText: 'Используйте браузеры Yandex-Браузер / Google Chrome / Chromium / Firefox.',
      landingTooltipRecording: 'Организатор еще не разместил запись трансляции. Обычно это занимает от 2 до 5 дней. Обновите страницу Ctrl+F.',
      landingTooltipMaterials: 'Организатор еще не разместил материалы вебинара. Обновите страницу Ctrl+F5.',
      landingFooterText: '© 2026 Страница создана по материалам вебинара',
      registryWidth: '1400',
      quickLinks: [],
      quickLinksOrder: [],
      mailRecipients: [],
      sendExportFields: ['date', 'link_participant', 'link_host', 'moderator_code'],
      columnOrder: [],
      columnLabels: {},
      activeYearFilter: '',
      activeFilters: {},
      landingDefaultTemplate: {
        themePreset: 'wood', dateFontPx: 54, titleFontPx: 46, metaFontPx: 17, buttonFontPx: 16, footerFontPx: 14,
        backgroundImage: '', backgroundIntensity: 100, titleWidthPct: 100,
      },
    };
  }

  const QUICKLINK_ICON_CHOICES = [
    { value: 'fa-solid fa-link', label: '🔗 Ссылка' },
    { value: 'fa-solid fa-building', label: '🏢 Сайт / CRM' },
    { value: 'fa-solid fa-folder-open', label: '🗂️ Папка' },
    { value: 'fa-solid fa-chart-line', label: '📈 Аналитика' },
    { value: 'fa-solid fa-gear', label: '⚙️ Настройки' },
    { value: 'fa-solid fa-globe', label: '🌐 Сайт' },
    { value: 'fa-solid fa-database', label: '🗄️ База данных' },
    { value: 'fa-solid fa-users', label: '👥 Пользователи' },
    { value: 'fa-solid fa-envelope', label: '✉️ Почта' },
    { value: 'fa-solid fa-arrow-up-right-from-square', label: '↗️ Внешняя ссылка' },
  ];

  function applyRegistryWidth() {
    const app = document.querySelector('.app');
    if (!app) return;
    app.style.maxWidth = settings.registryWidth === 'full' ? 'none' : `${settings.registryWidth}px`;
  }

  function populateTemplatesDropdowns() {
    const templates = settings.landingTemplates || {};
    const keys = Object.keys(templates);
    
    let options = '<option value="">-- Выберите шаблон --</option>';
    keys.forEach(k => {
      options += `<option value="${escapeHtml(k)}">${escapeHtml(k)}</option>`;
    });
    
    const lpSel = $('#lp_templateSelect');
    if (lpSel) lpSel.innerHTML = options;
    
    const bulkSel = $('#bulk_lp_templateSelect');
    if (bulkSel) {
      bulkSel.innerHTML = '<option value="">-- Шаблон по умолчанию --</option>' + keys.map(k => `<option value="${escapeHtml(k)}">${escapeHtml(k)}</option>`).join('');
    }
  }

  async function loadSettingsFromServer() {
    try {
      const res = await fetch(SETTINGS_API);
      const json = await res.json();
      const defaults = defaultSettings();
      if (json.ok && json.data) {
        settings = { ...defaults, ...json.data, colWidths: { ...defaults.colWidths, ...(json.data.colWidths || {}) } };
        populateTemplatesDropdowns();
      }
    } catch (e) { /* остаёмся на значениях по умолчанию */ }
  }

  let saveSettingsTimer = null;
  function saveSettings() {
    clearTimeout(saveSettingsTimer);
    saveSettingsTimer = setTimeout(async () => {
      try { await fetch(SETTINGS_API, { method: 'POST', body: JSON.stringify(settings) }); }
      catch (e) { /* сеть недоступна — настройки останутся только локально до следующей попытки */ }
    }, 350);
  }

  function effectiveWidthPx(col) {
    if (settings.colWidths[col.key]) return Number(settings.colWidths[col.key]);
    return parseInt(col.width || col.minWidth || '140px', 10) || 140;
  }

  function colorFromList(list, name) {
    const found = list.find(item => item.name === name);
    return found ? found.color : '#8A97A6';
  }
  const orgColor = name => colorFromList(organizersList, name);
  const mailingListColor = name => colorFromList(mailingListsList, name);
  const directionColor = name => colorFromList(directionsList, name);
  const publishedColor = name => colorFromList(publishedOnSiteList, name);
  const subscriptionColor = name => colorFromList(subscriptionList, name);

  function badgeColorForColumn(col, v) {
    if (col.key === 'direction') return directionColor(v);
    if (col.key === 'published_on_site') return publishedColor(v);
    if (col.key === 'subscription') return subscriptionColor(v);
    return orgColor(v);
  }

  async function loadDirections() {
    try {
      const res = await fetch(`${TAXONOMY_API}?type=direction`);
      const json = await res.json();
      directionsList = json.ok ? json.data : [];
    } catch (e) { directionsList = []; }
  }

  async function loadSpeakers() {
    try {
      const res = await fetch(`${TAXONOMY_API}?type=speaker`);
      const json = await res.json();
      speakersList = json.ok ? json.data : [];
    } catch (e) { speakersList = []; }
  }

  async function loadPublishedOnSiteList() {
    try {
      const res = await fetch(`${TAXONOMY_API}?type=published_on_site`);
      const json = await res.json();
      publishedOnSiteList = json.ok ? json.data : [];
    } catch (e) { publishedOnSiteList = []; }
  }

  async function loadInSubscriptionList() {
    try {
      const res = await fetch(`${TAXONOMY_API}?type=subscription`);
      const json = await res.json();
      subscriptionList = json.ok ? json.data : [];
    } catch (e) { subscriptionList = []; }
  }

  async function loadMailingLists() {
    try {
      const res = await fetch(`${TAXONOMY_API}?type=mailing_list`);
      const json = await res.json();
      mailingListsList = json.ok ? json.data : [];
    } catch (e) { mailingListsList = []; }
  }

  function contrastText(hex) {
    const c = String(hex || '').replace('#', '');
    if (c.length !== 6) return '#142322';
    const r = parseInt(c.substr(0, 2), 16), g = parseInt(c.substr(2, 2), 16), b = parseInt(c.substr(4, 2), 16);
    const lum = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return lum > 0.6 ? '#142322' : '#FFFFFF';
  }

  function formatMoney(v) {
    return (Number(v) || 0).toLocaleString('ru-RU');
  }

  // "09 июля 2026 г."
  function formatDateRu(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr + 'T00:00:00');
    if (isNaN(d)) return dateStr;
    return `${String(d.getDate()).padStart(2,'0')} ${MONTHS_RU[d.getMonth()]} ${d.getFullYear()} г.`;
  }

  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  function visibleColumns() {
    return COLUMNS.filter(c => !settings.hidden.includes(c.key));
  }

  // ---------- Data / columns loading ----------

  async function loadColumns() {
    const res = await fetch(COLUMNS_API);
    const json = await res.json();
    customColumns = json.ok ? json.data : [];
    COLUMNS = BASE_COLUMNS.concat(customColumns.map(c => ({
      key: c.key, label: c.label, type: c.type || 'text',
      filterable: true, sortable: false, width: '140px', removable: true,
    })));
  }

  async function loadOrganizers() {
    const res = await fetch(`${TAXONOMY_API}?type=organizer`);
    const json = await res.json();
    organizersList = json.ok ? json.data : [];
  }

  async function loadRows() {
    const res = await fetch(API);
    const json = await res.json();
    if (!json.ok) throw new Error(json.error || 'Не удалось загрузить данные');
    rows = json.data;
  }

  // ---------- Filtering pipeline ----------

  function passesFilters(row, ignoreCol = null) {
    for (const col in filters) {
      if (col === ignoreCol) continue;
      const set = filters[col];
      if (!set || set.size === 0) continue;
      if (col === 'mailing_list') {
        const tags = String(row.mailing_list || '').split(',').map(t => t.trim()).filter(Boolean);
        if (!tags.some(t => set.has(t))) return false;
      } else if (!set.has(String(row[col] ?? ''))) {
        return false;
      }
    }
    return true;
  }

  function passesSearch(row) {
    if (!search) return true;
    const q = search.toLowerCase();
    const haystack = [
      row.title, row.speaker, row.organizer,
      row.price, formatMoney(row.price),
    ].join(' ').toLowerCase();
    return haystack.includes(q);
  }

  function passesYearFilter(row) {
    if (!settings.activeYearFilter) return true;
    return String(row.date || '').slice(0, 4) === settings.activeYearFilter;
  }

  function persistFilters() {
    const out = {};
    for (const col in filters) {
      if (filters[col] && filters[col].size > 0) out[col] = Array.from(filters[col]);
    }
    settings.activeFilters = out;
    saveSettings();
  }

  function loadFiltersFromSettings() {
    filters = {};
    const stored = settings.activeFilters || {};
    for (const col in stored) {
      if (Array.isArray(stored[col]) && stored[col].length) filters[col] = new Set(stored[col]);
    }
  }

  function visibleRows() {
    let out = rows.filter(r => passesFilters(r) && passesSearch(r) && passesYearFilter(r));
    if (sortState.col) {
      const dir = sortState.dir === 'asc' ? 1 : -1;
      out = out.slice().sort((a, b) => {
        const av = a[sortState.col], bv = b[sortState.col];
        if (av < bv) return -1 * dir;
        if (av > bv) return 1 * dir;
        return 0;
      });
    }
    return out;
  }

  // ---------- Rendering: stats / pills ----------

  function renderStats() {
    const vis = visibleRows();
    const today = new Date().toISOString().slice(0, 10);
    const upcoming = rows.filter(r => r.date >= today).sort((a,b) => a.date < b.date ? -1 : 1)[0];

    const quickLinksHtml = orderedQuickLinks().map(l => `
      <a class="stat-chip quicklink-chip" href="${escapeHtml(l.url)}" target="_blank" rel="noopener">
        <i class="${escapeHtml(l.icon)}"></i><span>${escapeHtml(l.label)}</span>
      </a>
    `).join('');

    $('#statStrip').innerHTML = `
      <div class="stat-chip"><div class="label">Показано</div><div class="value">${vis.length} из ${rows.length}</div></div>
      <div class="stat-chip"><div class="label">Ближайший</div><div class="value">${upcoming ? formatDateRu(upcoming.date) : '—'}</div></div>
      ${quickLinksHtml}
    `;
  }

  function renderPills() {
    const pills = [];
    for (const col in filters) {
      const set = filters[col];
      if (!set || set.size === 0) continue;
      const info = COLUMNS.find(c => c.key === col);
      if (!info) continue;
      const vals = Array.from(set);
      const shown = vals.slice(0, 1).map(v => formatCell(info, v)).join(', ');
      const extra = vals.length > 1 ? ` +${vals.length - 1}` : '';
      pills.push(`<span class="filter-pill" data-col="${col}">${info.label}: ${escapeHtml(shown)}${extra}<button data-clear="${col}">×</button></span>`);
    }
    $('#filterPills').innerHTML = pills.join('');
    $('#resetFiltersBtn').style.display = pills.length ? 'inline-flex' : 'none';
    $$('#filterPills [data-clear]').forEach(btn => {
      btn.addEventListener('click', () => { delete filters[btn.dataset.clear]; persistFilters(); renderAll(); });
    });
  }

  function formatCell(col, v) {
    switch (col.type) {
      case 'money': return formatMoney(v) + ' ₽';
      case 'date':  return formatDateRu(v);
      case 'boolean': return (v === '1' || v === true) ? 'Да' : 'Нет';
      default:      return v || '—';
    }
  }

  // ---------- Drag-to-resize columns (as in Excel) ----------

  let resizing = null;

  function onResizerMouseDown(e) {
    const key = e.currentTarget.dataset.resize;
    const th = e.currentTarget.closest('th');
    resizing = { key, startX: e.clientX, startWidth: th.getBoundingClientRect().width };
    document.body.classList.add('col-resizing');
    document.addEventListener('mousemove', onResizerMouseMove);
    document.addEventListener('mouseup', onResizerMouseUp);
    e.preventDefault();
  }

  function onResizerMouseMove(e) {
    if (!resizing) return;
    const delta = e.clientX - resizing.startX;
    settings.colWidths[resizing.key] = Math.max(40, Math.round(resizing.startWidth + delta));
    renderColgroup();
  }

  function onResizerMouseUp() {
    if (!resizing) return;
    resizing = null;
    document.body.classList.remove('col-resizing');
    document.removeEventListener('mousemove', onResizerMouseMove);
    document.removeEventListener('mouseup', onResizerMouseUp);
    saveSettings();
    if (settingsOverlay.style.display === 'flex') renderSettingsList();
  }

  // ---------- Header ----------

  const CHECK_COL_PX = 36;

  function renderColgroup() {
    const cols = visibleColumns();
    const table = $('#dataTable');
    let html = `<col style="width:${CHECK_COL_PX}px">`;

    if (settings.tableWidthMode === 'fit') {
      const total = cols.reduce((s, c) => s + effectiveWidthPx(c), 0) || 1;
      cols.forEach(c => { html += `<col style="width:${(effectiveWidthPx(c) / total * 100).toFixed(3)}%">`; });
      table.style.width = '100%';
    } else {
      cols.forEach(c => { html += `<col style="width:${effectiveWidthPx(c)}px">`; });
      const total = CHECK_COL_PX + cols.reduce((s, c) => s + effectiveWidthPx(c), 0);
      table.style.width = total + 'px';
    }
    table.style.tableLayout = 'fixed';
    $('#tableColgroup').innerHTML = html;
  }

  function renderHead() {
    const cols = visibleColumns();
    let html = '<tr>';
    html += `<th class="col-check"><input type="checkbox" id="selectAllBox" title="Выбрать все"></th>`;
    cols.forEach(col => {
      html += `<th data-col="${col.key}">
        <div class="th-inner">
          <span>${escapeHtml(col.label)}</span>
          <div class="th-controls">
            ${col.sortable ? `<button class="th-btn" data-sort="${col.key}" title="Сортировать">↕</button>` : ''}
            ${col.filterable ? `<button class="th-btn" data-filter="${col.key}" title="Фильтр">▾</button>` : ''}
            ${col.removable ? `<button class="th-btn" data-removecol="${col.key}" title="Удалить столбец">✕</button>` : ''}
          </div>
        </div>
        <div class="col-resizer" data-resize="${col.key}" title="Потяните, чтобы изменить ширину"></div>
      </th>`;
    });
    html += '</tr>';
    $('#tableHead').innerHTML = html;

    $$('.col-resizer').forEach(el => el.addEventListener('mousedown', onResizerMouseDown));

    $('#selectAllBox').addEventListener('change', e => {
      const vis = visibleRows();
      if (e.target.checked) vis.forEach(r => selectedIds.add(r.id));
      else vis.forEach(r => selectedIds.delete(r.id));
      renderTable();
      updateBulkBar();
    });
  }

  function linkPill(url, label, plainText) {
    if (!url) return `<span class="link-empty">—</span>`;
    if (plainText) return escapeHtml(url);
    return `<a class="link-pill" href="${escapeHtml(url)}" target="_blank" rel="noopener">${label} ↗</a>`;
  }

  function daysSince(dateStr) {
    if (!dateStr) return -Infinity;
    const d = new Date(dateStr + 'T00:00:00');
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return Math.floor((today - d) / 86400000);
  }

  function cellHtml(col, row) {
    const v = row[col.key];
    switch (col.type) {
      case 'money':  return `<td class="num">${formatMoney(v)} ₽</td>`;
      case 'badge': {
        const color = badgeColorForColumn(col, v);
        if (!v) return `<td><span class="link-empty">—</span></td>`;
        return `<td><span class="org-badge" style="background:${color};color:${contrastText(color)}">${escapeHtml(v)}</span></td>`;
      }
      case 'date':   return `<td class="date-cell">${formatDateRu(v)}</td>`;
      case 'boolean': {
        const isYes = v === '1' || v === true;
        return `<td><span class="bool-badge ${isYes ? 'yes' : 'no'}">${isYes ? 'Да' : 'Нет'}</span></td>`;
      }
      case 'tags': {
        const tags = String(v || '').split(',').map(t => t.trim()).filter(Boolean);
        if (!tags.length) return `<td><span class="link-empty">—</span></td>`;
        return `<td><div class="tags-cell">${tags.map(t => `<span class="tag-chip" style="background:${mailingListColor(t)}20;color:${mailingListColor(t)};border-color:${mailingListColor(t)}55">${escapeHtml(t)}</span>`).join('')}</div></td>`;
      }
      case 'link': {
        const asText = settings.linkAsText.includes(col.key);
        const label = col.key === 'link_materials' ? 'Файлы' : (col.key === 'link_recording' ? 'Запись' :
          col.key === 'doc_official_letter' ? 'Письмо' : col.key === 'doc_program' ? 'Программа' :
          col.key === 'doc_invitation' ? 'Приглашение' : 'Ссылка');
        if (col.key === 'link_recording' && !v && daysSince(row.date) > 3) {
          return `<td class="recording-overdue" title="Прошло больше 3 дней после вебинара, а ссылки на запись всё ещё нет"><span class="link-empty">—</span></td>`;
        }
        return `<td>${linkPill(v, label, asText)}</td>`;
      }
      case 'actions': {
        return `
        <td>
          <div class="row-actions">
            <button class="icon-btn" data-edit="${row.id}" title="Редактировать">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </button>
            <button class="icon-btn" data-dup="${row.id}" title="Дублировать">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            </button>
            <button class="icon-btn danger" data-del="${row.id}" title="Удалить">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            </button>
          </div>
        </td>`;
      }
      case 'text':
      default:
        if (col.key === 'title') return `<td class="title-cell">${escapeHtml(v)}</td>`;
        return `<td>${escapeHtml(v || '')}</td>`;
    }
  }

  function renderTable() {
    const cols = visibleColumns();
    const vis = visibleRows();
    if (vis.length === 0) {
      $('#tableBody').innerHTML = `<tr><td colspan="${cols.length + 1}"><div class="empty-state"><strong>Ничего не найдено</strong>Попробуйте изменить фильтры или поисковый запрос.</div></td></tr>`;
      return;
    }
    $('#tableBody').innerHTML = vis.map(r => `
      <tr data-id="${r.id}" class="${selectedIds.has(r.id) ? 'row-selected' : ''}">
        <td class="col-check"><input type="checkbox" class="row-check" data-id="${r.id}" ${selectedIds.has(r.id) ? 'checked' : ''}></td>
        ${cols.map(col => cellHtml(col, r)).join('')}
      </tr>
    `).join('');

    $$('[data-edit]').forEach(b => b.addEventListener('click', () => openModal(Number(b.dataset.edit))));
    $$('[data-del]').forEach(b => b.addEventListener('click', () => deleteRow(Number(b.dataset.del))));
    $$('[data-dup]').forEach(b => b.addEventListener('click', () => duplicateRow(Number(b.dataset.dup))));
    $$('.row-check').forEach(b => b.addEventListener('change', () => {
      const id = Number(b.dataset.id);
      if (b.checked) selectedIds.add(id); else selectedIds.delete(id);
      b.closest('tr').classList.toggle('row-selected', b.checked);
      updateBulkBar();
      syncSelectAllBox();
    }));
  }

  function syncSelectAllBox() {
    const box = $('#selectAllBox');
    if (!box) return;
    const vis = visibleRows();
    const allSelected = vis.length > 0 && vis.every(r => selectedIds.has(r.id));
    const someSelected = vis.some(r => selectedIds.has(r.id));
    box.checked = allSelected;
    box.indeterminate = someSelected && !allSelected;
  }

  function updateBulkBar() {
    const n = selectedIds.size;
    $('#bulkBar').style.display = n > 0 ? 'flex' : 'none';
    $('#bulkCount').textContent = `Выбрано: ${n}`;
    syncSelectAllBox();
  }

  function updateSortIcons() {
    $$('[data-sort]').forEach(btn => {
      const col = btn.dataset.sort;
      btn.classList.toggle('active', sortState.col === col);
      btn.textContent = sortState.col === col ? (sortState.dir === 'asc' ? '↑' : '↓') : '↕';
    });
    $$('[data-filter]').forEach(btn => {
      const col = btn.dataset.filter;
      btn.classList.toggle('active', !!(filters[col] && filters[col].size));
    });
  }

  function applyColumnOrderAndLabels() {
    COLUMNS.forEach(c => {
      const base = BASE_COLUMNS.find(b => b.key === c.key);
      const customDef = customColumns.find(cc => cc.key === c.key);
      const originalLabel = base ? base.label : (customDef ? customDef.label : c.label);
      c.label = settings.columnLabels[c.key] || originalLabel;
    });
    if (settings.columnOrder && settings.columnOrder.length) {
      const orderIndex = key => { const i = settings.columnOrder.indexOf(key); return i === -1 ? 9999 : i; };
      COLUMNS.sort((a, b) => orderIndex(a.key) - orderIndex(b.key));
    }
  }

  function renderAll() {
    applyColumnOrderAndLabels();
    renderColgroup();
    renderHead();
    renderStats();
    renderPills();
    renderTable();
    updateSortIcons();
    updateBulkBar();
  }

  // ---------- Excel-style filter popover ----------

  function closePopover() { $('#filterPop').style.display = 'none'; openPopoverCol = null; }

  const MONTHS_RU_NOM = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];

  function openPopover(col, anchorBtn) {
    if (openPopoverCol === col) { closePopover(); return; }
    openPopoverCol = col;
    const info = COLUMNS.find(c => c.key === col);
    const working = rows.filter(r => passesFilters(r, col) && passesSearch(r));
    const active = filters[col] || new Set();
    const sortableHere = info.sortable;
    const isDateCol = col === 'date';
    const isTagsCol = info.type === 'tags';

    let allValues, counts, monthOf = null;
    if (isDateCol) {
      // Группируем по месяцам: значение чекбокса — "YYYY-MM", раскрывается в реальные даты при применении.
      const monthMap = new Map(); // "YYYY-MM" -> Set of exact dates present in data
      rows.forEach(r => {
        const d = String(r.date || '');
        if (!d) return;
        const mk = d.slice(0, 7);
        if (!monthMap.has(mk)) monthMap.set(mk, new Set());
        monthMap.get(mk).add(d);
      });
      allValues = Array.from(monthMap.keys()).sort();
      monthOf = monthMap;
      counts = new Map();
      working.forEach(r => {
        const d = String(r.date || '');
        if (!d) return;
        const mk = d.slice(0, 7);
        counts.set(mk, (counts.get(mk) || 0) + 1);
      });
    } else if (isTagsCol) {
      counts = new Map();
      const allSet = new Set();
      rows.forEach(r => String(r[col] || '').split(',').map(t => t.trim()).filter(Boolean).forEach(t => allSet.add(t)));
      working.forEach(r => String(r[col] || '').split(',').map(t => t.trim()).filter(Boolean).forEach(t => counts.set(t, (counts.get(t) || 0) + 1)));
      allValues = Array.from(allSet).sort((a, b) => a.localeCompare(b, 'ru'));
    } else {
      counts = new Map();
      working.forEach(r => {
        const v = String(r[col] ?? '');
        counts.set(v, (counts.get(v) || 0) + 1);
      });
      allValues = Array.from(new Set(rows.map(r => String(r[col] ?? ''))));
      allValues.sort((a, b) => a.localeCompare(b, 'ru'));
    }

    const formatOptLabel = v => {
      if (isDateCol) {
        const [y, m] = v.split('-');
        return `${MONTHS_RU_NOM[parseInt(m, 10) - 1]} ${y}`;
      }
      return formatCell(info, v);
    };
    const isChecked = v => {
      if (active.size === 0) return true;
      if (isDateCol) return Array.from(monthOf.get(v)).some(d => active.has(d));
      return active.has(v);
    };

    const pop = $('#filterPop');
    pop.innerHTML = `
      <div class="filter-pop-search"><input type="text" placeholder="Поиск значений…" id="popSearchInput"></div>
      ${sortableHere ? `
      <div class="filter-pop-sort">
        <button data-dir="asc" class="${sortState.col===col && sortState.dir==='asc' ? 'active':''}">↑ По возрастанию</button>
        <button data-dir="desc" class="${sortState.col===col && sortState.dir==='desc' ? 'active':''}">↓ По убыванию</button>
      </div>` : ''}
      <div class="filter-pop-list" id="popList">
        ${allValues.map(v => `
          <label class="filter-opt" data-val="${escapeHtml(v)}">
            <input type="checkbox" ${isChecked(v) ? 'checked' : ''}>
            ${(info.type === 'badge' || info.type === 'tags') ? `<span class="org-dot" style="background:${info.type === 'tags' ? mailingListColor(v) : badgeColorForColumn(info, v)}"></span>` : ''}
            <span>${escapeHtml(formatOptLabel(v))}</span>
            <span class="count">${counts.get(v) || 0}</span>
          </label>
        `).join('')}
      </div>
      <div class="filter-pop-actions">
        <button class="btn btn-ghost" id="popClear">Сбросить</button>
        <button class="btn btn-primary" id="popDone">Готово</button>
      </div>
    `;

    const rect = anchorBtn.getBoundingClientRect();
    pop.style.display = 'flex';
    const popW = 260;
    let left = rect.right - popW;
    if (left < 8) left = 8;
    if (left + popW > window.innerWidth - 8) left = window.innerWidth - popW - 8;
    pop.style.left = left + 'px';
    pop.style.top = Math.min(rect.bottom + 6, window.innerHeight - 380) + 'px';

    $('#popSearchInput').addEventListener('input', e => {
      const q = e.target.value.toLowerCase();
      $$('#popList .filter-opt').forEach(opt => {
        opt.style.display = opt.dataset.val.toLowerCase().includes(q) ? 'flex' : 'none';
      });
    });

    if (sortableHere) {
      pop.querySelectorAll('.filter-pop-sort button').forEach(btn => {
        btn.addEventListener('click', () => {
          sortState = { col, dir: btn.dataset.dir };
          renderAll();
          closePopover();
        });
      });
    }

    $('#popClear').addEventListener('click', () => { delete filters[col]; persistFilters(); renderAll(); closePopover(); });

    $('#popDone').addEventListener('click', () => {
      const checkedKeys = new Set();
      let uncheckedCount = 0;
      $$('#popList .filter-opt').forEach(opt => {
        const box = opt.querySelector('input');
        if (box.checked) checkedKeys.add(opt.dataset.val);
        else uncheckedCount++;
      });
      if (uncheckedCount === 0) {
        filters[col] = new Set();
      } else if (isDateCol) {
        const expanded = new Set();
        checkedKeys.forEach(mk => monthOf.get(mk).forEach(d => expanded.add(d)));
        filters[col] = expanded;
      } else {
        filters[col] = checkedKeys;
      }
      persistFilters();
      renderAll();
      closePopover();
    });
  }

  document.addEventListener('click', e => {
    const filterBtn = e.target.closest('[data-filter]');
    if (filterBtn) { e.stopPropagation(); openPopover(filterBtn.dataset.filter, filterBtn); return; }

    const sortBtn = e.target.closest('[data-sort]');
    if (sortBtn) {
      const col = sortBtn.dataset.sort;
      sortState = sortState.col === col ? { col, dir: sortState.dir === 'asc' ? 'desc' : 'asc' } : { col, dir: 'asc' };
      renderAll();
      return;
    }

    const removeColBtn = e.target.closest('[data-removecol]');
    if (removeColBtn) { e.stopPropagation(); removeColumn(removeColBtn.dataset.removecol); return; }

    if (!e.target.closest('#filterPop')) closePopover();
  });

  // ---------- Search / reset ----------

  $('#searchInput').addEventListener('input', e => { search = e.target.value.trim(); renderAll(); });
  $('#resetFiltersBtn').addEventListener('click', () => { filters = {}; persistFilters(); renderAll(); });

  // ---------- Modal / CRUD ----------

  const overlay = $('#modalOverlay');

  function stripGuillemets(s) {
    return String(s ?? '').replace(/^«/, '').replace(/»$/, '');
  }

  function populateOrganizerSelect(selectedName) {
    const sel = $('#f_organizer');
    let html = organizersList.map(o => `<option value="${escapeHtml(o.name)}" ${o.name === selectedName ? 'selected' : ''}>${escapeHtml(o.name)}</option>`).join('');
    if (selectedName && !organizersList.some(o => o.name === selectedName)) {
      html = `<option value="${escapeHtml(selectedName)}" selected>${escapeHtml(selectedName)}</option>` + html;
    }
    html += `<option value="__new__">+ Добавить нового…</option>`;
    sel.innerHTML = html;
  }

  $('#f_organizer').addEventListener('change', e => {
    $('#newOrgBox').style.display = e.target.value === '__new__' ? 'flex' : 'none';
    if (e.target.value === '__new__') { $('#newOrgName').value = ''; $('#newOrgName').focus(); }
  });

  $('#newOrgCancel').addEventListener('click', () => {
    $('#newOrgBox').style.display = 'none';
    populateOrganizerSelect(organizersList[0] ? organizersList[0].name : '');
  });

  $('#newOrgSave').addEventListener('click', async () => {
    const name = $('#newOrgName').value.trim();
    if (!name) return;
    const color = $('#newOrgColor').value;
    try {
      const res = await fetch(`${TAXONOMY_API}?type=organizer`, { method: 'POST', body: JSON.stringify({ name, color }) });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось добавить организатора');
      await loadOrganizers();
      populateOrganizerSelect(json.data.name);
      $('#newOrgBox').style.display = 'none';
      showToast(`Организатор «${json.data.name}» добавлен`);
    } catch (err) {
      showToast(err.message, true);
    }
  });

  function populateDirectionSelect(selectedName) {
    const sel = $('#f_direction');
    let html = `<option value="">—</option>` + directionsList.map(d => `<option value="${escapeHtml(d.name)}" ${d.name === selectedName ? 'selected' : ''}>${escapeHtml(d.name)}</option>`).join('');
    if (selectedName && !directionsList.some(d => d.name === selectedName)) {
      html += `<option value="${escapeHtml(selectedName)}" selected>${escapeHtml(selectedName)}</option>`;
    }
    html += `<option value="__new__">+ Добавить новое…</option>`;
    sel.innerHTML = html;
  }

  $('#f_direction').addEventListener('change', e => {
    $('#newDirBox').style.display = e.target.value === '__new__' ? 'flex' : 'none';
    if (e.target.value === '__new__') { $('#newDirName').value = ''; $('#newDirName').focus(); }
  });
  $('#newDirCancel').addEventListener('click', () => {
    $('#newDirBox').style.display = 'none';
    populateDirectionSelect('');
  });
  $('#newDirSave').addEventListener('click', async () => {
    const name = $('#newDirName').value.trim();
    if (!name) return;
    const color = $('#newDirColor').value;
    try {
      const res = await fetch(`${TAXONOMY_API}?type=direction`, { method: 'POST', body: JSON.stringify({ name, color }) });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось добавить направление');
      await loadDirections();
      populateDirectionSelect(json.data.name);
      $('#newDirBox').style.display = 'none';
      showToast(`Направление «${json.data.name}» добавлено`);
    } catch (err) { showToast(err.message, true); }
  });

  function populatePublishedSelect(selectedName) {
    const sel = $('#f_published_on_site');
    let html = `<option value="">—</option>` + publishedOnSiteList.map(d => `<option value="${escapeHtml(d.name)}" ${d.name === selectedName ? 'selected' : ''}>${escapeHtml(d.name)}</option>`).join('');
    if (selectedName && !publishedOnSiteList.some(d => d.name === selectedName)) {
      html += `<option value="${escapeHtml(selectedName)}" selected>${escapeHtml(selectedName)}</option>`;
    }
    html += `<option value="__new__">+ Добавить своё значение…</option>`;
    sel.innerHTML = html;
  }

  $('#f_published_on_site').addEventListener('change', e => {
    $('#newPublishedBox').style.display = e.target.value === '__new__' ? 'flex' : 'none';
    if (e.target.value === '__new__') { $('#newPublishedName').value = ''; $('#newPublishedName').focus(); }
  });
  $('#newPublishedCancel').addEventListener('click', () => {
    $('#newPublishedBox').style.display = 'none';
    populatePublishedSelect('');
  });
  $('#newPublishedSave').addEventListener('click', async () => {
    const name = $('#newPublishedName').value.trim();
    if (!name) return;
    const color = $('#newPublishedColor').value;
    try {
      const res = await fetch(`${TAXONOMY_API}?type=published_on_site`, { method: 'POST', body: JSON.stringify({ name, color }) });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось добавить значение');
      await loadPublishedOnSiteList();
      populatePublishedSelect(json.data.name);
      $('#newPublishedBox').style.display = 'none';
      showToast(`«${json.data.name}» добавлено`);
    } catch (err) { showToast(err.message, true); }
  });

  function populateSubscriptionSelect(selectedName) {
    const sel = $('#f_subscription');
    let html = `<option value="">—</option>` + subscriptionList.map(d => `<option value="${escapeHtml(d.name)}" ${d.name === selectedName ? 'selected' : ''}>${escapeHtml(d.name)}</option>`).join('');
    if (selectedName && !subscriptionList.some(d => d.name === selectedName)) {
      html += `<option value="${escapeHtml(selectedName)}" selected>${escapeHtml(selectedName)}</option>`;
    }
    html += `<option value="__new__">+ Добавить своё значение…</option>`;
    sel.innerHTML = html;
  }

  $('#f_subscription').addEventListener('change', e => {
    $('#newSubscriptionBox').style.display = e.target.value === '__new__' ? 'flex' : 'none';
    if (e.target.value === '__new__') { $('#newSubscriptionName').value = ''; $('#newSubscriptionName').focus(); }
  });
  $('#newSubscriptionCancel').addEventListener('click', () => {
    $('#newSubscriptionBox').style.display = 'none';
    populateSubscriptionSelect('');
  });
  $('#newSubscriptionSave').addEventListener('click', async () => {
    const name = $('#newSubscriptionName').value.trim();
    if (!name) return;
    const color = $('#newSubscriptionColor').value;
    try {
      const res = await fetch(`${TAXONOMY_API}?type=subscription`, { method: 'POST', body: JSON.stringify({ name, color }) });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось добавить значение');
      await loadInSubscriptionList();
      populateSubscriptionSelect(json.data.name);
      $('#newSubscriptionBox').style.display = 'none';
      showToast(`«${json.data.name}» добавлено`);
    } catch (err) { showToast(err.message, true); }
  });

  // ---------- Спикер: поиск-автодополнение по управляемому справочнику ----------

  function updateSpeakerTriggerText() {
    const val = $('#f_speaker').value;
    $('#speakerTriggerText').textContent = val || 'Не выбран';
  }

  function renderSpeakerOptions(filterText) {
    const q = (filterText || '').trim().toLowerCase();
    const matches = q ? speakersList.filter(s => s.name.toLowerCase().startsWith(q)) : speakersList;
    if (!matches.length) {
      $('#speakerOptionsList').innerHTML = `<div class="filter-pop-empty hint" style="padding:8px">Ничего не найдено — добавьте нового спикера ниже.</div>`;
      return;
    }
    $('#speakerOptionsList').innerHTML = matches.map(s => `
      <label class="filter-opt" data-speaker-option="${escapeHtml(s.name)}">
        <span>${escapeHtml(s.name)}</span>
      </label>
    `).join('');
    $$('#speakerOptionsList [data-speaker-option]').forEach(el => el.addEventListener('click', () => {
      $('#f_speaker').value = el.dataset.speakerOption;
      updateSpeakerTriggerText();
      $('#speakerPanel').style.display = 'none';
    }));
  }

  $('#speakerTrigger').addEventListener('click', () => {
    const panel = $('#speakerPanel');
    const willOpen = panel.style.display === 'none';
    if (willOpen) {
      $('#speakerSearchInput').value = '';
      renderSpeakerOptions('');
      $('#newSpeakerBox').style.display = 'none';
      panel.style.display = 'block';
      $('#speakerSearchInput').focus();
    } else {
      panel.style.display = 'none';
    }
  });
  $('#speakerSearchInput').addEventListener('input', e => renderSpeakerOptions(e.target.value));
  document.addEventListener('click', e => {
    if (!e.target.closest('#speakerPanel') && !e.target.closest('#speakerTrigger')) {
      $('#speakerPanel').style.display = 'none';
    }
  });
  $('#addSpeakerBtn').addEventListener('click', () => {
    $('#newSpeakerBox').style.display = 'flex';
    $('#newSpeakerName').value = $('#speakerSearchInput').value.trim();
    $('#newSpeakerName').focus();
  });
  $('#newSpeakerCancel').addEventListener('click', () => { $('#newSpeakerBox').style.display = 'none'; });
  $('#newSpeakerSave').addEventListener('click', async () => {
    const name = $('#newSpeakerName').value.trim();
    if (!name) return;
    try {
      const res = await fetch(`${TAXONOMY_API}?type=speaker`, { method: 'POST', body: JSON.stringify({ name }) });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось добавить спикера');
      await loadSpeakers();
      $('#f_speaker').value = json.data.name;
      updateSpeakerTriggerText();
      $('#newSpeakerBox').style.display = 'none';
      $('#speakerPanel').style.display = 'none';
      showToast(`Спикер «${json.data.name}» добавлен`);
    } catch (err) { showToast(err.message, true); }
  });

  function buildCustomFields(row) {
    const box = $('#customFieldsContainer');
    if (customColumns.length === 0) { box.innerHTML = ''; return; }
    box.innerHTML = customColumns.map(c => {
      const val = row ? (row[c.key] || '') : '';
      if (c.type === 'boolean') {
        const checked = val === '1';
        return `
          <div class="field field-full">
            <label>${escapeHtml(c.label)}</label>
            <div class="publish-toggle-row">
              <label class="auto-gen-toggle">
                <input type="checkbox" data-custom-key="${c.key}" data-custom-type="boolean" ${checked ? 'checked' : ''}>
                <span class="toggle-track"><span class="toggle-thumb"></span></span>
              </label>
            </div>
          </div>
        `;
      }
      if (c.type === 'tags') {
        return `
          <div class="field field-full">
            <label for="f_custom_${c.key}">${escapeHtml(c.label)} <span class="hint">(через запятую, если несколько)</span></label>
            <input type="text" id="f_custom_${c.key}" data-custom-key="${c.key}" value="${escapeHtml(val)}">
          </div>
        `;
      }
      return `
        <div class="field field-full">
          <label for="f_custom_${c.key}">${escapeHtml(c.label)}</label>
          <input type="text" id="f_custom_${c.key}" data-custom-key="${c.key}" value="${escapeHtml(val)}">
        </div>
      `;
    }).join('');
  }

  function openModal(id = null) {
    const row = id ? rows.find(r => r.id === id) : null;
    $('#modalTitle').textContent = row ? 'Редактировать вебинар' : 'Новый вебинар';
    $('#f_id').value = row ? row.id : '';
    $('#f_date').value = row ? row.date : '';
    $('#f_price').value = row ? row.price : '';
    populateOrganizerSelect(row ? row.organizer : (organizersList[0] ? organizersList[0].name : ''));
    $('#newOrgBox').style.display = 'none';
    $('#f_speaker').value = row ? row.speaker : '';
    updateSpeakerTriggerText();
    populateDirectionSelect(row ? row.direction : '');
    $('#newDirBox').style.display = 'none';
    $('#f_title').value = row ? stripGuillemets(row.title) : '';
    $('#f_link_participant').value = row ? row.link_participant : '';
    $('#f_link_host').value = row ? row.link_host : '';
    $('#f_link_materials').value = row ? row.link_materials : '';
    $('#f_link_recording').value = row ? row.link_recording : '';
    $('#f_moderator_code').value = row ? row.moderator_code : '';
    populatePublishedSelect(row ? row.published_on_site : '');
    $('#newPublishedBox').style.display = 'none';
    populateSubscriptionSelect(row ? row.subscription : '');
    $('#newSubscriptionBox').style.display = 'none';
    renderMailingListCheckboxes(row ? row.mailing_list : '');
    $('#newMailingListBox').style.display = 'none';
    $('#mailingListPanel').style.display = 'none';
    $('#autoGenerateRow').style.display = row ? 'none' : 'inline-flex';
    $('#f_autoGenerate').checked = true;
    buildCustomFields(row);
    renderDocSlots(row);
    overlay.style.display = 'flex';
  }

  let docTypesList = [];

  async function loadDocTypes() {
    try {
      const res = await fetch('doc_types.php');
      const json = await res.json();
      docTypesList = json.ok ? json.data : [];
    } catch (e) { docTypesList = []; }
  }

  function renderDocSlots(row) {
    const hasId = !!row;
    $('#docsHint').style.display = hasId ? 'none' : 'flex';

    $('#docsSlotsWrap').innerHTML = docTypesList.map(t => {
      const field = 'doc_' + t.key;
      const url = row ? row[field] : '';
      const statusHtml = url
        ? `<a href="${escapeHtml(url)}" target="_blank" rel="noopener">${escapeHtml(decodeURIComponent(url.split('/').pop()))}</a>`
        : 'Не загружено';
      const acceptAttr = t.ext.map(e => '.' + e).join(',');
      return `
        <div class="doc-slot" data-doctype="${escapeHtml(t.key)}">
          <div class="doc-slot-label"><i class="${escapeHtml(t.icon)}"></i> ${escapeHtml(t.label)} <span class="hint">(${t.ext.map(e => '.' + e).join('/')})</span></div>
          <div class="doc-slot-status">${statusHtml}</div>
          <div class="doc-slot-actions">
            <label class="btn btn-ghost landing-upload-btn"><i class="fa-solid fa-upload"></i> Загрузить<input type="file" class="doc-file-input" data-doctype="${escapeHtml(t.key)}" accept="${acceptAttr}" style="display:none" ${hasId ? '' : 'disabled'}></label>
            <button type="button" class="btn btn-ghost doc-remove-btn" data-doctype="${escapeHtml(t.key)}" ${hasId ? '' : 'disabled'}><i class="fa-solid fa-xmark"></i> Убрать</button>
          </div>
        </div>
      `;
    }).join('');

    $$('#docsSlotsWrap .doc-file-input').forEach(input => input.addEventListener('change', handleDocFileChange));
    $$('#docsSlotsWrap .doc-remove-btn').forEach(btn => btn.addEventListener('click', handleDocRemoveClick));

    const anyDoc = docTypesList.some(t => row && row['doc_' + t.key]);
    $('#downloadDocsZipBtn').style.display = (hasId && anyDoc) ? 'inline-flex' : 'none';
  }

  $('#downloadDocsZipBtn').addEventListener('click', () => {
    const webinarId = $('#f_id').value;
    if (!webinarId) return;
    window.open(`webinar_docs.php?action=zip&webinarId=${encodeURIComponent(webinarId)}`, '_blank');
  });

  async function handleDocFileChange(e) {
    const input = e.target;
    const file = input.files[0];
    input.value = '';
    if (!file) return;
    const webinarId = $('#f_id').value;
    if (!webinarId) return;
    const docType = input.dataset.doctype;
    const fd = new FormData();
    fd.append('webinarId', webinarId);
    fd.append('docType', docType);
    fd.append('file', file);
    try {
      const res = await fetch('webinar_docs.php', { method: 'POST', body: fd });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось загрузить документ');
      await loadRows();
      renderDocSlots(rows.find(r => String(r.id) === String(webinarId)));
      renderAll();
      showToast('Документ загружен');
    } catch (err) {
      showToast(err.message, true);
    }
  }

  async function handleDocRemoveClick(e) {
    const btn = e.currentTarget;
    const webinarId = $('#f_id').value;
    if (!webinarId) return;
    const docType = btn.dataset.doctype;
    if (!confirm('Убрать этот документ?')) return;
    try {
      const res = await fetch(`webinar_docs.php?webinarId=${encodeURIComponent(webinarId)}&docType=${encodeURIComponent(docType)}`, { method: 'DELETE' });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось убрать документ');
      await loadRows();
      renderDocSlots(rows.find(r => String(r.id) === String(webinarId)));
      renderAll();
      showToast('Документ убран');
    } catch (err) {
      showToast(err.message, true);
    }
  }

  function updateMailingListTriggerText() {
    const checked = $$('#mailingListCheckboxes input:checked').map(b => b.value);
    $('#mailingListTriggerText').textContent = checked.length ? checked.join(', ') : 'Не выбрано';
  }

  function renderMailingListCheckboxes(currentValue) {
    const selected = new Set(String(currentValue || '').split(',').map(s => s.trim()).filter(Boolean));
    if (!mailingListsList.length) {
      $('#mailingListCheckboxes').innerHTML = `<div class="filter-pop-empty hint" style="padding:8px">Пока нет значений — добавьте первое ниже.</div>`;
    } else {
      $('#mailingListCheckboxes').innerHTML = mailingListsList.map(m => `
        <label class="filter-opt" data-mailing-chip="${escapeHtml(m.name)}">
          <input type="checkbox" value="${escapeHtml(m.name)}" ${selected.has(m.name) ? 'checked' : ''}>
          <span class="org-dot" style="background:${m.color}"></span>
          <span>${escapeHtml(m.name)}</span>
        </label>
      `).join('');
    }
    $$('#mailingListCheckboxes input[type="checkbox"]').forEach(box => box.addEventListener('change', updateMailingListTriggerText));
    updateMailingListTriggerText();
  }

  function collectMailingLists() {
    return $$('#mailingListCheckboxes input:checked').map(b => b.value).join(',');
  }

  $('#mailingListTrigger').addEventListener('click', () => {
    const panel = $('#mailingListPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
  });
  document.addEventListener('click', e => {
    if (!e.target.closest('#mailingListPanel') && !e.target.closest('#mailingListTrigger')) {
      $('#mailingListPanel').style.display = 'none';
    }
  });
  $('#mailingListDoneBtn').addEventListener('click', () => { $('#mailingListPanel').style.display = 'none'; });

  $('#addMailingListBtn').addEventListener('click', () => {
    $('#newMailingListBox').style.display = 'flex';
    $('#newMailingListName').value = '';
    $('#newMailingListName').focus();
  });
  $('#newMailingListCancel').addEventListener('click', () => { $('#newMailingListBox').style.display = 'none'; });
  $('#newMailingListSave').addEventListener('click', async () => {
    const name = $('#newMailingListName').value.trim();
    if (!name) return;
    const color = $('#newMailingListColor').value;
    try {
      const res = await fetch(`${TAXONOMY_API}?type=mailing_list`, { method: 'POST', body: JSON.stringify({ name, color }) });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось добавить значение');
      const currentlyChecked = collectMailingLists();
      await loadMailingLists();
      renderMailingListCheckboxes(currentlyChecked ? currentlyChecked + ',' + json.data.name : json.data.name);
      $('#newMailingListBox').style.display = 'none';
      showToast(`«${json.data.name}» добавлено`);
    } catch (err) { showToast(err.message, true); }
  });

  function closeModal() { overlay.style.display = 'none'; }

  $('#addBtn').addEventListener('click', () => openModal());
  $('#modalClose').addEventListener('click', closeModal);
  $('#modalCancel').addEventListener('click', closeModal);

  $('#webinarForm').addEventListener('submit', async e => {
    e.preventDefault();
    if ($('#f_organizer').value === '__new__') {
      showToast('Сначала добавьте нового организатора или выберите существующего', true);
      return;
    }
    const id = $('#f_id').value;
    const payload = {
      date: $('#f_date').value,
      price: $('#f_price').value,
      organizer: $('#f_organizer').value,
      speaker: $('#f_speaker').value,
      title: $('#f_title').value,
      link_participant: $('#f_link_participant').value,
      link_host: $('#f_link_host').value,
      link_materials: $('#f_link_materials').value,
      link_recording: $('#f_link_recording').value,
      moderator_code: $('#f_moderator_code').value,
      published_on_site: $('#f_published_on_site').value === '__new__' ? '' : $('#f_published_on_site').value,
      mailing_list: collectMailingLists(),
      direction: $('#f_direction').value === '__new__' ? '' : $('#f_direction').value,
      subscription: $('#f_subscription').value === '__new__' ? '' : $('#f_subscription').value,
    };
    const existingDocsRow = id ? rows.find(r => String(r.id) === String(id)) : null;
    payload.doc_official_letter = existingDocsRow ? existingDocsRow.doc_official_letter : '';
    payload.doc_program = existingDocsRow ? existingDocsRow.doc_program : '';
    payload.doc_invitation = existingDocsRow ? existingDocsRow.doc_invitation : '';
    $$('#customFieldsContainer [data-custom-key]').forEach(inp => {
      payload[inp.dataset.customKey] = inp.dataset.customType === 'boolean' ? (inp.checked ? '1' : '') : inp.value;
    });

    try {
      const res = id
        ? await fetch(API, { method: 'PUT', body: JSON.stringify({ id: Number(id), ...payload }) })
        : await fetch(API, { method: 'POST', body: JSON.stringify(payload) });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Ошибка сохранения');

      let message = id ? 'Изменения сохранены' : 'Вебинар добавлен (создан бэкап данных)';
      if (!id && $('#f_autoGenerate').checked) {
        try {
          if (!landingMeta.themes.length) await loadLandingMeta();
          applyDefaultTemplate();
          await publishLanding(json.data.id);
          message = 'Вебинар добавлен, страница участника опубликована автоматически';
        } catch (pubErr) {
          message = 'Вебинар добавлен, но страницу участника создать не удалось: ' + pubErr.message;
        }
      }

      await loadRows();
      renderAll();
      closeModal();
      showToast(message);
    } catch (err) {
      showToast(err.message, true);
    }
  });

  async function deleteRow(id) {
    const row = rows.find(r => r.id === id);
    if (!row) return;
    if (!confirm(`Удалить вебинар ${row.title}?`)) return;
    try {
      const res = await fetch(`${API}?id=${id}`, { method: 'DELETE' });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Ошибка удаления');
      selectedIds.delete(id);
      await loadRows();
      renderAll();
      showToast('Вебинар удалён');
    } catch (err) {
      showToast(err.message, true);
    }
  }

  async function duplicateRow(id) {
    const row = rows.find(r => r.id === id);
    if (!row) return;
    const payload = { ...row };
    delete payload.id;
    try {
      const res = await fetch(API, { method: 'POST', body: JSON.stringify(payload) });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Ошибка дублирования');
      await loadRows();
      renderAll();
      showToast('Строка продублирована');
    } catch (err) {
      showToast(err.message, true);
    }
  }

  // ---------- Custom columns (add / remove) ----------

  const addColumnOverlay = $('#addColumnOverlay');

  $('#addColBtn').addEventListener('click', () => {
    $('#newColumnLabel').value = '';
    $$('input[name="newColumnType"]').forEach(r => { r.checked = r.value === 'text'; });
    addColumnOverlay.style.display = 'flex';
  });
  $('#addColumnClose').addEventListener('click', () => addColumnOverlay.style.display = 'none');
  $('#addColumnCancel').addEventListener('click', () => addColumnOverlay.style.display = 'none');
  addColumnOverlay.addEventListener('click', e => { if (e.target === addColumnOverlay) addColumnOverlay.style.display = 'none'; });

  $('#addColumnSave').addEventListener('click', async () => {
    const label = $('#newColumnLabel').value.trim();
    if (!label) { showToast('Укажите название столбца', true); return; }
    const type = ($$('input[name="newColumnType"]').find(r => r.checked) || {}).value || 'text';
    try {
      const res = await fetch(COLUMNS_API, { method: 'POST', body: JSON.stringify({ label, type }) });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось добавить столбец');
      await loadColumns();
      renderAll();
      addColumnOverlay.style.display = 'none';
      showToast(`Столбец «${label}» добавлен`);
    } catch (err) {
      showToast(err.message, true);
    }
  });

  async function removeColumn(key) {
    const col = customColumns.find(c => c.key === key);
    if (!col) return;
    if (!confirm(`Удалить столбец «${col.label}»? Данные в нём будут потеряны.`)) return;
    try {
      const res = await fetch(`${COLUMNS_API}?key=${encodeURIComponent(key)}`, { method: 'DELETE' });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось удалить столбец');
      await loadColumns();
      await loadRows();
      renderAll();
      showToast('Столбец удалён');
    } catch (err) {
      showToast(err.message, true);
    }
  }

  // ---------- Display settings ----------

  const settingsOverlay = $('#settingsOverlay');

  let draggedColKey = null;

  function renderSettingsList() {
    $('#settingsList').innerHTML = COLUMNS.map(col => `
      <div class="settings-row" draggable="true" data-key="${col.key}">
        <span class="drag-handle" title="Перетащите, чтобы изменить порядок">⠿</span>
        <label class="chk">
          <input type="checkbox" data-setting-visible="${col.key}" ${settings.hidden.includes(col.key) ? '' : 'checked'}>
        </label>
        <input type="text" class="rename-input" data-rename="${col.key}" value="${escapeHtml(col.label)}">
        <span class="width-label">ширина</span>
        <input type="number" class="width-input" data-setting-width="${col.key}" value="${effectiveWidthPx(col)}" min="40" max="600" step="10">
        ${col.type === 'link' ? `
          <label class="chk">
            <input type="checkbox" data-setting-linktext="${col.key}" ${settings.linkAsText.includes(col.key) ? 'checked' : ''}>
            как текст
          </label>` : ''}
      </div>
    `).join('');

    $$('[data-setting-visible]').forEach(box => box.addEventListener('change', () => {
      const key = box.dataset.settingVisible;
      settings.hidden = box.checked ? settings.hidden.filter(k => k !== key) : [...new Set([...settings.hidden, key])];
      saveSettings();
      renderAll();
    }));
    $$('[data-setting-linktext]').forEach(box => box.addEventListener('change', () => {
      const key = box.dataset.settingLinktext;
      settings.linkAsText = box.checked ? [...new Set([...settings.linkAsText, key])] : settings.linkAsText.filter(k => k !== key);
      saveSettings();
      renderAll();
    }));
    $$('[data-setting-width]').forEach(inp => inp.addEventListener('change', () => {
      const key = inp.dataset.settingWidth;
      let val = parseInt(inp.value, 10);
      if (!val || val < 40) val = 40;
      settings.colWidths[key] = val;
      saveSettings();
      renderAll();
    }));

    $$('[data-rename]').forEach(inp => inp.addEventListener('change', () => {
      const key = inp.dataset.rename;
      const val = inp.value.trim();
      if (val) settings.columnLabels[key] = val; else delete settings.columnLabels[key];
      saveSettings();
      renderAll();
    }));

    $$('#settingsList .settings-row').forEach(row => {
      row.addEventListener('dragstart', () => { draggedColKey = row.dataset.key; row.classList.add('dragging'); });
      row.addEventListener('dragend', () => row.classList.remove('dragging'));
      row.addEventListener('dragover', e => { e.preventDefault(); row.classList.add('drag-over'); });
      row.addEventListener('dragleave', () => row.classList.remove('drag-over'));
      row.addEventListener('drop', e => {
        e.preventDefault();
        row.classList.remove('drag-over');
        const targetKey = row.dataset.key;
        if (!draggedColKey || draggedColKey === targetKey) return;
        let order = COLUMNS.map(c => c.key);
        order = order.filter(k => k !== draggedColKey);
        order.splice(order.indexOf(targetKey), 0, draggedColKey);
        settings.columnOrder = order;
        saveSettings();
        renderAll();
        renderSettingsList();
      });
    });
  }

  $$('input[name="tableWidthMode"]').forEach(radio => radio.addEventListener('change', () => {
    if (radio.checked) { settings.tableWidthMode = radio.value; saveSettings(); renderAll(); }
  }));

  $('#landingOutputBaseInput').addEventListener('change', e => {
    settings.landingOutputBase = e.target.value.trim().replace(/^\/+|\/+$/g, '');
    saveSettings();
  });

  $('#landingLinkLengthInput').addEventListener('change', e => {
    let val = parseInt(e.target.value, 10);
    if (isNaN(val) || val < 4) val = 4;
    if (val > 32) val = 32;
    settings.landingLinkLength = val;
    e.target.value = val;
    saveSettings();
  });

  ['landingPortsText', 'landingSupportText', 'landingSupportPhone', 'landingBrowserText', 'landingFooterText', 'landingTooltipRecording', 'landingTooltipMaterials'].forEach(key => {
    const inputId = `#${key}Input`;
    $(inputId).addEventListener('change', e => {
      settings[key] = e.target.value.trim();
      saveSettings();
    });
  });

  $('#registryWidthSelect').addEventListener('change', e => {
    settings.registryWidth = e.target.value;
    saveSettings();
    applyRegistryWidth();
  });

  // ---------- Quick links (шапка реестра) ----------

  let draggedQuickLinkId = null;

  function orderedQuickLinks() {
    const links = settings.quickLinks || [];
    const order = settings.quickLinksOrder || [];
    if (!order.length) return links;
    const idx = id => { const i = order.indexOf(id); return i === -1 ? 9999 : i; };
    return links.slice().sort((a, b) => idx(a.id) - idx(b.id));
  }

  function renderQuickLinksList() {
    const links = orderedQuickLinks();
    $('#quickLinksList').innerHTML = links.map(l => `
      <div class="quicklink-row" draggable="true" data-id="${l.id}">
        <span class="drag-handle" title="Перетащите, чтобы изменить порядок">⠿</span>
        <i class="${escapeHtml(l.icon)}"></i>
        <input type="text" class="rename-input quicklink-rename" data-rename-ql="${l.id}" value="${escapeHtml(l.label)}">
        <span class="quicklink-row-url">${escapeHtml(l.url)}</span>
        <button type="button" class="icon-btn danger" data-remove-quicklink="${l.id}" title="Удалить">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    `).join('');

    $$('[data-remove-quicklink]').forEach(btn => btn.addEventListener('click', () => {
      const id = btn.dataset.removeQuicklink;
      settings.quickLinks = (settings.quickLinks || []).filter(l => l.id !== id);
      settings.quickLinksOrder = (settings.quickLinksOrder || []).filter(k => k !== id);
      saveSettings();
      renderQuickLinksList();
      renderAll();
    }));

    $$('[data-rename-ql]').forEach(inp => inp.addEventListener('change', () => {
      const id = inp.dataset.renameQl;
      const link = (settings.quickLinks || []).find(l => l.id === id);
      if (link && inp.value.trim()) { link.label = inp.value.trim(); saveSettings(); renderAll(); }
    }));

    $$('#quickLinksList .quicklink-row').forEach(row => {
      row.addEventListener('dragstart', () => { draggedQuickLinkId = row.dataset.id; row.classList.add('dragging'); });
      row.addEventListener('dragend', () => row.classList.remove('dragging'));
      row.addEventListener('dragover', e => { e.preventDefault(); row.classList.add('drag-over'); });
      row.addEventListener('dragleave', () => row.classList.remove('drag-over'));
      row.addEventListener('drop', e => {
        e.preventDefault();
        row.classList.remove('drag-over');
        const targetId = row.dataset.id;
        if (!draggedQuickLinkId || draggedQuickLinkId === targetId) return;
        let order = orderedQuickLinks().map(l => l.id);
        order = order.filter(id => id !== draggedQuickLinkId);
        order.splice(order.indexOf(targetId), 0, draggedQuickLinkId);
        settings.quickLinksOrder = order;
        saveSettings();
        renderQuickLinksList();
        renderAll();
      });
    });

    $('#qlAddBtn').style.display = links.length >= 6 ? 'none' : 'inline-flex';
  }

  $('#qlIcon').innerHTML = QUICKLINK_ICON_CHOICES.map(o => `<option value="${o.value}">${o.label}</option>`).join('');

  $('#qlAddBtn').addEventListener('click', () => {
    $('#quickLinkForm').style.display = 'flex';
    $('#qlLabel').value = '';
    $('#qlUrl').value = '';
    $('#qlAddBtn').style.display = 'none';
  });
  $('#qlCancelBtn').addEventListener('click', () => {
    $('#quickLinkForm').style.display = 'none';
    renderQuickLinksList();
  });
  $('#qlSaveBtn').addEventListener('click', () => {
    const label = $('#qlLabel').value.trim();
    const url = $('#qlUrl').value.trim();
    if (!label || !url) { showToast('Укажите название и ссылку', true); return; }
    settings.quickLinks = settings.quickLinks || [];
    if (settings.quickLinks.length >= 6) { showToast('Максимум 6 быстрых ссылок', true); return; }
    settings.quickLinks.push({ id: 'ql_' + Math.random().toString(36).slice(2, 10), label, url, icon: $('#qlIcon').value });
    saveSettings();
    $('#quickLinkForm').style.display = 'none';
    renderQuickLinksList();
    renderAll();
    showToast('Ссылка добавлена');
  });

  // ---------- Постоянные получатели рассылки ----------

  function renderMailRecipientsList() {
    const list = settings.mailRecipients || [];
    $('#mailRecipientsList').innerHTML = list.map((r, i) => `
      <div class="quicklink-row">
        <i class="fa-solid fa-envelope"></i>
        <span class="quicklink-row-label">${escapeHtml(r.label || r.email)}</span>
        <span class="quicklink-row-url">${escapeHtml(r.email)}</span>
        <button type="button" class="icon-btn danger" data-remove-recipient="${i}" title="Удалить">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    `).join('');
    $$('[data-remove-recipient]').forEach(btn => btn.addEventListener('click', () => {
      settings.mailRecipients.splice(Number(btn.dataset.removeRecipient), 1);
      saveSettings();
      renderMailRecipientsList();
    }));
    $('#mrAddBtn').style.display = list.length >= 30 ? 'none' : 'inline-flex';
  }

  $('#mrAddBtn').addEventListener('click', () => {
    $('#mailRecipientForm').style.display = 'flex';
    $('#mrEmail').value = '';
    $('#mrLabel').value = '';
    $('#mrAddBtn').style.display = 'none';
  });
  $('#mrCancelBtn').addEventListener('click', () => {
    $('#mailRecipientForm').style.display = 'none';
    renderMailRecipientsList();
  });
  $('#mrSaveBtn').addEventListener('click', () => {
    const email = $('#mrEmail').value.trim();
    const label = $('#mrLabel').value.trim();
    if (!/^\S+@\S+\.\S+$/.test(email)) { showToast('Укажите корректный email', true); return; }
    settings.mailRecipients = settings.mailRecipients || [];
    settings.mailRecipients.push({ email, label });
    saveSettings();
    $('#mailRecipientForm').style.display = 'none';
    renderMailRecipientsList();
    showToast('Получатель добавлен');
  });

  // ---------- Управление значениями справочников: переименование + удаление ----------

  const TAXONOMY_UI_DEFS = [
    { type: 'organizer',         getList: () => organizersList,       containerId: 'organizerValuesList',   loader: loadOrganizers,           label: 'Организатор' },
    { type: 'mailing_list',      getList: () => mailingListsList,     containerId: 'mailingListValuesList', loader: loadMailingLists,         label: 'Рассылка' },
    { type: 'direction',         getList: () => directionsList,       containerId: 'directionValuesList',   loader: loadDirections,           label: 'Направление' },
    { type: 'published_on_site', getList: () => publishedOnSiteList,  containerId: 'publishedValuesList',   loader: loadPublishedOnSiteList,  label: 'Размещён на сайте' },
    { type: 'subscription',      getList: () => subscriptionList,     containerId: 'subscriptionValuesList',loader: loadInSubscriptionList,   label: 'Входит в подписку' },
    { type: 'speaker',           getList: () => speakersList,         containerId: 'speakerValuesList',     loader: loadSpeakers,             label: 'Спикер' },
  ];

  function renderTaxonomyValuesList(def) {
    const container = $(`#${def.containerId}`);
    if (!container) return;
    container.innerHTML = def.getList().map(item => `
      <div class="quicklink-row">
        <span class="org-dot" style="background:${item.color}"></span>
        <input type="text" class="rename-input tx-rename-input" data-tx-type="${def.type}" data-tx-old="${escapeHtml(item.name)}" value="${escapeHtml(item.name)}">
        <button type="button" class="icon-btn danger" data-tx-remove="${escapeHtml(item.name)}" data-tx-type="${def.type}" title="Удалить">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    `).join('');

    $$(`#${def.containerId} .tx-rename-input`).forEach(inp => inp.addEventListener('change', async () => {
      const oldName = inp.dataset.txOld;
      const newName = inp.value.trim();
      if (!newName || newName === oldName) { inp.value = oldName; return; }
      try {
        const res = await fetch(`${TAXONOMY_API}?type=${def.type}&action=rename`, { method: 'POST', body: JSON.stringify({ oldName, newName }) });
        const json = await res.json();
        if (!json.ok) throw new Error(json.error || 'Не удалось переименовать');
        await def.loader();
        renderTaxonomyValuesList(def);
        await loadRows();
        renderAll();
        showToast(`«${oldName}» переименовано в «${newName}» — обновлено везде, где использовалось`);
      } catch (err) { showToast(err.message, true); inp.value = oldName; }
    }));

    $$(`#${def.containerId} [data-tx-remove]`).forEach(btn => btn.addEventListener('click', async () => {
      if (!confirm(`Удалить значение «${btn.dataset.txRemove}» из справочника «${def.label}»?`)) return;
      try {
        const res = await fetch(`${TAXONOMY_API}?type=${def.type}&name=${encodeURIComponent(btn.dataset.txRemove)}`, { method: 'DELETE' });
        const json = await res.json();
        if (!json.ok) throw new Error(json.error || 'Не удалось удалить');
        await def.loader();
        renderTaxonomyValuesList(def);
        renderAll();
        showToast('Значение удалено');
      } catch (err) { showToast(err.message, true); }
    }));
  }

  function renderAllTaxonomyValuesLists() {
    TAXONOMY_UI_DEFS.forEach(renderTaxonomyValuesList);
  }

  // ---------- Настраиваемые типы документов ----------

  function renderDocTypesList() {
    $('#docTypesList').innerHTML = docTypesList.map(t => `
      <div class="quicklink-row">
        <i class="${escapeHtml(t.icon)}"></i>
        <span class="quicklink-row-label">${escapeHtml(t.label)}</span>
        <span class="quicklink-row-url">.${t.ext.join(', .')}</span>
        <button type="button" class="icon-btn danger" data-remove-doctype="${escapeHtml(t.key)}" title="Удалить">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    `).join('');
    $$('[data-remove-doctype]').forEach(btn => btn.addEventListener('click', async () => {
      if (!confirm(`Удалить тип документа «${btn.dataset.removeDoctype}»? Уже загруженные файлы этого типа останутся на диске, но ссылки на них в реестре пропадут.`)) return;
      try {
        const res = await fetch(`doc_types.php?key=${encodeURIComponent(btn.dataset.removeDoctype)}`, { method: 'DELETE' });
        const json = await res.json();
        if (!json.ok) throw new Error(json.error || 'Не удалось удалить');
        await loadDocTypes();
        renderDocTypesList();
        showToast('Тип документа удалён');
      } catch (err) { showToast(err.message, true); }
    }));
    $('#addDocTypeBtn').style.display = docTypesList.length >= 15 ? 'none' : 'inline-flex';
  }

  $('#addDocTypeBtn').addEventListener('click', () => {
    $('#addDocTypeForm').style.display = 'flex';
    $('#newDocTypeLabel').value = '';
    $('#newDocTypeExt').value = '';
    $('#addDocTypeBtn').style.display = 'none';
  });
  $('#addDocTypeCancelBtn').addEventListener('click', () => {
    $('#addDocTypeForm').style.display = 'none';
    renderDocTypesList();
  });
  $('#addDocTypeSaveBtn').addEventListener('click', async () => {
    const label = $('#newDocTypeLabel').value.trim();
    const ext = $('#newDocTypeExt').value.split(',').map(s => s.trim().toLowerCase()).filter(Boolean);
    if (!label || !ext.length) { showToast('Укажите название и хотя бы одно расширение', true); return; }
    try {
      const res = await fetch('doc_types.php', { method: 'POST', body: JSON.stringify({ label, ext, icon: 'fa-solid fa-file-lines' }) });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось добавить тип документа');
      await loadDocTypes();
      $('#addDocTypeForm').style.display = 'none';
      renderDocTypesList();
      showToast(`Тип «${json.data.label}» добавлен`);
    } catch (err) { showToast(err.message, true); }
  });

  $('#yearFilterSelect').addEventListener('change', e => {
    settings.activeYearFilter = e.target.value;
    saveSettings();
    renderAll();
  });

  function populateYearFilterSelect() {
    const years = Array.from(new Set(rows.map(r => String(r.date || '').slice(0, 4)).filter(Boolean))).sort();
    $('#yearFilterSelect').innerHTML = `<option value="">Все года</option>` +
      years.map(y => `<option value="${y}">${y}</option>`).join('');
    $('#yearFilterSelect').value = settings.activeYearFilter || '';
  }

  // ---------- Авторизация: текущий пользователь, выход, управление аккаунтами ----------

  async function loadCurrentUser() {
    try {
      const res = await fetch('auth_api.php?action=me');
      const json = await res.json();
      if (json.ok && json.data.username) {
        $('#currentUsername').textContent = json.data.username;
      } else {
        window.location.href = 'login.php';
      }
    } catch (e) {
      $('#currentUsername').textContent = '—';
    }
  }

  $('#logoutBtn').addEventListener('click', async () => {
    if (!confirm('Выйти из реестра?')) return;
    try { await fetch('auth_api.php?action=logout', { method: 'POST' }); } catch (e) { /* всё равно уходим */ }
    window.location.href = 'login.php';
  });

  async function loadUsersList() {
    try {
      const res = await fetch('auth_api.php?action=users');
      const json = await res.json();
      const users = json.ok ? json.data : [];
      $('#usersList').innerHTML = users.map(u => `
        <div class="quicklink-row user-row" data-username="${escapeHtml(u.username)}">
          <i class="fa-solid fa-user"></i>
          <span class="quicklink-row-label">${escapeHtml(u.displayName || u.username)}</span>
          <span class="quicklink-row-url">${escapeHtml(u.username)}</span>
          <button type="button" class="icon-btn" data-reset-user="${escapeHtml(u.username)}" title="Сменить пароль">
            <i class="fa-solid fa-key"></i>
          </button>
          <button type="button" class="icon-btn danger" data-remove-user="${escapeHtml(u.username)}" title="Удалить">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="reset-password-row" id="resetRow_${escapeHtml(u.username)}" style="display:none">
          <input type="password" placeholder="Новый пароль (мин. 6 символов)" data-reset-input="${escapeHtml(u.username)}">
          <button type="button" class="btn btn-primary" data-reset-confirm="${escapeHtml(u.username)}">Сохранить</button>
        </div>
      `).join('');
      $$('[data-remove-user]').forEach(btn => btn.addEventListener('click', async () => {
        if (!confirm(`Удалить пользователя «${btn.dataset.removeUser}»?`)) return;
        try {
          const res2 = await fetch(`auth_api.php?action=remove-user&username=${encodeURIComponent(btn.dataset.removeUser)}`, { method: 'DELETE' });
          const json2 = await res2.json();
          if (!json2.ok) throw new Error(json2.error || 'Не удалось удалить');
          loadUsersList();
          showToast('Пользователь удалён');
        } catch (err) { showToast(err.message, true); }
      }));
      $$('[data-reset-user]').forEach(btn => btn.addEventListener('click', () => {
        const row = $(`#resetRow_${btn.dataset.resetUser}`);
        row.style.display = row.style.display === 'none' ? 'flex' : 'none';
      }));
      $$('[data-reset-confirm]').forEach(btn => btn.addEventListener('click', async () => {
        const username = btn.dataset.resetConfirm;
        const input = $(`[data-reset-input="${username}"]`);
        const newPassword = input.value;
        if (newPassword.length < 6) { showToast('Пароль должен быть не короче 6 символов', true); return; }
        try {
          const res2 = await fetch('auth_api.php?action=reset-password', { method: 'POST', body: JSON.stringify({ username, newPassword }) });
          const json2 = await res2.json();
          if (!json2.ok) throw new Error(json2.error || 'Не удалось сменить пароль');
          input.value = '';
          $(`#resetRow_${username}`).style.display = 'none';
          showToast(`Пароль для «${username}» изменён`);
        } catch (err) { showToast(err.message, true); }
      }));
    } catch (e) { $('#usersList').innerHTML = ''; }
  }

  $('#addUserBtn').addEventListener('click', () => {
    $('#addUserForm').style.display = 'flex';
    $('#newUserLogin').value = '';
    $('#newUserPassword').value = '';
    $('#addUserBtn').style.display = 'none';
  });
  $('#addUserCancelBtn').addEventListener('click', () => {
    $('#addUserForm').style.display = 'none';
    $('#addUserBtn').style.display = 'inline-flex';
  });
  $('#addUserSaveBtn').addEventListener('click', async () => {
    const username = $('#newUserLogin').value.trim();
    const password = $('#newUserPassword').value;
    if (!username || password.length < 6) { showToast('Укажите логин и пароль от 6 символов', true); return; }
    try {
      const res = await fetch('auth_api.php?action=add-user', { method: 'POST', body: JSON.stringify({ username, password }) });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось добавить пользователя');
      $('#addUserForm').style.display = 'none';
      $('#addUserBtn').style.display = 'inline-flex';
      loadUsersList();
      showToast(`Пользователь «${username}» добавлен`);
    } catch (err) { showToast(err.message, true); }
  });

  $('#changePasswordBtn').addEventListener('click', async () => {
    const newPassword = $('#changePasswordInput').value;
    if (newPassword.length < 6) { showToast('Пароль должен быть не короче 6 символов', true); return; }
    try {
      const res = await fetch('auth_api.php?action=change-password', { method: 'POST', body: JSON.stringify({ newPassword }) });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось сменить пароль');
      $('#changePasswordInput').value = '';
      showToast('Пароль изменён');
    } catch (err) { showToast(err.message, true); }
  });

  // ---------- Модалка "Аккаунт" (пользователь + SMTP + импорт/экспорт) ----------

  const userAccountOverlay = $('#userAccountOverlay');

  async function loadSmtpSettings() {
    try {
      const res = await fetch('smtp.php');
      const json = await res.json();
      if (!json.ok) return;
      const s = json.data;
      $('#smtpHost').value = s.host || '';
      $('#smtpPort').value = s.port || 587;
      $('#smtpEncryption').value = s.encryption || 'tls';
      $('#smtpUsername').value = s.username || '';
      $('#smtpPassword').value = s.password || '';
      $('#smtpFromEmail').value = s.fromEmail || '';
      $('#smtpFromName').value = s.fromName || '';
      $('#smtpStatus').innerHTML = s.configured
        ? '<i class="fa-solid fa-circle-check"></i> SMTP настроен и готов к отправке.'
        : '<i class="fa-solid fa-circle-exclamation"></i> SMTP ещё не настроен — заполните поля ниже.';
    } catch (e) { $('#smtpStatus').textContent = 'Не удалось получить статус'; }
  }

  $('#smtpSaveBtn').addEventListener('click', async () => {
    const payload = {
      host: $('#smtpHost').value.trim(),
      port: Number($('#smtpPort').value) || 587,
      encryption: $('#smtpEncryption').value,
      username: $('#smtpUsername').value.trim(),
      password: $('#smtpPassword').value,
      fromEmail: $('#smtpFromEmail').value.trim(),
      fromName: $('#smtpFromName').value.trim(),
    };
    try {
      const res = await fetch('smtp.php', { method: 'POST', body: JSON.stringify(payload) });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось сохранить');
      showToast('Настройки SMTP сохранены');
      loadSmtpSettings();
    } catch (err) { showToast(err.message, true); }
  });

  $('#smtpTestBtn').addEventListener('click', async () => {
    const toEmail = $('#smtpTestEmail').value.trim();
    if (!toEmail) { showToast('Укажите адрес для теста', true); return; }
    showToast('Отправляю тестовое письмо…');
    try {
      const res = await fetch('smtp.php?action=test', { method: 'POST', body: JSON.stringify({ toEmail }) });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось отправить');
      showToast('Тестовое письмо отправлено');
    } catch (err) { showToast(err.message, true); }
  });

  $('#currentUsername').style.cursor = 'pointer';
  $('#currentUsername').title = 'Аккаунт, пользователи, SMTP, импорт/экспорт';
  $('#currentUsername').addEventListener('click', () => {
    $('#addUserForm').style.display = 'none';
    $('#addUserBtn').style.display = 'inline-flex';
    loadUsersList();
    loadSmtpSettings();
    userAccountOverlay.style.display = 'flex';
  });
  $('#userAccountClose').addEventListener('click', () => userAccountOverlay.style.display = 'none');
  $('#userAccountDone').addEventListener('click', () => userAccountOverlay.style.display = 'none');
  userAccountOverlay.addEventListener('click', e => { if (e.target === userAccountOverlay) userAccountOverlay.style.display = 'none'; });

  $('#settingsBtn').addEventListener('click', () => {
    $$('input[name="tableWidthMode"]').forEach(r => { r.checked = (r.value === settings.tableWidthMode); });
    $('#registryWidthSelect').value = settings.registryWidth || '1400';
    populateYearFilterSelect();
    loadContractTemplateStatus();
    $('#landingOutputBaseInput').value = settings.landingOutputBase || '';
    $('#landingLinkLengthInput').value = settings.landingLinkLength || 6;
    $('#landingBrowserTextInput').value = settings.landingBrowserText || '';
    $('#landingPortsTextInput').value = settings.landingPortsText || '';
    $('#landingSupportTextInput').value = settings.landingSupportText || '';
    $('#landingSupportPhoneInput').value = settings.landingSupportPhone || '';
    $('#landingFooterTextInput').value = settings.landingFooterText || '';
    $('#landingTooltipRecordingInput').value = settings.landingTooltipRecording || '';
    $('#landingTooltipMaterialsInput').value = settings.landingTooltipMaterials || '';
    $('#quickLinkForm').style.display = 'none';
    renderQuickLinksList();
    $('#mailRecipientForm').style.display = 'none';
    renderMailRecipientsList();
    renderAllTaxonomyValuesLists();
    renderDocTypesList();
    $('#addDocTypeForm').style.display = 'none';
    $('#addDocTypeBtn').style.display = 'inline-flex';
    renderSettingsList();
    settingsOverlay.style.display = 'flex';
  });
  $('#settingsClose').addEventListener('click', () => settingsOverlay.style.display = 'none');
  $('#settingsDone').addEventListener('click', () => settingsOverlay.style.display = 'none');
  settingsOverlay.addEventListener('click', e => { if (e.target === settingsOverlay) settingsOverlay.style.display = 'none'; });


  // ---------- JSON import / export ----------

  $('#exportJsonBtn').addEventListener('click', () => {
    const blob = new Blob([JSON.stringify(rows, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `webinars-export-${new Date().toISOString().slice(0,10)}.json`;
    a.click();
    URL.revokeObjectURL(url);
    showToast('JSON выгружен');
  });


  $('#importFileInput').addEventListener('change', async e => {
    const file = e.target.files[0];
    e.target.value = '';
    if (!file) return;
    try {
      const text = await file.text();
      const parsed = JSON.parse(text);
      if (!Array.isArray(parsed)) throw new Error('Файл должен содержать JSON-массив вебинаров');
      if (!confirm(`Заменить текущие данные (${rows.length} записей) на ${parsed.length} записей из файла?\nТекущие данные будут сохранены в бэкапе на сервере.`)) return;

      const res = await fetch(`${API}?action=import`, { method: 'POST', body: JSON.stringify(parsed) });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Ошибка импорта');
      await loadRows();
      selectedIds.clear();
      renderAll();
      showToast(`Импортировано записей: ${json.data.length}`);
    } catch (err) {
      showToast('Импорт не удался: ' + err.message, true);
    }
  });

  // ---------- Selection bulk bar / xlsx export ----------

  $('#clearSelectionBtn').addEventListener('click', () => { selectedIds.clear(); renderTable(); updateBulkBar(); });

  $('#exportXlsxBtn').addEventListener('click', () => {
    if (selectedIds.size === 0) return;
    const cols = visibleColumns();
    const selectedRows = rows.filter(r => selectedIds.has(r.id));
    const sheetData = selectedRows.map(r => {
      const obj = {};
      cols.forEach(col => {
        if (col.type === 'money') obj[col.label] = Number(r[col.key]) || 0;
        else if (col.type === 'date') obj[col.label] = formatDateRu(r[col.key]);
        else obj[col.label] = r[col.key] || '';
      });
      return obj;
    });
    const ws = XLSX.utils.json_to_sheet(sheetData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Вебинары');
    XLSX.writeFile(wb, `webinars-selected-${new Date().toISOString().slice(0,10)}.xlsx`);
    showToast(`Экспортировано в Excel: ${selectedRows.length}`);
  });

  // ---------- «Поля для CRM» ----------

  const crmFieldsOverlay = $('#crmFieldsOverlay');

  function getSelectedRowsSortedByDate() {
    return rows.filter(r => selectedIds.has(r.id)).sort((a, b) => (a.date < b.date ? -1 : a.date > b.date ? 1 : 0));
  }

  async function copyTextToClipboard(text) {
    try {
      await navigator.clipboard.writeText(text);
      return true;
    } catch (e) {
      return false;
    }
  }

  $('#crmFieldsBtn').addEventListener('click', () => {
    const selected = getSelectedRowsSortedByDate();
    if (!selected.length) { showToast('Сначала отметьте вебинары чекбоксом', true); return; }
    const lines = selected.map(r => `${formatDateRu(r.date)} ${r.speaker}`);
    $('#crmFieldsText').value = lines.join('\n');
    
    const lines2 = selected.map(r => `${formatDateRu(r.date)} «${stripGuillemets(r.title)}»`);
    $('#crmFieldsText2').value = lines2.join('\n');
    
    $('#crmFieldsCount').textContent = `${selected.length} стро${selected.length === 1 ? 'ка' : 'к'}`;
    crmFieldsOverlay.style.display = 'flex';
  });
  $('#crmFieldsClose').addEventListener('click', () => crmFieldsOverlay.style.display = 'none');
  $('#crmFieldsCloseBtn').addEventListener('click', () => crmFieldsOverlay.style.display = 'none');
  crmFieldsOverlay.addEventListener('click', e => { if (e.target === crmFieldsOverlay) crmFieldsOverlay.style.display = 'none'; });
  $('#crmFieldsCopyBtn').addEventListener('click', async () => {
    const ok = await copyTextToClipboard($('#crmFieldsText').value);
    if (ok) showToast('Скопирован список 1');
    else { $('#crmFieldsText').select(); showToast('Не удалось скопировать автоматически — текст выделен, нажмите Ctrl+C', true); }
  });
  $('#crmFieldsCopyBtn2').addEventListener('click', async () => {
    const ok = await copyTextToClipboard($('#crmFieldsText2').value);
    if (ok) showToast('Скопирован список 2');
    else { $('#crmFieldsText2').select(); showToast('Не удалось скопировать автоматически — текст выделен, нажмите Ctrl+C', true); }
  });

  // ---------- «Выгрузить для отправки» (таблица для Outlook/писем) ----------

  const sendExportOverlay = $('#sendExportOverlay');
  let sendExportPlainText = '';

  let sendExportSelectedRows = [];

  const EXPORT_FIELD_DEFS = [
    { key: 'date', label: 'Дата вебинара' },
    { key: 'price', label: 'Цена' },
    { key: 'organizer', label: 'Организатор' },
    { key: 'speaker', label: 'Спикер' },
    { key: 'title', label: 'Тема' },
    { key: 'link_participant', label: 'Ссылка для участников' },
    { key: 'link_host', label: 'Ссылка для организатора' },
    { key: 'link_materials', label: 'Ссылка на материалы' },
    { key: 'link_recording', label: 'Ссылка на запись' },
    { key: 'moderator_code', label: 'Код модератора' },
    { key: 'doc_official_letter', label: 'Официальное письмо' },
    { key: 'doc_program', label: 'Программа' },
    { key: 'doc_invitation', label: 'Приглашение' },
    { key: 'published_on_site', label: 'Размещён на сайте' },
    { key: 'mailing_list', label: 'Рассылка' },
    { key: 'direction', label: 'Направление' },
  ];

  function exportFieldValue(row, key) {
    const v = row[key];
    if (key === 'date') return formatDateRu(v);
    if (key === 'price') return formatMoney(v) + ' ₽';
    if (key === 'published_on_site') return (v === '1') ? 'Да' : 'Нет';
    return v || '';
  }

  function activeExportFields() {
    const active = (settings.sendExportFields && settings.sendExportFields.length)
      ? settings.sendExportFields : ['date', 'link_participant', 'link_host', 'moderator_code'];
    return EXPORT_FIELD_DEFS.filter(f => active.includes(f.key));
  }

  function buildSendExportTable() {
    const fields = activeExportFields();
    const headers = fields.map(f => f.label);
    const rowsHtml = sendExportSelectedRows.map(r => `
      <tr>${fields.map(f => `<td>${escapeHtml(exportFieldValue(r, f.key))}</td>`).join('')}</tr>
    `).join('');
    $('#sendExportTableWrap').innerHTML = `
      <table class="send-export-table">
        <thead><tr>${headers.map(h => `<th>${escapeHtml(h)}</th>`).join('')}</tr></thead>
        <tbody>${rowsHtml}</tbody>
      </table>
    `;
    sendExportPlainText = [headers.join('\t')]
      .concat(sendExportSelectedRows.map(r => fields.map(f => exportFieldValue(r, f.key)).join('\t')))
      .join('\n');
    $('#sendExportCount').textContent = `${sendExportSelectedRows.length} стро${sendExportSelectedRows.length === 1 ? 'ка' : 'к'}`;
  }

  function renderSendExportFieldsPanel() {
    const active = new Set(settings.sendExportFields && settings.sendExportFields.length
      ? settings.sendExportFields : ['date', 'link_participant', 'link_host', 'moderator_code']);
    $('#sendExportFieldsList').innerHTML = EXPORT_FIELD_DEFS.map(f => `
      <label class="filter-opt">
        <input type="checkbox" value="${f.key}" ${active.has(f.key) ? 'checked' : ''}>
        <span>${escapeHtml(f.label)}</span>
      </label>
    `).join('');
    $$('#sendExportFieldsList input[type="checkbox"]').forEach(box => box.addEventListener('change', () => {
      const checked = $$('#sendExportFieldsList input:checked').map(b => b.value);
      settings.sendExportFields = checked.length ? checked : ['date', 'link_participant', 'link_host', 'moderator_code'];
      saveSettings();
      buildSendExportTable();
    }));
  }

  $('#sendExportFieldsGearBtn').addEventListener('click', () => {
    const panel = $('#sendExportFieldsPanel');
    const willOpen = panel.style.display === 'none';
    if (willOpen) { renderSendExportFieldsPanel(); panel.style.display = 'block'; }
    else panel.style.display = 'none';
  });

  function renderSendExportRecipients() {
    const list = settings.mailRecipients || [];
    if (!list.length) {
      $('#sendExportRecipients').innerHTML = `<span class="hint">Нет сохранённых получателей — добавьте в «⚙ Настройка панели», или впишите email ниже вручную.</span>`;
      return;
    }
    $('#sendExportRecipients').innerHTML = list.map((r, i) => `
      <label class="mailing-list-chip" data-recipient-chip="${i}">
        <input type="checkbox" value="${escapeHtml(r.email)}">
        <i class="fa-solid fa-envelope" style="color:var(--teal)"></i>
        ${escapeHtml(r.label || r.email)}
      </label>
    `).join('');
    $$('#sendExportRecipients input[type="checkbox"]').forEach(box => box.addEventListener('change', () => {
      box.closest('.mailing-list-chip').classList.toggle('active', box.checked);
    }));
  }

  $('#sendExportBtn').addEventListener('click', () => {
    const selected = getSelectedRowsSortedByDate();
    if (!selected.length) { showToast('Сначала отметьте вебинары чекбоксом', true); return; }
    sendExportSelectedRows = selected;
    buildSendExportTable();
    $('#sendExportFieldsPanel').style.display = 'none';
    $('#sendExportCustomEmail').value = '';
    renderSendExportRecipients();
    sendExportOverlay.style.display = 'flex';
  });
  $('#sendExportClose').addEventListener('click', () => sendExportOverlay.style.display = 'none');
  $('#sendExportCloseBtn').addEventListener('click', () => sendExportOverlay.style.display = 'none');
  sendExportOverlay.addEventListener('click', e => { if (e.target === sendExportOverlay) sendExportOverlay.style.display = 'none'; });

  $('#sendExportCopyBtn').addEventListener('click', async () => {
    const html = $('#sendExportTableWrap').innerHTML;
    try {
      const item = new ClipboardItem({
        'text/html': new Blob([html], { type: 'text/html' }),
        'text/plain': new Blob([sendExportPlainText], { type: 'text/plain' }),
      });
      await navigator.clipboard.write([item]);
      showToast('Таблица скопирована — вставьте в письмо');
    } catch (e) {
      try {
        const range = document.createRange();
        range.selectNode($('#sendExportTableWrap'));
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
        document.execCommand('copy');
        sel.removeAllRanges();
        showToast('Таблица скопирована — вставьте в письмо');
      } catch (e2) {
        showToast('Не удалось скопировать автоматически — выделите таблицу и нажмите Ctrl+C', true);
      }
    }
  });

  function formatDateSlash(dateStr) {
    if (!dateStr) return '';
    const [y, m, d] = dateStr.split('-');
    return `${d}/${m}/${y}`;
  }

  const EXPORT_LINK_FIELDS = new Set(['link_participant', 'link_host', 'link_materials', 'link_recording', 'doc_official_letter', 'doc_program', 'doc_invitation']);

  $('#sendExportMailBtn').addEventListener('click', async () => {
    const checked = $$('#sendExportRecipients input:checked').map(b => b.value);
    const custom = $('#sendExportCustomEmail').value.split(',').map(s => s.trim()).filter(Boolean);
    const to = Array.from(new Set([...checked, ...custom]));
    if (!to.length) { showToast('Выберите получателя или впишите email вручную', true); return; }
    if (!sendExportSelectedRows.length) { showToast('Нет данных для отправки', true); return; }

    const dates = sendExportSelectedRows.map(r => r.date).sort();
    const subject = `Ссылки на вебинары {${formatDateSlash(dates[0])}-${formatDateSlash(dates[dates.length - 1])}}`;

    const fields = activeExportFields();
    const rowsHtml = sendExportSelectedRows.map((r, i) => `
      <tr style="background:${i % 2 ? '#EDF5F3' : '#FFFFFF'}">
        ${fields.map(f => {
          const val = exportFieldValue(r, f.key);
          const cell = EXPORT_LINK_FIELDS.has(f.key) && val
            ? `<a href="${escapeHtml(val)}" style="color:#0A5E5E;">${escapeHtml(val)}</a>`
            : escapeHtml(val || '—');
          return `<td style="padding:7px 12px;border-bottom:1px solid #E7EEEC;">${cell}</td>`;
        }).join('')}
      </tr>
    `).join('');
    const tableHtml = `
      <table class="mail-table" style="border-collapse:collapse;width:100%;font-size:13px;">
        <thead><tr>${fields.map(f => `<th style="background:#0A5E5E;color:#fff;text-align:left;padding:8px 12px;">${escapeHtml(f.label)}</th>`).join('')}</tr></thead>
        <tbody>${rowsHtml}</tbody>
      </table>
    `;

    try {
      const res = await fetch('mail_send.php', { method: 'POST', body: JSON.stringify({ to, subject, tableHtml }) });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось отправить');
      showToast(`Письмо отправлено: ${to.join(', ')}`);
    } catch (err) {
      showToast(err.message, true);
    }
  });

  // ---------- «Генерация подписчикам» (sub.json) ----------

  const subGenOverlay = $('#subGenOverlay');

  function subGenPublicUrl() {
    return new URL('sub.json', window.location.href).href;
  }

  function renderSubGenCategories() {
    const defaultChecked = new Set(['ЖКХ', 'ЗДРАВ']);
    $('#subGenCategories').innerHTML = directionsList.map(d => `
      <label class="mailing-list-chip" data-subgen-chip="${escapeHtml(d.name)}">
        <input type="checkbox" value="${escapeHtml(d.name)}" ${defaultChecked.has(d.name) ? 'checked' : ''}>
        <span class="org-dot" style="background:${d.color}"></span>
        ${escapeHtml(d.name)}
      </label>
    `).join('');
    $$('#subGenCategories input[type="checkbox"]').forEach(box => box.addEventListener('change', () => {
      box.closest('.mailing-list-chip').classList.toggle('active', box.checked);
    }));
  }

  async function loadSubGenStatus() {
    try {
      const res = await fetch('sub_generation.php?action=status');
      const json = await res.json();
      const el = $('#subGenStatus');
      if (!json.ok) { el.textContent = 'Не удалось получить статус'; return; }
      if (!json.data.exists || json.data.count === 0) {
        el.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Файл ещё пуст — сгенерируйте его, отметив хотя бы одному вебинару «Входит в подписку» = «Да».';
      } else {
        const when = json.data.generatedAt ? new Date(json.data.generatedAt).toLocaleString('ru-RU') : '—';
        el.innerHTML = `<i class="fa-solid fa-circle-check"></i> В файле сейчас ${json.data.count} вебинар(ов). Последняя генерация: ${escapeHtml(when)}.`;
      }
    } catch (e) { $('#subGenStatus').textContent = 'Не удалось получить статус'; }
  }

  $('#subGenBtn').addEventListener('click', () => {
    renderSubGenCategories();
    loadSubGenStatus();
    subGenOverlay.style.display = 'flex';
  });
  $('#subGenClose').addEventListener('click', () => subGenOverlay.style.display = 'none');
  $('#subGenCloseBtn').addEventListener('click', () => subGenOverlay.style.display = 'none');
  subGenOverlay.addEventListener('click', e => { if (e.target === subGenOverlay) subGenOverlay.style.display = 'none'; });

  $('#subGenCopyLinkBtn').addEventListener('click', () => copyLinkWithToast(subGenPublicUrl()));

  $('#subGenGenerateBtn').addEventListener('click', async () => {
    const categories = $$('#subGenCategories input:checked').map(b => b.value);
    if (!categories.length) { showToast('Выберите хотя бы одну категорию', true); return; }
    try {
      const res = await fetch('sub_generation.php?action=generate', {
        method: 'POST',
        body: JSON.stringify({ categories }),
      });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось сгенерировать');
      showToast(`sub.json пересобран: ${json.data.count} вебинар(ов)`);
      loadSubGenStatus();
    } catch (err) {
      showToast(err.message, true);
    }
  });

  // ---------- «Генерация для договоров» (Check.Мероприятия в template.json) ----------

  const contractGenOverlay = $('#contractGenOverlay');
  let contractGenLatestTemplate = null;

  async function loadContractTemplateStatus() {
    try {
      const res = await fetch('contract_template.php?action=status');
      const json = await res.json();
      const el = $('#contractTemplateStatus');
      if (!json.ok) { el.textContent = 'Не удалось получить статус'; return; }
      if (!json.data.uploaded) {
        el.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Файл ещё не загружен — загрузите template.json, чтобы пользоваться генерацией для договоров.';
      } else {
        el.innerHTML = `<i class="fa-solid fa-circle-check"></i> Загружен. Слотов «Мероприятия»: ${json.data.totalSlots}, свободно: ${json.data.emptySlots}, занято: ${json.data.filledSlots}.`;
      }
    } catch (e) {
      $('#contractTemplateStatus').textContent = 'Не удалось получить статус';
    }
  }

  $('#contractTemplateFile').addEventListener('change', async e => {
    const file = e.target.files[0];
    e.target.value = '';
    if (!file) return;
    const fd = new FormData();
    fd.append('file', file);
    try {
      const res = await fetch('contract_template.php', { method: 'POST', body: fd });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось загрузить файл');
      showToast('template.json загружен');
      loadContractTemplateStatus();
    } catch (err) {
      showToast(err.message, true);
    }
  });

  function contractTemplatePublicUrl() {
    return new URL('template.json', window.location.href).href;
  }

  async function copyLinkWithToast(url) {
    const ok = await copyTextToClipboard(url);
    if (ok) showToast('Ссылка скопирована: ' + url);
    else showToast('Не удалось скопировать — вот ссылка: ' + url, true);
  }

  const downloadBtn = $('#contractTemplateDownloadBtn');
  if (downloadBtn) {
    downloadBtn.addEventListener('click', () => {
      window.open('contract_template.php?action=download', '_blank');
    });
  }

  const copyLinkBtn = $('#contractTemplateCopyLinkBtn');
  if (copyLinkBtn) {
    copyLinkBtn.addEventListener('click', () => copyLinkWithToast(contractTemplatePublicUrl()));
  }

  $('#contractGenBtn').addEventListener('click', async () => {
    const selected = getSelectedRowsSortedByDate();
    if (!selected.length) { showToast('Сначала отметьте вебинары чекбоксом', true); return; }
    try {
      const res = await fetch('contract_template.php?action=generate', {
        method: 'POST',
        body: JSON.stringify({ webinarIds: selected.map(r => r.id) }),
      });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Не удалось сгенерировать');

      const d = json.data;
      contractGenLatestTemplate = d.template;

      const warnEl = $('#contractGenWarning');
      if (d.skippedCount > 0) {
        warnEl.style.display = 'block';
        warnEl.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> Слотов «Мероприятия» в template.json меньше, чем выбрано вебинаров — ${d.skippedCount} не поместились и не попали в файл.`;
      } else {
        warnEl.style.display = 'none';
        warnEl.innerHTML = '';
      }

      $('#contractGenSummary').textContent = `Зона «Мероприятия» пересобрана заново: заполнено ${d.filledCount} из ${d.totalSlots} слотов, остальные сброшены в «Пусто».`;

      $('#contractGenText').value = JSON.stringify(d.template, null, 2);
      contractGenOverlay.style.display = 'flex';
      loadContractTemplateStatus();
    } catch (err) {
      showToast(err.message, true);
    }
  });

  $('#contractGenClose').addEventListener('click', () => contractGenOverlay.style.display = 'none');
  $('#contractGenCloseBtn').addEventListener('click', () => contractGenOverlay.style.display = 'none');
  contractGenOverlay.addEventListener('click', e => { if (e.target === contractGenOverlay) contractGenOverlay.style.display = 'none'; });

  $('#contractGenCopyLinkBtn').addEventListener('click', () => copyLinkWithToast(contractTemplatePublicUrl()));

  $('#contractGenCopyBtn').addEventListener('click', async () => {
    const ok = await copyTextToClipboard($('#contractGenText').value);
    if (ok) showToast('Скопировано');
    else { $('#contractGenText').select(); showToast('Не удалось скопировать автоматически — текст выделен, нажмите Ctrl+C', true); }
  });

  $('#contractGenDownloadBtn').addEventListener('click', () => {
    if (!contractGenLatestTemplate) return;
    const blob = new Blob([JSON.stringify(contractGenLatestTemplate, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'template.json';
    a.click();
    URL.revokeObjectURL(url);
  });

  // ---------- Landing page builder (participant page) ----------

  const landingOverlay = $('#landingOverlay');
  let landingBackgroundUrl = '';
  let landingThemeKey = 'wood';
  let landingBackgroundIntensity = 100;
  let landingTitleWidthPct = 100;
  let landingMeta = { themes: [], icons: [], canonicalButtons: [] };
  let imageLibrary = [];

  const FONT_SLIDERS = [
    { key: 'dateFontPx',   label: 'Дата («ВЕБИНАР …»)', min: 12, max: 72, def: 54 },
    { key: 'titleFontPx',  label: 'Заголовок темы',      min: 14, max: 96, def: 46 },
    { key: 'metaFontPx',   label: 'Описания / требования / порты / поддержка', min: 12, max: 48, def: 17 },
    { key: 'buttonFontPx', label: 'Кнопки',              min: 12, max: 40, def: 16 },
    { key: 'footerFontPx', label: 'Футер',                min: 10, max: 30, def: 14 },
  ];
  let landingFontSizes = {};
  FONT_SLIDERS.forEach(s => { landingFontSizes[s.key] = s.def; });

  // Поля вебинара, из которых собираются автоматические кнопки на странице участника.
  const BUTTON_SOURCE_FIELDS = [
    { field: 'link_host',      label: 'Смотреть онлайн',            fromLabel: 'Ссылка для организатора / лектора' },
    { field: 'link_recording', label: 'Скачать запись трансляции',  fromLabel: 'Ссылка на запись' },
    { field: 'link_materials', label: 'Скачать материалы вебинара', fromLabel: 'Ссылка на материалы' },
  ];

  // ---------- Accordion (всегда свёрнут при открытии конструктора) ----------

  $$('.accordion-header').forEach(btn => btn.addEventListener('click', () => {
    btn.closest('.accordion').classList.toggle('open');
  }));

  function collapseAllAccordions() {
    $$('.accordion').forEach(a => a.classList.remove('open'));
  }

  async function loadLandingMeta() {
    try {
      const res = await fetch(`${LANDING_API}?action=meta`);
      const json = await res.json();
      if (json.ok) landingMeta = json.data;
    } catch (e) { /* останемся с пустыми списками */ }
  }

  async function loadImageLibrary() {
    try {
      const res = await fetch('images.php');
      const json = await res.json();
      imageLibrary = json.ok ? json.data : [];
    } catch (e) { imageLibrary = []; }
  }

  function renderThemeGrid() {
    $('#lp_themeGrid').innerHTML = landingMeta.themes.map(t => `
      <div class="landing-theme-item ${t.key === landingThemeKey ? 'active' : ''}" data-theme="${t.key}">
        <span class="landing-theme-swatch" style="background:${t.swatch}"></span>
        <span>${escapeHtml(t.label)}</span>
      </div>
    `).join('');
    $$('#lp_themeGrid .landing-theme-item').forEach(el => el.addEventListener('click', () => {
      landingThemeKey = el.dataset.theme;
      renderThemeGrid();
    }));
  }

  function renderSlidersGrid() {
    let html = '';
    FONT_SLIDERS.forEach(s => {
      html += `
        <div class="landing-slider-row">
          <span>${s.label}</span>
          <input type="range" data-slider="${s.key}" min="${s.min}" max="${s.max}" value="${landingFontSizes[s.key]}">
          <span class="slider-value" id="lp_sliderVal_${s.key}">${landingFontSizes[s.key]}px</span>
        </div>
      `;
      if (s.key === 'titleFontPx') {
        html += `
          <div class="landing-slider-row">
            <span>Ширина блока темы (100% = 1400px)</span>
            <input type="range" data-widthslider="titleWidthPct" min="100" max="200" step="5" value="${landingTitleWidthPct}">
            <span class="slider-value" id="lp_titleWidthVal">${landingTitleWidthPct}%</span>
          </div>
        `;
      }
    });
    $('#lp_slidersGrid').innerHTML = html;

    $$('#lp_slidersGrid [data-slider]').forEach(input => input.addEventListener('input', () => {
      const key = input.dataset.slider;
      landingFontSizes[key] = Number(input.value);
      $(`#lp_sliderVal_${key}`).textContent = `${input.value}px`;
    }));
    $('#lp_slidersGrid [data-widthslider]').addEventListener('input', e => {
      landingTitleWidthPct = Number(e.target.value);
      $('#lp_titleWidthVal').textContent = `${landingTitleWidthPct}%`;
    });
  }

  /** Превью автоматических кнопок: берутся из полей текущей формы вебинара. */
  function renderLandingButtonsPreview() {
    const linkField = key => (key === 'link_host' ? $('#f_link_host').value.trim()
      : key === 'link_recording' ? $('#f_link_recording').value.trim()
      : $('#f_link_materials').value.trim());

    $('#lp_buttonsPreview').innerHTML = BUTTON_SOURCE_FIELDS.map(b => {
      const url = linkField(b.field);
      const active = url !== '';
      return `<div class="landing-button-preview-row ${active ? '' : 'is-empty'}">
        <span class="landing-button-preview-dot ${active ? 'on' : ''}"></span>
        <span class="landing-button-preview-label">${escapeHtml(b.label)}</span>
        <span class="landing-button-preview-src">${active ? escapeHtml(url) : `нет ссылки — заполните «${escapeHtml(b.fromLabel)}»`}</span>
      </div>`;
    }).join('');
  }

  // ---------- Кастомные кнопки (добавляются после автоматических) ----------

  function addCustomButtonRow(data = { label: '', url: '', icon: 'link' }) {
    const row = document.createElement('div');
    row.className = 'landing-button-row';
    const iconOptions = (landingMeta.icons.length ? landingMeta.icons : [{ key: 'link', label: 'Ссылка' }])
      .map(o => `<option value="${o.key}" ${o.key === data.icon ? 'selected' : ''}>${escapeHtml(o.label)}</option>`).join('');
    row.innerHTML = `
      <input data-role="label" placeholder="Название кнопки" value="${escapeHtml(data.label || '')}">
      <input data-role="url" placeholder="https://…" value="${escapeHtml(data.url || '')}">
      <select data-role="icon">${iconOptions}</select>
      <button type="button" class="icon-btn danger" data-role="remove" title="Удалить кнопку">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    `;
    row.querySelector('[data-role="remove"]').addEventListener('click', () => row.remove());
    $('#lp_customButtonsList').appendChild(row);
  }

  function collectCustomButtons() {
    return $$('#lp_customButtonsList .landing-button-row').map(row => ({
      label: row.querySelector('[data-role="label"]').value.trim(),
      url: row.querySelector('[data-role="url"]').value.trim(),
      icon: row.querySelector('[data-role="icon"]').value,
    })).filter(b => b.label);
  }

  $('#lp_addCustomButtonBtn').addEventListener('click', () => addCustomButtonRow());

  function setImagePreview(el, url) {
    if (url) {
      el.style.backgroundImage = `url('${url}')`;
      el.textContent = '';
    } else {
      el.style.backgroundImage = '';
      el.textContent = 'Нет изображения';
    }
  }

  async function uploadLandingImage(file) {
    const fd = new FormData();
    fd.append('file', file);
    const res = await fetch(LANDING_UPLOAD_API, { method: 'POST', body: fd });
    const json = await res.json();
    if (!json.ok) throw new Error(json.error || 'Не удалось загрузить изображение');
    return json.data.url;
  }

  function renderBackgroundGallery() {
    const el = $('#lp_backgroundGallery');
    const items = [];
    landingMeta.themes.forEach(t => items.push({ url: t.baseImage, title: 'Тема: ' + t.label }));
    imageLibrary.forEach(img => items.push({ url: img.url, title: img.fileName }));
    if (items.length === 0) {
      el.innerHTML = `<div class="landing-gallery-empty">Пока ничего не загружено — загрузите первое изображение.</div>`;
      return;
    }
    el.innerHTML = items.map(it => `
      <div class="landing-gallery-item ${it.url === landingBackgroundUrl ? 'active' : ''}" style="background-image:url('${it.url}')" data-url="${escapeHtml(it.url)}" title="${escapeHtml(it.title)}"></div>
    `).join('');
    $$('.landing-gallery-item', el).forEach(item => item.addEventListener('click', () => {
      landingBackgroundUrl = item.dataset.url;
      setImagePreview($('#lp_backgroundPreview'), landingBackgroundUrl);
      el.style.display = 'none';
    }));
  }

  $('[data-gallery-toggle="background"]').addEventListener('click', () => {
    const el = $('#lp_backgroundGallery');
    const willOpen = el.style.display === 'none';
    if (willOpen) { renderBackgroundGallery(); el.style.display = 'grid'; }
    else el.style.display = 'none';
  });

  $('[data-gallery-clear="background"]').addEventListener('click', () => {
    landingBackgroundUrl = '';
    setImagePreview($('#lp_backgroundPreview'), '');
  });

  $('#lp_backgroundFile').addEventListener('change', async e => {
    const file = e.target.files[0];
    e.target.value = '';
    if (!file) return;
    try {
      landingBackgroundUrl = await uploadLandingImage(file);
      setImagePreview($('#lp_backgroundPreview'), landingBackgroundUrl);
      await loadImageLibrary();
      showToast('Фон загружен и добавлен в библиотеку');
    } catch (err) { showToast(err.message, true); }
  });

  $('#lp_backgroundIntensity').addEventListener('input', e => {
    landingBackgroundIntensity = Number(e.target.value);
    $('#lp_backgroundIntensityVal').textContent = `${landingBackgroundIntensity}%`;
  });

  function renderLandingReadonlySummary() {
    const title = $('#f_title').value.trim() || '—';
    const date = $('#f_date').value ? formatDateRu($('#f_date').value) : '—';
    const organizerSel = $('#f_organizer');
    const organizer = organizerSel.options[organizerSel.selectedIndex] ? organizerSel.options[organizerSel.selectedIndex].text : '—';
    const speaker = $('#f_speaker').value.trim() || '—';
    $('#landingReadonlySummary').innerHTML = `
      <span><strong>${escapeHtml(organizer)}</strong></span>
      <span>${escapeHtml(date)}</span>
      <span>${escapeHtml(speaker)}</span>
      <span style="flex-basis:100%">«${escapeHtml(title)}»</span>
    `;
  }

  function renderLandingStatus(record) {
    const el = $('#landingStatus');
    if (record && record.publicUrl) {
      el.classList.remove('is-empty');
      el.innerHTML = `Уже опубликовано: <a href="${escapeHtml(record.publicUrl)}" target="_blank" rel="noopener">${escapeHtml(record.publicUrl)}</a>`;
    } else {
      el.classList.add('is-empty');
      el.textContent = 'Страница ещё не опубликована.';
    }
  }

  /** Загружает конструктор состоянием по умолчанию (общий шаблон из настроек). */
  function applyDefaultTemplate() {
    const tmpl = settings.landingDefaultTemplate || {};
    landingThemeKey = tmpl.themePreset || 'wood';
    FONT_SLIDERS.forEach(s => { landingFontSizes[s.key] = tmpl[s.key] || s.def; });
    landingBackgroundUrl = tmpl.backgroundImage || '';
    landingBackgroundIntensity = tmpl.backgroundIntensity ?? 100;
    landingTitleWidthPct = tmpl.titleWidthPct ?? 100;
  }

  async function openLandingModal() {
    const webinarId = $('#f_id').value;
    if (!webinarId) {
      showToast('Сначала сохраните вебинар — потом можно будет собрать для него страницу', true);
      return;
    }
    if (!landingMeta.themes.length) await loadLandingMeta();
    await loadImageLibrary();

    renderLandingReadonlySummary();
    renderLandingButtonsPreview();
    collapseAllAccordions();
    $('#lp_eventTime').value = '10:00';
    $('#lp_backgroundGallery').style.display = 'none';
    $('#lp_customButtonsList').innerHTML = '';
    $('#lp_regenerateCode').checked = false;
    applyDefaultTemplate();
    setImagePreview($('#lp_backgroundPreview'), landingBackgroundUrl);
    $('#lp_backgroundIntensity').value = landingBackgroundIntensity;
    $('#lp_backgroundIntensityVal').textContent = `${landingBackgroundIntensity}%`;
    renderLandingStatus(null);
    $('#landingHint').textContent = '';

    try {
      const res = await fetch(`${LANDING_API}?webinarId=${encodeURIComponent(webinarId)}`);
      const json = await res.json();
      const record = json.ok ? json.data : null;
      if (record) {
        $('#lp_eventTime').value = record.eventTime || '10:00';
        landingThemeKey = record.themePreset || landingThemeKey;
        FONT_SLIDERS.forEach(s => { landingFontSizes[s.key] = record[s.key] || landingFontSizes[s.key]; });
        landingBackgroundUrl = record.backgroundImage || '';
        landingBackgroundIntensity = record.backgroundIntensity ?? landingBackgroundIntensity;
        landingTitleWidthPct = record.titleWidthPct ?? landingTitleWidthPct;
        setImagePreview($('#lp_backgroundPreview'), landingBackgroundUrl);
        $('#lp_backgroundIntensity').value = landingBackgroundIntensity;
        $('#lp_backgroundIntensityVal').textContent = `${landingBackgroundIntensity}%`;
        (record.customButtons || []).forEach(b => addCustomButtonRow(b));
        renderLandingStatus(record);
      }
    } catch (err) { /* черновика ещё нет — не страшно */ }

    renderThemeGrid();
    renderSlidersGrid();
    landingOverlay.style.display = 'flex';
  }

  function closeLandingModal() { landingOverlay.style.display = 'none'; }

  function collectLandingPayload() {
    return {
      webinarId: $('#f_id').value,
      eventTime: $('#lp_eventTime').value || '10:00',
      themePreset: landingThemeKey,
      dateFontPx: landingFontSizes.dateFontPx,
      titleFontPx: landingFontSizes.titleFontPx,
      metaFontPx: landingFontSizes.metaFontPx,
      buttonFontPx: landingFontSizes.buttonFontPx,
      footerFontPx: landingFontSizes.footerFontPx,
      backgroundImage: landingBackgroundUrl,
      backgroundIntensity: landingBackgroundIntensity,
      titleWidthPct: landingTitleWidthPct,
      customButtons: collectCustomButtons(),
      regenerateFileName: $('#lp_regenerateCode').checked,
    };
  }

  $('#openLandingBtn').addEventListener('click', openLandingModal);
  $('#landingClose').addEventListener('click', closeLandingModal);
  landingOverlay.addEventListener('click', e => { if (e.target === landingOverlay) closeLandingModal(); });

  $('#lp_saveDefaultBtn').addEventListener('click', () => {
    settings.landingDefaultTemplate = {
      themePreset: landingThemeKey,
      dateFontPx: landingFontSizes.dateFontPx,
      titleFontPx: landingFontSizes.titleFontPx,
      metaFontPx: landingFontSizes.metaFontPx,
      buttonFontPx: landingFontSizes.buttonFontPx,
      footerFontPx: landingFontSizes.footerFontPx,
      backgroundImage: landingBackgroundUrl,
      backgroundIntensity: landingBackgroundIntensity,
      titleWidthPct: landingTitleWidthPct,
    };
    saveSettings();
    showToast('Шаблон по умолчанию сохранён — новые вебинары будут использовать эти настройки');
  });

  // ---------- Landing page templates & saving ----------

  $('#lp_saveNamedTemplateBtn').addEventListener('click', async () => {
    const name = $('#lp_newTemplateName').value.trim();
    if (!name) { showToast('Введите имя шаблона', true); return; }
    if (!settings.landingTemplates) settings.landingTemplates = {};
    settings.landingTemplates[name] = {
      themePreset: landingThemeKey,
      dateFontPx: landingFontSizes.dateFontPx,
      titleFontPx: landingFontSizes.titleFontPx,
      metaFontPx: landingFontSizes.metaFontPx,
      buttonFontPx: landingFontSizes.buttonFontPx,
      footerFontPx: landingFontSizes.footerFontPx,
      backgroundImage: landingBackgroundUrl,
      backgroundIntensity: landingBackgroundIntensity,
      titleWidthPct: landingTitleWidthPct,
    };
    await saveSettings();
    populateTemplatesDropdowns();
    $('#lp_newTemplateName').value = '';
    showToast(`Шаблон «${name}» сохранён!`);
  });

  $('#lp_templateSelect').addEventListener('change', e => {
    const name = e.target.value;
    if (!name) return;
    const tpl = settings.landingTemplates[name];
    if (!tpl) return;
    landingThemeKey = tpl.themePreset || 'wood';
    FONT_SLIDERS.forEach(s => { landingFontSizes[s.key] = tpl[s.key] || landingFontSizes[s.key]; });
    landingBackgroundUrl = tpl.backgroundImage || '';
    landingBackgroundIntensity = tpl.backgroundIntensity !== undefined ? tpl.backgroundIntensity : 100;
    landingTitleWidthPct = tpl.titleWidthPct !== undefined ? tpl.titleWidthPct : 100;
    renderThemeGrid();
    renderSlidersGrid();
    setImagePreview($('#lp_backgroundPreview'), landingBackgroundUrl);
    $('#lp_backgroundIntensity').value = landingBackgroundIntensity;
    $('#lp_backgroundIntensityVal').textContent = `${landingBackgroundIntensity}%`;
  });

  // ---------- Массовое редактирование (Bulk Editing) & Ссылки ----------

  const bulkEditOverlay = $('#bulkEditOverlay');

  function renderBulkEditRows(selected) {
    $('#bulkEditTableBody').innerHTML = selected.map(w => `
      <tr data-bulk-id="${w.id}" style="border-bottom: 1px solid var(--line);">
        <td style="padding: 10px 12px; vertical-align: top;">
          <div style="font-weight: 700; color: var(--ink); margin-bottom: 4px;">${formatDateRu(w.date)}</div>
          <div style="color: var(--ink-soft); font-size: 12px; margin-bottom: 8px; line-height: 1.3;">${escapeHtml(w.title)}</div>
          <div style="display: flex; gap: 4px;">
            <button type="button" class="btn btn-ghost" data-copy-bulk-date="${w.id}" title="Скопировать дату" style="padding: 4px 8px; height: 26px; font-size: 11px;"><i class="fa-regular fa-calendar-days"></i> Дата</button>
            <button type="button" class="btn btn-ghost" data-copy-bulk-theme="${w.id}" title="Скопировать тему" style="padding: 4px 8px; height: 26px; font-size: 11px;"><i class="fa-regular fa-font"></i> Тема</button>
            <button type="button" class="btn btn-ghost" data-copy-bulk-both="${w.id}" title="Скопировать дату и тему" style="padding: 4px 8px; height: 26px; font-size: 11px;"><i class="fa-regular fa-copy"></i> Всё</button>
          </div>
        </td>
        <td style="padding: 10px 12px; vertical-align: top;"><input type="text" data-bulk-field="link_participant" value="${escapeHtml(w.link_participant || '')}" placeholder="https://..." style="width: 100%; border: 1px solid var(--line); border-radius: 6px; padding: 6px 8px; font-size:12.5px;"></td>
        <td style="padding: 10px 12px; vertical-align: top;"><input type="text" data-bulk-field="link_host" value="${escapeHtml(w.link_host || '')}" placeholder="https://..." style="width: 100%; border: 1px solid var(--line); border-radius: 6px; padding: 6px 8px; font-size:12.5px;"></td>
        <td style="padding: 10px 12px; vertical-align: top;"><input type="text" data-bulk-field="moderator_code" value="${escapeHtml(w.moderator_code || '')}" placeholder="1600" style="width: 100%; border: 1px solid var(--line); border-radius: 6px; padding: 6px 8px; font-size:12.5px;"></td>
        <td style="padding: 10px 12px; vertical-align: top;"><input type="text" data-bulk-field="link_materials" value="${escapeHtml(w.link_materials || '')}" placeholder="https://..." style="width: 100%; border: 1px solid var(--line); border-radius: 6px; padding: 6px 8px; font-size:12.5px;"></td>
        <td style="padding: 10px 12px; vertical-align: top;"><input type="text" data-bulk-field="link_recording" value="${escapeHtml(w.link_recording || '')}" placeholder="https://..." style="width: 100%; border: 1px solid var(--line); border-radius: 6px; padding: 6px 8px; font-size:12.5px;"></td>
      </tr>
    `).join('');

    // Привязываем события копирования к кнопкам
    selected.forEach(w => {
      const btnDate = $(`[data-copy-bulk-date="${w.id}"]`);
      if (btnDate) btnDate.addEventListener('click', () => { copyTextToClipboard(formatDateRu(w.date)); showToast('Дата скопирована'); });

      const btnTheme = $(`[data-copy-bulk-theme="${w.id}"]`);
      if (btnTheme) btnTheme.addEventListener('click', () => { copyTextToClipboard(wrapTitle(w.title)); showToast('Тема скопирована'); });

      const btnBoth = $(`[data-copy-bulk-both="${w.id}"]`);
      if (btnBoth) btnBoth.addEventListener('click', () => { copyTextToClipboard(`${formatDateRu(w.date)} ${wrapTitle(w.title)}`); showToast('Дата и тема скопированы'); });
    });
  }

  // Снятие внешних кавычек перед оборачиванием
  function wrapTitle(s) {
    s = String(s ?? '').trim();
    s = s.replace(/^«|»$/g, '').trim();
    return s ? `«${s}»` : '';
  }

  $('#bulkEditBtn').addEventListener('click', async () => {
    const selected = getSelectedRowsSortedByDate();
    if (!selected.length) { showToast('Сначала отметьте вебинары чекбоксом', true); return; }

    $('#bulkEditSelectionCount').textContent = `Выбрано вебинаров для редактирования: ${selected.length}`;
    renderBulkEditRows(selected);

    // Загрузка шаблонов в селект
    populateTemplatesDropdowns();

    $('#bulk_lp_linkLength').value = settings.landingLinkLength || 6;
    $('#bulk_lp_regenerateCode').checked = false;
    $('#bulk_lp_newTemplateName').value = '';

    bulkEditOverlay.style.display = 'flex';
  });

  if ($('#bulkEditClose')) $('#bulkEditClose').addEventListener('click', () => bulkEditOverlay.style.display = 'none');
  if ($('#bulkEditCancelBtn')) $('#bulkEditCancelBtn').addEventListener('click', () => bulkEditOverlay.style.display = 'none');

  // Сохранить все изменения из таблицы
  $('#bulkEditSaveBtn').addEventListener('click', async () => {
    const rowsToSave = [];
    const trs = Array.from(document.querySelectorAll('#bulkEditTableBody tr'));
    
    for (const tr of trs) {
      const id = Number(tr.dataset.bulkId);
      const original = rows.find(r => r.id === id);
      if (!original) continue;

      const payload = { ...original };
      payload.link_participant = tr.querySelector('[data-bulk-field="link_participant"]').value.trim();
      payload.link_host = tr.querySelector('[data-bulk-field="link_host"]').value.trim();
      payload.moderator_code = tr.querySelector('[data-bulk-field="moderator_code"]').value.trim();
      payload.link_materials = tr.querySelector('[data-bulk-field="link_materials"]').value.trim();
      payload.link_recording = tr.querySelector('[data-bulk-field="link_recording"]').value.trim();

      rowsToSave.push({ id, payload });
    }

    const btn = $('#bulkEditSaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Сохраняем…';

    let success = 0;
    let fail = 0;

    for (const item of rowsToSave) {
      try {
        const res = await fetch(API, { method: 'PUT', body: JSON.stringify({ id: item.id, ...item.payload }) });
        const json = await res.json();
        if (json.ok) success++; else fail++;
      } catch (e) { fail++; }
    }

    await loadRows();
    renderAll();

    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Сохранить изменения';

    if (fail === 0) {
      showToast(`Успешно сохранено вебинаров: ${success}`);
      bulkEditOverlay.style.display = 'none';
    } else {
      showToast(`Сохранено: ${success}, ошибок: ${fail}`, true);
    }
  });

  // Сохранение шаблона прямо из массового редактирования
  $('#bulk_lp_saveTemplateBtn').addEventListener('click', async () => {
    const name = $('#bulk_lp_newTemplateName').value.trim();
    if (!name) { showToast('Укажите имя для шаблона', true); return; }
    if (!settings.landingTemplates) settings.landingTemplates = {};

    const selVal = $('#bulk_lp_templateSelect').value;
    const src = selVal ? settings.landingTemplates[selVal] : (settings.landingDefaultTemplate || {});

    settings.landingTemplates[name] = {
      themePreset: src.themePreset || 'wood',
      dateFontPx: src.dateFontPx || 54,
      titleFontPx: src.titleFontPx || 46,
      metaFontPx: src.metaFontPx || 17,
      buttonFontPx: src.buttonFontPx || 16,
      footerFontPx: src.footerFontPx || 14,
      backgroundImage: src.backgroundImage || '',
      backgroundIntensity: src.backgroundIntensity !== undefined ? src.backgroundIntensity : 100,
      titleWidthPct: src.titleWidthPct !== undefined ? src.titleWidthPct : 100,
    };

    await saveSettings();
    populateTemplatesDropdowns();
    $('#bulk_lp_templateSelect').value = name;
    $('#bulk_lp_newTemplateName').value = '';
    showToast(`Шаблон «${name}» сохранён!`);
  });

  // Генерация страниц для массового редактирования
  async function publishBulkLanding(webinar, templateOptionName, lengthVal) {
    const src = templateOptionName ? settings.landingTemplates[templateOptionName] : (settings.landingDefaultTemplate || {});
    const payload = {
      webinarId: String(webinar.id),
      eventTime: '10:00',
      themePreset: src.themePreset || 'wood',
      dateFontPx: src.dateFontPx || 54,
      titleFontPx: src.titleFontPx || 46,
      metaFontPx: src.metaFontPx || 17,
      buttonFontPx: src.buttonFontPx || 16,
      footerFontPx: src.footerFontPx || 14,
      backgroundImage: src.backgroundImage || '',
      backgroundIntensity: src.backgroundIntensity !== undefined ? src.backgroundIntensity : 100,
      titleWidthPct: src.titleWidthPct !== undefined ? src.titleWidthPct : 100,
      customButtons: [],
      regenerateFileName: $('#bulk_lp_regenerateCode').checked,
    };

    const res = await fetch(`${LANDING_API}?action=publish`, { method: 'POST', body: JSON.stringify(payload) });
    const json = await res.json();
    if (!json.ok) throw new Error(json.error || 'Не удалось опубликовать страницу');
    return json.data;
  }

  $('#bulk_lp_generateBtn').addEventListener('click', async () => {
    const selected = getSelectedRowsSortedByDate();
    if (!selected.length) return;

    let lengthVal = parseInt($('#bulk_lp_linkLength').value, 10);
    if (isNaN(lengthVal) || lengthVal < 4) lengthVal = 4;
    if (lengthVal > 32) lengthVal = 32;

    settings.landingLinkLength = lengthVal;
    await saveSettings();

    const tplName = $('#bulk_lp_templateSelect').value;

    const btn = $('#bulk_lp_generateBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Выполняется…';

    let successCount = 0;
    let failCount = 0;

    for (const w of selected) {
      try {
        const data = await publishBulkLanding(w, tplName, lengthVal);
        successCount++;
        // Находим строку в таблице оверлея и заполняем новое значение ссылки
        const trInput = $(`tr[data-bulk-id="${w.id}"] [data-bulk-field="link_participant"]`);
        if (trInput) trInput.value = data.publicUrl;
      } catch (err) {
        failCount++;
      }
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-rotate"></i> Сгенерировать страницы';

    if (failCount === 0) {
      showToast(`Сгенерировано страниц участников: ${successCount}`);
    } else {
      showToast(`Сгенерировано: ${successCount}, ошибок: ${failCount}`, true);
    }
  });

  async function publishLanding(webinarId) {
    const payload = webinarId ? { ...collectLandingPayload(), webinarId: String(webinarId) } : collectLandingPayload();
    const res = await fetch(`${LANDING_API}?action=publish`, { method: 'POST', body: JSON.stringify(payload) });
    const json = await res.json();
    if (!json.ok) throw new Error(json.error || 'Не удалось опубликовать страницу');
    return json.data;
  }

  $('#landingPublishBtn').addEventListener('click', async () => {
    try {
      const data = await publishLanding();
      $('#f_link_participant').value = data.publicUrl;
      renderLandingStatus({ publicUrl: data.publicUrl });
      await loadRows();
      renderAll();
      showToast('Страница опубликована, ссылка подставлена в форму');
      closeLandingModal();
    } catch (err) {
      showToast(err.message, true);
    }
  });

  // ---------- Toast ----------

  let toastTimer = null;
  function showToast(msg, isError = false) {
    const el = $('#toast');
    el.textContent = msg;
    el.classList.toggle('error', isError);
    el.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('show'), 2800);
  }

  // ---------- Init ----------

  (async function init() {
    try {
      await loadCurrentUser();
      await loadColumns();
      await loadOrganizers();
      await loadMailingLists();
      await loadDirections();
      await loadSpeakers();
      await loadPublishedOnSiteList();
      await loadInSubscriptionList();
      await loadDocTypes();
      await loadSettingsFromServer();
      loadFiltersFromSettings();
      applyRegistryWidth();
      await loadLandingMeta();
      await loadRows();
      renderAll();
      $('#dataTable').addEventListener('dblclick', e => {
        const td = e.target.closest('td.title-cell');
        if (!td) return;
        const tr = td.closest('tr');
        if (!tr) return;
        const id = Number(tr.dataset.id);
        if (!isNaN(id)) {
          openModal(id);
        }
      });
    } catch (err) {
      $('#tableHead').innerHTML = '';
      $('#tableBody').innerHTML = `<tr><td><div class="empty-state"><strong>Не удалось загрузить данные</strong>${escapeHtml(err.message)}</div></td></tr>`;
    }
  })();

})();
