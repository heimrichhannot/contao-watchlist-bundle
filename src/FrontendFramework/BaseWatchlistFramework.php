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
use HeimrichHannot\WatchlistBundle\Manager\AjaxManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
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