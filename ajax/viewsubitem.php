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

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

$allowed_types = [
    \GlpiPlugin\Ideabox\Comment::class,
    \GlpiPlugin\Ideabox\ConfigTranslation::class,
];
$allowed_parent_types = [
    \GlpiPlugin\Ideabox\Ideabox::class,
    \GlpiPlugin\Ideabox\Config::class,
];

if (
    isset($_POST['type'])
    && $_POST['type'] === \GlpiPlugin\Ideabox\ConfigTranslation::class
    && !Session::haveRight('config', UPDATE)
) {
    http_response_code(403);
    echo __s('Access denied');
    exit;
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
    if ($parent->getFromDB($_POST["items_id"])) {
        $item->showForm($_POST["id"], ['parent' => $parent]);
    } else {
        echo __s('Access denied');
    }
}
