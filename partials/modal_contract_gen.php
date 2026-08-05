<!-- Модальное окно "Генерация для договоров" -->
<div id="contractGenOverlay" class="modal-overlay" style="display:none">
  <div class="modal modal-wide">
    <div class="modal-header">
      <h2><i class="fa-solid fa-file-contract"></i> Генерация для договоров</h2>
      <button class="icon-btn" id="contractGenClose">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body modal-body-stack">
      <div id="contractGenWarning" class="contract-gen-warning" style="display:none"></div>
      <p class="hint" style="margin:0 0 10px" id="contractGenSummary"></p>
      <textarea id="contractGenText" class="crm-fields-textarea" readonly rows="14"></textarea>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" id="contractGenCloseBtn">Закрыть</button>
      <button type="button" class="btn btn-ghost" id="contractGenCopyLinkBtn"><i class="fa-solid fa-link"></i> Скопировать ссылку</button>
      <button type="button" class="btn btn-ghost" id="contractGenDownloadBtn"><i class="fa-solid fa-download"></i> Скачать template.json</button>
      <button type="button" class="btn btn-primary" id="contractGenCopyBtn"><i class="fa-solid fa-copy"></i> Скопировать</button>
    </div>
  </div>
</div>
