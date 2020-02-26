<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\PartialTemplate;

use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;

class OpenWatchlistWindowActionPartialTemplate extends AbstractPartialTemplate
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
    private $watchlistContainerCssId;

    public function __construct(WatchlistConfigModel $configuration, WatchlistModel $watchlist, string $watchlistContainerCssId)
    {
        $this->configuration = $configuration;
        $this->watchlist = $watchlist;
        $this->watchlistContainerCssId = $watchlistContainerCssId;
    }

    public function getTemplateName(): string
    {
        return static::TEMPLATE_OPEN_WATCHLIST_WINDOW;
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function generate(): string
    {
        $url = $this->builder->getRouter()->generate('huh_watchlist_open_watchlist_window');
        $frontendFramework = $this->builder->getFrontendFramework($this->configuration);

        $count = 0;

        if (null !== ($watchlistItems = $this->builder->getWatchlistManager()->getItemsFromWatchlist($this->watchlist->id))) {
            $count = $watchlistItems->count();
        }

        $attributes = $this->createDefaultActionAttributes($this->configuration, $url, static::ACTION_TYPE_TOGGLE);
        $attributes['watchlistContainer'] = $this->watchlistContainerCssId;
        $attributes['watchlist'] = $this->watchlist->id;

        $template = $this->getTemplate($frontendFramework);
        $context = $this->createDefaultActionContext($attributes, $this->configuration, $this->watchlist);
        $context['cssClass'] .= ' huh_watchlist_open_watchlist_window huh_watchlist_show_count';
        $context['itemCount'] = $count;
        if ($this->configuration->watchlistTitle) {
            $context['link'] = $this->builder->getTranslator()->trans($this->configuration->watchlistTitle);
        } else {
            $context['link'] = $this->builder->getTranslator()->trans('huh.watchlist.watchlist_label.default');
        }

        return $this->builder->getTwig()->render($template, $context);
    }
}
