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

class OpenWatchlistWindowPartialTemplate extends AbstractPartialTemplate
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

    public function getTemplateType(): string
    {
        return static::TEMPLATE_OPEN_WATCHLIST_WINDOW;
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

        $attributes = $this->createDefaultActionAttributes($this->configuration, $url, static::ACTION_TYPE_TOGGLE);
        $attributes['watchlistContainer'] = $this->watchlistContainerCssId;
        $attributes['watchlist'] = $this->watchlist->id;

        $template = $this->getTemplate($frontendFramework);
        $context = $this->createDefaultActionContext($attributes, $this->watchlist);
        $context['itemCount'] = $count;
        $context['content'] = $GLOBALS['TL_LANG']['WATCHLIST']['toggleLink'];

        return $this->builder->getTwig()->render($template, $context);
    }
}