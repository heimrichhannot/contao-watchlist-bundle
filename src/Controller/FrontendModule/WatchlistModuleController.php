<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(WatchlistModuleController::TYPE,category="miscellaneous")
 */
class WatchlistModuleController extends AbstractFrontendModuleController
{
    const TYPE = 'watchlist';

    protected function getResponse(Template $template, ModuleModel $module, Request $request): ?Response
    {
        return $template->getResponse();
    }
}
