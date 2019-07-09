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


use Contao\StringUtil;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;

/**
 * Class AddToWatchlistPartialTemplate
 * @package HeimrichHannot\WatchlistBundle\PartialTemplate
 *
 * @property PartialTemplateBuilder $builder
 */
class AddToWatchlistPartialTemplate extends AbstractPartialTemplate
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
     * @var array
     */
    private $entryData;
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
    private $fileField;

    /**
     * AddToWatchlistPartialTemplate constructor.
     */
    public function __construct(WatchlistConfigModel $configuration, array $entryData, string $dataContainer, bool $downloadable = true, string $fileField = 'uuid'
    )
    {
        $this->configuration = $configuration;
        $this->entryData = $entryData;
        $this->dataContainer = $dataContainer;
        $this->downloadable = $downloadable;
        $this->fileField = $fileField;
    }

    public function getTemplateType(): string
    {
        return static::TEMPLATE_ADD_TO_WATCHLIST;
    }


    public function generate(): string
    {
        if (null === ($fileUuid = StringUtil::deserialize($this->entryData[$this->fileField], true)[0])) {
            return '';
        }
        $url = $this->builder->getRouter()->generate('huh_watchlist_add_to_watchlist');

        $attributes = $this->createDefaultActionAttributes($this->configuration, $url, static::ACTION_TYPE_UPDATE);
        $attributes['type'] = WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE;
        $attributes['added'] = '0'; //$this->builder->getWatchlistItemManager()->isItemInWatchlist($this->watchlist->id, $this->fileUuid);
        $attributes['fileUuid'] = bin2hex($fileUuid);
        $options = $this->entryData['options'] ?: [];
        $attributes['options'] = json_encode($options);
        $attributes['downloadable'] = $this->downloadable;
        $attributes['dataContainer'] = $this->dataContainer;
        $attributes['title'] = $this->entryData['watchlistTitle'];


        $context = $this->createDefaultActionContext($attributes);
        $context['title'] = $this->builder->getTranslator()->trans('huh.watchlist.item.add.title', ['%item%' => $this->entryData['title']]);
        $context['link'] = $this->entryData['linkTitle'] ?: $this->builder->getTranslator()->trans('huh.watchlist.item.add.link');
        $context = $this->prepareContext($context);

        $template = $this->getTemplate($this->builder->getFrontendFramework($this->configuration));
        return $this->builder->getTwig()->render($template, $context);
    }
}