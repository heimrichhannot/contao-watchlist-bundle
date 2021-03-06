<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Item;

use Contao\System;

class DownloadItemEntity extends DownloadItem
{
    /**
     * {@inheritdoc}
     */
    public function setRaw(array $data = []): void
    {
        if (null === ($entity = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($data['ptable'], $data['ptableId']))) {
            $this->_raw = null;
        }

        $this->_raw = $entity->row();
    }

    public function getDownloads(): ?array
    {
        return null;
    }
}
