<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\WatchlistBundle\ConfigElement;


use Contao\PageModel;
use HeimrichHannot\ListBundle\ConfigElementType\ListConfigElementData;
use HeimrichHannot\ListBundle\ConfigElementType\ListConfigElementTypeInterface;
use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\PartialTemplate\AddToWatchlistActionPartialTemplate;
use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateBuilder;

class WatchlistConfigElementType implements ListConfigElementTypeInterface
{
    const TYPE = 'huh_watchlist';
    /**
     * @var WatchlistManager
     */
    private $watchlistManager;
    /**
     * @var PartialTemplateBuilder
     */
    private $templateBuilder;


    public function __construct(WatchlistManager $watchlistManager, PartialTemplateBuilder $templateBuilder)
    {
        $this->watchlistManager = $watchlistManager;
        $this->templateBuilder = $templateBuilder;
    }

    public function addToItemData(ItemInterface $item, ListConfigElementModel $listConfigElement)
    {
        /** @var PageModel $objPage */
        global $objPage;
        if (!$objPage) {
            return;
        }

        if ($listConfigElement->overrideWatchlistConfig) {
            $configuration = WatchlistConfigModel::findByPk($listConfigElement->watchlistConfig);
        }
        if (!$configuration) {
            $configuration = WatchlistConfigModel::findByPage($objPage);
        }

        if (!$configuration) {
            return;
        }

        $watchlistModel = $this->watchlistManager->getWatchlistModel($configuration);
        $button = $this->templateBuilder->generate(
            new AddToWatchlistActionPartialTemplate(
                $configuration, $item->getDataContainer(), $item->{$listConfigElement->fileField}, $item->{$listConfigElement->titleField}, $watchlistModel, $objPage->id
            )
        );

        $templateVariable = $listConfigElement->templateVariable ? $listConfigElement->templateVariable : 'addToWatchlistButton';
        $item->{$templateVariable} = $button;
    }

    /**
     * Return the list config element type palette
     *
     * @return string
     */
    public function getPalette(): string
    {
        return '{config_legend},overrideWatchlistConfig,fileField,titleField;';
    }

    /**
     * Update the item data.
     *
     * @param ListConfigElementData $configElementData
     */
    public function addToListItemData(ListConfigElementData $configElementData): void
    {
        $this->addToItemData($configElementData->getItem(), $configElementData->getListConfigElement());
    }

    /**
     * Return the list config element type alias.
     *
     * @return string
     */
    public static function getType(): string
    {
        return static::TYPE;
    }
}