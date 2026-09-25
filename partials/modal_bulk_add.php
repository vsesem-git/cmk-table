<!-- Модальное окно массового добавления вебинаров (колонки как в «Новый вебинар», без документов) -->
<div id="bulkAddOverlay" class="modal-overlay" style="display:none">
  <div class="modal modal-wider" style="max-width: 1560px; max-height: 95vh;">
    <div class="modal-header">
      <h2 id="bulkAddTitle"><i class="fa-solid fa-table-list"></i> Массовое добавление вебинаров</h2>
      <button type="button" class="icon-btn" id="bulkAddClose">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body" style="padding: 14px 18px; overflow: auto;">
      <div class="hint" style="margin-bottom: 10px;">
        <i class="fa-solid fa-circle-info"></i> Колонки как в форме «Новый вебинар», кроме документов. Пустые строки не сохраняются; для каждой строки обязательны дата, тема, организатор, спикер и цена.
      </div>

      <div style="border: 1px solid var(--line); border-radius: 10px; overflow: auto; max-height: 60vh; background: #fff;">
        <table id="bulkAddTable" class="bulk-add-table" style="width: 100%; border-collapse: collapse; min-width: 1900px; font-size: 13px;">
          <thead id="bulkAddTableHead"></thead>
          <tbody id="bulkAddTableBody"></tbody>
        </table>
      </div>

      <div style="display: flex; gap: 10px; align-items: center; margin-top: 10px;">
        <button type="button" class="btn btn-ghost" id="bulkAddRowBtn"><i class="fa-solid fa-plus"></i> Добавить строку</button>
        <span class="hint" id="bulkAddRowCount"></span>
      </div>

      <div style="display: flex; align-items: center; gap: 10px; margin-top: 12px; border-top: 1px dashed var(--line); padding-top: 12px;">
        <label class="auto-gen-toggle" title="Сразу после создания опубликовать страницу участника по шаблону по умолчанию">
          <input type="checkbox" id="bulkAddAutoPublish" checked>
          <span class="toggle-track"><span class="toggle-thumb"></span></span>
        </label>
        <span style="font-size: 13px;">Автоматически опубликовать страницы участников <span class="hint">(по шаблону по умолчанию)</span></span>
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
