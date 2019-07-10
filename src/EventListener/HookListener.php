<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\WatchlistBundle\EventListener;

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
     * Hook: parseTemplate
     *
     * @param Template $template
     */
    public function onParseTemplate(Template $template)
    {
        $configuration = $this->container->getParameter('huh_watchlist');

        if (in_array($template->type, $configuration['content_elements']))
        {
            if ($template->disableWatchlist)
            {
                return;
            }
            $configuration = null;
            if ($template->overrideWatchlistConfig) {
                $configuration = WatchlistConfigModel::findByPk($template->watchlistConfig);
            }
            if (!$configuration) {

                // TODO: Get default config

                /** @var WatchlistConfigModel $configuration */
                $configuration = WatchlistConfigModel::findAll()->current();
            }
            $this->eventDispatcher->dispatch(
                WatchlistPrepareElementEvent::NAME,
                new WatchlistPrepareElementEvent($template, $configuration)
            );
        }
    }
}
