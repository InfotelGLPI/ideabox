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

use GlpiPlugin\Ideabox\Ideabox;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Database-free coverage of the pure state helpers on Ideabox.
 */
final class StateTest extends TestCase
{
    public function testStateConstantsAreStableIntegers(): void
    {
        // These integers are persisted in the database; changing them would
        // silently reinterpret existing rows, so pin them.
        $this->assertSame(1, Ideabox::NEW);
        $this->assertSame(2, Ideabox::STUDY);
        $this->assertSame(3, Ideabox::IN_PROGRESS);
        $this->assertSame(4, Ideabox::CLOSED);
    }

    public function testStateConstantsAreDistinct(): void
    {
        $states = [
            Ideabox::NEW,
            Ideabox::STUDY,
            Ideabox::IN_PROGRESS,
            Ideabox::CLOSED,
        ];

        $this->assertSame($states, array_values(array_unique($states)));
    }

    /**
     * @return array<string, array{0: int, 1: string}>
     */
    public static function stateColorProvider(): array
    {
        return [
            'study'       => [Ideabox::STUDY, '#D1A712'],
            'in progress' => [Ideabox::IN_PROGRESS, '#4DAA77'],
            'closed'      => [Ideabox::CLOSED, '#d5703b'],
            'new'         => [Ideabox::NEW, '#2d98b1'],
        ];
    }

    #[DataProvider('stateColorProvider')]
    public function testGetStateColorReturnsExpectedHex(int $state, string $expected): void
    {
        $this->assertSame($expected, Ideabox::getStateColor($state));
    }

    public function testGetStateColorFallsBackToDefaultForUnknownState(): void
    {
        // Any value outside the known set must resolve to the default colour,
        // never null or an empty string.
        $this->assertSame('#2d98b1', Ideabox::getStateColor(999));
    }

    public function testGetStateColorAlwaysReturnsAValidCssHex(): void
    {
        foreach ([Ideabox::NEW, Ideabox::STUDY, Ideabox::IN_PROGRESS, Ideabox::CLOSED, 0, -1] as $state) {
            $this->assertMatchesRegularExpression('/^#[0-9a-fA-F]{6}$/', Ideabox::getStateColor($state));
        }
    }

    public function testGetIconIsATablerBulb(): void
    {
        $this->assertSame('ti ti-bulb', Ideabox::getIcon());
    }
}
