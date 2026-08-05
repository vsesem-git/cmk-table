<!-- Модальное окно "Выгрузить для отправки" -->
<div id="sendExportOverlay" class="modal-overlay" style="display:none">
  <div class="modal modal-wide">
    <div class="modal-header">
      <h2><i class="fa-solid fa-paper-plane"></i> Выгрузить для отправки</h2>
      <div style="display:flex;align-items:center;gap:6px">
        <button class="icon-btn" id="sendExportFieldsGearBtn" title="Выбрать поля для выгрузки">
          <i class="fa-solid fa-gear"></i>
        </button>
        <button class="icon-btn" id="sendExportClose">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </div>
    <div class="send-export-fields-panel" id="sendExportFieldsPanel" style="display:none">
      <div class="settings-group-title" style="margin-top:0"><i class="fa-solid fa-list-check"></i> Какие поля выгружать</div>
      <div id="sendExportFieldsList" class="ml-dropdown-list" style="max-height:220px"></div>
    </div>
    <div class="modal-body modal-body-stack">
      <p class="hint" style="margin:0 0 10px">Выделите таблицу или нажмите «Скопировать» — вставляется прямо в письмо/Outlook с сохранением таблицы.</p>
      <div id="sendExportTableWrap" class="send-export-table-wrap"></div>

      <div class="landing-section-title" style="margin-top:16px"><i class="fa-solid fa-envelope"></i> Отправить на почту</div>
      <div id="sendExportRecipients" class="mailing-list-checkboxes"></div>
      <div class="landing-grid" style="margin-top:8px">
        <div class="field field-full">
          <input type="email" id="sendExportCustomEmail" placeholder="Или впишите email вручную (через запятую, если несколько)">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <span class="hint" style="margin-right:auto" id="sendExportCount"></span>
      <button type="button" class="btn btn-ghost" id="sendExportCloseBtn">Закрыть</button>
      <button type="button" class="btn btn-ghost" id="sendExportCopyBtn"><i class="fa-solid fa-copy"></i> Скопировать</button>
      <button type="button" class="btn btn-primary" id="sendExportMailBtn"><i class="fa-solid fa-paper-plane"></i> Отправить на почту</button>
    </div>
  </div>
</div>
