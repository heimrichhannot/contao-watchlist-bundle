<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Filesystem\FileDownloadHelper;
use Contao\CoreBundle\Filesystem\FilesystemItem;
use Contao\CoreBundle\Filesystem\FilesystemItemIterator;
use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Contao\Template;
use HeimrichHannot\WatchlistBundle\Item\WatchlistItem;
use HeimrichHannot\WatchlistBundle\Item\WatchlistItemFactory;
use HeimrichHannot\WatchlistBundle\Item\WatchlistItemType;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use HeimrichHannot\WatchlistBundle\Watchlist\WatchlistContentFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\RouterInterface;

#[AsFrontendModule(ShareListModuleController::TYPE, category: 'miscellaneous', template: 'frontend_module/watchlist_share_list')]
class ShareListModuleController extends AbstractFrontendModuleController
{
    const TYPE = 'watchlist_share_list';

    public function __construct(
        protected ContaoFramework $framework,
        protected WatchlistUtil $watchlistUtil,
        private readonly RouterInterface $router,
        private readonly WatchlistItemFactory $watchlistItemFactory,
        private readonly FileDownloadHelper $downloadHelper,
        private readonly VirtualFilesystemInterface $filesStorage,
        private readonly WatchlistContentFactory $watchlistContentFactory,
    )
    {
    }

    public function __invoke(Request $request, ModuleModel $model, string $section, ?array $classes = null): Response
    {
        $this->handleDownload($request);
        return parent::__invoke($request, $model, $section, $classes);
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        if (!($watchlistUuid = $request->get('watchlist'))) {
            $template->watchlistNotFound = true;

            return $template->getResponse();
        }

        $watchlist = WatchlistModel::findByUuid($watchlistUuid);

        if (!$watchlist) {
            $template->watchlistNotFound = true;
            return $template->getResponse();
        }

        $watchlistItemModels = $this->watchlistUtil->getWatchlistItems(
            $watchlist->id,
            ['modelOptions' => ['order' => 'title ASC'],],
        );
        $items = $this->watchlistItemFactory->buildForCollection($watchlistItemModels);
        $template->set('items', $items);



        foreach ($items as $item) {
            if (WatchlistItemType::FILE === $item->getType() && $item->fileExist()) {
                $template->hasDownloadableFiles = true;
                $template->set('has_downloads', true);

                $downloadAll = $this->router->generate('huh_watchlist_downlad_all', ['watchlist' => $watchlist->id]);
                $template->watchlistDownloadAllUrl = $downloadAll;
                $template->set('download_all', $downloadAll);
                break;
            }
        }

        return $template->getResponse();
    }

    protected function getFilesystemItems(WatchlistModel $watchlist): FilesystemItemIterator
    {
        $content =  $this->watchlistContentFactory->build(
            $watchlist,
            pageModel: $this->getPageModel(),
        );

        $fileItems = array_map(fn(WatchlistItem $item) => $item->getFile(), $content->items);
        $fileItems = array_values(array_filter($fileItems));
        return new FilesystemItemIterator($fileItems);
    }

    protected function handleDownload(Request $request): void
    {
        $response = $this->downloadHelper->handle(
            $request,
            $this->filesStorage,
            function (FilesystemItem $item, array $context): Response|null {
                $watchlist = WatchlistModel::findByPk($context['watchlist'] ?? 0);
                if (null === $watchlist) {
                    return new Response('', Response::HTTP_NO_CONTENT);
                }

                if (!$this->getFilesystemItems($watchlist)->any(static fn (FilesystemItem $listItem) => $listItem->getPath() === $item->getPath())) {
                    return new Response('The resource can not be accessed anymore.', Response::HTTP_GONE);
                }

                return null;
            },
        );

        if ($response instanceof StreamedResponse || $response instanceof BinaryFileResponse) {
            throw new ResponseException($response);
        }
    }
}
