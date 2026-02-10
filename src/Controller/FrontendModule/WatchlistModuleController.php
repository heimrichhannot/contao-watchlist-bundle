<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Environment;
use Contao\ModuleModel;
use HeimrichHannot\EncoreContracts\PageAssetsTrait;
use HeimrichHannot\UtilsBundle\Util\Utils;
use HeimrichHannot\WatchlistBundle\Controller\AjaxController;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use HeimrichHannot\WatchlistBundle\Watchlist\WatchlistContentFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsFrontendModule(WatchlistModuleController::TYPE, category: 'miscellaneous')]
class WatchlistModuleController extends AbstractFrontendModuleController
{
    use PageAssetsTrait;

    public const TYPE = 'watchlist';

    public function __construct(
        protected WatchlistUtil              $watchlistUtil,
        private readonly Utils               $utils,
        private readonly TranslatorInterface $translator,
        private readonly WatchlistContentFactory $watchlistContentFactory,
    ) {}

    public function getResponse(FragmentTemplate $template, ModuleModel $module, Request $request): Response
    {
        if ($this->isBackendScope($request)) {
            return new Response('');
        }

        $this->addPageEntrypoint('contao-watchlist-bundle', [
            'TL_JAVASCRIPT' => [
                'contao-watchlist-bundle' => 'bundles/heimrichhannotwatchlist/assets/contao-watchlist-bundle.js|static',
            ],
        ]);

        $objPage = $this->getPageModel();

        // watchlist
        $config = $this->watchlistUtil->getCurrentWatchlistConfig();
        $watchlist = $this->watchlistUtil->getCurrentWatchlist();

        if (!$config) {
            return $template->getResponse();
        }

        $currentUrl = parse_url((string)Environment::get('uri'), \PHP_URL_PATH);

        $template->set(
            'watchlistUpdateUrl',
            $this->utils->url()->addQueryStringParameterToUrl(
                parameter: 'wl_root_page=' . $objPage->rootId . '&wl_url=' . urlencode($currentUrl),
                url: Environment::get('url') . AjaxController::WATCHLIST_CONTENT_URI
            )
        );

        if (null === $watchlist) {
            $template->set('title', $this->translator->trans('MSC.watchlistBundle.watchlist', [], 'contao_default'));
        } else {
            $template->set('title', $watchlist->title);
        }



//        $contentTemplate = new FrontendTemplate($config->watchlistContentTemplate ?: 'watchlist_content_default');


//        $template->set('watchlistContent', $this->watchlistUtil->parseWatchlistContent(
//            template: $contentTemplate,
//            rootPage: $objPage->rootId,
//            config: $config,
//            watchlist: $watchlist
//        ));

        $template->set('watchlistContent', $this->watchlistContentFactory->build(
            $watchlist ?: $config,
            pageModel: $objPage,
        ));

        return $template->getResponse();
    }
}
