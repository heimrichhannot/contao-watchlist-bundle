<?php

namespace HeimrichHannot\WatchlistBundle\EventListener\DataContainer\Watchlist;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use HeimrichHannot\WatchlistBundle\Watchlist\AuthorType;

#[AsCallback(table: 'tl_watchlist', target: 'config.onload')]
class ConfigOnLoadListener
{
    public function __invoke(DataContainer|null $dc = null): void
    {
        if (!$dc?->id) {
            return;
        }

        $type = AuthorType::tryFrom($dc->getCurrentRecord()['authorType'] ?? '');
        $authorField = &$GLOBALS['TL_DCA']['tl_watchlist']['fields']['author'];

        switch ($type) {
            case AuthorType::MEMBER:
//                $authorField['inputType'] = 'picker';
//                $authorField['relation'] = [
//                    'type' => 'hasOne',
//                    'load' => 'lazy',
//                    'table' => 'tl_member',
//                ];
//                $authorField['context'] = 'dc.tl_member';
                $authorField['foreignKey'] = 'tl_member.username';
//                $authorField['inputType'] = 'select';
                break;
            case AuthorType::USER:
//                $authorField['inputType'] = 'picker';
//                $authorField['relation'] = [
//                    'type' => 'hasOne',
//                    'load' => 'lazy',
//                    'table' => 'tl_user',
//                ];
//                $authorField['context'] = 'dc.tl_user';
                $authorField['foreignKey'] = 'tl_user.username';
//                $authorField['inputType'] = 'select';
                break;
            default:
                $authorField['inputType'] = 'text';
                $authorField['eval']['readonly'] = true;
        }
    }
}