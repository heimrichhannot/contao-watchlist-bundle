<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\PartialTemplate;

class AddFileToWatchlistActionPartialTemplate extends AbstractAddToWatchlistActionPartialTemplate
{
    protected function getAttributes(array $attributes = []): array
    {
        $attributes['uuid'] = $this->uuid;

        return $attributes;
    }
}
