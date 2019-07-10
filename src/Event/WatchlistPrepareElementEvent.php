<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
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
     * @param Template $template
     */
    public function __construct(Template $template, WatchlistConfigModel $configuration)
    {
        $this->template = $template;
        $this->configuration = $configuration;
    }

    /**
     * @return Template
     */
    public function getTemplate(): Template
    {
        return $this->template;
    }

    /**
     * @param Template $template
     */
    public function setTemplate(Template $template): void
    {
        $this->template = $template;
    }

    /**
     * @return WatchlistConfigModel
     */
    public function getConfiguration(): WatchlistConfigModel
    {
        return $this->configuration;
    }

    /**
     * @param WatchlistConfigModel $configuration
     */
    public function setConfiguration(WatchlistConfigModel $configuration): void
    {
        $this->configuration = $configuration;
    }





}