<!-- Модальное окно "Добавить столбец" -->
<div id="addColumnOverlay" class="modal-overlay" style="display:none">
  <div class="modal modal-narrow">
    <div class="modal-header">
      <h2><i class="fa-solid fa-table-columns"></i> Новый столбец</h2>
      <button class="icon-btn" id="addColumnClose">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body settings-body">
      <div class="field field-full">
        <label>Название столбца</label>
        <input type="text" id="newColumnLabel" placeholder="например «Ответственный»">
      </div>
      <div class="settings-group-title" style="margin-top:14px">Тип значения</div>
      <label class="radio-row">
        <input type="radio" name="newColumnType" value="text" checked>
        Текст — произвольная надпись (как сейчас)
      </label>
      <label class="radio-row">
        <input type="radio" name="newColumnType" value="boolean">
        Да / Нет — переключатель, зелёная/красная метка (как «Размещён на сайте»)
      </label>
      <label class="radio-row">
        <input type="radio" name="newColumnType" value="tags">
        Несколько значений — цветные метки через запятую (как «Рассылка»)
      </label>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" id="addColumnCancel">Отмена</button>
      <button type="button" class="btn btn-primary" id="addColumnSave"><i class="fa-solid fa-check"></i> Добавить</button>
    </div>
  </div>
</div>
