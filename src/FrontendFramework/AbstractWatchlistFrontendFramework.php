<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
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
     *
     * @param string $action
     * @return string
     */
    public function getTemplate(string $action): string
    {
        return $action.'_'.$this->getType();
    }
}