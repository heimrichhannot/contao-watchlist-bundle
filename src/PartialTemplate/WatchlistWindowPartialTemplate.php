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


use HeimrichHannot\WatchlistBundle\FrontendFramework\AbstractWatchlistFrontendFramework;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;

/**
 * Class WatchlistWindowPartialTemplate
 * @package HeimrichHannot\WatchlistBundle\PartialTemplate
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
     * WatchlistWindowPartialTemplate constructor.
     * @param WatchlistConfigModel $configuration
     * @param int|null $watchlistId
     */
    public function __construct(WatchlistConfigModel $configuration, ?int $watchlistId)
    {
        $this->configuration = $configuration;
        $this->watchlistId = $watchlistId;
    }

    public function getTemplateType(): string
    {
        return static::TEMPLATE_WATCHLIST_WINDOW;
    }


    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig_Error_Loader
     */
    public function generate(): string
    {
        $context = [];
        $watchlistModel = $this->builder->getWatchlistManager()->getWatchlistModel($this->configuration, $this->watchlistId);
        if (!$watchlistModel)
        {
            $context['content'] = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];
        }
        else {
            $watchlistItems = $this->builder->getWatchlistManager()->getCurrentWatchlistItems($this->configuration, $this->watchlistId);
            $context['content'] = $this->builder->getWatchlistTemplateManager()->getWatchlist($this->configuration, $watchlistItems, $watchlistModel->id);
        }

        $context['headline'] = $this->builder->getWatchlistManager()->getWatchlistName($this->configuration, $watchlistModel);
        $context = $this->builder->getFrontendFramework($this->configuration)->compile($context);

        $template = $this->getTemplate($this->builder->getFrontendFramework($this->configuration));
        return $this->builder->getTwig()->render($template, $context);
    }
}