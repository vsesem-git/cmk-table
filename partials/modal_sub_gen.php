<!-- Модальное окно "Генерация подписчикам" -->
<div id="subGenOverlay" class="modal-overlay" style="display:none">
  <div class="modal modal-wide">
    <div class="modal-header">
      <h2><i class="fa-solid fa-users-rectangle"></i> Генерация подписчикам</h2>
      <button class="icon-btn" id="subGenClose">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body modal-body-stack">
      <p class="hint" style="margin:0 0 10px">
        Автоматически собирает все вебинары, где «Направление» входит в отмеченные категории
        и «Входит в подписку» = «Да» — без ручного выделения. Каждая генерация полностью
        пересобирает <code>sub.json</code> заново.
      </p>
      <div class="settings-group-title" style="margin-top:0"><i class="fa-solid fa-signs-post"></i> Категории (Направление)</div>
      <div id="subGenCategories" class="mailing-list-checkboxes"></div>

      <div class="contract-template-status" id="subGenStatus" style="margin-top:14px">Загрузка…</div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" id="subGenCopyLinkBtn"><i class="fa-solid fa-link"></i> Скопировать ссылку</button>
      <button type="button" class="btn btn-ghost" id="subGenCloseBtn">Закрыть</button>
      <button type="button" class="btn btn-primary" id="subGenGenerateBtn"><i class="fa-solid fa-rotate"></i> Сгенерировать</button>
    </div>
  </div>
</div>
