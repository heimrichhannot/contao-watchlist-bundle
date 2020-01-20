<?php

$dca = &$GLOBALS['TL_DCA']['tl_filter_config_element'];

$dca['palettes'][\HeimrichHannot\WatchlistBundle\Filter\Type\WatchlistDownloadType::TYPE] = '{general_legend},title,type;{publish_legend},published;';