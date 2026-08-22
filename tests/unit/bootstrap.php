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

// Lightweight, database-free bootstrap for the plugin's unit suite.
// It loads GLPI's Composer autoloader (so plugin classes can extend core
// classes such as CommonDBTM/CommonGLPI) and registers a PSR-4 autoloader for
// the GlpiPlugin\Ideabox\ namespace, without booting the GLPI kernel or a DB.
// Tests here must therefore target pure/deterministic logic only.

$glpi_root = dirname(__DIR__, 4);

if (!file_exists($glpi_root . '/vendor/autoload.php')) {
    fwrite(STDERR, "\nGLPI vendor/autoload.php not found — run `composer install` at the GLPI root.\n\n");
    exit(1);
}

require_once $glpi_root . '/vendor/autoload.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'GlpiPlugin\\Ideabox\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
    $file     = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $relative . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
}, prepend: true);
