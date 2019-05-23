<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Choice;

use Contao\System;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class WatchlistItemFileChoice extends AbstractChoice
{
    public function collect()
    {
        $choices = [];

        $config = System::getContainer()->getParameter('huh_watchlist');

        if (!isset($config['watchlistFileItems'])) {
            return $choices;
        }

        foreach ($config['watchlistFileItems'] as $manager) {
            $choices[$manager['name']] = $manager['class'];
        }

        asort($choices);

        return $choices;
    }
}
