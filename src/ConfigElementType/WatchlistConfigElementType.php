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
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ListBundle\ConfigElementType\ListConfigElementData;
use HeimrichHannot\ListBundle\ConfigElementType\ListConfigElementTypeInterface;
use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;
use HeimrichHannot\WatchlistBundle\Item\WatchlistItem;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
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


    public function __construct(
        WatchlistManager $watchlistManager,
        PartialTemplateBuilder $templateBuilder,
        ContaoFrameworkInterface $framework
    ) {
        $this->watchlistManager = $watchlistManager;
        $this->templateBuilder  = $templateBuilder;
        $this->framework        = $framework;
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

        $watchlist = $this->watchlistManager->getWatchlistModel($configuration);

        $button = $this->templateBuilder->generate(
            new AddToWatchlistActionPartialTemplate($configuration, $watchlist,
                $this->getButtonData($item, $listConfigElement, $objPage, $watchlist))
        );

        $templateVariable          = $listConfigElement->templateVariable ? $listConfigElement->templateVariable : 'addToWatchlistButton';
        $item->{$templateVariable} = $button;
    }

    /**
     * @param ItemInterface $item
     * @param ListConfigElementModel $listConfigElement
     * @param PageModel $objPage
     * @return array
     */
    protected function getButtonData(
        ItemInterface $item,
        ListConfigElementModel $listConfigElement,
        PageModel $objPage,
        WatchlistModel $watchlist
    ): array {
        $translator = System::getContainer()->get('translator');
        $label      = $translator->trans($listConfigElement->customLabel ? $listConfigElement->customLabel : 'huh.watchlist.item.add.link');
        $title      =  $item->{$listConfigElement->titleField} ? $translator->trans('huh.watchlist.item.add.title', ['%item%' => $item->{$listConfigElement->titleField}]) : '';

        $basicButtonData = [
            'label' => $label,
            'title' => $item->{$listConfigElement->titleField},
            'pageId' => $objPage->id,
            'downloadable' => true,
            'type' => $listConfigElement->watchlistType
        ];


        if (WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE == $listConfigElement->watchlistType) {
            $buttonData = array_merge($basicButtonData,
                $this->getFileButtonData($item, $listConfigElement, $watchlist));
        } else {
            $buttonData = array_merge($basicButtonData, $this->getEntityButtonData($item, $watchlist));
        }

        return $buttonData;
    }

    /**
     * @param ItemInterface $item
     * @param ListConfigElementModel $listConfigElement
     * @return array
     */
    protected function getFileButtonData(
        ItemInterface $item,
        ListConfigElementModel $listConfigElement,
        WatchlistModel $watchlist
    ): array {
        $uuid = $item->getRawValue($listConfigElement->fileField);
        $added = System::getContainer()->get('huh.watchlist.watchlist_item_manager')->isItemInWatchlist($watchlist->id, $uuid);

        return [
            'fileUuid'  => StringUtil::binToUuid($uuid),
            'added'     => (int)$added
        ];
    }

    /**
     * @param ItemInterface $item
     * @return array
     */
    protected function getEntityButtonData(ItemInterface $item, WatchlistModel $watchlist): array
    {
        $ptable   = $item->getDataContainer();
        $ptableId = $item->getRawValue('id');
        $added    = System::getContainer()->get('huh.watchlist.watchlist_item_manager')->isItemInWatchlist($watchlist->id,
            null, $ptable, $ptableId);

        return [
            'ptable'    => $ptable,
            'ptableId'  => $ptableId,
            'added'     => (int)$added
        ];
    }

    /**
     * Return the list config element type palette
     *
     * @return string
     */
    public function getPalette(): string
    {
        return '{config_legend},overrideWatchlistConfig,watchlistType,titleField;';
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