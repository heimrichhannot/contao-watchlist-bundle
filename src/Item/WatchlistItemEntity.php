<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Item;

use Contao\System;

class WatchlistItemEntity extends WatchlistItem
{
    public function setRaw(array $data = []): void
    {
        if (null === ($entity = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($data['parentTable'], $data['parentTableId']))) {
            return;
        }

        $this->_raw = $entity->raw();
    }
}
