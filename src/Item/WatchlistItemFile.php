<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Item;

use Contao\System;

class WatchlistItemFile extends WatchlistItem
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->setTitle($data['title']);
        $this->setFile();
    }

    public function setFile()
    {
        if (null !== ($file = System::getContainer()->get('huh.utils.file')->getPathFromUuid($this->_raw['uuid']))) {
            $this->_file = $file;
        }
    }
}
