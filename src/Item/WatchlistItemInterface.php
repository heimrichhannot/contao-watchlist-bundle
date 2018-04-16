<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Item;

use Contao\ModuleModel;

interface WatchlistItemInterface
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

    public function setTitle(string $title = '');

    public function getTitle();

    public function setFile();

    public function getFile();

    public function getEditActions(ModuleModel $module);
}
