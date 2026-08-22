<?php

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

namespace GlpiPlugin\Ideabox\Tests\Unit;

use GlpiPlugin\Ideabox\Servicecatalog;
use PHPUnit\Framework\TestCase;

/**
 * Database-free coverage of the service-catalog menu contract.
 *
 * Only the literal getters are exercised here: the URL getters reference the
 * PLUGIN_IDEABOX_WEBDIR constant, which is defined during plugin boot and is
 * intentionally out of scope for this kernel-free suite.
 */
final class ServicecatalogTest extends TestCase
{
    public function testMenuLogoCssIsANonEmptyFontSizeRule(): void
    {
        $css = Servicecatalog::getMenuLogoCss();

        $this->assertNotSame('', $css);
        $this->assertStringContainsString('font-size', $css);
    }

    public function testMenuTitleAndCommentAreNonEmptyStrings(): void
    {
        // Rendered through __s(); without a loaded locale it echoes the source
        // string, so we only assert the contract (non-empty string), not wording.
        $this->assertNotSame('', Servicecatalog::getMenuTitle());
        $this->assertNotSame('', Servicecatalog::getMenuComment());
    }

    public function testListGettersAreEmptyByDesign(): void
    {
        // The service catalog exposes no sub-list; these must stay empty strings
        // (the menu renderer concatenates them verbatim).
        $this->assertSame('', Servicecatalog::getLinkList());
        $this->assertSame('', Servicecatalog::getList());
    }
}
