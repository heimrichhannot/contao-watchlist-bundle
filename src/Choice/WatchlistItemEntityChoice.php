<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Choice;

use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WatchlistItemEntityChoice extends AbstractChoice
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

    public function collect()
    {
        $choices = [];

        $config = $this->container->getParameter('huh_watchlist');

        if (!isset($config['watchlistEntityItems'])) {
            return $choices;
        }

        foreach ($config['watchlistEntityItems'] as $manager) {
            $choices[$manager['name']] = $manager['class'];
        }

        asort($choices);

        return $choices;
    }
}
