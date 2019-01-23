<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Event;

use Contao\ModuleModel;
use Symfony\Component\EventDispatcher\Event;

class WatchlistBeforeSendNotificationEvent extends Event
{
    const NAME = 'huh.watchlist.event.watchlist_before_send_notification';

    /**
     * @var array
     */
    protected $submissionData;

    /**
     * @var ModuleModel
     */
    protected $module;

    public function __construct(array $submissionData, ModuleModel $module)
    {
        $this->submissionData = $submissionData;
        $this->module = $module;
    }

    public function setSubmissionData(array $data)
    {
        $this->submissionData = $data;
    }

    public function getSubmissionData()
    {
        return $this->submissionData;
    }

    public function setModule(ModuleModel $module)
    {
        $this->module = $module;
    }

    public function getModule()
    {
        return $this->module;
    }
}
