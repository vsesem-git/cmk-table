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
              <th style="padding: 10px 12px; width: 280px;">Мероприятие (Дата и Тема)</th>
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
      <div class="settings-group" style="border: 1px solid var(--line); border-radius: 10px; padding: 16px; background: #fdfefe; margin-bottom: 10px;">
        <div class="settings-group-title" style="margin-top:0; border-bottom: 1px solid var(--line); padding-bottom: 8px; margin-bottom: 12px; font-weight: 700; color: var(--teal-dark);"><i class="fa-solid fa-wand-magic-sparkles"></i> Генерация страниц участников (массово)</div>
        
        <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center;">
          <div style="flex: 1 1 250px;">
            <label style="display:block; font-size: 12.5px; font-weight: 600; color: var(--ink-soft); margin-bottom: 5px;">Шаблон оформления</label>
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

        <div style="display: flex; gap: 8px; align-items: center; margin-top: 14px; border-top: 1px dashed var(--line); padding-top: 12px;">
          <input type="text" id="bulk_lp_newTemplateName" placeholder="Имя для сохранения текущего шаблона" style="flex: 1; border:1px solid var(--line); border-radius:8px; padding:8px 10px; font-size:13px;">
          <button type="button" class="btn btn-ghost" id="bulk_lp_saveTemplateBtn"><i class="fa-solid fa-floppy-disk"></i> Сохранить этот шаблон</button>
        </div>
      </div>
    </div>
    <div class="modal-footer" style="padding: 14px 22px;">
      <button type="button" class="btn btn-ghost" id="bulkEditCancelBtn">Отмена</button>
      <button type="button" class="btn btn-primary" id="bulkEditSaveBtn"><i class="fa-solid fa-floppy-disk"></i> Сохранить изменения</button>
    </div>
  </div>
</div>
