<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Martin Kunitzsch <m.kunitzsch@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\WatchlistBundle\ConfigElementType;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Model;
use Contao\PageModel;
use Contao\System;
use HeimrichHannot\ReaderBundle\ConfigElementType\ReaderConfigElementData;
use HeimrichHannot\ReaderBundle\ConfigElementType\ReaderConfigElementTypeInterface;
use HeimrichHannot\ReaderBundle\Item\ItemInterface;
use HeimrichHannot\ReaderBundle\Model\ReaderConfigElementModel;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\PartialTemplate\AbstractAddToWatchlistActionPartialTemplate;
use HeimrichHannot\WatchlistBundle\PartialTemplate\AddFileToWatchlistActionPartialTemplate;
use HeimrichHannot\WatchlistBundle\PartialTemplate\AddEntityToWatchlistActionPartialTemplate;
use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateBuilder;
use Model\Collection;

class WatchlistConfigElementReaderType implements ReaderConfigElementTypeInterface
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
        $this->templateBuilder  = $templateBuilder;
        $this->framework        = $framework;

        global $objPage;
        $this->page = $objPage;
    }

    public function addToItemData(ItemInterface $item, ReaderConfigElementModel $readerConfigElement): void
    {
        /** @var PageModel $objPage */
        global $objPage;
        if (!$objPage) {
            return;
        }

        /** @var WatchlistConfigModel|null $configuration */
        $configuration = null;
        if ($readerConfigElement->overrideWatchlistConfig) {
            $configuration = $this->framework->getAdapter(WatchlistConfigModel::class)->findByPk($readerConfigElement->watchlistConfig);
        }
        if (!$configuration) {
            $configuration = $this->framework->getAdapter(WatchlistConfigModel::class)->findByPage($this->page);
        }

        if (!$configuration) {
            return;
        }

        $watchlist = $this->watchlistManager->getWatchlistModel($configuration);

        /**
         * @var AbstractAddToWatchlistActionPartialTemplate
         */
        $button = $this->getButton($configuration,$watchlist,$item,$readerConfigElement);

        $templateVariable          = $readerConfigElement->templateVariable ? $readerConfigElement->templateVariable : 'addToWatchlistButton';
        $item->{$templateVariable} = $this->templateBuilder->generate($button);
        $item->watchlistData       = $button->getContext();
    }

    /**
     * @param WatchlistConfigModel $config
     * @param WatchlistModel|Collection $watchlist
     * @param ItemInterface $item
     * @param ReaderConfigElementModel $readerConfigElement
     * @return string
     */
    protected function getButton(WatchlistConfigModel $config, $watchlist, ItemInterface $item, ReaderConfigElementModel $readerConfigElement)
    {
        if($watchlist instanceof Collection) {
            $watchlist = $watchlist[0];
        }

        if(WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE == $readerConfigElement->watchlistType) {
            return new AddFileToWatchlistActionPartialTemplate(
                $config,
                $watchlist,
                $this->getButtonData($item, $readerConfigElement,  $watchlist),
                $item->getRawValue($readerConfigElement->fileField)
            );
        } else {
            return new AddEntityToWatchlistActionPartialTemplate(
                $config,
                $watchlist,
                $this->getButtonData($item, $readerConfigElement,  $watchlist),
                null,
                $item->getDataContainer(),
                $item->getRawValue('id')
            );
        }
    }


    /**
     * @param ItemInterface $item
     * @param ReaderConfigElementModel $readerConfigElement
     * @param WatchlistModel $watchlist
     * @return array
     */
    protected function getButtonData(ItemInterface $item, ReaderConfigElementModel $readerConfigElement, WatchlistModel $watchlist ): array
    {
        $buttonData = $this->getDefaultButtonData($item, $readerConfigElement);

        if (WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE == $readerConfigElement->watchlistType) {
            $buttonData['added'] = (int)System::getContainer()->get('huh.watchlist.watchlist_item_manager')->isItemInWatchlist($watchlist->id, $item->getRawValue($readerConfigElement->fileField));
        } else {
            $buttonData['added'] = (int)System::getContainer()->get('huh.watchlist.watchlist_item_manager')->isItemInWatchlist($watchlist->id, null, $item->getDataContainer(), $item->getRawValue('id'));
        }

        return $buttonData;
    }

    /**
     * @param ItemInterface $item
     * @param ReaderConfigElementModel $readerConfigElement
     * @return array
     */
    protected function getDefaultButtonData(ItemInterface $item, ReaderConfigElementModel $readerConfigElement): array
    {
        $translator = System::getContainer()->get('translator');
        $label      = $translator->trans($readerConfigElement->customLabel ? $readerConfigElement->customLabel : 'huh.watchlist.item.add.link');
        $title      =  $item->{$readerConfigElement->titleField} ? $translator->trans('huh.watchlist.item.add.title', ['%item%' => $item->{$readerConfigElement->titleField}]) : '';

        return [
            'label'         => $label,
            'linkTitle'         => $title,
            'title'     => $item->{$readerConfigElement->titleField},
            'pageId'        => $this->page->id,
            'downloadable'  => true,
            'type'          => $readerConfigElement->watchlistType
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
     * @param ReaderConfigElementData $configElementData
     */
    public function addToReaderItemData(ReaderConfigElementData $configElementData): void
    {
        $this->addToItemData($configElementData->getItem(), $configElementData->getReaderConfigElement());
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