<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Input;
use Contao\StringUtil;
use Contao\Validator;
use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\WatchlistBundle\Controller\AjaxController;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use Twig\Environment;

/**
 * @Hook("replaceInsertTags")
 */
class ReplaceInsertTagsListener
{
    protected Environment         $twig;
    protected WatchlistUtil       $watchlistUtil;
    protected TwigTemplateLocator $twigTemplateLocator;
    protected ModelUtil           $modelUtil;
    protected DatabaseUtil        $databaseUtil;

    public function __construct(
        Environment $twig,
        WatchlistUtil $watchlistUtil,
        TwigTemplateLocator $twigTemplateLocator,
        ModelUtil $modelUtil,
        DatabaseUtil $databaseUtil
    ) {
        $this->twig = $twig;
        $this->watchlistUtil = $watchlistUtil;
        $this->twigTemplateLocator = $twigTemplateLocator;
        $this->modelUtil = $modelUtil;
        $this->databaseUtil = $databaseUtil;
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

        switch ($parts[0]) {
            case 'watchlist_add_item_link':
                $watchlist = $this->watchlistUtil->getCurrentWatchlist();

                $postData = [
                    'page' => $objPage->id,
                    'rootPage' => $objPage->rootId,
                ];

                // caution: do not use Input because then it would be marked as "used" erroneously
                if ($_GET['auto_item']) {
                    $postData['autoItem'] = $_GET['auto_item'];
                }

                switch ($parts[1]) {
                    case WatchlistItemContainer::TYPE_FILE:
                        $fileUuid = Validator::isBinaryUuid($parts[2]) ? StringUtil::binToUuid($parts[2]) : $parts[2];
                        $title = $parts[3] ?? null;
                        $watchlistUuid = $parts[4] ?? null;

                        // file not existing?
                        if (null === $this->modelUtil->callModelMethod('tl_files', 'findByUuid', $fileUuid)) {
                            return '';
                        }

                        $postData['type'] = WatchlistItemContainer::TYPE_FILE;
                        $postData['file'] = $fileUuid;

                        if ($title) {
                            $postData['title'] = $title;
                        }

                        if ($watchlistUuid) {
                            $postData['pid'] = $watchlistUuid;
                        }

                        $data = [
                            'href' => \Contao\Environment::get('url').AjaxController::WATCHLIST_ITEM_URI,
                            'isAdded' => null !== $watchlist && null !== $this->watchlistUtil->getItemInWatchlist($postData, $watchlist->id),
                            'postData' => $postData,
                        ];

                        $config = $this->watchlistUtil->getCurrentWatchlistConfig();

                        return $this->twig->render(
                            $this->twigTemplateLocator->getTemplatePath(
                                $config->insertTagAddItemTemplate ?: '_watchlist_insert_tag_add_item_default.html.twig'
                            ),
                            $data
                        );

                    case WatchlistItemContainer::TYPE_ENTITY:
                        $entityTable = $parts[2];
                        $entity = $parts[3];
                        $title = $parts[4];
                        $watchlistUuid = $parts[5] ?? null;

                        // entity not existing?
                        $existing = $this->databaseUtil->findResultByPk($entityTable, $entity);

                        if (null === $existing || $existing->numRows < 1) {
                            return '';
                        }

                        $postData['type'] = WatchlistItemContainer::TYPE_ENTITY;
                        $postData['entityTable'] = $entityTable;
                        $postData['entity'] = $entity;
                        $postData['title'] = $title;

                        if ($watchlistUuid) {
                            $postData['pid'] = $watchlistUuid;
                        }

                        $data = [
                            'href' => \Contao\Environment::get('url').AjaxController::WATCHLIST_ITEM_URI,
                            'isAdded' => null !== $watchlist && null !== $this->watchlistUtil->getItemInWatchlist($postData, $watchlist->id),
                            'postData' => $postData,
                        ];

                        $config = $this->watchlistUtil->getCurrentWatchlistConfig();

                        return $this->twig->render(
                            $this->twigTemplateLocator->getTemplatePath(
                                $config->insertTagAddItemTemplate ?: '_watchlist_insert_tag_add_item_default.html.twig'
                            ),
                            $data
                        );

                    default:
                        return '';
                }
        }

        return false;
    }
}
