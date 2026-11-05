<!-- Модальное окно управления дизайном страниц участников: ЦМК и КОДЕКС (с Live Preview) -->
<div id="pageDesignOverlay" class="modal-overlay" style="display:none">
  <div class="modal modal-wider pd-modal">
    <div class="modal-header">
      <h2><i class="fa-solid fa-palette"></i> Дизайн страниц участников: ЦМК и КОДЕКС</h2>
      <button type="button" class="icon-btn" id="pageDesignClose">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <div class="modal-body pd-body">
      <!-- Закреплённый верхний блок: выбор шаблона (ЦМК / КОДЕКС) + LIVE PREVIEW -->
      <div class="pd-sticky-top">
        <div class="pd-tabs-bar">
          <div class="pd-tabs" role="tablist">
            <button type="button" class="pd-tab active" data-pd-tab="cmk">
              <span class="pd-tab-dot" style="background:#0A5E5E"></span>
              <span>Шаблон ЦМК</span>
              <span class="pd-tab-sub">классические темы</span>
            </button>
            <button type="button" class="pd-tab" data-pd-tab="kodex">
              <span class="pd-tab-dot" style="background:#F6C912"></span>
              <span>Шаблон КОДЕКС</span>
              <span class="pd-tab-sub">тема КОДЕКС</span>
            </button>
          </div>
          <button type="button" class="btn btn-ghost pd-reset-btn" id="pdResetProfileBtn" title="Вернуть заводские настройки для выбранного шаблона">
            <i class="fa-solid fa-rotate-left"></i> Сбросить шаблон
          </button>
        </div>

        <div class="pd-preview-wrap">
          <div class="pd-preview-toolbar">
            <div class="pd-preview-badge-wrap">
              <span class="pd-live-badge"><span class="pd-live-dot"></span> LIVE PREVIEW</span>
              <span class="pd-preview-hint">Наведите мышь на кнопки ниже, чтобы проверить цвета, тень, курсор и POPup-комментарии</span>
            </div>
            <div class="pd-preview-controls">
              <label class="pd-preview-ctrl" id="pdPreviewThemeWrap">
                <span>Тема ЦМК:</span>
                <select id="pdPreviewThemeSelect"></select>
              </label>
              <label class="pd-preview-ctrl">
                <span>Режим кнопок:</span>
                <select id="pdPreviewStateSelect">
                  <option value="mixed">1 активна + 2 неактивны</option>
                  <option value="active">Все активны</option>
                  <option value="inactive">Все неактивны</option>
                </select>
              </label>
            </div>
          </div>

          <style id="pdLivePreviewStyle"></style>
          <div class="pd-stage" id="pdPreviewStage" data-profile="cmk">
            <div class="pd-stage-bg" id="pdPreviewStageBg"></div>
            <div class="pd-stage-overlay" id="pdPreviewStageOverlay"></div>
            <div class="pd-stage-inner">
              <div class="pd-stage-kicker">ВЕБИНАР 05.11.2026 10:00</div>
              <div class="pd-stage-title">«Актуальные изменения законодательства:<br>практические рекомендации для специалистов»</div>
              <div class="pd-stage-meta" id="pdPreviewStageMeta"></div>
              <div class="pd-stage-buttons" id="pdPreviewButtons"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Сетка настроек цветов: Активный режим и Неактивный режим -->
      <div class="pd-cards-grid">
        <div class="pd-card">
          <div class="pd-card-title">
            <i class="fa-solid fa-circle-check" style="color:#1F7A3D"></i>
            Активный режим кнопки
            <span class="hint">(ссылка размещена)</span>
          </div>

          <label class="pd-check-row" id="pdUseThemeBgRow">
            <input type="checkbox" id="pd_useThemeActiveBg">
            <span>Брать цвет подложки из темы оформления вебинара (Дерево, ЖКХ, Агро и др.)</span>
          </label>

          <div class="pd-color-grid">
            <div class="pd-color-field" id="pdActiveBgField">
              <label>Цвет подложки (фон)</label>
              <div class="pd-color-input-wrap">
                <input type="color" id="pd_activeBgPicker" value="#33acc2">
                <input type="text" id="pd_activeBgHex" value="#33acc2" maxlength="7" spellcheck="false">
              </div>
            </div>
            <div class="pd-color-field">
              <label>Цвет текста кнопки</label>
              <div class="pd-color-input-wrap">
                <input type="color" id="pd_activeTextPicker" value="#ffffff">
                <input type="text" id="pd_activeTextHex" value="#ffffff" maxlength="7" spellcheck="false">
              </div>
            </div>
          </div>
        </div>

        <div class="pd-card">
          <div class="pd-card-title">
            <i class="fa-solid fa-circle-pause" style="color:#6B7280"></i>
            Неактивный режим кнопки
            <span class="hint">(ссылки ещё нет)</span>
          </div>

          <div class="pd-color-grid">
            <div class="pd-color-field">
              <label>Цвет подложки (фон)</label>
              <div class="pd-color-input-wrap">
                <input type="color" id="pd_inactiveBgPicker" value="#7a7a7a">
                <input type="text" id="pd_inactiveBgHex" value="#7a7a7a" maxlength="7" spellcheck="false">
              </div>
            </div>
            <div class="pd-color-field">
              <label>Цвет текста кнопки</label>
              <div class="pd-color-input-wrap">
                <input type="color" id="pd_inactiveTextPicker" value="#ffffff">
                <input type="text" id="pd_inactiveTextHex" value="#ffffff" maxlength="7" spellcheck="false">
              </div>
            </div>
          </div>

          <div class="pd-presets-row">
            <span class="hint">Быстрый стиль:</span>
            <button type="button" class="pd-chip-btn" data-pd-inactive-preset="#fafafa,#7a7a7a">Светлая (#fafafa)</button>
            <button type="button" class="pd-chip-btn" data-pd-inactive-preset="#7a7a7a,#ffffff">Серая (#7a7a7a)</button>
            <button type="button" class="pd-chip-btn" data-pd-inactive-preset="#3b4248,#e2e8f0">Тёмная (#3b4248)</button>
          </div>

          <div class="pd-cursor-row">
            <label for="pd_inactiveCursor"><i class="fa-solid fa-arrow-pointer"></i> Курсор при наведении на неактивную кнопку</label>
            <select id="pd_inactiveCursor">
              <option value="default">Обычная мышь (без зачёркнутого кружка)</option>
              <option value="pointer">Указатель-рука (pointer)</option>
              <option value="not-allowed">Запрещающий кружок (not-allowed)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Скругление, тень и иконки -->
      <div class="pd-card">
        <div class="pd-card-title">
          <i class="fa-solid fa-vector-square"></i>
          Скругление, тень и иконки кнопок
        </div>

        <div class="pd-sliders-grid">
          <div class="pd-slider-block">
            <div class="pd-slider-head">
              <label for="pd_borderRadius">Скругление углов</label>
              <div class="pd-quick-chips">
                <button type="button" class="pd-chip-btn" data-pd-radius="0">0px</button>
                <button type="button" class="pd-chip-btn" data-pd-radius="4">4px</button>
                <button type="button" class="pd-chip-btn" data-pd-radius="8">8px</button>
                <button type="button" class="pd-chip-btn" data-pd-radius="14">14px</button>
                <button type="button" class="pd-chip-btn" data-pd-radius="28">Капсула</button>
              </div>
              <span class="pd-slider-val" id="pd_borderRadiusVal">8px</span>
            </div>
            <input type="range" id="pd_borderRadius" min="0" max="32" step="1" value="8">
          </div>

          <div class="pd-slider-block">
            <div class="pd-slider-head">
              <label for="pd_shadowIntensity">Тень кнопки</label>
              <div class="pd-quick-chips">
                <button type="button" class="pd-chip-btn" data-pd-shadow="0">Нет</button>
                <button type="button" class="pd-chip-btn" data-pd-shadow="20">Лёгкая</button>
                <button type="button" class="pd-chip-btn" data-pd-shadow="60">Средняя</button>
                <button type="button" class="pd-chip-btn" data-pd-shadow="100">Глубокая</button>
              </div>
              <span class="pd-slider-val" id="pd_shadowIntensityVal">60%</span>
            </div>
            <input type="range" id="pd_shadowIntensity" min="0" max="100" step="5" value="60">
          </div>
        </div>

        <div class="pd-toggles-strip">
          <label class="pd-switch-item">
            <span class="auto-gen-toggle">
              <input type="checkbox" id="pd_shadowOnInactive">
              <span class="toggle-track"><span class="toggle-thumb"></span></span>
            </span>
            <span>Тень также на неактивных кнопках</span>
          </label>

          <label class="pd-switch-item">
            <span class="auto-gen-toggle">
              <input type="checkbox" id="pd_showIcons" checked>
              <span class="toggle-track"><span class="toggle-thumb"></span></span>
            </span>
            <span>Показывать иконки на кнопках</span>
          </label>
        </div>
      </div>

      <!-- Кнопки и комментарии (POPup) — свой комментарий на каждую кнопку -->
      <div class="pd-card">
        <div class="pd-card-title">
          <i class="fa-solid fa-comment-dots"></i>
          Названия кнопок и всплывающие комментарии (POPup)
          <span class="hint">— свой комментарий и включение/отключение отдельно на каждую кнопку</span>
        </div>

        <div id="pdButtonsConfigList" class="pd-buttons-config-list"></div>
      </div>
    </div>

    <div class="modal-footer">
      <span class="hint" style="margin-right:auto">Настройки применяются ко всем новым и уже открытым динамическим страницам ЦМК / КОДЕКС</span>
      <button type="button" class="btn btn-ghost" id="pageDesignCancelBtn"><i class="fa-solid fa-xmark"></i> Отмена</button>
      <button type="button" class="btn btn-primary" id="pageDesignSaveBtn"><i class="fa-solid fa-floppy-disk"></i> Сохранить дизайн</button>
    </div>
  </div>
</div>
