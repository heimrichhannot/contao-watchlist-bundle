<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\FrontendModule;

use HeimrichHannot\Submissions\Creator\ModuleSubmissionReader;

class DownloadLinkSubmission extends ModuleSubmissionReader
{
    protected $strFormClass = 'HeimrichHannot\\WatchlistBundle\\Form\\DownloadLinkForm';

    public function generate()
    {
        return parent::generate();
    }
}
