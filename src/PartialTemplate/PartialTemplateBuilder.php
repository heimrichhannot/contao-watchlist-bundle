<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\PartialTemplate;

use HeimrichHannot\WatchlistBundle\FrontendFramework\WatchlistFrameworkInterface;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistFrontendFrameworksManager;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistItemManager;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistTemplateManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Environment;

class PartialTemplateBuilder
{
    /**
     * @var Router
     */
    private $router;
    /**
     * @var WatchlistFrontendFrameworksManager
     */
    private $frameworksManager;
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var WatchlistManager
     */
    private $watchlistManager;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var WatchlistTemplateManager
     */
    private $watchlistTemplateManager;
    /**
     * @var WatchlistItemManager
     */
    private $watchlistItemManager;
    /**
     * @var Translator
     */
    private $translator;

    /**
     * PartialTemplateBuilder constructor.
     */
    public function __construct(ContainerInterface $container, RouterInterface $router, WatchlistFrontendFrameworksManager $frameworksManager, Environment $twig, WatchlistManager $watchlistManager, WatchlistTemplateManager $watchlistTemplateManager, WatchlistItemManager $watchlistItemManager, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->frameworksManager = $frameworksManager;
        $this->twig = $twig;
        $this->watchlistManager = $watchlistManager;
        $this->container = $container;
        $this->watchlistTemplateManager = $watchlistTemplateManager;
        $this->watchlistItemManager = $watchlistItemManager;
        $this->translator = $translator;
    }

    /**
     * Generate the template.
     */
    public function generate(PartialTemplateInterface $template): string
    {
        $template->setBuilder($this);

        return $template->generate();
    }

    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    public function getFrameworksManager(): WatchlistFrontendFrameworksManager
    {
        return $this->frameworksManager;
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }

    public function getWatchlistManager(): WatchlistManager
    {
        return $this->watchlistManager;
    }

    /**
     * Return the current csrf token.
     *
     * @return string
     */
    public function getCsrfToken()
    {
        return $this->container->get('security.csrf.token_manager')->getToken($this->container->getParameter('contao.csrf_token_name'))->getValue();
    }

    /**
     * Return the frontend framework for given watchlist configuration.
     *
     * @return WatchlistFrameworkInterface|null
     */
    public function getFrontendFramework(WatchlistConfigModel $configuration)
    {
        return $this->frameworksManager->getFrameworkByType($configuration->watchlistFrontendFramework);
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function getWatchlistTemplateManager(): WatchlistTemplateManager
    {
        return $this->watchlistTemplateManager;
    }

    public function getWatchlistItemManager(): WatchlistItemManager
    {
        return $this->watchlistItemManager;
    }

    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }
}
