<?php

namespace HeimrichHannot\WatchlistBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\ModuleModel;
use Contao\Template;
use HeimrichHannot\EncoreContracts\PageAssetsTrait;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use HeimrichHannot\WatchlistBundle\Asset\AssetManager;
use HeimrichHannot\WatchlistBundle\Controller\AjaxController;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

#[AsFrontendModule(type: WatchlistModuleController::TYPE, category: 'miscellaneous')]
class WatchlistModuleController extends AbstractFrontendModuleController implements ServiceSubscriberInterface
{
    use PageAssetsTrait;

    public const TYPE = 'watchlist';

    public function __construct(
        private readonly AssetManager $assetManager,
        private readonly UrlUtil $urlUtil,
        private readonly Utils $utils,
        private readonly WatchlistUtil $watchlistUtil,
    ) {}

    public function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        $this->assetManager->attachAssets();

        global $objPage;

        // watchlist
        $config = $this->watchlistUtil->getCurrentWatchlistConfig();
        $watchlist = $this->watchlistUtil->getCurrentWatchlist();

        if (!$config) {
            return null;
        }

        $currentUrl = parse_url(Environment::get('uri'), \PHP_URL_PATH);

        $template->watchlistUpdateUrl = $this->utils->url()->addQueryStringParameterToUrl(
            parameter: \sprintf('wl_root_page=%s&wl_url=%s', $objPage->rootId, \urlencode($currentUrl)),
            url: Environment::get('url') . AjaxController::WATCHLIST_CONTENT_URI
        );

        if (null === $watchlist) {
            $template->title = $GLOBALS['TL_LANG']['MSC']['watchlistBundle']['watchlist'] ?? 'Watchlist';
        } else {
            $template->title = $watchlist->title;
        }

        $contentTemplate = new FrontendTemplate($config->watchlistContentTemplate ?: 'watchlist_content_default');

        $template->watchlistContent = $this->watchlistUtil->parseWatchlistContent(
            template: $contentTemplate,
            currentUrl: $currentUrl,
            rootPage: $objPage->rootId,
            config: $config,
            watchlist: $watchlist
        );

        return null;
    }
}
