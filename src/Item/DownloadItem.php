<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Item;

class DownloadItem implements DownloadItemInterface
{
    /**
     * item data.
     *
     * @var array
     */
    protected $_raw = [];

    /**
     * item title.
     *
     * @var
     */
    protected $_title;

    /**
     * item file.
     *
     * @var
     */
    protected $_file;

    public function __construct(array $data = [])
    {
        $this->setRaw($data);
    }

    public function getRaw(): array
    {
        return $this->_raw;
    }

    public function setRaw(array $data = []): void
    {
        $this->_raw = $data;
    }

    public function setTitle(string $title = '')
    {
        $this->_title = $title;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function getFile()
    {
        return $this->_file;
    }

    public function setFile()
    {
    }

    public function getDownloads(): ?array
    {
    }
}
