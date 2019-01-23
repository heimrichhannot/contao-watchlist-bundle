<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Module;

use Contao\StringUtil;
use HeimrichHannot\FrontendEdit\ModuleReader;

class DownloadLinkSubmission extends ModuleReader
{
    protected $strFormClass = 'HeimrichHannot\\WatchlistBundle\\Form\\DownloadLinkForm';

    public function generate()
    {
        return parent::generate();
    }

    public function addDefaultValues(array $arrValues = [])
    {
        $this->arrData['formHybridAddDefaultValues'] = true;
        $this->arrData['formHybridDefaultValues'] = serialize(
            array_merge(StringUtil::deserialize($this->formHybridDefaultValues, true), $arrValues)
        );
    }
}
