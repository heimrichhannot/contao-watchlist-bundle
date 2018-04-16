<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Item;

interface DownloadItemInterface
{
    /**
     * Get entire raw item data.
     *
     * @return array
     */
    public function getRaw(): array;

    /**
     * Set entire raw item data.
     *
     * @param array $data
     */
    public function setRaw(array $data = []): void;

    public function setFile();

    public function getFile();

    public function setTitle(string $title = '');

    public function getTitle();

    public function retrieveItem();
}
