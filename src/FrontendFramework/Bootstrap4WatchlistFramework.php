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


class Bootstrap4WatchlistFramework implements WatchlistFrameworkInterface
{

    /**
     * Return an alias for the framework. Example bs4 for Bootstrap 4.
     * Only lowercase letters a-z, numbers and underscores are allowed (regexp: ^[a-z0-9_]+$).
     *
     * @return string
     */
    public function getType(): string
    {
        return 'bs4';
    }

    /**
     * Returns the twig template path.
     *
     * @return string
     */
    public function getTemplate(): string
    {
        return '@HeimrichHannotContaoWatchlist/watchlist/watchlist_window_model_bootstrap4.html.twig';
    }

    public function compile(array $context): array
    {
        $context['modelCssClass'] = 'fade';
        $context['modalDialogCssClass'] = 'modal-xl';
        return $context;
    }
}