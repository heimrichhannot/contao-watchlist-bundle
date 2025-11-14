<?php

namespace HeimrichHannot\WatchlistBundle\EventListener\DataContainer\Watchlist;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;

#[AsCallback(table: 'tl_watchlist', target: 'fields.config.options')]
class FieldsConfigOptionsListener
{
    public function __invoke(?DataContainer $dc = null): array
    {
        return WatchlistConfigModel::findAll()?->fetchEach('title') ?? [];
    }
}