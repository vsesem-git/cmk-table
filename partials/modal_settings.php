<!-- Модальное окно настроек отображения столбцов -->
<div id="settingsOverlay" class="modal-overlay" style="display:none">
  <div class="modal modal-narrow">
    <div class="modal-header">
      <h2>Настройка панели</h2>
      <button class="icon-btn" id="settingsClose">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body settings-body" style="padding: 14px 22px; display: flex; flex-direction: column; gap: 10px;">

      <!-- Категория 1: Интерфейс и отображение реестра -->
      <div class="accordion">
        <button type="button" class="accordion-header">
          <span><i class="fa-solid fa-sliders"></i> Интерфейс и отображение реестра</span>
          <span class="accordion-arrow">▾</span>
        </button>
        <div class="accordion-body">
          <div class="settings-group" style="margin-top:0">
            <div class="settings-group-title"><i class="fa-solid fa-calendar-days"></i> Показывать вебинары за год</div>
            <select id="yearFilterSelect" class="settings-select" style="width:100%; border:1px solid var(--line); border-radius:8px; padding:7px 10px; font-size:13px;"></select>
          </div>

          <div class="settings-group" style="margin-top:12px">
            <div class="settings-group-title"><i class="fa-solid fa-arrows-left-right"></i> Ширина страницы реестра</div>
            <select id="registryWidthSelect" class="settings-select" style="width:100%; border:1px solid var(--line); border-radius:8px; padding:7px 10px; font-size:13px;">
              <option value="1200">1200 px</option>
              <option value="1400">1400 px (по умолчанию)</option>
              <option value="1600">1600 px</option>
              <option value="1800">1800 px</option>
              <option value="2000">2000 px</option>
              <option value="full">Во всю ширину экрана</option>
            </select>
          </div>

          <div class="settings-group" style="margin-top:12px">
            <div class="settings-group-title"><i class="fa-solid fa-table-cells"></i> Ширина таблицы</div>
            <label class="radio-row" style="display:block; margin-bottom: 6px; font-size: 13px;">
              <input type="radio" name="tableWidthMode" value="scroll">
              Фиксированная (со скроллом) — столбцы держат заданную ширину
            </label>
            <label class="radio-row" style="display:block; font-size: 13px;">
              <input type="radio" name="tableWidthMode" value="fit">
              Растянуть по ширине экрана — столбцы сжимаются/растягиваются
            </label>
          </div>

          <div class="settings-group" style="margin-top:12px">
            <div class="settings-group-title"><i class="fa-solid fa-table-columns"></i> Столбцы <span class="hint">(перетаскивайте за ⠿, чтобы менять порядок; переименовывайте и меняйте ширину)</span></div>
            <button type="button" class="btn btn-ghost" id="addColBtn" style="margin-bottom:8px"><i class="fa-solid fa-plus"></i> Добавить столбец</button>
            <div id="settingsList"></div>
          </div>
        </div>
      </div>

      <!-- Категория 2: Страницы участников (Лендинги) -->
      <div class="accordion">
        <button type="button" class="accordion-header">
          <span><i class="fa-solid fa-display"></i> Страницы участников (Лендинги)</span>
          <span class="accordion-arrow">▾</span>
        </button>
        <div class="accordion-body">
          <div class="settings-group" style="margin-top:0">
            <div class="settings-group-title"><i class="fa-solid fa-globe"></i> Публикация страниц</div>
            <div class="settings-row" style="border-bottom:none; margin-bottom:8px;">
              <span class="col-label" style="flex:0 0 auto; font-size:13px;">Подпапка на сервере</span>
              <input type="text" id="landingOutputBaseInput" placeholder="например land, или оставьте пустым" style="flex:1;border:1px solid var(--line);border-radius:8px;padding:7px 10px;font-size:13px;">
            </div>
            <div class="settings-row" style="border-bottom:none">
              <span class="col-label" style="flex:0 0 auto; font-size:13px;">Длина окончания ссылки (символов)</span>
              <input type="number" id="landingLinkLengthInput" min="4" max="32" placeholder="6" style="width:80px;border:1px solid var(--line);border-radius:8px;padding:7px 10px;font-size:13px; margin-left: 10px;">
            </div>
          </div>

          <div class="settings-group" style="margin-top:12px">
            <div class="settings-group-title"><i class="fa-solid fa-file-lines"></i> Общие тексты и поддержка</div>
            <div class="field field-full" style="margin-bottom:8px">
              <label style="font-size:12px;">Требования к браузеру</label>
              <textarea id="landingBrowserTextInput" placeholder="Используйте браузеры Yandex-Браузер..."></textarea>
            </div>
            <div class="field field-full" style="margin-bottom:8px">
              <label style="font-size:12px;">Строка с портами</label>
              <input type="text" id="landingPortsTextInput" placeholder="Откройте порты TCP:443 UDP:16384 - 32768">
            </div>
            <div class="field field-full" style="margin-bottom:8px">
              <label style="font-size:12px;">Текст блока поддержки</label>
              <input type="text" id="landingSupportTextInput" placeholder="Техническая поддержка">
            </div>
            <div class="field field-full" style="margin-bottom:8px">
              <label style="font-size:12px;">Телефон поддержки</label>
              <input type="text" id="landingSupportPhoneInput" placeholder="+7 910 154 76 86">
            </div>
            <div class="field field-full" style="margin-bottom:8px">
              <label style="font-size:12px;">Текст в футере страницы</label>
              <input type="text" id="landingFooterTextInput" placeholder="© 2026 Страница создана по материалам вебинара">
            </div>
          </div>

          <div class="settings-group" style="margin-top:12px">
            <div class="settings-group-title"><i class="fa-solid fa-circle-info"></i> Всплывающие подсказки при отсутствии ссылок</div>
            <div class="field field-full" style="margin-bottom:8px">
              <label style="font-size:12px;">Подсказка при отсутствии записи</label>
              <input type="text" id="landingTooltipRecordingInput" placeholder="Организатор еще не разместил запись трансляции...">
            </div>
            <div class="field field-full">
              <label style="font-size:12px;">Подсказка при отсутствии материалов</label>
              <input type="text" id="landingTooltipMaterialsInput" placeholder="Организатор еще не разместил материалы вебинара...">
            </div>
          </div>
        </div>
      </div>

      <!-- Категория 3: Шаблоны и документы -->
      <div class="accordion">
        <button type="button" class="accordion-header">
          <span><i class="fa-solid fa-file-contract"></i> Шаблоны и документы</span>
          <span class="accordion-arrow">▾</span>
        </button>
        <div class="accordion-body">
          <div class="settings-group" style="margin-top:0">
            <div class="settings-group-title"><i class="fa-solid fa-file-contract"></i> Шаблон договора (template.json)</div>
            <div class="contract-template-status" id="contractTemplateStatus">Загрузка…</div>
            <div class="landing-img-actions" style="margin-top:8px">
              <label class="btn btn-ghost landing-upload-btn"><i class="fa-solid fa-upload"></i> Загрузить / заменить файл<input type="file" id="contractTemplateFile" accept=".json" style="display:none"></label>
              <button type="button" class="btn btn-ghost" id="contractTemplateDownloadBtn"><i class="fa-solid fa-download"></i> Скачать</button>
              <button type="button" class="btn btn-ghost" id="contractTemplateCopyLinkBtn"><i class="fa-solid fa-link"></i> Скопировать ссылку</button>
            </div>
          </div>

          <div class="settings-group" style="margin-top:12px">
            <div class="settings-group-title"><i class="fa-solid fa-paperclip"></i> Типы документов <span class="hint">(слоты загрузки в вебинаре)</span></div>
            <div id="docTypesList" class="quicklinks-list" style="margin-bottom: 8px;"></div>
            <div id="addDocTypeForm" class="quicklink-form" style="display:none; gap:6px; margin-bottom:8px;">
              <input type="text" id="newDocTypeLabel" placeholder="Название">
              <input type="text" id="newDocTypeExt" placeholder="Расширения (docx,pdf)">
              <button type="button" class="btn btn-primary" id="addDocTypeSaveBtn"><i class="fa-solid fa-check"></i></button>
              <button type="button" class="btn btn-ghost" id="addDocTypeCancelBtn"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <button type="button" class="btn btn-ghost" id="addDocTypeBtn"><i class="fa-solid fa-plus"></i> Добавить тип документа</button>
          </div>
        </div>
      </div>

      <!-- Категория 4: Быстрые переходы и рассылка -->
      <div class="accordion">
        <button type="button" class="accordion-header">
          <span><i class="fa-solid fa-envelope"></i> Быстрые переходы и получатели</span>
          <span class="accordion-arrow">▾</span>
        </button>
        <div class="accordion-body">
          <div class="settings-group" style="margin-top:0">
            <div class="settings-group-title"><i class="fa-solid fa-bolt"></i> Быстрые ссылки <span class="hint">(показываются вверху реестра)</span></div>
            <div id="quickLinksList" class="quicklinks-list" style="margin-bottom: 8px;"></div>
            <div id="quickLinkForm" class="quicklink-form" style="display:none; gap:6px; margin-bottom:8px;">
              <input type="text" id="qlLabel" placeholder="Название">
              <input type="text" id="qlUrl" placeholder="https://…">
              <select id="qlIcon" style="border:1px solid var(--line); border-radius:8px; padding:6px;"></select>
              <button type="button" class="btn btn-primary" id="qlSaveBtn"><i class="fa-solid fa-check"></i></button>
              <button type="button" class="btn btn-ghost" id="qlCancelBtn"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <button type="button" class="btn btn-ghost" id="qlAddBtn"><i class="fa-solid fa-plus"></i> Добавить ссылку</button>
          </div>

          <div class="settings-group" style="margin-top:12px">
            <div class="settings-group-title"><i class="fa-solid fa-envelope"></i> Постоянные получатели рассылки</div>
            <div id="mailRecipientsList" class="quicklinks-list" style="margin-bottom: 8px;"></div>
            <div id="mailRecipientForm" class="quicklink-form" style="display:none; gap:6px; margin-bottom:8px;">
              <input type="email" id="mrEmail" placeholder="email@example.com">
              <input type="text" id="mrLabel" placeholder="Название">
              <button type="button" class="btn btn-primary" id="mrSaveBtn"><i class="fa-solid fa-check"></i></button>
              <button type="button" class="btn btn-ghost" id="mrCancelBtn"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <button type="button" class="btn btn-ghost" id="mrAddBtn"><i class="fa-solid fa-plus"></i> Добавить получателя</button>
          </div>
        </div>
      </div>

      <!-- Категория 5: Справочники значений полей -->
      <div class="accordion">
        <button type="button" class="accordion-header">
          <span><i class="fa-solid fa-database"></i> Справочники значений полей</span>
          <span class="accordion-arrow">▾</span>
        </button>
        <div class="accordion-body">
          <div class="landing-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div class="settings-group" style="margin-top:0">
              <div class="settings-group-title"><i class="fa-solid fa-building-user"></i> Организатор</div>
              <div id="organizerValuesList" class="quicklinks-list scrollable-list" style="max-height: 140px; overflow-y: auto; border: 1px solid var(--line); border-radius: 8px; padding: 6px;"></div>
            </div>
            <div class="settings-group" style="margin-top:0">
              <div class="settings-group-title"><i class="fa-solid fa-user"></i> Спикер</div>
              <div id="speakerValuesList" class="quicklinks-list scrollable-list" style="max-height: 140px; overflow-y: auto; border: 1px solid var(--line); border-radius: 8px; padding: 6px;"></div>
            </div>
            <div class="settings-group" style="margin-top:0">
              <div class="settings-group-title"><i class="fa-solid fa-signs-post"></i> Направление</div>
              <div id="directionValuesList" class="quicklinks-list scrollable-list" style="max-height: 140px; overflow-y: auto; border: 1px solid var(--line); border-radius: 8px; padding: 6px;"></div>
            </div>
            <div class="settings-group" style="margin-top:0">
              <div class="settings-group-title"><i class="fa-solid fa-tags"></i> Рассылка</div>
              <div id="mailingListValuesList" class="quicklinks-list scrollable-list" style="max-height: 140px; overflow-y: auto; border: 1px solid var(--line); border-radius: 8px; padding: 6px;"></div>
            </div>
            <div class="settings-group" style="margin-top:0">
              <div class="settings-group-title"><i class="fa-solid fa-globe"></i> Размещён на сайте</div>
              <div id="publishedValuesList" class="quicklinks-list scrollable-list" style="max-height: 140px; overflow-y: auto; border: 1px solid var(--line); border-radius: 8px; padding: 6px;"></div>
            </div>
            <div class="settings-group" style="margin-top:0">
              <div class="settings-group-title"><i class="fa-solid fa-star"></i> Входит в подписку</div>
              <div id="subscriptionValuesList" class="quicklinks-list scrollable-list" style="max-height: 140px; overflow-y: auto; border: 1px solid var(--line); border-radius: 8px; padding: 6px;"></div>
            </div>
          </div>
        </div>
      </div>

    </div>
    <div class="modal-footer">
      <span class="hint" style="margin-right:auto">Настройки хранятся на сервере</span>
      <button type="button" class="btn btn-primary" id="settingsDone"><i class="fa-solid fa-check"></i> Готово</button>
    </div>
  </div>
</div>
