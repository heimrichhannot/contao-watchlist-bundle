<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use HeimrichHannot\EncoreContracts\PageAssetsTrait;
use HeimrichHannot\WatchlistBundle\Asset\AssetManager;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer;
use HeimrichHannot\WatchlistBundle\Generator\WatchlistLinkGenerator;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

#[AsHook('replaceInsertTags')]
class ReplaceInsertTagsListener implements ServiceSubscriberInterface
{
    use PageAssetsTrait;

    public function __construct(
        private readonly AssetManager $assetManager,
        private readonly WatchlistLinkGenerator $linkGenerator,
    ) {}

    public function __invoke(
        string $insertTag,
        bool $useCache,
        string $cachedValue,
        array $flags,
        array $tags,
        array $cache,
        int $_rit,
        int $_cnt
    ): false|string {
        if (!\str_starts_with($insertTag, 'watchlist')) {
            return false;
        }

        $this->assetManager->attachAssets();

        $parts = \explode('::', $insertTag);

        return match($parts[0] ?? null) {
            'watchlist_assets' => '',
            'watchlist_add_item_link' => $this->tagAddItemLink($parts),
            default => false,
        };
    }

    private function tagAddItemLink(array $parts): string
    {
        switch ($parts[1])
        {
            case WatchlistItemContainer::TYPE_FILE:
                if (\count($parts) < 3) {
                    throw new \InvalidArgumentException('Invalid number of parameters for watchlist_add_item_link tag.');
                }

                $fileUuid = $parts[2];
                $title = self::filterValue($parts[3] ?? null);
                $watchlistUuid = self::filterValue($parts[4] ?? null);

                return $this->linkGenerator->generateAddFileLink(
                    fileUuid: $fileUuid,
                    title: $title,
                    watchlistUuid: $watchlistUuid
                );

            case WatchlistItemContainer::TYPE_ENTITY:
                if (\count($parts) < 5) {
                    throw new \InvalidArgumentException('Invalid number of parameters for watchlist_add_item_link tag.');
                }

                [
                    2 => $entityTable,
                    3 => $entity,
                    4 => $title,
                ] = $parts;

                $entityUrl = self::filterValue($parts[5] ?? null);
                $entityFile = self::filterValue($parts[6] ?? null);
                $watchlistUuid = self::filterValue($parts[7] ?? null);

                return $this->linkGenerator->generateEntityLink(
                    table: $entityTable,
                    id: $entity,
                    title: $title,
                    entityUrl: $entityUrl,
                    entityFile: $entityFile,
                    watchlistUuid: $watchlistUuid
                );
        }

        return '';
    }

    private static function filterValue(mixed $value): mixed
    {
        if (\is_null($value)) {
            return null;
        }

        if (!\is_string($value)) {
            return $value ?: null;
        }

        if (!$value && \in_array(\strtolower($value), ['null', 'none', '0'])) {
            return null;
        }

        return $value;
    }
}
