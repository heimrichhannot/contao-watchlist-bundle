<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Test\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\DelegatingParser;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\AjaxBundle\HeimrichHannotContaoAjaxBundle;
use HeimrichHannot\WatchlistBundle\ContaoManager\Plugin;
use HeimrichHannot\WatchlistBundle\HeimrichHannotContaoWatchlistBundle;

class PluginTest extends ContaoTestCase
{
    /**
     * test instantitation.
     */
    public function testCanBeInstantiated()
    {
        $this->assertInstanceOf(Plugin::class, new Plugin());
    }

    public function testGetBundles()
    {
        $plugin = new Plugin();

        $bundles = $plugin->getBundles(new DelegatingParser());

        $this->assertCount(1, $bundles);
        $this->assertInstanceOf(BundleConfig::class, $bundles[0]);
        $this->assertSame(HeimrichHannotContaoWatchlistBundle::class, $bundles[0]->getName());
        $this->assertSame(ContaoCoreBundle::class, $bundles[0]->getLoadAfter()[0]);
        $this->assertSame(HeimrichHannotContaoAjaxBundle::class, $bundles[0]->getLoadAfter()[1]);
    }
}
