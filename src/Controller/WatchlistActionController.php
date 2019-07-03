<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\WatchlistBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistFrontendFrameworksManager;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistTemplateManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class WatchlistActionController
 * @package HeimrichHannot\WatchlistBundle\Controller
 *
 * @Route("/contao-watchlist/action")
 */
class WatchlistActionController extends AbstractController
{
    /**
     * @var WatchlistFrontendFrameworksManager
     */
    private $frontendFrameworksManager;
    /**
     * @var WatchlistManager
     */
    private $watchlistManager;
    /**
     * @var WatchlistTemplateManager
     */
    private $templateManager;
    /**
     * @var ContaoFramework
     */
    private $contaoFramework;

    public function __construct(ContaoFramework $contaoFramework, WatchlistFrontendFrameworksManager $frameworksManager, WatchlistManager $watchlistManager, WatchlistTemplateManager $templateManager)
    {
        $this->frontendFrameworksManager = $frameworksManager;
        $this->watchlistManager = $watchlistManager;
        $this->templateManager = $templateManager;
        $this->contaoFramework = $contaoFramework;
        $this->contaoFramework->initialize();
    }

    /**
     * @Route("/openWatchlistWindow", name="huh_watchlist_open_watchlist_window")
     */
    public function openWatchlistWindow(Request $request)
    {
        $configuration = WatchlistConfigModel::findByPk($request->get('watchlistConfig'));
        $watchlistId = $request->get('watchlist');
        if (!$configuration)
        {
            return new Response("No watchlist configuration could be found.", 404);
        }

        $framework = $this->frontendFrameworksManager->getFrameworkByType('base');
        if (!$framework)
        {
            return new Response("No frontend framework for watchlist found.", 404);
        }
        $context = [];
        $watchlistModel = $this->watchlistManager->getWatchlistModel($configuration, $request->get('watchlist'));
        if (!$watchlistModel)
        {
            $context['content'] = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];
        }
        else {
            $watchlistItems = $this->watchlistManager->getCurrentWatchlistItems($configuration, $watchlistId);
            $context['content'] = $this->templateManager->getWatchlist($configuration, $watchlistItems, $watchlistModel->id);
        }

        $context['headline'] = $this->watchlistManager->getWatchlistName($configuration, $watchlistModel);
        $context = $framework->compile($context);
        $responseContent = $this->container->get('twig')->render($framework->getWindowTemplate(), $context);

        return new Response($responseContent);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Route("addToWatchlist", name="huh_watchlist_add_to_watchlist")
     */
    public function addToWatchlist(Request $request)
    {
        $configuration = WatchlistConfigModel::findByPk($request->get('watchlistConfig'));
        if (!$configuration)
        {
            return new Response("No watchlist configuration could be found.", 404);
        }
//
//
//
//        $data     = json_decode($data);
//        $moduleId = $data->moduleId;
//        $type     = $data->type;
//        $itemData = $data->itemData;
//
//        if (FE_USER_LOGGED_IN) {
//            return $this->watchlistShowModalAddAction($moduleId, $type, $itemData);
//        }
//
//        if (isset($itemData->options) && is_array($itemData->options) && count($itemData->options) > 1) {
//            $responseContent = $this->watchlistTemplate->getWatchlistItemOptions($moduleId, $type, $itemData->options);
//
//            return $this->getModalResponse($responseContent);
//        }
//
//        if (!isset($itemData->uuid)) {
//            return new ResponseError();
//        }
//
//        return $this->addItemToWatchlist($this->container->get('session')->get(WatchlistModel::WATCHLIST_SELECT), $type,
//            $itemData);
    }
}