<?php

use HeimrichHannot\WatchlistBundle\Controller\FrontendModule\WatchlistModuleController;
use HeimrichHannot\WatchlistBundle\Controller\FrontendModule\ShareListModuleController;

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$dca = &$GLOBALS['TL_DCA']['tl_module'];

/*
 * Palettes
 */
$dca['palettes'][WatchlistModuleController::TYPE] =
    '{title_legend},name,headline,type;{config_legend},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$dca['palettes'][ShareListModuleController::TYPE] =
    '{title_legend},name,headline,type;{image_legend},imgSize;{config_legend},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
