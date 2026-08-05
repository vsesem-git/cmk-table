<!-- Модальное окно конструктора страницы для участников -->
<div id="landingOverlay" class="modal-overlay" style="display:none">
  <div class="modal modal-wide">
    <div class="modal-header">
      <h2><i class="fa-solid fa-display"></i> Страница для участников</h2>
      <button class="icon-btn" id="landingClose">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body landing-body">
      <div class="landing-readonly" id="landingReadonlySummary"></div>

      <div class="landing-grid">
        <div class="field">
          <label><i class="fa-regular fa-clock"></i> Время начала</label>
          <input type="time" id="lp_eventTime" value="10:00">
        </div>
      </div>

      <div class="accordion" data-accordion="theme">
        <button type="button" class="accordion-header">
          <span><i class="fa-solid fa-palette"></i> Тема оформления</span>
          <span class="accordion-arrow">▾</span>
        </button>
        <div class="accordion-body">
          <div class="landing-subtitle"><i class="fa-solid fa-swatchbook"></i> Тема</div>
          <div class="landing-theme-grid" id="lp_themeGrid"></div>

          <div class="landing-subtitle"><i class="fa-solid fa-image"></i> Фоновое изображение <span class="hint">(если не выбрать — фон темы)</span></div>
          <div class="landing-img-preview" id="lp_backgroundPreview"></div>
          <div class="landing-img-actions">
            <button type="button" class="btn btn-ghost" data-gallery-toggle="background"><i class="fa-solid fa-folder-open"></i> Библиотека</button>
            <label class="btn btn-ghost landing-upload-btn"><i class="fa-solid fa-upload"></i> Загрузить новое<input type="file" id="lp_backgroundFile" accept="image/*" style="display:none"></label>
            <button type="button" class="btn btn-ghost" data-gallery-clear="background"><i class="fa-solid fa-xmark"></i> Убрать</button>
          </div>
          <div class="landing-gallery" id="lp_backgroundGallery" style="display:none"></div>
          <div class="landing-slider-row" style="margin-top:12px">
            <span><i class="fa-solid fa-droplet"></i> Интенсивность заливки</span>
            <input type="range" id="lp_backgroundIntensity" min="0" max="100" value="100">
            <span class="slider-value" id="lp_backgroundIntensityVal">100%</span>
          </div>

          <div class="landing-subtitle"><i class="fa-solid fa-text-height"></i> Размер шрифтов</div>
          <div class="landing-sliders" id="lp_slidersGrid"></div>

          <button type="button" class="btn btn-ghost" id="lp_saveDefaultBtn" style="margin-top:14px"><i class="fa-solid fa-floppy-disk"></i> Сохранить как шаблон по умолчанию</button>
        </div>
      </div>

      <div class="field field-full">
        <label><i class="fa-solid fa-align-left"></i> Требования к браузеру / устройству</label>
        <div class="landing-static-inline"><i class="fa-solid fa-lock"></i> Общий текст для всех страниц — редактируется в «⚙ Настройка панели»</div>
      </div>

      <div class="landing-section-title"><i class="fa-solid fa-link"></i> Кнопки на странице</div>
      <div class="hint" style="margin-bottom:6px">Автоматические — подставляются из ссылок вебинара:</div>
      <div class="landing-buttons-preview" id="lp_buttonsPreview"></div>

      <div class="accordion" data-accordion="buttons">
        <button type="button" class="accordion-header">
          <span><i class="fa-solid fa-plus"></i> Свои кнопки (добавляются после автоматических)</span>
          <span class="accordion-arrow">▾</span>
        </button>
        <div class="accordion-body">
          <div id="lp_customButtonsList" class="landing-buttons-list"></div>
          <button type="button" class="btn btn-ghost" id="lp_addCustomButtonBtn" style="margin-top:8px"><i class="fa-solid fa-plus"></i> Добавить кнопку</button>
        </div>
      </div>

      <div class="landing-status" id="landingStatus"></div>

      <label class="regen-checkbox">
        <input type="checkbox" id="lp_regenerateCode">
        <i class="fa-solid fa-rotate"></i> Сгенерировать новый код страницы (старый файл будет удалён после бэкапа, ссылка изменится)
      </label>
    </div>
    <div class="modal-footer">
      <span class="hint" style="margin-right:auto" id="landingHint"></span>
      <button type="button" class="btn btn-primary" id="landingPublishBtn"><i class="fa-solid fa-upload"></i> Опубликовать</button>
    </div>
  </div>
</div>
