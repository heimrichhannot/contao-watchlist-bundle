<?php

namespace HeimrichHannot\WatchlistBundle\Twig;

use Contao\CoreBundle\Filesystem\FilesystemItem;
use Contao\FilesModel;
use HeimrichHannot\WatchlistBundle\Generator\WatchlistLinkGenerator;
use Symfony\Component\Uid\Uuid;
use Twig\Extension\RuntimeExtensionInterface;

class WatchlistRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly WatchlistLinkGenerator $watchlistLinkGenerator
    ) {}

    public function watchlistAddFile(FilesModel|string|Uuid|FilesystemItem $file, ?string $title = null, ?string $watchlistUuid = null): string
    {
        if ($file instanceof FilesystemItem) {
            $file = $file->getUuid();
        }

        return $this->watchlistLinkGenerator->generateAddFileLink($file, $title, $watchlistUuid);
    }
}