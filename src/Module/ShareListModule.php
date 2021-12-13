<?php

namespace HeimrichHannot\WatchlistBundle\Module;

use Contao\Module;
use Contao\System;
use HeimrichHannot\WatchlistBundle\Controller\FrontendModule\ShareListModuleController;

class ShareListModule extends Module
{

    protected function compile()
    {
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();
        if ($request) {
            $module = System::getContainer()->get(ShareListModuleController::class)->getResponse($this->Template, $this->getModel(), $request);
        }
    }
}