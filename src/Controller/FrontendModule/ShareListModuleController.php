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
use Contao\ModuleModel;
use Contao\Template;
use HeimrichHannot\WatchlistBundle\Item\WatchlistItemFactory;
use HeimrichHannot\WatchlistBundle\Item\WatchlistItemType;
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
        private readonly WatchlistItemFactory $watchlistItemFactory,
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

        $items = [];

        $watchlistItemModels = $this->watchlistUtil->getWatchlistItems(
            $watchlist->id,
            ['modelOptions' => ['order' => 'title ASC'],],
        );
        foreach ($watchlistItemModels as $model) {
            $item = $model->row();
            $wlItem = $this->watchlistItemFactory->build($model);
            $item = $wlItem->applyToTemplateData($item);

            if (WatchlistItemType::FILE === $wlItem->getType() && $wlItem->fileExist()) {
                $template->hasDownloadableFiles = true;
            }

            $item['watchlistConfig'] = $watchlist->config;

            $items[] = $item;
        }

        $template->items = $items;

        $template->watchlistDownloadAllUrl = $this->router->generate('huh_watchlist_downlad_all', ['watchlist' => $watchlist->id]);

        return $template->getResponse();
    }
}
