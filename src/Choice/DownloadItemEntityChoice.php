<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Choice;

use Contao\System;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class DownloadItemEntityChoice extends AbstractChoice
{
    protected function collect()
    {
        $choices = [];

        $config = System::getContainer()->getParameter('huh.watchlist');

        if (!isset($config['watchlist']['downloadEntityItems'])) {
            return $choices;
        }

        foreach ($config['watchlist']['downloadEntityItems'] as $manager) {
            $choices[$manager['name']] = $manager['class'];
        }

        asort($choices);

        return $choices;
    }
}
