<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Item;

use Contao\System;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;

class WatchlistItemEntity extends WatchlistItem
{
    public function setRaw(array $data = []): void
    {
        if (null === ($entity = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($data['ptable'], $data['ptableId']))) {
            return;
        }

        $this->_raw = $entity->row();
    }

    public function getDetailsUrl(WatchlistConfigModel $configuration): ?string
    {
        if (!$configuration->jumpToDetails) {
            return null;
        }

        if (null === ($page = System::getContainer()->get('huh.utils.url')->getJumpToPageUrl($configuration->jumpToDetails))) {
            return null;
        }

        if (!$configuration->alias) {
            return $page;
        }

        return $page.\DIRECTORY_SEPARATOR.$this->_raw[$configuration->alias];
    }
}
