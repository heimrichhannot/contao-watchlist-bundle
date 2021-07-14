<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Test;

use HeimrichHannot\WatchlistBundle\DependencyInjection\HeimrichHannotContaoWatchlistExtension;
use HeimrichHannot\WatchlistBundle\HeimrichHannotContaoWatchlistBundle;
use PHPUnit\Framework\TestCase;

class HeimrichHannotContaoWatchlistBundleTest extends TestCase
{
    public function testCanBeInstantiated()
    {
        $bundle = new HeimrichHannotContaoWatchlistBundle();
        $this->assertInstanceOf(HeimrichHannotContaoWatchlistBundle::class, $bundle);
    }

    public function testGetTheContainerExtension()
    {
        $bundle = new HeimrichHannotContaoWatchlistBundle();
        $this->assertInstanceOf(HeimrichHannotContaoWatchlistExtension::class, $bundle->getContainerExtension());
    }
}
