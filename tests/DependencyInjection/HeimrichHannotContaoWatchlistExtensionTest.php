<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Test\DependencyInjection;

class HeimrichHannotContaoWatchlistExtensionTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();
        $container = new ContainerBuilder(new ParameterBag(['kernel.debug' => false]));
        $extension = new HeimrichHannotContaoWatchlistExtension();
        $extension->load([], $container);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $extension = new HeimrichHannotContaoWatchlistExtension();

        $this->assertInstanceOf(HeimrichHannotContaoWatchlistExtension::class, $extension);
    }
}
