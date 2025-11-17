<?php

namespace HeimrichHannot\WatchlistBundle\Watchlist;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum AuthorType: string implements TranslatableLabelInterface
{
    case NONE = 'none';
    case MEMBER = 'member';
    case USER = 'user';
    case SESSION = 'session';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('tl_watchlist.' . $this->value,  [], 'contao_tl_watchlist');
    }
}