<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\PartialTemplate;

use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;

/**
 * Class WatchlistWindowPartialTemplate.
 *
 * @property PartialTemplateBuilder $builder
 */
class WatchlistWindowPartialTemplate extends AbstractPartialTemplate
{
    /**
     * @var WatchlistConfigModel
     */
    private $configuration;
    /**
     * @var int|null
     */
    private $watchlistId;
    /**
     * @var string|null
     */
    private $content;
    /**
     * @var array
     */
    private $context;

    /**
     * WatchlistWindowPartialTemplate constructor.
     */
    public function __construct(WatchlistConfigModel $configuration, ?int $watchlistId, ?string $content = null, array $context = [])
    {
        $this->configuration = $configuration;
        $this->watchlistId = $watchlistId;
        $this->content = $content;
        $this->context = $context;
    }

    public function getTemplateName(): string
    {
        return static::TEMPLATE_WATCHLIST_WINDOW;
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig_Error_Loader
     */
    public function generate(): string
    {
        $context = [];
        $watchlistModel = $this->builder->getWatchlistManager()->getWatchlistModel($this->configuration, $this->watchlistId);
        if (!$this->content) {
            if (!$watchlistModel) {
                $context['content'] = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];
            } else {
                $watchlistItems = $this->builder->getWatchlistManager()->getCurrentWatchlistItems($this->configuration, $this->watchlistId);
                $context['content'] = $this->builder->getWatchlistTemplateManager()->getWatchlist($this->configuration, $watchlistItems, $watchlistModel->id);
            }
        } else {
            $context['content'] = $this->content;
        }
        $context['headline'] = '<span class="huh_watchlist_window_headline">'.$this->builder->getWatchlistManager()
                ->getWatchlistName($this->configuration, $watchlistModel).'</span>';
        $context['containerSelector'] = '.watchlist-content.watchlist-'.$this->watchlistId;
        $context = $this->builder->getFrontendFramework($this->configuration)->prepareContext($context, $this);

        $template = $this->getTemplate($this->builder->getFrontendFramework($this->configuration));

        return $this->builder->getTwig()->render($template, $context);
    }
}
