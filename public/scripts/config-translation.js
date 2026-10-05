/**
 * -------------------------------------------------------------------------
 * ideabox plugin for GLPI
 * Copyright (C) 2025-2026 by the ideabox Development Team.
 *
 * https://github.com/InfotelGLPI/ideabox
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ideabox.
 *
 * ideabox is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * ideabox is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ideabox. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

/*
 * Translations tab of the configuration (ConfigTranslation::showTranslations()). The
 * "Add a new translation" button and a click on a row load the translation form in the
 * container, whose data-* attributes carry the parent item: no PHP value reaches a script.
 */

/**
 * The form carries the scripts of its fields: a range fragment keeps them runnable
 * once inserted, unlike innerHTML.
 */
const replaceContent = (container, html) => {
    const range = document.createRange();
    range.selectNodeContents(container);
    range.deleteContents();
    container.append(range.createContextualFragment(html));
};

/**
 * @param {HTMLElement} container element carrying the data-ideabox-translation-view attribute
 * @param {string}      id        translation to edit, -1 to add one
 */
const loadForm = (container, id) => {
    const body = new URLSearchParams({
        type: container.dataset.type,
        parenttype: container.dataset.parenttype,
        items_id: container.dataset.itemsId,
        id,
    });

    fetch(container.dataset.ideaboxTranslationView, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body,
    })
        .then((response) => response.text())
        .then((html) => replaceContent(container, html));
};

document.addEventListener('click', (event) => {
    const button = event.target.closest('button[data-ideabox-translation-add]');
    if (button !== null) {
        const container = document.getElementById(button.dataset.ideaboxTranslationAdd);
        if (container !== null) {
            loadForm(container, '-1');
        }
        return;
    }

    const row = event.target.closest('tr.cursor-pointer[data-id]');
    // The massive action checkbox of the row keeps its own behaviour
    if (row === null || event.target.closest('input, a, button, label') !== null) {
        return;
    }
    const table = row.closest('table[id]');
    if (table === null) {
        return;
    }
    const container = document.querySelector(
        `[data-ideabox-translation-datatable="${CSS.escape(table.id)}"]`,
    );
    if (container !== null) {
        loadForm(container, row.dataset.id);
    }
});
