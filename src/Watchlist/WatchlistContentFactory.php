<?php

namespace HeimrichHannot\WatchlistBundle\Watchlist;

use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Routing\PageFinder;
use Contao\FrontendTemplate;
use Contao\PageModel;
use HeimrichHannot\WatchlistBundle\Item\WatchlistItemFactory;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\Routing\PageFinder as WatchlistPageFinder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class WatchlistContentFactory
{
    public function __construct(
        private readonly InsertTagParser          $insertTagParser,
        private readonly RequestStack             $requestStack,
        private readonly PageFinder               $pageFinder,
        private readonly RouterInterface          $router,
        private readonly ContentUrlGenerator      $contentUrlGenerator,
        private readonly WatchlistItemFactory     $itemFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly WatchlistPageFinder      $wlPageFinder,
    ) {}

    public function build(
        WatchlistModel|WatchlistConfigModel $watchlistModel,
        ?PageModel                          $pageModel = null,
        ?FrontendTemplate                   $legacyTemplate = null,
    ): WatchlistContent
    {
        if ($watchlistModel instanceof WatchlistConfigModel) {
            $config = $watchlistModel;
            $watchlistModel = null;
        } else {
            $config = WatchlistConfigModel::findByPk($watchlistModel->config);
        }
        if (null === $config) {
            throw new \RuntimeException(sprintf('Could not find watchlist config with id %s', $watchlistModel->config));
        }

        if (null === $legacyTemplate) {
            $legacyTemplate = new FrontendTemplate($config->watchlistContentTemplate ?: 'watchlist_content_default');
        }

        $rootPage = $this->getRootPageModel($pageModel, $config);
        $sharePage = $config->addShare ? PageModel::findByPk($config->shareJumpTo) : null;

        $downloadAllUrl = $rootPage
            ? $this->router->generate(
                'huh_watchlist_downlad_all',
                ['wl_root_page' => $rootPage->id]
            )
            : null;
        return new WatchlistContent(
            config: $config,
            items: $watchlistModel === null ? [] : $this->fetchItems($watchlistModel),
            insertTagParser: $this->insertTagParser,
            eventDispatcher: $this->eventDispatcher,
            watchlistUrl: $rootPage ? $this->router->generate('huh_watchlist', ['wl_root_page' => $rootPage->id]) : null,
            itemUrl: $rootPage ? $this->router->generate('huh_watchlist_item') : null,
            downloadAllUrl: $downloadAllUrl,
            legacyTemplate: $legacyTemplate,
            shareUrl: ($sharePage && $watchlistModel) ? $this->contentUrlGenerator->generate($sharePage, ['watchlist' => $watchlistModel->uuid]) : null,
            rootPage: $rootPage,
        );
    }

    private function fetchItems(WatchlistModel $watchlist): array
    {
        $watchlistItemModels = WatchlistItemModel::findBy(
            ['tl_watchlist_item.pid=?'],
            [$watchlist->id],
            ['order' => 'dateAdded DESC']
        );

        return $this->itemFactory->buildForCollection($watchlistItemModels);
    }

    private function getRootPageModel(?PageModel $pageModel, WatchlistConfigModel $config): ?PageModel
    {
        if (null !== $pageModel) {
            if ($pageModel->type === 'root') {
                return $pageModel;
            }
            return PageModel::findPublishedById($pageModel->loadDetails()->rootId);
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return $this->wlPageFinder->findByConfig($config, 1);
        }

        return $this->pageFinder->findRootPageForRequest($request);
    }
}