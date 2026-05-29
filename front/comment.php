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
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 ideabox is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with ideabox. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

use GlpiPlugin\Ideabox\Comment;
use GlpiPlugin\Ideabox\Ideabox;

if (!Session::haveRight('plugin_ideabox', READ)) {
    Html::displayRightError();
    exit;
}

if (!isset($_GET["plugin_ideabox_ideaboxes_id"])) $_GET["plugin_ideabox_ideaboxes_id"] = "";

$comment = new Comment();

Html::popHeader(Ideabox::getTypeName(2));

$comment->seeComments($_GET["plugin_ideabox_ideaboxes_id"], false);

