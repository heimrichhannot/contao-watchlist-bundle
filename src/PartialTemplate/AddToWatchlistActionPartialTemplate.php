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
     * @var WatchlistModel
     */
    private $watchlist;
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
     * @var string
     */
    private $title;
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
     * AddToWatchlistPartialTemplate constructor.
     * @param WatchlistConfigModel $configuration
     * @param string $dataContainer
     * @param string $uuid The uuid (binary format) of the file
     * @param string $fileTitle A friendly file name
     * @param array $options
     * @param string $linkText Override default link text
     * @param string $linkTitle Override default link title attribute text
     * @param bool $downloadable
     */
    public function __construct(
        WatchlistConfigModel $configuration,
        string $dataContainer,
        string $uuid,
        string $fileTitle,
        array $options = [],
        string $linkText = '',
        string $linkTitle = '',
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
    }

    public function getTemplateName(): string
    {
        return static::TEMPLATE_ACTION;
    }


    public function generate(): string
    {
        $url = $this->builder->getRouter()->generate('huh_watchlist_add_to_watchlist');

        $attributes = $this->createDefaultActionAttributes($this->configuration, $url, static::ACTION_TYPE_UPDATE);
        $attributes['type'] = WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE;
        $attributes['added'] = '0'; //$this->builder->getWatchlistItemManager()->isItemInWatchlist($this->watchlist->id, $this->fileUuid);
        $attributes['fileUuid'] = bin2hex($this->uuid);
        $attributes['options'] = json_encode($this->options);
        $attributes['downloadable'] = $this->downloadable;
        $attributes['dataContainer'] = $this->dataContainer;
        $attributes['title'] = $this->fileTitle;


        $context = $this->createDefaultActionContext($attributes, $this->configuration);
        $context['cssClass'] .= ' huh_watchlist_add_to_watchlist';
        $context['linkText'] = $this->linkText ?: $this->builder->getTranslator()->trans('huh.watchlist.item.add.link');
        $context['linkTitle'] = $this->linkTitle ?: $this->builder->getTranslator()->trans('huh.watchlist.item.add.title', ['%item%' => $this->fileTitle]);
        $context = $this->prepareContext($context);

        $template = $this->getTemplate($this->builder->getFrontendFramework($this->configuration));
        return $this->builder->getTwig()->render($template, $context);
    }
}