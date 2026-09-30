<!-- Модальное окно массового добавления вебинаров (колонки как в «Новый вебинар», без документов) -->
<div id="bulkAddOverlay" class="modal-overlay" style="display:none">
  <div class="modal modal-wider" style="max-width: 1560px; max-height: 95vh;">
    <div class="modal-header">
      <h2 id="bulkAddTitle"><i class="fa-solid fa-table-list"></i> Массовое добавление вебинаров</h2>
      <button type="button" class="icon-btn" id="bulkAddClose">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body modal-body-stack" style="padding: 14px 18px; overflow: auto;">

      <div style="border: 1px solid var(--line); border-radius: 10px; overflow: auto; max-height: 60vh; background: #fff;">
        <table id="bulkAddTable" class="bulk-add-table" style="width: 100%; border-collapse: collapse; min-width: 2050px; font-size: 13px;">
          <thead id="bulkAddTableHead"></thead>
          <tbody id="bulkAddTableBody"></tbody>
        </table>
      </div>

      <div style="display: flex; gap: 10px; align-items: center; margin-top: 10px;">
        <button type="button" class="btn btn-ghost" id="bulkAddRowBtn"><i class="fa-solid fa-plus"></i> Добавить строку</button>
        <span class="hint" id="bulkAddRowCount"></span>
      </div>

      <!-- Настройки публикации страниц участников -->
      <div class="accordion" data-accordion="bulkadd-publish" style="margin-top: 12px; border: 1px solid var(--line); border-radius: 8px;">
        <button type="button" class="accordion-header">
          <span><i class="fa-solid fa-upload"></i> Публикация страниц участников</span>
          <span class="accordion-arrow">▾</span>
        </button>
        <div class="accordion-body" style="padding: 12px 14px;">
          <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
            <label class="auto-gen-toggle" title="Сразу после создания опубликовать страницу участника">
              <input type="checkbox" id="bulkAddAutoPublish" checked>
              <span class="toggle-track"><span class="toggle-thumb"></span></span>
            </label>
            <span style="font-size: 13px;">Автоматически опубликовать страницы участников</span>
          </div>

          <div style="margin-bottom: 10px;">
            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;"><i class="fa-solid fa-palette"></i> Шаблон оформления</label>
            <select id="bulkAddLpTemplate" style="width: 100%; border: 1px solid var(--line); border-radius: 8px; padding: 7px 10px; font-size: 13px; background: #fff;"></select>
            <div class="hint" style="margin-top: 4px;">«По умолчанию» — из настроек; для вебинаров организатора «КОДЕКС» по умолчанию используется тема КОДЕКС.</div>
          </div>

          <div>
            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;"><i class="fa-solid fa-link"></i> Длина кода ссылки</label>
            <input type="number" id="bulkAddLinkLength" min="4" max="32" value="4" style="width: 140px; border: 1px solid var(--line); border-radius: 8px; padding: 7px 10px; font-size: 13px;">
          </div>
        </div>
      </div>

      <div class="landing-status" id="bulkAddStatus" style="margin-top: 8px;"></div>
    </div>
    <div class="modal-footer">
      <span class="hint" style="margin-right: auto;">Новые вебинары добавятся в конец реестра</span>
      <button type="button" class="btn btn-ghost" id="bulkAddCancelBtn"><i class="fa-solid fa-xmark"></i> Отмена</button>
      <button type="button" class="btn btn-primary" id="bulkAddSubmitBtn"><i class="fa-solid fa-plus"></i> Добавить вебинары</button>
    </div>
  </div>
</div>
