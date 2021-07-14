<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Test\ConfigElementType;

use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ListBundle\ConfigElementType\ListConfigElementData;
use HeimrichHannot\ListBundle\Item\DefaultItem;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;
use HeimrichHannot\UtilsBundle\Tests\ModelMockTrait;
use HeimrichHannot\WatchlistBundle\ConfigElement\WatchlistConfigElementType;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateBuilder;

class WatchlistConfigElementTypeTest extends ContaoTestCase
{
    use ModelMockTrait;

    public function testAddToListItemData()
    {
        $watchlistConfigModel = $this->mockAdapter(['findByPage', 'findByPk']);
        $watchlistConfigModel->method('findByPage')->willReturn(null);
        $watchlistConfigModel->method('findByPk')->willReturnCallback(function ($id) {
            $watchlistConfig = $this->mockModelObject(WatchlistConfigModel::class, []);
            switch ($id) {
                case 2:
                    $watchlistConfig->id = 2;

                    return $watchlistConfig;
                default:
                    return null;
            }
        });

        $watchlistManager = $this->createMock(WatchlistManager::class);
        $watchlistManager->method('getWatchlistModel')->willReturnCallback(function (?WatchlistConfigModel $configuration = null, ?int $watchlistId = null) {
            $id = $configuration->id;
            $watchlistModel = $this->mockModelObject(WatchlistModel::class, []);
            switch ($id) {
                case 2:
                    $watchlistModel->id = 2;

                    return $watchlistModel;
                default:
                    return null;
            }
        });

        $templateBuilder = $this->createMock(PartialTemplateBuilder::class);
        $framework = $this->mockContaoFramework([
            WatchlistConfigModel::class => $watchlistConfigModel,
        ]);

        $configElement = new WatchlistConfigElementType($watchlistManager, $templateBuilder, $framework);

        /** @var DefaultItem $item */
        $item = $this->createMock(DefaultItem::class);
        /** @var ListConfigElementModel $listConfigElementModel */
        $listConfigElementModel = $this->mockClassWithProperties(ListConfigElementModel::class, []);

        $itemData = new ListConfigElementData($item, $listConfigElementModel);

        $before = $item->getRaw();

        $configElement->addToListItemData($itemData);
        $this->assertSame($before, $item->getRaw());

        $page = $this->mockModelObject(PageModel::class, ['id' => 1]);
        $GLOBALS['objPage'] = $page;
        $configElement->addToListItemData($itemData);
        $this->assertSame($before, $item->getRaw());

        $listConfigElementModel = $this->mockClassWithProperties(ListConfigElementModel::class, ['watchlistConfig' => 1]);
        $itemData = new ListConfigElementData($item, $listConfigElementModel);
        $configElement->addToListItemData($itemData);
        $this->assertSame($before, $item->getRaw());

        $item = $this->mockClassWithProperties(DefaultItem::class, ['uuid' => 'abcd', 'title' => 'ABC']);
        $listConfigElementModel = $this->mockClassWithProperties(ListConfigElementModel::class, [
            'overrideWatchlistConfig' => true,
            'watchlistConfig' => 2,
            'fileField' => 'uuid',
            'titleField' => 'title',
        ]);
        $itemData = new ListConfigElementData($item, $listConfigElementModel);
        $configElement->addToListItemData($itemData);
        $this->assertSame($before, $item->getRaw());

        unset($GLOBALS['objPage']);
    }

    public function testGetPalette()
    {
        $watchlistManager = $this->createMock(WatchlistManager::class);
        $templateBuilder = $this->createMock(PartialTemplateBuilder::class);
        $framework = $this->mockContaoFramework();
        $configElement = new WatchlistConfigElementType($watchlistManager, $templateBuilder, $framework);
        $this->assertTrue(\is_string($configElement->getPalette()));
    }

    public function testGetType()
    {
        $this->assertSame(WatchlistConfigElementType::TYPE, WatchlistConfigElementType::getType());
    }
}
