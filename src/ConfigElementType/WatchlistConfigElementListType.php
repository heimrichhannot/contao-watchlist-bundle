<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\ConfigElementType;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\System;
use HeimrichHannot\ListBundle\ConfigElementType\ListConfigElementData;
use HeimrichHannot\ListBundle\ConfigElementType\ListConfigElementTypeInterface;
use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\PartialTemplate\AbstractAddToWatchlistActionPartialTemplate;
use HeimrichHannot\WatchlistBundle\PartialTemplate\AddEntityToWatchlistActionPartialTemplate;
use HeimrichHannot\WatchlistBundle\PartialTemplate\AddFileToWatchlistActionPartialTemplate;
use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateBuilder;

class WatchlistConfigElementListType implements ListConfigElementTypeInterface
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

    /**
     * @var PageModel
     */
    private $page;

    public function __construct(
        WatchlistManager $watchlistManager,
        PartialTemplateBuilder $templateBuilder,
        ContaoFrameworkInterface $framework
    ) {
        $this->watchlistManager = $watchlistManager;
        $this->templateBuilder = $templateBuilder;
        $this->framework = $framework;

        global $objPage;
        $this->page = $objPage;
    }

    public function addToItemData(ItemInterface $item, ListConfigElementModel $listConfigElement)
    {
        /* @var PageModel $objPage */
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
            $configuration = $this->framework->getAdapter(WatchlistConfigModel::class)->findByPage($this->page);
        }

        if (!$configuration) {
            return;
        }

        $watchlist = $this->watchlistManager->getWatchlistModel($configuration);

        if ($watchlist instanceof Collection) {
            $watchlist = $watchlist[0];
        }

        /**
         * @var AbstractAddToWatchlistActionPartialTemplate
         */
        $button = $this->getButton($configuration, $watchlist, $item, $listConfigElement);

        $templateVariable = $listConfigElement->templateVariable ? $listConfigElement->templateVariable : 'addToWatchlistButton';
        $item->{$templateVariable} = $this->templateBuilder->generate($button);
        $item->watchlistData = $button->getContext();
    }

    /**
     * Return the list config element type palette.
     */
    public function getPalette(): string
    {
        return '{config_legend},overrideWatchlistConfig,watchlistType,titleField;';
    }

    /**
     * Update the item data.
     */
    public function addToListItemData(ListConfigElementData $configElementData): void
    {
        $this->addToItemData($configElementData->getItem(), $configElementData->getListConfigElement());
    }

    /**
     * Return the list config element type alias.
     */
    public static function getType(): string
    {
        return static::TYPE;
    }

    /**
     * @return string
     */
    protected function getButton(WatchlistConfigModel $config, WatchlistModel $watchlist, ItemInterface $item, ListConfigElementModel $listConfigElement)
    {
        if (WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE == $listConfigElement->watchlistType) {
            return new AddFileToWatchlistActionPartialTemplate(
                $config,
                $watchlist,
                $this->getButtonData($item, $listConfigElement, $watchlist),
                $item->getRawValue($listConfigElement->fileField)
            );
        }

        return new AddEntityToWatchlistActionPartialTemplate(
                $config,
                $watchlist,
                $this->getButtonData($item, $listConfigElement, $watchlist),
                null,
                $item->getDataContainer(),
                $item->getRawValue('id')
            );
    }

    /**
     * @param PageModel $objPage
     */
    protected function getButtonData(ItemInterface $item, ListConfigElementModel $listConfigElement, WatchlistModel $watchlist): array
    {
        $buttonData = $this->getDefaultButtonData($item, $listConfigElement);

        if (WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE == $listConfigElement->watchlistType) {
            $buttonData['added'] = (int) System::getContainer()->get('huh.watchlist.watchlist_item_manager')->isItemInWatchlist($watchlist->id, $item->getRawValue($listConfigElement->fileField));
        } else {
            $buttonData['added'] = (int) System::getContainer()->get('huh.watchlist.watchlist_item_manager')->isItemInWatchlist($watchlist->id, null, $item->getDataContainer(), $item->getRawValue('id'));
        }

        if ($buttonData['added']) {
            $buttonData['label'] = System::getContainer()->get('translator')->trans('huh.watchlist.item.add.watching');
        }

        return $buttonData;
    }

    protected function getDefaultButtonData(ItemInterface $item, ListConfigElementModel $listConfigElement)
    {
        $translator = System::getContainer()->get('translator');
        $label = $translator->trans($listConfigElement->customLabel ? $listConfigElement->customLabel : 'huh.watchlist.item.add.link');
        $title = $item->{$listConfigElement->titleField} ? $translator->trans('huh.watchlist.item.add.title', ['%item%' => $item->{$listConfigElement->titleField}]) : '';

        return [
            'label' => $label,
            'linkTitle' => $title,
            'title' => $item->{$listConfigElement->titleField},
            'pageId' => $this->page->id,
            'downloadable' => true,
            'type' => $listConfigElement->watchlistType,
        ];
    }
}
