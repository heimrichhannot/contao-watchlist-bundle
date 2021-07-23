<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$dca = &$GLOBALS['TL_DCA']['tl_module'];

/*
 * Palettes
 */
$dca['palettes'][\HeimrichHannot\WatchlistBundle\Controller\FrontendModule\WatchlistModuleController::TYPE] =
    '{title_legend},name,headline,type;{config_legend},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$dca['palettes'][\HeimrichHannot\WatchlistBundle\Controller\FrontendModule\ShareListModuleController::TYPE] =
    '{title_legend},name,headline,type;{config_legend},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
