<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\WatchlistBundle\PartialTemplate;


use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;

class WatchlistWindowPartialTemplate extends AbstractPartialTemplate
{
    /**
     * @var WatchlistConfigModel
     */
    private $configuration;
    /**
     * @var WatchlistModel
     */
    private $watchlist;
    /**
     * @var string
     */
    private $watchlistContainerCssId;

    public function __construct(WatchlistConfigModel $configuration, WatchlistModel $watchlist, string $watchlistContainerCssId)
    {
        $this->configuration = $configuration;
        $this->watchlist = $watchlist;
        $this->watchlistContainerCssId = $watchlistContainerCssId;
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function generate(): string
    {
        $url = $this->builder->getRouter()->generate('huh_watchlist_open_watchlist_window');
        $frontendFramework = $this->builder->getFrontendFramework($this->configuration);

        $count = 0;

        if (null !== ($watchlistItems = $this->builder->getWatchlistManager()->getItemsFromWatchlist($this->watchlist->id))) {
            $count = $watchlistItems->count();
        }

        $dataAttributes = '';

        $attributes = [
            'action-url' => $url,
            'watchlist-config' => $this->configuration->id,
            'watchlist' => $this->watchlist->id,
            'frontend' => $frontendFramework->getType(),
            'request-token' => $this->builder->getCsrfToken(),
            'watchlist-container' => $this->watchlistContainerCssId,
        ];

        array_walk($attributes, function($value, $key) use (&$dataAttributes) {
            $dataAttributes .= "data-$key=$value ";
        });

        return $this->builder->getTwig()->render($frontendFramework->getActionTemplate(), [
            'dataAttributes' => $dataAttributes,
            'cssClass' => 'huh_watchlist_element',
            'itemCount' => $count,
            'content' => $GLOBALS['TL_LANG']['WATCHLIST']['toggleLink'],
        ]);
    }
}