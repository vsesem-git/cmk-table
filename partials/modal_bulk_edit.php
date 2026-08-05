<!-- Модальное окно "Массовое редактирование" -->
<div id="bulkEditOverlay" class="modal-overlay" style="display:none">
  <div class="modal modal-wider" style="max-width: 1300px; max-height: 95vh;">
    <div class="modal-header">
      <h2><i class="fa-solid fa-pen-to-square"></i> Массовое редактирование вебинаров</h2>
      <button class="icon-btn" id="bulkEditClose">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body modal-body-stack" style="padding: 18px 22px;">
      <div id="bulkEditSelectionCount" style="font-weight: 600; margin-bottom: 12px; color: var(--teal-dark); font-size: 14px;"></div>

      <!-- Таблица редактирования -->
      <div style="overflow-x: auto; border: 1px solid var(--line); border-radius: 10px; margin-bottom: 20px; background: #fff;">
        <table class="bulk-edit-table" style="width: 100%; border-collapse: collapse; min-width: 1100px; font-size: 13px;">
          <thead>
            <tr style="background: #f7f9f9; border-bottom: 1px solid var(--line); text-align: left; font-weight: 600; color: var(--ink-soft);">
              <th style="padding: 10px 12px; width: 320px;">Мероприятие (Дата и Тема)</th>
              <th style="padding: 10px 12px; width: 200px;">Ссылка для участников</th>
              <th style="padding: 10px 12px; width: 200px;">Ссылка для организатора / лектора</th>
              <th style="padding: 10px 12px; width: 100px;">Код модератора</th>
              <th style="padding: 10px 12px; width: 180px;">Ссылка на материалы</th>
              <th style="padding: 10px 12px; width: 180px;">Ссылка на запись</th>
            </tr>
          </thead>
          <tbody id="bulkEditTableBody"></tbody>
        </table>
      </div>

      <!-- Блок Генерации страниц участников -->
      <div class="settings-group" style="border: 1px solid var(--line); border-radius: 10px; padding: 16px; background: #fdfefe; margin-bottom: 14px;">
        <div class="settings-group-title" style="margin-top:0; border-bottom: 1px solid var(--line); padding-bottom: 8px; margin-bottom: 12px; font-weight: 700; color: var(--teal-dark);"><i class="fa-solid fa-wand-magic-sparkles"></i> Генерация страниц участников (массово)</div>
        
        <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center; margin-bottom: 12px;">
          <div style="flex: 1 1 250px;">
            <label style="display:block; font-size: 12.5px; font-weight: 600; color: var(--ink-soft); margin-bottom: 5px;">Загрузить шаблон оформления</label>
            <select id="bulk_lp_templateSelect" style="width:100%; border:1px solid var(--line); border-radius:8px; padding:8px 10px; font-size:13px; background: #fff;">
              <option value="">-- Шаблон по умолчанию --</option>
            </select>
          </div>

          <div style="flex: 1 1 200px;">
            <label style="display:block; font-size: 12.5px; font-weight: 600; color: var(--ink-soft); margin-bottom: 5px;">Длина окончания ссылки (символов)</label>
            <input type="number" id="bulk_lp_linkLength" min="4" max="32" value="6" style="width:100%; border:1px solid var(--line); border-radius:8px; padding:8px 10px; font-size:13px;">
          </div>

          <div style="flex: 1 1 250px; display: flex; align-items: flex-end; height: 100%;">
            <label class="regen-checkbox" style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
              <input type="checkbox" id="bulk_lp_regenerateCode">
              <span>Сгенерировать новый код страницы (старый файл удалится)</span>
            </label>
          </div>

          <div style="flex: 1 1 200px; display: flex; align-items: flex-end;">
            <button type="button" class="btn btn-primary" id="bulk_lp_generateBtn" style="width: 100%; height: 38px; font-size: 13.5px;"><i class="fa-solid fa-rotate"></i> Сгенерировать страницы</button>
          </div>
        </div>

        <!-- Аккордеон темы оформления (перенесённый из Страница для участников) -->
        <div class="accordion" data-accordion="theme" id="bulk_lp_themeAccordion" style="margin-bottom: 14px; border: 1px solid var(--line); border-radius: 8px;">
          <button type="button" class="accordion-header" style="background: #fafbfc; border-radius: 8px; padding: 10px 14px;">
            <span><i class="fa-solid fa-palette"></i> Индивидуальная настройка темы оформления</span>
            <span class="accordion-arrow">▾</span>
          </button>
          <div class="accordion-body" style="padding: 14px;">
            <div class="landing-subtitle"><i class="fa-solid fa-swatchbook"></i> Тема</div>
            <div class="landing-theme-grid" id="bulk_lp_themeGrid"></div>

            <div class="landing-subtitle" style="margin-top: 12px;"><i class="fa-solid fa-image"></i> Фоновое изображение <span class="hint">(если не выбрать — фон темы)</span></div>
            <div class="landing-img-preview" id="bulk_lp_backgroundPreview"></div>
            <div class="landing-img-actions">
              <button type="button" class="btn btn-ghost" data-gallery-toggle="bulk-background"><i class="fa-solid fa-folder-open"></i> Библиотека</button>
              <label class="btn btn-ghost landing-upload-btn"><i class="fa-solid fa-upload"></i> Загрузить новое<input type="file" id="bulk_lp_backgroundFile" accept="image/*" style="display:none"></label>
              <button type="button" class="btn btn-ghost" data-gallery-clear="bulk-background"><i class="fa-solid fa-xmark"></i> Убрать</button>
            </div>
            <div class="landing-gallery" id="bulk_lp_backgroundGallery" style="display:none"></div>
            <div class="landing-slider-row" style="margin-top:12px">
              <span><i class="fa-solid fa-droplet"></i> Интенсивность заливки</span>
              <input type="range" id="bulk_lp_backgroundIntensity" min="0" max="100" value="100">
              <span class="slider-value" id="bulk_lp_backgroundIntensityVal">100%</span>
            </div>

            <div class="landing-subtitle" style="margin-top: 12px;"><i class="fa-solid fa-text-height"></i> Размер шрифтов</div>
            <div class="landing-sliders" id="bulk_lp_slidersGrid"></div>
          </div>
        </div>

        <div style="display: flex; gap: 8px; align-items: center; border-top: 1px dashed var(--line); padding-top: 12px;">
          <input type="text" id="bulk_lp_newTemplateName" placeholder="Имя для сохранения текущего шаблона" style="flex: 1; border:1px solid var(--line); border-radius:8px; padding:8px 10px; font-size:13px;">
          <button type="button" class="btn btn-ghost" id="bulk_lp_saveTemplateBtn"><i class="fa-solid fa-floppy-disk"></i> Сохранить этот шаблон</button>
        </div>
      </div>

      <!-- Блок Выгрузки для отправки -->
      <div class="settings-group" style="border: 1px solid var(--line); border-radius: 10px; padding: 16px; background: #fdfefe; margin-top: 14px;">
        <div style="display:flex; align-items:center; justify-content:space-between; border-bottom: 1px solid var(--line); padding-bottom: 8px; margin-bottom: 12px;">
          <div class="settings-group-title" style="margin:0; font-weight: 700; color: var(--teal-dark);"><i class="fa-solid fa-paper-plane"></i> Выгрузить для отправки (Outlook / Почта)</div>
          <button type="button" class="icon-btn" id="bulk_sendExportFieldsGearBtn" title="Выбрать поля для выгрузки">
            <i class="fa-solid fa-gear"></i>
          </button>
        </div>

        <div class="send-export-fields-panel" id="bulk_sendExportFieldsPanel" style="display:none; margin-bottom: 12px; padding: 10px; border: 1px dashed var(--line); border-radius: 8px; background: #fdfefe;">
          <div class="settings-group-title" style="margin-top:0; font-size:12.5px;"><i class="fa-solid fa-list-check"></i> Какие поля выгружать</div>
          <div id="bulk_sendExportFieldsList" class="ml-dropdown-list" style="display:flex; flex-wrap:wrap; gap:10px; max-height:none;"></div>
        </div>

        <p class="hint" style="margin:0 0 10px">Таблица ниже обновляется на лету при редактировании полей. Скопируйте её для вставки в письмо или отправьте на почту:</p>
        <div id="bulk_sendExportTableWrap" class="send-export-table-wrap" style="overflow-x: auto; border: 1px solid var(--line); border-radius: 8px; margin-bottom: 12px; background: #fff;"></div>

        <div class="landing-section-title" style="margin-top:16px; font-weight: 600; font-size: 13px; margin-bottom: 8px;"><i class="fa-solid fa-envelope"></i> Отправить на почту</div>
        <div id="bulk_sendExportRecipients" class="mailing-list-checkboxes" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:10px;"></div>
        
        <input type="email" id="bulk_sendExportCustomEmail" placeholder="Или впишите email вручную (через запятую, если несколько)" style="width:100%; border:1px solid var(--line); border-radius:8px; padding:8px 10px; font-size:13px; margin-bottom:12px;">

        <div style="display:flex; gap: 8px;">
          <button type="button" class="btn btn-ghost" id="bulk_sendExportCopyBtn" style="height: 38px; font-size: 13.5px;"><i class="fa-solid fa-copy"></i> Скопировать таблицу</button>
          <button type="button" class="btn btn-primary" id="bulk_sendExportMailBtn" style="height: 38px; font-size: 13.5px;"><i class="fa-solid fa-paper-plane"></i> Отправить на почту</button>
        </div>
      </div>

    </div>
    <div class="modal-footer" style="padding: 14px 22px;">
      <button type="button" class="btn btn-ghost" id="bulkEditCancelBtn">Отмена</button>
      <button type="button" class="btn btn-primary" id="bulkEditSaveBtn"><i class="fa-solid fa-floppy-disk"></i> Сохранить изменения</button>
    </div>
  </div>
</div>
