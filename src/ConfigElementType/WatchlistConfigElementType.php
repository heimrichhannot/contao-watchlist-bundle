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


use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
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
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;


    public function __construct(WatchlistManager $watchlistManager, PartialTemplateBuilder $templateBuilder, ContaoFrameworkInterface $framework)
    {
        $this->watchlistManager = $watchlistManager;
        $this->templateBuilder = $templateBuilder;
        $this->framework = $framework;
    }

    public function addToItemData(ItemInterface $item, ListConfigElementModel $listConfigElement)
    {
        /** @var PageModel $objPage */
        global $objPage;
        if (!$objPage) {
            return;
        }

        /** @var WatchlistConfigModel|null $configuration */
        $configuration = null;
        if ($listConfigElement->overrideWatchlistConfig) {
            $configuration = $this->framework->getAdapter(WatchlistConfigModel::class)->findByPk($listConfigElement->watchlistConfig);
        }
        if (!$configuration) {
            $configuration = $this->framework->getAdapter(WatchlistConfigModel::class)->findByPage($objPage);
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