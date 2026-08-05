<!-- Модальное окно добавления/редактирования -->
<div id="modalOverlay" class="modal-overlay" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <h2 id="modalTitle">Новый вебинар</h2>
      <button class="icon-btn" id="modalClose">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <form id="webinarForm">
      <input type="hidden" id="f_id">
      <div class="modal-body">
        <div class="field-row field-row-quad">
          <div class="field" style="--fr-w:20%">
            <label for="f_organizer">Организатор</label>
            <select id="f_organizer" required></select>
            <div id="newOrgBox" class="new-org-box" style="display:none">
              <input type="text" id="newOrgName" placeholder="Название организатора">
              <input type="color" id="newOrgColor" value="#7A9E9F" title="Цвет бейджа">
              <button type="button" class="btn btn-primary" id="newOrgSave"><i class="fa-solid fa-check"></i> Добавить</button>
              <button type="button" class="btn btn-ghost" id="newOrgCancel"><i class="fa-solid fa-xmark"></i> Отмена</button>
            </div>
          </div>
          <div class="field" style="--fr-w:22%">
            <label for="f_date">Дата вебинара</label>
            <input type="date" id="f_date" required>
          </div>
          <div class="field" style="--fr-w:36%">
            <label for="speakerTrigger">Спикер</label>
            <input type="hidden" id="f_speaker">
            <button type="button" class="ml-dropdown-trigger select-styled" id="speakerTrigger">
              <span id="speakerTriggerText">Не выбран</span>
              <i class="fa-solid fa-chevron-down"></i>
            </button>
            <div class="ml-dropdown-panel" id="speakerPanel" style="display:none">
              <div class="filter-pop-search"><input type="text" id="speakerSearchInput" placeholder="Введите первые буквы…"></div>
              <div id="speakerOptionsList" class="ml-dropdown-list"></div>
              <div class="ml-dropdown-footer">
                <div id="newSpeakerBox" class="new-org-box" style="display:none">
                  <input type="text" id="newSpeakerName" placeholder="ФИО спикера">
                  <button type="button" class="btn btn-primary" id="newSpeakerSave"><i class="fa-solid fa-check"></i></button>
                  <button type="button" class="btn btn-ghost" id="newSpeakerCancel"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <button type="button" class="btn btn-ghost" id="addSpeakerBtn"><i class="fa-solid fa-plus"></i> Добавить спикера</button>
              </div>
            </div>
          </div>
          <div class="field" style="--fr-w:22%">
            <label for="f_price">Цена, ₽</label>
            <input type="number" id="f_price" min="0" step="100" required>
          </div>
        </div>
        <div class="field field-full">
          <label for="f_title">Тема вебинара <span class="hint">(сохранится в «кавычках» автоматически)</span></label>
          <textarea id="f_title" required></textarea>
        </div>
        <div class="field-row field-row-quad">
          <div class="field" style="--fr-w:25%">
            <label for="f_direction"><i class="fa-solid fa-signs-post"></i> Направление</label>
            <select id="f_direction"></select>
            <div id="newDirBox" class="new-org-box" style="display:none">
              <input type="text" id="newDirName" placeholder="Название направления">
              <input type="color" id="newDirColor" value="#7A9E9F" title="Цвет метки">
              <button type="button" class="btn btn-primary" id="newDirSave"><i class="fa-solid fa-check"></i></button>
              <button type="button" class="btn btn-ghost" id="newDirCancel"><i class="fa-solid fa-xmark"></i></button>
            </div>
          </div>
          <div class="field" style="--fr-w:25%">
            <label><i class="fa-solid fa-tags"></i> Рассылка <span class="hint">(можно несколько)</span></label>
            <button type="button" class="ml-dropdown-trigger select-styled" id="mailingListTrigger">
              <span id="mailingListTriggerText">Не выбрано</span>
              <i class="fa-solid fa-chevron-down"></i>
            </button>
            <div class="ml-dropdown-panel" id="mailingListPanel" style="display:none">
              <div id="mailingListCheckboxes" class="ml-dropdown-list"></div>
              <div class="ml-dropdown-footer">
                <div id="newMailingListBox" class="new-org-box" style="display:none">
                  <input type="text" id="newMailingListName" placeholder="Название рассылки">
                  <input type="color" id="newMailingListColor" value="#7A9E9F" title="Цвет метки">
                  <button type="button" class="btn btn-primary" id="newMailingListSave"><i class="fa-solid fa-check"></i></button>
                  <button type="button" class="btn btn-ghost" id="newMailingListCancel"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <button type="button" class="btn btn-ghost" id="addMailingListBtn"><i class="fa-solid fa-plus"></i> Добавить значение</button>
                <button type="button" class="btn btn-primary" id="mailingListDoneBtn">Готово</button>
              </div>
            </div>
          </div>
          <div class="field" style="--fr-w:25%">
            <label for="f_published_on_site"><i class="fa-solid fa-globe"></i> Размещён на сайте</label>
            <select id="f_published_on_site"></select>
            <div id="newPublishedBox" class="new-org-box" style="display:none">
              <input type="text" id="newPublishedName" placeholder="Своё значение">
              <input type="color" id="newPublishedColor" value="#7A9E9F" title="Цвет метки">
              <button type="button" class="btn btn-primary" id="newPublishedSave"><i class="fa-solid fa-check"></i></button>
              <button type="button" class="btn btn-ghost" id="newPublishedCancel"><i class="fa-solid fa-xmark"></i></button>
            </div>
          </div>
          <div class="field" style="--fr-w:25%">
            <label for="f_subscription"><i class="fa-solid fa-star"></i> Входит в подписку</label>
            <select id="f_subscription"></select>
            <div id="newSubscriptionBox" class="new-org-box" style="display:none">
              <input type="text" id="newSubscriptionName" placeholder="Своё значение">
              <input type="color" id="newSubscriptionColor" value="#7A9E9F" title="Цвет метки">
              <button type="button" class="btn btn-primary" id="newSubscriptionSave"><i class="fa-solid fa-check"></i></button>
              <button type="button" class="btn btn-ghost" id="newSubscriptionCancel"><i class="fa-solid fa-xmark"></i></button>
            </div>
          </div>
        </div>
        <div class="field field-full">
          <label for="f_link_participant">Ссылка для участников</label>
          <div class="input-with-btn">
            <input type="text" id="f_link_participant" placeholder="https://edu.vsesem.ru/2026/…">
            <button type="button" class="btn btn-ghost" id="openLandingBtn" title="Собрать и опубликовать страницу для участников"><i class="fa-solid fa-display"></i> Страница</button>
            <label class="auto-gen-toggle" id="autoGenerateRow" title="Сгенерировать страницу автоматически при сохранении (по шаблону по умолчанию)">
              <input type="checkbox" id="f_autoGenerate" checked>
              <span class="toggle-track"><span class="toggle-thumb"></span></span>
            </label>
          </div>
        </div>
        <div class="field-row field-row-70-30">
          <div class="field">
            <label for="f_link_host">Ссылка для организатора / лектора</label>
            <input type="text" id="f_link_host" placeholder="https://vks.vsesem.ru/rooms/…">
          </div>
          <div class="field">
            <label for="f_moderator_code">Код модератора</label>
            <input type="text" id="f_moderator_code" placeholder="например 1600">
          </div>
        </div>
        <div class="field-row field-row-50-50">
          <div class="field">
            <label for="f_link_materials">Ссылка на материалы</label>
            <input type="text" id="f_link_materials" placeholder="https://edu.vsesem.ru/land/…">
          </div>
          <div class="field">
            <label for="f_link_recording">Ссылка на запись</label>
            <input type="text" id="f_link_recording" placeholder="https://…">
          </div>
        </div>
        <div id="customFieldsContainer" class="field-full"></div>

        <div class="field-full">
          <div class="landing-section-title"><i class="fa-solid fa-paperclip"></i> Документы</div>
          <div id="docsHint" class="landing-static-inline" style="display:none">
            <i class="fa-solid fa-circle-info"></i> Сначала сохраните вебинар — потом можно будет прикрепить документы.
          </div>
          <div id="docsSlotsWrap" class="doc-slots"></div>
          <button type="button" class="btn btn-ghost" id="downloadDocsZipBtn" style="margin-top:10px"><i class="fa-solid fa-file-zipper"></i> Скачать всё в ZIP</button>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" id="modalCancel"><i class="fa-solid fa-xmark"></i> Отмена</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Сохранить</button>
      </div>
    </form>
  </div>
</div>
