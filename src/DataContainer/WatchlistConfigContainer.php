<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\CoreBundle\Twig\Finder\FinderFactory;
use Contao\DataContainer;
use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;

class WatchlistConfigContainer
{

    public function __construct(
        protected readonly ContaoFramework $framework,
        private readonly FinderFactory $finderFactory,
    )
    {
    }

    /**
     * @Callback(table="tl_watchlist_config", target="fields.insertTagAddItemTemplate.options")
     */
    public function getInsertTagAddItemTemplates(DataContainer $dc)
    {
        return $this->finderFactory->create()->identifier('insert_tag/watchlist_add_item')
            ->extension('html.twig')
            ->withVariants()
            ->asTemplateOptions();
    }

    /**
     * @Callback(table="tl_watchlist_config", target="fields.watchlistContentTemplate.options")
     */
    public function getWatchlistContentTemplates(DataContainer $dc)
    {
        return $this->framework->getAdapter(Controller::class)->getTemplateGroup('watchlist_content_');
    }
}
