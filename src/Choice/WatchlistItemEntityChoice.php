<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Choice;

use Contao\System;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class WatchlistItemEntityChoice extends AbstractChoice
{
    public function collect()
    {
        $choices = [];

        $config = System::getContainer()->getParameter('huh.watchlist');

        if (!isset($config['watchlist']['watchlistEntityItems'])) {
            return $choices;
        }

        foreach ($config['watchlist']['watchlistEntityItems'] as $manager) {
            $choices[$manager['name']] = $manager['class'];
        }

        asort($choices);

        return $choices;
    }
}
