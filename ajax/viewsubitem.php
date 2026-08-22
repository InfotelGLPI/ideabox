<?php

/*
 -------------------------------------------------------------------------
 ideabox plugin for GLPI
 Copyright (C) 2025-2026 by the ideabox Development Team.

 https://github.com/InfotelGLPI/ideabox
 -------------------------------------------------------------------------

 LICENSE

 This file is part of ideabox.

 ideabox is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 ideabox is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with ideabox. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

use Glpi\Exception\Http\AccessDeniedHttpException;
use GlpiPlugin\Ideabox\Comment;
use GlpiPlugin\Ideabox\Config;
use GlpiPlugin\Ideabox\ConfigTranslation;
use GlpiPlugin\Ideabox\Ideabox;

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

$allowed_types = [
    Comment::class,
    ConfigTranslation::class,
];
$allowed_parent_types = [
    Ideabox::class,
    Config::class,
];

if (
    isset($_POST['type'])
    && $_POST['type'] === ConfigTranslation::class
    && !Session::haveRight('config', UPDATE)
) {
    throw new AccessDeniedHttpException();
}

if (!isset($_POST['type']) || !in_array($_POST['type'], $allowed_types, true)) {
    return;
}
if (!isset($_POST['parenttype']) || !in_array($_POST['parenttype'], $allowed_parent_types, true)) {
    return;
}

if (
    ($item = getItemForItemtype($_POST['type']))
    && ($parent = getItemForItemtype($_POST['parenttype']))
) {
    if (!$parent->getFromDB($_POST["items_id"])) {
        throw new AccessDeniedHttpException();
    }

    // The Comment sub-form discloses comment content: require the plugin READ
    // right and access to the parent idea (right + entity) before rendering it,
    // like Comment::seeComments() does. The parent table carries the entity
    // scope, so $parent->can() enforces the cross-entity boundary.
    if (
        $_POST['type'] === Comment::class
        && (
            !Session::haveRight('plugin_ideabox', READ)
            || !$parent->can($parent->getID(), READ)
        )
    ) {
        throw new AccessDeniedHttpException();
    }

    $item->showForm($_POST["id"], ['parent' => $parent]);
}
