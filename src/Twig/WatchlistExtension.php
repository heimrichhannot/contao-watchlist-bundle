<?php

namespace HeimrichHannot\WatchlistBundle\Twig;

use HeimrichHannot\WatchlistBundle\Watchlist\WatchlistContent;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Runtime\EscaperRuntime;
use Twig\TwigFunction;

class WatchlistExtension extends AbstractExtension
{
    public function __construct(
        private readonly Environment $environment,
    ) {
        $escaperRuntime = $this->environment->getRuntime(EscaperRuntime::class);
        $escaperRuntime->addSafeClass(WatchlistContent::class, ['html', 'contao_html']);
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('watchlist_add_file', [WatchlistRuntime::class, 'watchlistAddFile'], ['is_safe' => ['html']]),
        ];
    }


}