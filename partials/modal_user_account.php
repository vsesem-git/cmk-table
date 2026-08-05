<!-- Модальное окно "Аккаунт" — открывается по клику на имя пользователя -->
<div id="userAccountOverlay" class="modal-overlay" style="display:none">
  <div class="modal modal-wide">
    <div class="modal-header">
      <h2><i class="fa-solid fa-user-gear"></i> Аккаунт и данные</h2>
      <button class="icon-btn" id="userAccountClose">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body modal-body-stack">

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-users"></i> Пользователи реестра</div>
        <div id="usersList" class="quicklinks-list"></div>
        <div id="addUserForm" class="quicklink-form" style="display:none">
          <input type="text" id="newUserLogin" placeholder="Логин">
          <input type="password" id="newUserPassword" placeholder="Пароль (мин. 6 символов)">
          <button type="button" class="btn btn-primary" id="addUserSaveBtn"><i class="fa-solid fa-check"></i> Добавить</button>
          <button type="button" class="btn btn-ghost" id="addUserCancelBtn"><i class="fa-solid fa-xmark"></i> Отмена</button>
        </div>
        <button type="button" class="btn btn-ghost" id="addUserBtn" style="margin-top:8px"><i class="fa-solid fa-user-plus"></i> Добавить пользователя</button>

        <div class="landing-section-title" style="margin-top:16px"><i class="fa-solid fa-key"></i> Сменить свой пароль</div>
        <div class="landing-grid">
          <div class="field">
            <input type="password" id="changePasswordInput" placeholder="Новый пароль (мин. 6 символов)">
          </div>
          <div class="field">
            <button type="button" class="btn btn-ghost" id="changePasswordBtn" style="width:100%;justify-content:center"><i class="fa-solid fa-key"></i> Сменить пароль</button>
          </div>
        </div>
      </div>

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-paper-plane"></i> SMTP для отправки писем</div>
        <div id="smtpStatus" class="contract-template-status">Загрузка…</div>
        <div class="landing-grid" style="margin-top:10px">
          <div class="field">
            <label>SMTP-сервер</label>
            <input type="text" id="smtpHost" placeholder="smtp.yandex.ru">
          </div>
          <div class="field">
            <label>Порт</label>
            <input type="number" id="smtpPort" placeholder="587">
          </div>
          <div class="field">
            <label>Шифрование</label>
            <select id="smtpEncryption" class="settings-select">
              <option value="tls">STARTTLS</option>
              <option value="ssl">SSL</option>
              <option value="none">Без шифрования</option>
            </select>
          </div>
          <div class="field">
            <label>Логин SMTP</label>
            <input type="text" id="smtpUsername" placeholder="noreply@vsesem.ru">
          </div>
          <div class="field">
            <label>Пароль SMTP</label>
            <input type="password" id="smtpPassword" placeholder="••••••••">
          </div>
          <div class="field">
            <label>Email отправителя</label>
            <input type="email" id="smtpFromEmail" placeholder="noreply@vsesem.ru">
          </div>
          <div class="field field-full">
            <label>Имя отправителя</label>
            <input type="text" id="smtpFromName" placeholder="ЦМК Реестр вебинаров">
          </div>
        </div>
        <div class="landing-img-actions" style="margin-top:10px">
          <button type="button" class="btn btn-primary" id="smtpSaveBtn"><i class="fa-solid fa-floppy-disk"></i> Сохранить</button>
          <input type="email" id="smtpTestEmail" placeholder="куда отправить тест" style="flex:1;min-width:160px;border:1px solid var(--line);border-radius:8px;padding:8px 10px;font-size:13px;">
          <button type="button" class="btn btn-ghost" id="smtpTestBtn"><i class="fa-solid fa-paper-plane"></i> Отправить тестовое</button>
        </div>
      </div>

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-database"></i> Импорт / экспорт данных</div>
        <div class="landing-img-actions">
          <label class="btn btn-ghost landing-upload-btn"><i class="fa-solid fa-file-import"></i> Импорт JSON<input type="file" id="importFileInput" accept=".json" style="display:none"></label>
          <button type="button" class="btn btn-ghost" id="exportJsonBtn"><i class="fa-solid fa-file-export"></i> Экспорт JSON</button>
        </div>
        <div class="hint" style="padding:6px 4px 0">Импорт полностью заменяет текущие данные (перед заменой делается бэкап на сервере).</div>
      </div>

    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-primary" id="userAccountDone"><i class="fa-solid fa-check"></i> Готово</button>
    </div>
  </div>
</div>
