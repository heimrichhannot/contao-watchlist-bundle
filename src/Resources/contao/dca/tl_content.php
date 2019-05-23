<?php
$table = 'tl_content';
\Contao\Controller::loadDataContainer($table);

$dca = $GLOBALS['TL_DCA'][$table];
$dca['palettes']['download'] = str_replace('{template_legend', '{watchlist_legend},addAddToWatchlistButton;{template_legend', $dca['palettes']['downloads']);
$dca['palettes']['downloads'] = str_replace('{template_legend', '{watchlist_legend},addAddToWatchlistButton;{template_legend', $dca['palettes']['downloads']);

\HeimrichHannot\WatchlistBundle\Helper\DcaHelper::addDcaFields($table);