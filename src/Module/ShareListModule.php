<?php

namespace HeimrichHannot\WatchlistBundle\Module;

use Contao\Module;
use Contao\System;
use HeimrichHannot\WatchlistBundle\Controller\FrontendModule\ShareListModuleController;

class ShareListModule extends Module
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_watchlist_share_list';

    protected function compile()
    {
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();
        if ($request) {
            System::getContainer()->get(ShareListModuleController::class)->getResponse($this->Template, $this->getModel(), $request);
        }
    }
}