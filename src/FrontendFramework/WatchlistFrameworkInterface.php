<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\FrontendFramework;

use Contao\Module;
use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateInterface;

interface WatchlistFrameworkInterface
{
    /**
     * Return an alias for the framework. Example bs4 for Bootstrap 4.
     * Only lowercase letters a-z, numbers and underscores are allowed (regexp: ^[a-z0-9_]+$).
     */
    public function getType(): string;

    /**
     * Return the twig template format, e.g. 'html.twig'.
     */
    public function getTemplateFormat(): string;

    /**
     * Returns the twig template name for the given action.
     *
     * Override this method for custom template names.
     */
    public function getTemplate(string $action): string;

    /**
     * Add or edit the module template variables.
     */
    public function prepareModuleTemplate(array $context, Module $template): array;

    /**
     * Add or edit data attributes.
     */
    public function prepareDataAttributes(array $attributes, PartialTemplateInterface $template): array;

    /**
     * Prepare the template context.
     */
    public function prepareContext(array $context, PartialTemplateInterface $template): array;
}
