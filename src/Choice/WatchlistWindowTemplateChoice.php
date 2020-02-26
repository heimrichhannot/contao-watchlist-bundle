<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Choice;

use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WatchlistWindowTemplateChoice extends AbstractChoice
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container->get('contao.framework'));
        $this->container = $container;
    }

    /**
     * @return array
     */
    protected function collect()
    {
        $templates = $this->container->get('huh.utils.template')->getTemplateGroup('watchlist_window');

        return $templates;
    }
}
