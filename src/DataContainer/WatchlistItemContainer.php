<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use HeimrichHannot\UtilsBundle\Util\Utils;

class WatchlistItemContainer
{
    const TYPE_FILE = 'file';
    const TYPE_ENTITY = 'entity';

    const TYPES = [
        self::TYPE_FILE,
        self::TYPE_ENTITY,
    ];

    public function __construct(
        private readonly Utils $utils,
        private readonly ContaoFramework $framework
    )
    {
        $this->framework = $framework;
    }

    #[AsCallback(table: 'tl_watchlist_item', target: 'fields.entityTable.options')]
    public function getDataContainers()
    {
        $arrTables = Database::getInstance()->listTables();
        return array_values($arrTables);
    }

    #[AsCallback(table: 'tl_watchlist_item', target: 'fields.entity.options')]
    public function getEntities(DataContainer $dc)
    {
        if (null === ($item = $this->utils->model()->findModelInstanceByPk('tl_watchlist_item', $dc->id)) || !$item->entityTable) {
            return [];
        }

        $models = $this->utils->model()->findModelInstancesBy(
            $item->entityTable,
            null,
            null
        );

        if (null === $models) {
            return [];
        }

        $options = [];
        foreach ($models as $model) {
            $label = $model->name ?: $model->title ?: $model->headline ?: null;
            if (null === $label) {
                $label = 'ID '.$model->id;
            } else {
                $label .= ' (ID '.$model->id.')';
            }
            $options[$model->id] = $label;
        }

        return $options;
    }
}
