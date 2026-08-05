<!-- Модальное окно настроек отображения столбцов -->
<div id="settingsOverlay" class="modal-overlay" style="display:none">
  <div class="modal modal-narrow">
    <div class="modal-header">
      <h2>Настройка панели</h2>
      <button class="icon-btn" id="settingsClose">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body settings-body">

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-calendar-days"></i> Показывать вебинары за год</div>
        <select id="yearFilterSelect" class="settings-select">
          <option value="">Все года</option>
        </select>
      </div>

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-file-contract"></i> Шаблон договора (template.json)</div>
        <div class="contract-template-status" id="contractTemplateStatus">Загрузка…</div>
        <div class="landing-img-actions" style="margin-top:8px">
          <label class="btn btn-ghost landing-upload-btn"><i class="fa-solid fa-upload"></i> Загрузить / заменить файл<input type="file" id="contractTemplateFile" accept=".json" style="display:none"></label>
          <button type="button" class="btn btn-ghost" id="contractTemplateDownloadBtn"><i class="fa-solid fa-download"></i> Скачать текущий</button>
          <button type="button" class="btn btn-ghost" id="contractTemplateCopyLinkBtn"><i class="fa-solid fa-link"></i> Скопировать ссылку</button>
        </div>
        <div class="hint" style="padding:6px 4px 0">Работаем только с зоной Check → Мероприятия — остальные поля договора (реквизиты и т.п.) не трогаем.</div>
      </div>

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-arrows-left-right"></i> Ширина страницы реестра</div>
        <select id="registryWidthSelect" class="settings-select">
          <option value="1200">1200 px</option>
          <option value="1400">1400 px (по умолчанию)</option>
          <option value="1600">1600 px</option>
          <option value="1800">1800 px</option>
          <option value="2000">2000 px</option>
          <option value="full">Во всю ширину экрана</option>
        </select>
      </div>

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-bolt"></i> Быстрые ссылки <span class="hint">(до 6 штук, показываются вверху реестра)</span></div>
        <div id="quickLinksList" class="quicklinks-list"></div>
        <div id="quickLinkForm" class="quicklink-form" style="display:none">
          <input type="text" id="qlLabel" placeholder="Название">
          <input type="text" id="qlUrl" placeholder="https://…">
          <select id="qlIcon"></select>
          <button type="button" class="btn btn-primary" id="qlSaveBtn"><i class="fa-solid fa-check"></i> Добавить</button>
          <button type="button" class="btn btn-ghost" id="qlCancelBtn"><i class="fa-solid fa-xmark"></i> Отмена</button>
        </div>
        <button type="button" class="btn btn-ghost" id="qlAddBtn" style="margin-top:8px"><i class="fa-solid fa-plus"></i> Добавить ссылку</button>
      </div>

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-envelope"></i> Постоянные получатели рассылки <span class="hint">(для «Выгрузить для отправки» → почта)</span></div>
        <div id="mailRecipientsList" class="quicklinks-list"></div>
        <div id="mailRecipientForm" class="quicklink-form" style="display:none">
          <input type="email" id="mrEmail" placeholder="email@example.com">
          <input type="text" id="mrLabel" placeholder="Название (необязательно)">
          <button type="button" class="btn btn-primary" id="mrSaveBtn"><i class="fa-solid fa-check"></i> Добавить</button>
          <button type="button" class="btn btn-ghost" id="mrCancelBtn"><i class="fa-solid fa-xmark"></i> Отмена</button>
        </div>
        <button type="button" class="btn btn-ghost" id="mrAddBtn" style="margin-top:8px"><i class="fa-solid fa-plus"></i> Добавить получателя</button>
      </div>

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-building-user"></i> Значения «Организатор»</div>
        <div id="organizerValuesList" class="quicklinks-list scrollable-list"></div>
      </div>

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-tags"></i> Значения «Рассылка» <span class="hint">(добавляются также прямо из формы вебинара)</span></div>
        <div id="mailingListValuesList" class="quicklinks-list scrollable-list"></div>
      </div>

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-signs-post"></i> Значения «Направление» <span class="hint">(добавляются также прямо из формы вебинара)</span></div>
        <div id="directionValuesList" class="quicklinks-list scrollable-list"></div>
      </div>

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-globe"></i> Значения «Размещён на сайте»</div>
        <div id="publishedValuesList" class="quicklinks-list scrollable-list"></div>
      </div>

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-star"></i> Значения «Входит в подписку»</div>
        <div id="subscriptionValuesList" class="quicklinks-list scrollable-list"></div>
      </div>

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-user"></i> Значения «Спикер»</div>
        <div id="speakerValuesList" class="quicklinks-list scrollable-list"></div>
      </div>

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-paperclip"></i> Типы документов <span class="hint">(слоты загрузки в карточке вебинара)</span></div>
        <div id="docTypesList" class="quicklinks-list"></div>
        <div id="addDocTypeForm" class="quicklink-form" style="display:none">
          <input type="text" id="newDocTypeLabel" placeholder="Название (например «Смета»)">
          <input type="text" id="newDocTypeExt" placeholder="Расширения через запятую (docx,pdf)">
          <button type="button" class="btn btn-primary" id="addDocTypeSaveBtn"><i class="fa-solid fa-check"></i> Добавить</button>
          <button type="button" class="btn btn-ghost" id="addDocTypeCancelBtn"><i class="fa-solid fa-xmark"></i> Отмена</button>
        </div>
        <button type="button" class="btn btn-ghost" id="addDocTypeBtn" style="margin-top:8px"><i class="fa-solid fa-plus"></i> Добавить тип документа</button>
      </div>

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-globe"></i> Публикация страниц участников</div>
        <div class="settings-row" style="border-bottom:none">
          <span class="col-label" style="flex:0 0 auto">Подпапка на сервере (внутри корня сайта)</span>
          <input type="text" id="landingOutputBaseInput" placeholder="например land, или оставьте пустым" style="flex:1;border:1px solid var(--line);border-radius:8px;padding:7px 10px;font-size:13px;">
        </div>
        <div class="hint" style="padding:0 4px 10px">Страницы публикуются в «(эта подпапка)/(год вебинара)/» прямо в корне сайта — например «2026/ab12cd.html». Домен нигде не зашит в код, при переезде на другой домен менять не нужно.</div>
      </div>

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-file-lines"></i> Общие тексты страницы участника <span class="hint">(одни и те же на всех страницах)</span></div>
        <div class="field field-full">
          <label>Требования к браузеру / устройству</label>
          <textarea id="landingBrowserTextInput" placeholder="Используйте браузеры Yandex Browser / Google Chrome / Firefox последней версии."></textarea>
        </div>
        <div class="field field-full">
          <label>Строка с портами («терминальный» блок)</label>
          <input type="text" id="landingPortsTextInput" placeholder="Откройте порты TCP:443 UDP:16384 - 32768">
        </div>
        <div class="landing-grid" style="margin-top:10px">
          <div class="field">
            <label>Текст блока поддержки</label>
            <input type="text" id="landingSupportTextInput" placeholder="Техническая поддержка">
          </div>
          <div class="field">
            <label>Телефон поддержки</label>
            <input type="text" id="landingSupportPhoneInput" placeholder="+7 910 154 76 86">
          </div>
          <div class="field field-full">
            <label>График поддержки</label>
            <input type="text" id="landingSupportScheduleInput" placeholder="(гарантируется только в день вебинара с 10:00 до 15:00)">
          </div>
          <div class="field field-full">
            <label>Текст в футере страницы</label>
            <input type="text" id="landingFooterTextInput" placeholder="© 2026 Страница создана по материалам вебинара">
          </div>
        </div>
      </div>

      <div class="settings-group">
        <div class="settings-group-title"><i class="fa-solid fa-table-cells"></i> Ширина таблицы</div>
        <label class="radio-row">
          <input type="radio" name="tableWidthMode" value="scroll">
          Фиксированная (со скроллом) — столбцы держат заданную ширину, лишнее уходит в горизонтальную прокрутку
        </label>
        <label class="radio-row">
          <input type="radio" name="tableWidthMode" value="fit">
          Растянуть по ширине экрана — столбцы сжимаются/растягиваются, чтобы таблица помещалась целиком
        </label>
      </div>

      <div class="settings-group-title"><i class="fa-solid fa-table-columns"></i> Столбцы <span class="hint">(перетаскивайте за ⠿, чтобы менять порядок; название можно переименовать; ширину — тянуть мышкой прямо за границу столбца в таблице)</span></div>
      <button type="button" class="btn btn-ghost" id="addColBtn" style="margin-bottom:8px"><i class="fa-solid fa-plus"></i> Добавить столбец</button>
      <div id="settingsList"></div>
    </div>
    <div class="modal-footer">
      <span class="hint" style="margin-right:auto">Настройки общие для всех — хранятся на сервере (data/settings.json)</span>
      <button type="button" class="btn btn-primary" id="settingsDone"><i class="fa-solid fa-check"></i> Готово</button>
    </div>
  </div>
</div>
