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
     * @var array
     */
    private $buttonData;
    /**
     * @var array
     */
    private $options;

    /**
     * AddToWatchlistPartialTemplate constructor.
     * @param WatchlistConfigModel $configuration
     * @param WatchlistModel $watchlist
     * @param array $buttonData
     * @param array $options
     */
    public function __construct(
        WatchlistConfigModel $configuration,
        WatchlistModel $watchlist,
        array $buttonData,
        array $options = []
    ) {
        $this->configuration  = $configuration;
        $this->watchlistModel = $watchlist;
        $this->buttonData     = $buttonData;
        $this->options        = $options;

    }

    public function getTemplateName(): string
    {
        return static::TEMPLATE_ACTION;
    }

    public function generate(): string
    {
        $attributes = $this->getAttributes();
        $context    = $this->getContext($attributes);

        $template = $this->getTemplate($this->builder->getFrontendFramework($this->configuration));
        return $this->builder->getTwig()->render($template, $context);
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function getContext(array $attributes = []): array
    {
        $context              = $this->createDefaultActionContext($attributes, $this->configuration);
        $context['cssClass']  .= ' huh_watchlist_add_to_watchlist' . $this->buttonData['added'] ? ' added' : '';
        $context['linkText']  = $this->buttonData['label'];
        $context['linkTitle'] = $this->buttonData['title'];

        return $this->prepareContext($context);
    }

    /**
     * get action attributes
     *
     * @return array
     */
    protected function getAttributes(): array
    {
        $attributes = $this->createDefaultActionAttributes($this->configuration, $this->getUrl(),static::ACTION_TYPE_UPDATE);

        foreach($this->buttonData as $key => $value) {
            if('uuid' == $key) {
                continue;
            }

            $attributes[$key] = $value;
        }

        if($this->options) {
            $attributes['options'] = json_encode($this->options);
        }

        return $attributes;
    }

    /**
     * @return string
     */
    protected function getUrl(): string
    {
        return $this->builder->getRouter()->generate('huh_watchlist_add_to_watchlist');
    }
}