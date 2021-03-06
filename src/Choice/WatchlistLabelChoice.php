<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Choice;

use Contao\System;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class WatchlistLabelChoice extends AbstractChoice
{
    public function collect()
    {
        // TODO: Implement collect() method.
        $choices = [];

        $prefixes = $this->getContext();

        if (!\is_array($prefixes)) {
            $prefixes = [$prefixes];
        }

        $translator = System::getContainer()->get('translator');

        $catalog = $translator->getCatalogue();
        $all = $catalog->all();
        $messages = $all['messages'];

        if (!\is_array($messages)) {
            return $choices;
        }

        $choices = System::getContainer()->get('huh.utils.array')->filterByPrefixes($messages, $prefixes);

        foreach ($choices as $key => $value) {
            $choices[$key] = $value.'['.$key.']';
        }

        return $choices;
    }
}
