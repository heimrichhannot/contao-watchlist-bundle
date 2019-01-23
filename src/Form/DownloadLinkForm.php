<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Form;

use Contao\System;
use HeimrichHannot\FormHybrid\Form;

class DownloadLinkForm extends Form
{
    protected $strDownloadLinkFormId = 'watchlist-downloadLink-form';
    protected $strMethod = 'POST';

    public function modifyDC(&$arrDca = null)
    {
        $this->addEditableField('moduleId', [
            'label' => $this->iso_agbText,
            'exclude' => true,
            'inputType' => 'hidden',
            'load_callback' => [['huh.watchlist.data_container.module_container', 'getModuleId']],
        ], true);

        $this->addEditableField('watchlistId', [
            'label' => $this->iso_agbText,
            'exclude' => true,
            'inputType' => 'hidden',
            'load_callback' => [['huh.watchlist.data_container.module_container', 'getWatchlistId']],
        ], true);
    }

    public function getFormId($blnAddEntityId = true)
    {
        return $this->strDownloadLinkFormId;
    }

    public function onSubmitCallback(\DataContainer $dc)
    {
        $submission = $this->getSubmission();
        $submission->downloadLink = System::getContainer()->get('huh.watchlist.action_manager')->generateDownloadLink($submission->module, $submission->watchlistId);
    }

    protected function compile()
    {
    }
}
