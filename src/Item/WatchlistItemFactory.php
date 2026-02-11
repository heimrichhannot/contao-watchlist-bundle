<?php

namespace HeimrichHannot\WatchlistBundle\Item;

use Contao\CoreBundle\Filesystem\FileDownloadHelper;
use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\Model\Collection;
use Contao\PageModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\Routing\PageFinder;
use Symfony\Component\HttpFoundation\RequestStack;

class WatchlistItemFactory
{
    public function __construct(
        private readonly VirtualFilesystemInterface $filesStorage,
        private readonly Studio $studio,
        private readonly FileDownloadHelper $fileDownloadHelper,
        private readonly RequestStack $requestStack,
        private readonly PageFinder $pageFinder,
        private readonly ContentUrlGenerator $contentUrlGenerator,

    )
    {
    }

    public function build(int|WatchlistItemModel $instance): WatchlistItem
    {
        if (is_int($instance)) {
            $instance = WatchlistItemModel::findByPk($instance);
        }

        if (!($instance instanceof WatchlistItemModel)) {
            throw new \RuntimeException(sprintf('Could not find watchlist item with id %s', $instance));
        }

        $url = $this->getUrl($instance);
        $helper = $this->fileDownloadHelper;

        return new WatchlistItem(
            $instance,
            $this->filesStorage,
            $this->studio,
            function(WatchlistItem $item) use ($url, $helper) {
                    return $helper->generateDownloadUrl(
                        $url,
                        $item->getFile(),
                        context: ['watchlist' => $item->getModel()->pid]
                    );
            }
        );
    }

    /**
     * @param Collection<WatchlistItemModel>|null $collection
     * @return array<WatchlistItem>
     */
    public function buildForCollection(Collection|null $collection = null): array
    {
        if (null === $collection) {
            return [];
        }

        $items = [];
        foreach ($collection as $item) {
            $items[] = $this->build($item);
        }

        return $items;
    }

    private function getUrl(WatchlistItemModel $item): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request) {
            $wlUrl = $request->query->get('wl_url');
            if (is_string($wlUrl)) {
                if (str_starts_with($wlUrl, 'http')) {
                    return $wlUrl;
                }
                if (!str_starts_with($wlUrl, '/')) {
                    $wlUrl = '/'.$wlUrl;
                }
                return $request->getSchemeAndHttpHost().$wlUrl;
            }

            return $request->getUri();
        }

        $watchlist = WatchlistModel::findByPk($item->pid);
        $config = WatchlistConfigModel::findByPk($watchlist->config ?: 0);
        if (!$config) {
            throw new \RuntimeException(sprintf('Could not find watchlist config with id %s', $watchlist->pid));
        }
        $page = $this->pageFinder->findByConfig($config, 1);
        if (!$page) {
            throw new \RuntimeException(sprintf('Could not find page for watchlist config with id %s', $config->id));
        }
        return $this->contentUrlGenerator->generate($page);
    }
}