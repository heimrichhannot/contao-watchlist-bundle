<?php
$table = 'tl_content';

$dca = \HeimrichHannot\WatchlistBundle\Helper\DcaHelper::addDcaFields($table, '{template_legend', 'download');
\HeimrichHannot\WatchlistBundle\Helper\DcaHelper::addDcaMapping($dca, '{template_legend', 'downloads');