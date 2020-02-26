<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\FrontendFramework;

use Contao\Module;
use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateInterface;

class Bootstrap4WatchlistFramework extends AbstractWatchlistFrontendFramework
{
    /**
     * Return an alias for the framework. Example bs4 for Bootstrap 4.
     * Only lowercase letters a-z, numbers and underscores are allowed (regexp: ^[a-z0-9_]+$).
     */
    public function getType(): string
    {
        return 'bs4';
    }

    public function prepareContext(array $context, PartialTemplateInterface $template): array
    {
        $context['modelCssClass'] = 'fade';
        $context['modalDialogCssClass'] = 'modal-xl';

        return $context;
    }

    /**
     * Add or edit data attributes.
     */
    public function prepareDataAttributes(array $attributes, PartialTemplateInterface $template): array
    {
        return $attributes;
    }

    public function prepareModuleTemplate(array $context, Module $template): array
    {
        return $context;
    }
}
