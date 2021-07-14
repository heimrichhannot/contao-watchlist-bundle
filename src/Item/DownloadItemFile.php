<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Item;

use Contao\System;

class DownloadItemFile extends DownloadItem
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->setFile();
    }

    /**
     * set path to item file.
     */
    public function setFile()
    {
        $this->_file = System::getContainer()->get('huh.utils.file')->getPathFromUuid($this->_raw['uuid']);
    }

    /**
     * retrieve the item for download.
     */
    public function getDownloads(): ?array
    {
        if (null === ($file = $this->getFile())) {
            return null;
        }

        if (isset($this->_raw['title'])) {
            $this->setTitle($this->_raw['title']);
        }

        if ('' == $this->getTitle() && '' == $this->getFile()) {
            return null;
        }

        return [
            'title' => $this->getTitle(),
            'file' => $this->getFile(),
        ];
    }
}
