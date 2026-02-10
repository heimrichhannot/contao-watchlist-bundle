<?php

namespace HeimrichHannot\WatchlistBundle\Twig;

use HeimrichHannot\WatchlistBundle\Watchlist\WatchlistContent;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Runtime\EscaperRuntime;

class WatchlistExtension extends AbstractExtension
{
    public function __construct(
        private readonly Environment $environment,
    ) {
        $escaperRuntime = $this->environment->getRuntime(EscaperRuntime::class);
        $escaperRuntime->addSafeClass(WatchlistContent::class, ['html', 'contao_html']);
    }
}