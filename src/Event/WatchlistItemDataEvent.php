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
    public function __construct(private array $item, private readonly WatchlistConfigModel $configModel)
    {
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
