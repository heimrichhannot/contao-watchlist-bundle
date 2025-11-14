<?php

namespace HeimrichHannot\WatchlistBundle\EventListener\DataContainer\WatchlistItem;

use Contao\Config;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Date;
use Contao\System;
use HeimrichHannot\UtilsBundle\Util\Utils;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer;

#[AsCallback(table: 'tl_watchlist_item', target: 'list.sorting.child_record')]
class ListSortingChildRecordListener
{
    public function __construct(
        private readonly Utils $utils,
        private readonly ContaoFramework $framework,
    )
    {
    }

    public function __invoke(array $row): string
    {
        $label = $row['title'];

        switch ($row['type']) {
            case WatchlistItemContainer::TYPE_FILE:
                if (null !== ($path = $this->utils->file()->getPathFromUuid($row['file']))) {
                    $label .= " ($path)";
                }

                break;

            case WatchlistItemContainer::TYPE_ENTITY:
                $this->framework->getAdapter(System::class)->loadLanguageFile($row['entityTable']);
                $entity = $this->utils->model()->findOneModelInstanceBy(
                    $row['entityTable'],
                    [$row['entityTable'].'.id=?'],
                    [$row['entity']]
                );


                if (null !== $entity) {
                    foreach (['name', 'title', 'headline',] as $titleField) {
                        if (isset($GLOBALS['TL_DCA'][$row['entityTable']]['fields'][$titleField])) {
                            $label .= ' ('.($entity->{$titleField} ? $entity->{$titleField}.', ' : '').'ID '.$entity->id.')';

                            break;
                        }
                    }
                }

                break;
        }

        return '<div class="tl_content_left">'.$label.' <span style="color:#b3b3b3; padding-left:3px">['.
            Date::parse(Config::get('datimFormat'), trim((string) $row['dateAdded'])).']</span></div>';
    }
}