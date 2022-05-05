<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Event;

use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use Symfony\Contracts\EventDispatcher\Event;

class WatchlistItemDataEvent extends Event
{
    /**
     * @var array
     */
    private $item;
    /**
     * @var WatchlistConfigModel
     */
    private $configModel;

    public function __construct(array $item, WatchlistConfigModel $configModel)
    {
        $this->item = $item;
        $this->configModel = $configModel;
    }

    public function getItem(): array
    {
        return $this->item;
    }

    public function setItem(array $item): void
    {
        $this->item = $item;
    }

    public function getConfigModel(): WatchlistConfigModel
    {
        return $this->configModel;
    }
}
