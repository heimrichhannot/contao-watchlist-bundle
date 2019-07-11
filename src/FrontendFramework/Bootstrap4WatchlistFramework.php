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


use Contao\Module;
use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateInterface;

class Bootstrap4WatchlistFramework extends AbstractWatchlistFrontendFramework
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
     * @param array $context
     * @param PartialTemplateInterface $template
     * @return array
     */
    public function prepareContext(array $context, PartialTemplateInterface $template): array
    {
        $context['modelCssClass'] = 'fade';
        $context['modalDialogCssClass'] = 'modal-xl';
        return $context;
    }

    /**
     * Add or edit data attributes
     *
     * @param array $attributes
     * @param PartialTemplateInterface $template
     * @return array
     */
    public function prepareDataAttributes(array $attributes, PartialTemplateInterface $template): array
    {
        return $attributes;
    }

    /**
     * @param array $context
     * @param Module $template
     * @return array
     */
    public function prepareModuleTemplate(array $context, Module $template): array
    {
        return $context;
    }
}