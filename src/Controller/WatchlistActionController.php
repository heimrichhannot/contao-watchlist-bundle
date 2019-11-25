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
use Contao\FilesModel;
use Contao\Folder;
use Contao\FrontendIndex;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\ZipWriter;
use HeimrichHannot\FilenameSanitizerBundle\Util\FilenameSanitizerUtil;
use HeimrichHannot\WatchlistBundle\Item\DownloadItemInterface;
use HeimrichHannot\WatchlistBundle\Manager\AjaxManager;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistActionManager;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistFrontendFrameworksManager;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistTemplateManager;
use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateBuilder;
use HeimrichHannot\WatchlistBundle\PartialTemplate\WatchlistWindowPartialTemplate;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class WatchlistActionController
 * @package HeimrichHannot\WatchlistBundle\Controller
 *
 * @Route("/contao-watchlist/action", defaults={"_scope" = "frontend", "_token_check" = true})
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
    /**
     * @var string
     */
    private $rootDir;
    private $isInitialized = false;

    public function __construct(ContaoFramework $contaoFramework, WatchlistFrontendFrameworksManager $frameworksManager, WatchlistManager $watchlistManager, WatchlistTemplateManager $templateManager, PartialTemplateBuilder $templateBuilder, WatchlistActionManager $actionManager, string $rootDir)
    {
        $this->frontendFrameworkManager = $frameworksManager;
        $this->watchlistManager         = $watchlistManager;
        $this->templateManager          = $templateManager;
        $this->contaoFramework          = $contaoFramework;
        $this->templateBuilder = $templateBuilder;
        $this->actionManager = $actionManager;
        $this->rootDir = $rootDir;
    }

    public function initializeController()
    {
        if (!$this->isInitialized) {
            $this->contaoFramework->initialize();
            new FrontendIndex(); // initialize BE_USER_LOGGED_IN or FE_USER_LOGGED_IN
            $this->isInitialized = true;
        }

    }

    /**
     * @Route("/open-watchlist-window", name="huh_watchlist_open_watchlist_window")
     */
    public function openWatchlistWindow(Request $request)
    {
        $this->initializeController();
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
     * @Route("/add-to-watchlist", name="huh_watchlist_add_to_watchlist")
     */
    public function addToWatchlist(Request $request)
    {
        $this->initializeController();
        $this->contaoFramework->initialize();
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

        $itemData->pageId        = $request->get('pageId');
        $itemData->ptable        = $request->get('ptable');
        $itemData->ptableId        = $request->get('ptableId');

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

        if (WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE == $type && !isset($itemData->uuid)) {
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

    /**
     * @Route("/download-all", name="huh_watchlist_download_all")
     */
    public function downloadAll(Request $request)
    {
        $this->initializeController();
        $this->contaoFramework->initialize();
        $watchlistId = $request->get('watchlist');
        $configuration = WatchlistConfigModel::findByPk($request->get('watchlistConfig'));
        if (!$configuration)
        {
            return new Response("No watchlist configuration could be found.", 404);
        }
        if (null === ($items = $this->watchlistManager->getItemsFromWatchlist($watchlistId))) {
            return new Response("Empty download list");
        }

        $watchlistName = AjaxManager::XHR_GROUP;

        if ($watchlist = $this->watchlistManager->getWatchlistModel(null, $watchlistId)) {
            $watchlistName = (WatchlistManager::WATCHLIST_SESSION_BE == $watchlist->name
                || WatchlistManager::WATCHLIST_SESSION_FE == $watchlist->name) ? $watchlistName : $watchlist->name;
        }

        if (1 == count($items)) {
            $file = FilesModel::findByUuid($items[0]->uuid);
            if ($file) {
                return $this->file($file->path);
            }
            else {
                return new Response("File not found", 404);
            }
        }

        return $this->file(
            $this->rootDir.$this->createDownloadZipFile($items, $configuration, $watchlistName),
            $watchlistName.'_'.date('Ymd').'.zip',
            ResponseHeaderBag::DISPOSITION_ATTACHMENT
        );
    }

    protected function createDownloadZipFile(Collection $items, WatchlistConfigModel $configuration, string $watchlistName)
    {
        $filesystem = new Filesystem();

        $unique = false;
        while(!$unique) {
            $fileName = '/files/tmp/'.uniqid($watchlistName.'_'.date('Ymd').'_').'.zip';
            $unique = !$filesystem->exists($this->rootDir.$fileName);
        }


        if (!is_dir($this->rootDir.'/web/files/tmp'))
        {
            $folder = new Folder('files/tmp');
            $folder->unprotect();

            try {
                $application = new Application($this->container->get('kernel'));
                $application->setAutoExit(false);

                $input = new ArrayInput([
                    'command' => 'contao:symlinks',
                ]);
                $output = new NullOutput();
                $application->run($input, $output);
            } catch (\Exception $e) {
                throw new \Exception("Could not create temporary folder. Please contact the system admin.");
            }
        }

        $zipWriter = new ZipWriter($fileName);

        foreach ($items as $item) {
            if (!$item->uuid && !$item->parentTable && !$item->parentTableId) {
                continue;
            }

            $class = '';
            switch ($item->type) {
                case WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE:
                    $class = $this->watchlistManager
                        ->getClassByName($configuration->downloadItemFile, WatchlistManager::WATCHLIST_DOWNLOAD_FILE_GROUP);
                    break;
                case WatchlistItemModel::WATCHLIST_ITEM_TYPE_ENTITY:
                    $class = $this->watchlistManager
                        ->getClassByName($configuration->downloadItemEntity, WatchlistManager::WATCHLIST_DOWNLOAD_ENTITY_GROUP);
            }

            if ('' == $class) {
                continue;
            }

            /** @var DownloadItemInterface $downloadItem */
            if (null === ($downloadItem = new $class($item->row()))) {
                continue;
            }

            if (null === ($download = $downloadItem->retrieveItem())) {
                continue;
            }


            $zipEntryFileName = StringUtil::generateAlias($download->getTitle()).'.'.pathinfo($download->getFile(), PATHINFO_EXTENSION);
            $zipWriter->addFile($download->getFile(), $zipEntryFileName);
        }

        $zipWriter->close();
//        chmod($fileName, '644');

        return $fileName;
    }
}