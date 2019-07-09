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
use HeimrichHannot\WatchlistBundle\Manager\WatchlistActionManager;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistFrontendFrameworksManager;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistTemplateManager;
use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateBuilder;
use HeimrichHannot\WatchlistBundle\PartialTemplate\WatchlistWindowPartialTemplate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    private $frontendFrameworkManager;
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
    /**
     * @var PartialTemplateBuilder
     */
    private $templateBuilder;
    /**
     * @var WatchlistActionManager
     */
    private $actionManager;

    public function __construct(ContaoFramework $contaoFramework, WatchlistFrontendFrameworksManager $frameworksManager, WatchlistManager $watchlistManager, WatchlistTemplateManager $templateManager, PartialTemplateBuilder $templateBuilder, WatchlistActionManager $actionManager)
    {
        $this->frontendFrameworkManager = $frameworksManager;
        $this->watchlistManager         = $watchlistManager;
        $this->templateManager          = $templateManager;
        $this->contaoFramework          = $contaoFramework;
        $this->contaoFramework->initialize();
        $this->templateBuilder = $templateBuilder;
        $this->actionManager = $actionManager;
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

        $framework = $this->frontendFrameworkManager->getFrameworkByType($configuration->watchlistFrontendFramework);
        if (!$framework)
        {
            return new Response("No frontend framework for watchlist found.", 404);
        }
        $responseContent = $this->templateBuilder->generate(new WatchlistWindowPartialTemplate($configuration, $watchlistId));

        return new Response($responseContent);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @Route("/addToWatchlist", name="huh_watchlist_add_to_watchlist")
     */
    public function addToWatchlist(Request $request)
    {
        $configuration = WatchlistConfigModel::findByPk($request->get('watchlistConfig'));
        if (!$configuration)
        {
            return new Response("No watchlist configuration could be found.", 404);
        }

        $type = $request->get('type');
        $itemData               = new \stdClass();
        $itemData->options      = $request->get('options');
        $itemData->uuid         = $request->get('fileUuid');
        $itemData->downloadable = $request->get('downloadable');
        $itemData->title        = $request->get('title');

        if (FE_USER_LOGGED_IN)
        {
            list($message, $modal, $count) = $this->templateManager->getWatchlistAddModal($configuration, $type, $itemData);
            return new JsonResponse(['message' => $message, 'watchlistContent' => $modal, 'count' => $count]);
        }

        if (isset($itemData->options) && is_array($itemData->options) && count($itemData->options) > 1) {
            $responseContent = $this->templateManager->getWatchlistItemOptions($configuration, $type, $itemData->options);

            $content = $this->templateBuilder->generate(new WatchlistWindowPartialTemplate($configuration, null, $responseContent));

            return new JsonResponse(['watchlistContent' => $content]);
        }

        if (!isset($itemData->uuid)) {
            return new Response('Missing file identifier', 404);
        }

        $watchlistId = $request->getSession()->get(WatchlistModel::WATCHLIST_SELECT);
        if (null === ($responseData = $this->actionManager->addItemToWatchlist($watchlistId, $type, $itemData)))
        {
            return new Response('Missing file identifier', 404);
        }
        $count = 0;
        if (null !== ($watchlistItems = $this->watchlistManager->getItemsFromWatchlist($watchlistId))) {
            $count = $watchlistItems->count();
        }

        $content = $this->templateBuilder->generate(new WatchlistWindowPartialTemplate($configuration, $watchlistId));

        return new JsonResponse([
            'message' => $responseData,
            'count' => $count,
            'watchlist' => $watchlistId,
            'watchlistContent' => $content
        ]);
    }
}