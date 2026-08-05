  <header class="masthead">
    <div class="brand">
      <div class="brand-mark">ЦМК</div>
      <div class="brand-text">
        <h1>Реестр вебинаров</h1>
        <p>vsesem.ru · расписание, ссылки и стоимость мероприятий</p>
      </div>
    </div>
    <div class="stat-strip" id="statStrip"><!-- заполняется JS --></div>
    <div class="user-chip">
      <i class="fa-solid fa-user"></i>
      <span id="currentUsername">…</span>
      <button type="button" class="icon-btn" id="logoutBtn" title="Выйти">
        <i class="fa-solid fa-right-from-bracket"></i>
      </button>
    </div>
  </header>

  <div class="toolbar">
    <div class="search-box">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" id="searchInput" placeholder="Поиск по теме, спикеру, организатору, цене…">
    </div>
    <div class="filter-pills" id="filterPills"></div>
    <button class="btn btn-ghost" id="resetFiltersBtn" style="display:none"><i class="fa-solid fa-filter-circle-xmark"></i> Сбросить фильтры</button>
    <button class="btn btn-primary" id="addBtn">
      <i class="fa-solid fa-plus"></i> Добавить вебинар
    </button>
  </div>

  <div class="toolbar toolbar-secondary">
    <button class="btn btn-ghost" id="settingsBtn"><i class="fa-solid fa-gear"></i> Настройка панели</button>
    <button class="btn btn-ghost" id="crmFieldsBtn"><i class="fa-solid fa-address-card"></i> Поля для CRM</button>
    <button class="btn btn-ghost" id="sendExportBtn"><i class="fa-solid fa-paper-plane"></i> Выгрузить для отправки</button>
    <button class="btn btn-ghost" id="contractGenBtn"><i class="fa-solid fa-file-contract"></i> Генерация для договоров</button>
    <button class="btn btn-ghost" id="subGenBtn"><i class="fa-solid fa-users-rectangle"></i> Генерация подписчикам</button>
  </div>

  <div id="bulkBar" class="bulk-bar" style="display:none">
    <span id="bulkCount"><i class="fa-solid fa-check-double"></i> Выбрано: 0</span>
    <button class="btn btn-primary" id="bulkEditBtn" style="color: #ffffff; background: var(--teal-dark); border-color: var(--teal-dark); margin-right: 8px;"><i class="fa-solid fa-pen-to-square"></i> Массовое редактирование</button>
    <button class="btn btn-primary" id="exportXlsxBtn"><i class="fa-solid fa-file-excel"></i> Экспорт в Excel (выбранные)</button>
    <button class="btn btn-ghost" id="clearSelectionBtn"><i class="fa-solid fa-xmark"></i> Снять выделение</button>
  </div>

  <div class="table-wrap">
    <table id="dataTable">
      <colgroup id="tableColgroup"></colgroup>
      <thead id="tableHead"></thead>
      <tbody id="tableBody"></tbody>
    </table>
  </div>
