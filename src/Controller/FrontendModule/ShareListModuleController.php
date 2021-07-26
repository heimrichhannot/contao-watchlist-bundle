<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Controller\FrontendModule;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\Template;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Image\ImageUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(ShareListModuleController::TYPE,category="miscellaneous")
 */
class ShareListModuleController extends AbstractFrontendModuleController
{
    const TYPE = 'watchlist_share_list';

    protected ContaoFramework $framework;
    protected DatabaseUtil    $databaseUtil;
    protected WatchlistUtil   $watchlistUtil;
    protected UrlUtil         $urlUtil;
    protected FileUtil        $fileUtil;
    protected ImageUtil       $imageUtil;

    public function __construct(ContaoFramework $framework, DatabaseUtil $databaseUtil, WatchlistUtil $watchlistUtil, UrlUtil $urlUtil, FileUtil $fileUtil, ImageUtil $imageUtil)
    {
        $this->framework = $framework;
        $this->databaseUtil = $databaseUtil;
        $this->watchlistUtil = $watchlistUtil;
        $this->urlUtil = $urlUtil;
        $this->fileUtil = $fileUtil;
        $this->imageUtil = $imageUtil;
    }

    protected function getResponse(Template $template, ModuleModel $module, Request $request): ?Response
    {
        if (!($watchlistUuid = $request->get('watchlist'))) {
            $template->watchlistNotFound = true;

            return $template->getResponse();
        }

        $watchlist = $this->databaseUtil->findOneResultBy('tl_watchlist', [
            'tl_watchlist.uuid=?',
        ], [
            $watchlistUuid,
        ]);

        if ($watchlist->numRows < 1) {
            $template->watchlistNotFound = true;

            return $template->getResponse();
        }

        $config = $this->databaseUtil->findResultByPk('tl_watchlist_config', $watchlist->config);

        $items = [];

        foreach ($this->watchlistUtil->getWatchlistItems($watchlist->id, [
            'modelOptions' => ['order' => 'title ASC'],
        ]) as $item) {
            $item['watchlistConfig'] = $watchlist->config;

            switch ($item['type']) {
                case WatchlistItemContainer::TYPE_FILE:
                    $item['file'] = StringUtil::binToUuid($item['file']);

                    $file = $this->fileUtil->getFileFromUuid($item['file']);

                    if ($file->path) {
                        $item['existing'] = true;

                        $template->hasDownloadableFiles = true;

                        // create the url with file-GET-parameter so that also nonpublic files can be accessed safely
                        $item['downloadUrl'] = $this->framework->getAdapter(Controller::class)->replaceInsertTags('{{download_link::'.$file->path.'}}');

                        // add image if file is such
                        if (\in_array($file->extension, explode(',', Config::get('validImageTypes')))) {
                            $imgSize = $module->imgSize ?: $config->imgSize ?: null;

                            // Override the default image size
                            if ($imgSize) {
                                $size = StringUtil::deserialize($imgSize);

                                if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                                    $item['size'] = $imgSize;
                                }
                            }

                            // force lightbox support
                            $item['fullsize'] = true;

                            $this->imageUtil->addToTemplateData('file', '',
                                $item, $item, null, $watchlistUuid, $watchlistUuid);
                        }
                    } else {
                        $item['existing'] = false;
                    }

                    break;

                case WatchlistItemContainer::TYPE_ENTITY:
                    $existing = $this->databaseUtil->findResultByPk($item['entityTable'], $item['entity']);

                    $item['existing'] = $existing->numRows > 0;

                    break;
            }

            $items[] = $item;
        }

        $template->items = $items;

        return $template->getResponse();
    }
}
