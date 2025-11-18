<?php

namespace HeimrichHannot\WatchlistBundle\Item;

use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use Contao\CoreBundle\Image\Studio\Studio;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;

class WatchlistItemFactory
{
    public function __construct(
        private readonly VirtualFilesystemInterface $filesStorage,
        private readonly Studio $studio,
    )
    {
    }

    public function build(int|WatchlistItemModel $instance): WatchlistItem
    {
        if (is_int($instance)) {
            $instance = WatchlistItemModel::findByPk($instance);
        }

        return new WatchlistItem(
            $instance,
            $this->filesStorage,
            $this->studio,
        );
    }
}