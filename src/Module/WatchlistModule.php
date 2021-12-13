<?php

namespace HeimrichHannot\WatchlistBundle\Module;

use Contao\Module;
use Contao\System;
use HeimrichHannot\UtilsBundle\Util\Utils;
use HeimrichHannot\WatchlistBundle\Controller\FrontendModule\WatchlistModuleController;

class WatchlistModule extends Module
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_watchlist';

    protected function compile()
    {
        if (System::getContainer()->get(Utils::class)->container()->isBackend()) {
            return;
        }
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();
        if ($request) {
            System::getContainer()->get(WatchlistModuleController::class)->getResponse($this->Template, $this->getModel(), $request);
        }
    }
}