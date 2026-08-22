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

use GlpiPlugin\Ideabox\Ideabox;
use GlpiPlugin\Ideabox\Config;
use Glpi\Application\View\TemplateRenderer;
use Glpi\Exception\Http\AccessDeniedHttpException;
use GlpiPlugin\Servicecatalog\Main;

$idea = new Ideabox();

// Evaluate the access right BEFORE emitting any HTML header, so a denied user
// gets a clean 403 instead of the full interface chrome (which would leak the
// module's existence and degrade the error page).
if (!($idea->canView() || Session::haveRight("config", UPDATE))) {
    throw new AccessDeniedHttpException();
}

if (Session::getCurrentInterface() == 'central') {
    Html::header(Ideabox::getTypeName(2), '', "tools", Ideabox::class);
} else {
    if (Plugin::isPluginActive('servicecatalog')) {
        Main::showDefaultHeaderHelpdesk(Ideabox::getTypeName(2));
    } else {
        Html::helpHeader(Ideabox::getTypeName(2));
    }
}

if ($_SESSION['glpiactiveprofile']['interface'] != 'central') {
    if ($idea->canCreate()) {
        $config = new Config();
        $config->getFromDB(1);
        TemplateRenderer::getInstance()->display('@ideabox/home_intro.html.twig', [
            'title'        => Config::displayField($config, 'title'),
            'comment'      => Config::displayField($config, 'comment'),
            'icon'         => Ideabox::getIcon(),
            'submit_label' => __('Submit an idea', 'ideabox'),
        ]);
    }

    Ideabox::showSearchForm();

    Ideabox::showList($_GET);
} else {
    Search::show(Ideabox::class);
}


if (Session::getCurrentInterface() != 'central'
    && Plugin::isPluginActive('servicecatalog')) {
    Main::showNavBarFooter('ideabox');
}

if (Session::getCurrentInterface() == 'central') {
    Html::footer();
} else {
    Html::helpFooter();
}
