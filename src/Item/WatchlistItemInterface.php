<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Item;

use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;

interface WatchlistItemInterface
{
    /**
     * Get entire raw item data.
     */
    public function getRaw(): array;

    /**
     * Set entire raw item data.
     */
    public function setRaw(array $data = []): void;

    public function setTitle(string $title = '');

    public function getTitle();

    public function setFile();

    public function getFile();

    public function getEditActions(WatchlistConfigModel $configuration, int $watchlistId);

    public function getDetailsUrl(WatchlistConfigModel $configuration): ?string;
}
