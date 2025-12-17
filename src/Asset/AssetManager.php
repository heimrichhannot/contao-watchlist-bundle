<?php

namespace HeimrichHannot\WatchlistBundle\Asset;

use HeimrichHannot\EncoreContracts\PageAssetsTrait;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class AssetManager implements ServiceSubscriberInterface
{
    use PageAssetsTrait;

    public function attachAssets(): void
    {
        $this->addPageEntrypoint('contao-watchlist-bundle', [
            'TL_JAVASCRIPT' => [
                'contao-watchlist-bundle' => 'bundles/heimrichhannotwatchlistbundle/assets/contao-watchlist-bundle.js|static',
            ],
        ]);
    }
}