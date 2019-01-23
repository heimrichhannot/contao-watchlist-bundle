<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Test\ListItem;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ListBundle\Manager\ListManagerInterface;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;
use HeimrichHannot\WatchlistBundle\ListItem\WatchlistListItem;
use HeimrichHannot\WatchlistBundle\Model\WatchlistTemplateManager;

class WatchlistListItemTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testCanBeInstantiated()
    {
        $this->assertInstanceOf(WatchlistListItem::class, new WatchlistListItem($this->createMock(ListManagerInterface::class)));
    }

    public function testGetAddToWatchlistButton()
    {
        $this->markTestSkipped();

        $manager = $this->createMock(ListManagerInterface::class);
        $manager->method('getModuleData')->willReturn(['listConfig' => 1]);

        $item = new WatchlistListItem($manager);
        $this->assertNull($item->addToWatchlistButton);

        $container = $this->mockContainer();

        $configRegistry = $this->createMock(ListConfigRegistry::class);
        $configRegistry->method('findByPk')->willReturn(null);
        $container->set('huh.list.list-config-registry', $configRegistry);
        System::setContainer($container);

        $item->getAddToWatchlistButton();

        $this->assertNull($item->addToWatchlistButton);

        $container = $this->mockContainer();

        $listConfigModel = $this->mockClassWithProperties(ListConfigModel::class, ['watchlist_config' => 1, 'tableFields' => []]);

        $configRegistry = $this->createMock(ListConfigRegistry::class);
        $configRegistry->method('findByPk')->willReturn($listConfigModel);
        $container->set('huh.list.list-config-registry', $configRegistry);

        $templateManager = $this->createMock(WatchlistTemplateManager::class);
        $templateManager->method('getAddToWatchlistButton')->willReturn('addToWatchlistButton');
        $container->set('huh.watchlist.template_manager', $templateManager);

        System::setContainer($container);

        $item = new WatchlistListItem($manager, ['id' => 1, 'title' => 'title', 'uploadedFiles' => 'uuid']);

        $item->setDataContainer('dataContainer');

        $item->getAddToWatchlistButton();
    }
}
