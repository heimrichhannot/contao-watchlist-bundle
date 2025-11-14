<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\Template;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Image\ImageUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

#[AsFrontendModule(ShareListModuleController::TYPE, category: 'miscellaneous')]
class ShareListModuleController extends AbstractFrontendModuleController
{
    const TYPE = 'watchlist_share_list';

    protected ContaoFramework $framework;
    protected DatabaseUtil    $databaseUtil;
    protected WatchlistUtil   $watchlistUtil;
    protected UrlUtil         $urlUtil;
    protected FileUtil        $fileUtil;
    protected ImageUtil       $imageUtil;
    protected ModelUtil       $modelUtil;
    private RouterInterface   $router;

    public function __construct(
        ContaoFramework $framework,
        DatabaseUtil $databaseUtil,
        WatchlistUtil $watchlistUtil,
        UrlUtil $urlUtil,
        FileUtil $fileUtil,
        ImageUtil $imageUtil,
        ModelUtil $modelUtil,
        RouterInterface $router
    ) {
        $this->framework = $framework;
        $this->databaseUtil = $databaseUtil;
        $this->watchlistUtil = $watchlistUtil;
        $this->urlUtil = $urlUtil;
        $this->fileUtil = $fileUtil;
        $this->imageUtil = $imageUtil;
        $this->modelUtil = $modelUtil;
        $this->router = $router;
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

        $config = $this->modelUtil->findModelInstanceByPk('tl_watchlist_config', $watchlist->config);

        $items = [];

        $watchlist = $this->modelUtil->findModelInstanceByPk('tl_watchlist', $watchlist->id);

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
                        $this->watchlistUtil->addImageToItemData($item, 'file', $file, $config, $watchlist);
                    } else {
                        $item['existing'] = false;
                    }

                    break;

                case WatchlistItemContainer::TYPE_ENTITY:
                    $existing = $this->databaseUtil->findResultByPk($item['entityTable'], $item['entity']);

                    $item['existing'] = $existing->numRows > 0;

                    $file = $this->fileUtil->getFileFromUuid($item['entityFile']);

                    if ($file->path) {
                        // add image if file is such
                        $this->watchlistUtil->addImageToItemData($item, 'entityFile', $file, $config, $watchlist);
                    }

                    break;
            }

            $items[] = $item;
        }

        $template->items = $items;

        $template->watchlistDownloadAllUrl = $this->router->generate('huh_watchlist_downlad_all', ['watchlist' => $watchlist->id]);

        return $template->getResponse();
    }
}
