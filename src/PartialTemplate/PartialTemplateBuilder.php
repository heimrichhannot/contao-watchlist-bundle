<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\WatchlistBundle\PartialTemplate;


use HeimrichHannot\WatchlistBundle\FrontendFramework\WatchlistFrameworkInterface;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistFrontendFrameworksManager;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;
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
     * PartialTemplateBuilder constructor.
     */
    public function __construct(ContainerInterface $container, Router $router, WatchlistFrontendFrameworksManager $frameworksManager, Environment $twig, WatchlistManager $watchlistManager)
    {
        $this->router = $router;
        $this->frameworksManager = $frameworksManager;
        $this->twig = $twig;
        $this->watchlistManager = $watchlistManager;
        $this->container = $container;
    }

    /**
     * Generate the template
     *
     * @param PartialTemplateInterface $template
     * @return string
     */
    public function generate(PartialTemplateInterface $template): string
    {
        $template->setBuilder($this);
        return $template->generate();
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * @return WatchlistFrontendFrameworksManager
     */
    public function getFrameworksManager(): WatchlistFrontendFrameworksManager
    {
        return $this->frameworksManager;
    }

    /**
     * @return Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * @return WatchlistManager
     */
    public function getWatchlistManager(): WatchlistManager
    {
        return $this->watchlistManager;
    }

    /**
     * Return the current csrf token
     *
     * @return string
     */
    public function getCsrfToken()
    {
        return $this->container->get('security.csrf.token_manager')->getToken($this->container->getParameter('contao.csrf_token_name'))->getValue();
    }

    /**
     * Return the frontend framework for given watchlist configuration
     *
     * @param WatchlistConfigModel $configuration
     * @return WatchlistFrameworkInterface|null
     */
    public function getFrontendFramework(WatchlistConfigModel $configuration)
    {
        return $this->frameworksManager->getFrameworkByType($configuration->watchlistFrontendFramework);
    }
}