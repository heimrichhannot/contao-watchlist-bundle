<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Manager;

use HeimrichHannot\WatchlistBundle\FrontendFramework\WatchlistFrameworkInterface;

class WatchlistFrontendFrameworksManager
{
    /**
     * @var WatchlistFrameworkInterface[]|array
     */
    private $frameworks;

    public function addFramework(WatchlistFrameworkInterface $framework)
    {
        if (!preg_match('/^[a-z0-9_]+$/u', $framework->getType())) {
            throw new \Exception('Not a valid watchlist frontend framework type. Only use these characters: [a-z0-9_]');
        }
        if (\strlen($framework->getType()) > 32) {
            throw new \Exception('Not a valid watchlist frontend framework type. Must not be longer than 32 characters.');
        }
        $this->frameworks[$framework->getType()] = $framework;
    }

    /**
     * Return the framework by type.
     * Returns NULL if type not found.
     *
     * @return WatchlistFrameworkInterface|null
     */
    public function getFrameworkByType(string $type)
    {
        return isset($this->frameworks[$type]) ? $this->frameworks[$type] : null;
    }

    /**
     * Returns all frameworks.
     *
     * @return array|WatchlistFrameworkInterface[]
     */
    public function getAllFrameworks()
    {
        return $this->frameworks;
    }
}
