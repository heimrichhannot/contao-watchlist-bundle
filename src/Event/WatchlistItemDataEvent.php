<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Event;

use HeimrichHannot\WatchlistBundle\Item\WatchlistItem;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use Symfony\Contracts\EventDispatcher\Event;

class WatchlistItemDataEvent extends Event
{
    public function __construct(
        public array                         $itemData,
        public readonly WatchlistConfigModel $configModel,
        public readonly WatchlistItem        $item,
    ) {}

    /**
     * @deprecated Use property access instead
     */
    public function getItem(): array
    {
        return $this->itemData;
    }

    /**
     * @deprecated Use property access instead
     */
    public function setItem(array $itemData): void
    {
        $this->itemData = $itemData;
    }

    /**
     * @deprecated Use property access instead
     */
    public function getConfigModel(): WatchlistConfigModel
    {
        return $this->configModel;
    }
}
