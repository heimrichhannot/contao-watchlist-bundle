<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Module;

use HeimrichHannot\FrontendEdit\ModuleReader;
use HeimrichHannot\Submissions\Creator\ModuleSubmissionReader;

class DownloadLinkSubmission extends ModuleSubmissionReader
{
    protected $strFormClass = 'HeimrichHannot\\WatchlistBundle\\Form\\DownloadLinkForm';

    public function generate()
    {
        return parent::generate();
    }
}
