<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Model;
use Contao\ModuleModel;
use Contao\Template;
use HeimrichHannot\UtilsBundle\Util\Utils;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

#[AsFrontendModule(ShareListModuleController::TYPE, category: 'miscellaneous', template: 'frontend_modules/watchlist_share_list')]
class ShareListModuleController extends AbstractFrontendModuleController
{
    const TYPE = 'watchlist_share_list';

    public function __construct(
        protected ContaoFramework $framework,
        protected WatchlistUtil $watchlistUtil,
        private readonly RouterInterface $router,
        private readonly Utils $utils,
        private readonly InsertTagParser $insertTagParser,
    )
    {
    }

    protected function getResponse(Template $template, ModuleModel $module, Request $request): Response
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

        $config = WatchlistConfigModel::findByPk($watchlist->config);

        $items = [];

        foreach ($this->watchlistUtil->getWatchlistItems($watchlist->id, [
            'modelOptions' => ['order' => 'title ASC'],
        ]) as $item) {
            $item['watchlistConfig'] = $watchlist->config;

            switch ($item['type']) {
                case WatchlistItemContainer::TYPE_FILE:
                    $figure = $this->watchlistUtil->addImageToItemData($item, 'file', $item['file'], $config, $watchlist);
                    $item['existing'] = false;
                    if ($figure !== null) {
                        $template->hasDownloadableFiles = true;
                        $item['figure'] = $figure;
                        $item['existing'] = true;
                        $item['downloadUrl'] = $this->insertTagParser->replace('{{download_link::' . $figure->getImage()->getFilePath() . '}}');
                    }


//
//                    $item['file'] = StringUtil::binToUuid($item['file']);
//
//                    $file = $this->fileUtil->getFileFromUuid($item['file']);
//
//                    if ($file->path) {
//                        $item['existing'] = true;
//
//                        $template->hasDownloadableFiles = true;
//
//                        // create the url with file-GET-parameter so that also nonpublic files can be accessed safely
//                        $item['downloadUrl'] = $this->framework->getAdapter(Controller::class)->replaceInsertTags('{{download_link::'.$file->path.'}}');
//
//                        // add image if file is such
//                        $this->watchlistUtil->addImageToItemData($item, 'file', $item['file'], $config, $watchlist);
//                    } else {
//                        $item['existing'] = false;
//                    }

                    break;

                case WatchlistItemContainer::TYPE_ENTITY:
                    $existing = $this->utils->model()->findModelInstanceByPk($item['entityTable'], $item['entity']);

                    $item['existing'] = $existing instanceof Model;

                    $item['figure'] = $this->watchlistUtil->addImageToItemData($item, 'entityFile', $item['entityFile'], $config, $watchlist);

//                    $file = $this->fileUtil->getFileFromUuid($item['entityFile']);
//
//                    if ($file->path) {
//                        // add image if file is such
//                        $this->watchlistUtil->addImageToItemData($item, 'entityFile', $file, $config, $watchlist);
//                    }

                    break;
            }

            $items[] = $item;
        }

        $template->items = $items;

        $template->watchlistDownloadAllUrl = $this->router->generate('huh_watchlist_downlad_all', ['watchlist' => $watchlist->id]);

        return $template->getResponse();
    }
}
