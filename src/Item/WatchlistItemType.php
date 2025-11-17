<?php

namespace HeimrichHannot\WatchlistBundle\Item;

enum WatchlistItemType: string
{
    case FILE = 'file';
    case ENTITY = 'entity';
}
