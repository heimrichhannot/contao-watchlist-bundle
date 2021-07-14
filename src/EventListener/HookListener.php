<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\EventListener;

use Contao\PageModel;
use Contao\Template;
use HeimrichHannot\WatchlistBundle\Event\WatchlistPrepareElementEvent;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class HookListener
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var PartialTemplateBuilder
     */
    private $templateBuilder;
    /**
     * @var WatchlistManager
     */
    private $watchlistManager;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(ContainerInterface $container, PartialTemplateBuilder $templateBuilder, WatchlistManager $watchlistManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->container = $container;
        $this->templateBuilder = $templateBuilder;
        $this->watchlistManager = $watchlistManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onGetPageLayout()
    {
        // Register and check for ajax actions
        $this->container->get('huh.watchlist.ajax_manager')->ajaxActions();
    }

    /**
     * Hook: parseTemplate.
     */
    public function onParseTemplate(Template $template)
    {
        /* @var PageModel $objPage */
        global $objPage;
        if (!$objPage) {
            return;
        }
        $rootPage = PageModel::findByPk($objPage->rootId);
        if (!$rootPage) {
            return;
        }
        if (!$rootPage->enableWatchlist && !$template->overrideWatchlistConfig) {
            return;
        }

        $bundleConfig = $this->container->getParameter('huh_watchlist');

        if (\in_array($template->type, $bundleConfig['content_elements'], true)) {
            if ($template->disableWatchlist) {
                return;
            }
            $bundleConfig = null;
            if ($template->overrideWatchlistConfig) {
                $watchlistConfig = WatchlistConfigModel::findByPk($template->watchlistConfig);
            }
            if (!$watchlistConfig && $rootPage->enableWatchlist) {
                /** @var WatchlistConfigModel $bundleConfig */
                $watchlistConfig = WatchlistConfigModel::findByPk($rootPage->watchlistConfig);
            }
            if (!$watchlistConfig) {
                return;
            }
            $this->eventDispatcher->dispatch(
                WatchlistPrepareElementEvent::NAME,
                new WatchlistPrepareElementEvent($template, $watchlistConfig)
            );
        }
    }
}
