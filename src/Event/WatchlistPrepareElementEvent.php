<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Event;

use Contao\Template;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use Symfony\Component\EventDispatcher\Event;

class WatchlistPrepareElementEvent extends Event
{
    const NAME = 'huh.watchlist.event.prepare_element';

    /**
     * @var Template
     */
    private $template;
    /**
     * @var WatchlistConfigModel
     */
    private $configuration;

    /**
     * WatchlistPrepareElementEvent constructor.
     */
    public function __construct(Template $template, WatchlistConfigModel $configuration)
    {
        $this->template = $template;
        $this->configuration = $configuration;
    }

    public function getTemplate(): Template
    {
        return $this->template;
    }

    public function setTemplate(Template $template): void
    {
        $this->template = $template;
    }

    public function getConfiguration(): WatchlistConfigModel
    {
        return $this->configuration;
    }

    public function setConfiguration(WatchlistConfigModel $configuration): void
    {
        $this->configuration = $configuration;
    }
}
