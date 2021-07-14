<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Test\DependencyInjection;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\WatchlistBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ConfigurationTest extends ContaoTestCase
{
    public function testCanBeInstantiated()
    {
        $configuration = new Configuration(true);

        $this->assertInstanceOf(Configuration::class, $configuration);
    }

    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration(true);

        $treeBuilder = $configuration->getConfigTreeBuilder();

        $this->assertInstanceOf(TreeBuilder::class, $treeBuilder);
    }
}
