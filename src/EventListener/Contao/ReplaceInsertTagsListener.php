<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\EventListener\Contao;

use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer;
use HeimrichHannot\WatchlistBundle\Generator\WatchlistLinkGenerator;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use Twig\Environment;

/**
 * Hook("replaceInsertTags").
 */
class ReplaceInsertTagsListener
{
    /** @var Environment */
    protected $twig;
    /** @var WatchlistUtil */
    protected $watchlistUtil;
    /** @var TwigTemplateLocator */
    protected $twigTemplateLocator;
    /** @var ModelUtil */
    protected $modelUtil;
    /** @var DatabaseUtil */
    protected $databaseUtil;
    private WatchlistLinkGenerator $linkGenerator;

    public function __construct(
        Environment $twig,
        WatchlistUtil $watchlistUtil,
        TwigTemplateLocator $twigTemplateLocator,
        ModelUtil $modelUtil,
        DatabaseUtil $databaseUtil,
        WatchlistLinkGenerator $linkGenerator
    ) {
        $this->twig = $twig;
        $this->watchlistUtil = $watchlistUtil;
        $this->twigTemplateLocator = $twigTemplateLocator;
        $this->modelUtil = $modelUtil;
        $this->databaseUtil = $databaseUtil;
        $this->linkGenerator = $linkGenerator;
    }

    public function __invoke(
        string $insertTag,
        bool $useCache,
        string $cachedValue,
        array $flags,
        array $tags,
        array $cache,
        int $_rit,
        int $_cnt
    ) {
        $parts = explode('::', $insertTag);

        global $objPage;

        $nullValues = ['null', 'NULL', "''", '0'];

        switch ($parts[0]) {
            case 'watchlist_add_item_link':
                switch ($parts[1]) {
                    case WatchlistItemContainer::TYPE_FILE:
                        $title = null;

                        if (isset($parts[3]) && !\in_array($parts[3], $nullValues)) {
                            $title = $parts[3];
                        }

                        $watchlistUuid = null;

                        if (isset($parts[4]) && !\in_array($parts[4], $nullValues)) {
                            $watchlistUuid = $parts[4];
                        }

                        return $this->linkGenerator->generateAddFileLink($parts[2], $title, $watchlistUuid);

                    case WatchlistItemContainer::TYPE_ENTITY:
                        $entityTable = $parts[2];
                        $entity = $parts[3];
                        $title = $parts[4];
                        $entityUrl = $parts[5] ?? null;
                        $entityFile = $parts[6] ?? null;
                        $watchlistUuid = $parts[7] ?? null;

                        if ($entityUrl && \in_array($entityUrl, $nullValues)) {
                            $entityUrl = null;
                        }

                        if ($entityFile && \in_array($entityFile, $nullValues)) {
                            $entityFile = null;
                        }

                        if ($watchlistUuid && \in_array($watchlistUuid, $nullValues)) {
                            $watchlistUuid = null;
                        }

                        return $this->linkGenerator->generateEntityLink($entityTable, $entity, $title, $entityUrl, $entityFile, $watchlistUuid);

                    default:
                        return '';
                }
        }

        return false;
    }
}
