<!-- Модальное окно "Генерация ссылок" -->
<div id="linkGenOverlay" class="modal-overlay" style="display:none">
  <div class="modal modal-wide">
    <div class="modal-header">
      <h2><i class="fa-solid fa-link"></i> Генерация ссылок</h2>
      <button class="icon-btn" id="linkGenClose">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body modal-body-stack">
      <div id="linkGenSelectionCount" style="font-weight: 600; margin-bottom: 12px; color: var(--teal-dark);"></div>

      <div class="settings-row" style="border-bottom:none; margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
        <span class="col-label" style="flex: 0 0 auto; font-weight: 600;">Длина окончания ссылки (символов)</span>
        <input type="number" id="bulk_lp_linkLength" min="4" max="32" value="6" style="width:80px;border:1px solid var(--line);border-radius:8px;padding:7px 10px;font-size:13px;">
      </div>

      <label class="regen-checkbox" style="margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
        <input type="checkbox" id="bulk_lp_regenerateCode">
        <span>Сгенерировать новый код страницы (старый файл будет удалён после бэкапа, ссылка изменится)</span>
      </label>

      <!-- Аккордеон темы оформления -->
      <div class="accordion" data-accordion="theme" id="bulk_lp_themeAccordion" style="margin-bottom: 14px;">
        <button type="button" class="accordion-header">
          <span><i class="fa-solid fa-palette"></i> Тема оформления</span>
          <span class="accordion-arrow">▾</span>
        </button>
        <div class="accordion-body">
          <div class="landing-subtitle"><i class="fa-solid fa-swatchbook"></i> Тема</div>
          <div class="landing-theme-grid" id="bulk_lp_themeGrid"></div>

          <div class="landing-subtitle"><i class="fa-solid fa-image"></i> Фоновое изображение <span class="hint">(если не выбрать — фон темы)</span></div>
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

          <div class="landing-subtitle"><i class="fa-solid fa-text-height"></i> Размер шрифтов</div>
          <div class="landing-sliders" id="bulk_lp_slidersGrid"></div>
        </div>
      </div>

      <!-- Поле для результата генерации -->
      <div id="linkGenResultContainer" style="display:none; margin-top: 14px;">
        <div class="settings-group-title" style="margin-top:0"><i class="fa-solid fa-check-double"></i> Сгенерированные ссылки</div>
        <textarea id="linkGenResultText" readonly rows="8" class="crm-fields-textarea" style="width: 100%; border: 1px solid var(--line); border-radius: 8px; padding: 9px 11px; font-family: ui-monospace, SFMono-Regular, monospace; font-size: 13px;"></textarea>
        <button type="button" class="btn btn-primary" id="linkGenCopyResultBtn" style="margin-top: 8px;"><i class="fa-solid fa-copy"></i> Скопировать список</button>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" id="linkGenCloseBtn">Закрыть</button>
      <button type="button" class="btn btn-primary" id="linkGenGenerateBtn"><i class="fa-solid fa-rotate"></i> Сгенерировать</button>
    </div>
  </div>
</div>
