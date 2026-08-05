<!-- Модальное окно "Поля для CRM" -->
<div id="crmFieldsOverlay" class="modal-overlay" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <h2><i class="fa-solid fa-address-card"></i> Поля для CRM</h2>
      <button class="icon-btn" id="crmFieldsClose">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body modal-body-stack">
      <p class="hint" style="margin:0 0 10px">По одной строке на отмеченный вебинар — для создания мероприятия в vtiger.</p>
      <textarea id="crmFieldsText" class="crm-fields-textarea" readonly rows="12"></textarea>
    </div>
    <div class="modal-footer">
      <span class="hint" style="margin-right:auto" id="crmFieldsCount"></span>
      <button type="button" class="btn btn-ghost" id="crmFieldsCloseBtn">Закрыть</button>
      <button type="button" class="btn btn-primary" id="crmFieldsCopyBtn"><i class="fa-solid fa-copy"></i> Скопировать</button>
    </div>
  </div>
</div>
