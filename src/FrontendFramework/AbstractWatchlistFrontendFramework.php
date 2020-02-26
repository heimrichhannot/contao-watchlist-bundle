<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\FrontendFramework;

abstract class AbstractWatchlistFrontendFramework implements WatchlistFrameworkInterface
{
    public function getTemplateFormat(): string
    {
        return 'html.twig';
    }

    /**
     * Returns the twig template name for the given action.
     */
    public function getTemplate(string $action): string
    {
        return $action.'_'.$this->getType();
    }
}
