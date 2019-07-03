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


use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseWatchlistFramework implements WatchlistFrameworkInterface
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

    public function getWindowTemplate(): string
    {
        return '@HeimrichHannotContaoWatchlist/watchlist/watchlist_window_base.html.twig';
    }

    public function compile(array $context): array
    {
        return $context;
    }

    public function getActionTemplate()
    {
        return '@HeimrichHannotContaoWatchlist/action/watchlist_action_base.html.twig';
    }
}