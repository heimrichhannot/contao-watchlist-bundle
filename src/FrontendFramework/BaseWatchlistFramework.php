<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\FrontendFramework;

use Contao\Module;
use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseWatchlistFramework extends AbstractWatchlistFrontendFramework
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getType(): string
    {
        return 'base';
    }

    public function prepareContext(array $context, PartialTemplateInterface $template): array
    {
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
