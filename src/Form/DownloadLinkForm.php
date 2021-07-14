<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Form;

use Contao\System;
use HeimrichHannot\FormHybrid\Form;
use HeimrichHannot\FormHybrid\Submission;
use HeimrichHannot\Haste\Util\Url;

class DownloadLinkForm extends Form
{
    protected $strDownloadLinkFormId = 'watchlist-downloadLink-form';
    protected $strMethod = 'POST';

    public function modifyDC(&$arrDca = null)
    {
        parent::modifyDC($arrDca); // TODO: Change the autogenerated stub

        $arrDca['fields']['privacyJumpTo']['label'][0] = sprintf($GLOBALS['TL_LANG']['tl_submission']['privacyJumpTo'][0], Url::generateFrontendUrl($this->jumpToPrivacy));
    }

    /**
     * TODO: change to normal formhybrid form handling.
     *
     * @param bool $blnFormatted
     * @param bool $blnSkipDefaults
     *
     * @return \FilesModel|Submission|\Model|null
     */
    public function getSubmission($blnFormatted = true, $blnSkipDefaults = false)
    {
        $submission = new Submission();

        $data = (array) json_decode(System::getContainer()->get('huh.request')->getPost('data'));

        foreach ($data as $key => $value) {
            if ('REQUEST_TOKEN' == $key) {
                continue;
            }

            $submission->{$key} = $value;
        }

        $submission->arrData = $data;

        return $submission;
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
