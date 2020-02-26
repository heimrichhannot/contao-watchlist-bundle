<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\PartialTemplate;

interface PartialTemplateInterface
{
    /**
     * Generate the template.
     */
    public function generate(): string;

    /**
     * Set the template builder to get the dependencies from the getter methods.
     *
     * @return mixed
     */
    public function setBuilder(PartialTemplateBuilder $builder);
}
