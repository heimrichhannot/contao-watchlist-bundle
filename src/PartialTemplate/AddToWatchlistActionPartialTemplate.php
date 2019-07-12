<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\WatchlistBundle\PartialTemplate;


use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;

/**
 * Class AddToWatchlistPartialTemplate
 * @package HeimrichHannot\WatchlistBundle\PartialTemplate
 *
 * @property PartialTemplateBuilder $builder
 */
class AddToWatchlistActionPartialTemplate extends AbstractPartialTemplate
{
    /**
     * @var WatchlistConfigModel
     */
    private $configuration;
    /**
     * @var string
     */
    private $dataContainer;
    /**
     * @var bool
     */
    private $downloadable;
    /**
     * @var string
     */
    private $uuid;
    /**
     * @var array
     */
    private $options;
    /**
     * @var string
     */
    private $linkText;
    /**
     * @var string
     */
    private $linkTitle;
    /**
     * @var string
     */
    private $fileTitle;
    /**
     * @var int
     */
    private $pageId;
    /**
     * @var string
     */
    private $ptable;
    /**
     * @var string
     */
    private $ptableId;
    /**
     * @var WatchlistModel
     */
    private $watchlistModel;

    /**
     * AddToWatchlistPartialTemplate constructor.
     * @param WatchlistConfigModel $configuration
     * @param string $dataContainer
     * @param string $uuid The uuid (binary format) of the file
     * @param string $fileTitle A friendly file name
     * @param int $pageId
     * @param array $options
     * @param string $linkText Override default link text
     * @param string $linkTitle Override default link title attribute text
     * @param string $ptable
     * @param string $ptableId
     * @param bool $downloadable
     */
    public function __construct(
        WatchlistConfigModel $configuration,
        string $dataContainer,
        string $uuid,
        string $fileTitle,
        WatchlistModel $watchlistModel = null,
        int $pageId = 0,
        array $options = [],
        string $linkText = '',
        string $linkTitle = '',
        string $ptable = '',
        string $ptableId = '',
        bool $downloadable = true
    )
    {
        $this->configuration = $configuration;
        $this->dataContainer = $dataContainer;
        $this->uuid = $uuid;
        $this->downloadable = $downloadable;
        $this->options = $options;
        $this->linkText = $linkText;
        $this->linkTitle = $linkTitle;
        $this->fileTitle = $fileTitle;
        $this->pageId = $pageId;
        $this->ptable = $ptable;
        $this->ptableId = $ptableId;
        $this->watchlistModel = $watchlistModel;
    }

    public function getTemplateName(): string
    {
        return static::TEMPLATE_ACTION;
    }


    public function generate(): string
    {
        $url = $this->builder->getRouter()->generate('huh_watchlist_add_to_watchlist');
        $added = $this->watchlistModel ?
            $this->builder->getWatchlistItemManager()->isItemInWatchlist($this->watchlistModel->id, $this->uuid) : false;
        $attributes = $this->createDefaultActionAttributes($this->configuration, $url, static::ACTION_TYPE_UPDATE);
        $attributes['type'] = WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE;
        $attributes['added'] = (int) $added;
        $attributes['fileUuid'] = bin2hex($this->uuid);
        $attributes['options'] = json_encode($this->options);
        $attributes['downloadable'] = $this->downloadable;
        $attributes['dataContainer'] = $this->dataContainer;
        $attributes['title'] = $this->fileTitle;
        $attributes['pageId'] = $this->pageId;
        $attributes['ptable'] = $this->ptable;
        $attributes['ptableId'] = $this->ptableId;


        $context = $this->createDefaultActionContext($attributes, $this->configuration);
        $context['cssClass'] .= ' huh_watchlist_add_to_watchlist';
        if ($added) $context['cssClass'] .= ' added';
        $context['linkText'] = $this->linkText ?: $this->builder->getTranslator()->trans('huh.watchlist.item.add.link');
        $context['linkTitle'] = $this->linkTitle ?: $this->builder->getTranslator()
            ->trans('huh.watchlist.item.add.title', ['%item%' => $this->fileTitle]);
        $context = $this->prepareContext($context);

        $template = $this->getTemplate($this->builder->getFrontendFramework($this->configuration));
        return $this->builder->getTwig()->render($template, $context);
    }
}