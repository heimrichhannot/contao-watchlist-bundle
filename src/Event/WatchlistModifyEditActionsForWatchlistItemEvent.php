<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Event;

use Contao\FrontendTemplate;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use Symfony\Component\EventDispatcher\Event;

class WatchlistModifyEditActionsForWatchlistItemEvent extends Event
{
    const NAME = 'huh.watchlist.event.watchlist_modify_edit_actions_for_watchlist_item';

    /**
     * @var FrontendTemplate
     */
    protected $template;

    /**
     * @var WatchlistConfigModel
     */
    protected $config;

    /**
     * @var int
     */
    protected $watchlist;

    /**
     * @var array
     */
    protected $item;

    public function __construct(FrontendTemplate $template, WatchlistConfigModel $config, int $watchlist, array $item)
    {
        $this->template = $template;
        $this->config = $config;
        $this->watchlist = $watchlist;
        $this->item = $item;
    }

    public function getTemplate(): FrontendTemplate
    {
        return $this->template;
    }

    public function setTemplate(FrontendTemplate $template): void
    {
        $this->template = $template;
    }

    public function getConfig(): WatchlistConfigModel
    {
        return $this->config;
    }

    public function setConfig(WatchlistConfigModel $config): void
    {
        $this->config = $config;
    }

    public function getWatchlist(): int
    {
        return $this->watchlist;
    }

    public function setWatchlist(int $watchlist): void
    {
        $this->watchlist = $watchlist;
    }

    public function getItem(): array
    {
        return $this->item;
    }

    public function setItem(array $item): void
    {
        $this->item = $item;
    }
}
