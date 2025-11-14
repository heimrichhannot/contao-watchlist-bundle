<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Database;
use Contao\Date;
use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\UtilsBundle\Choice\DataContainerChoice;
use HeimrichHannot\UtilsBundle\Choice\ModelInstanceChoice;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;

class WatchlistItemContainer
{
    const TYPE_FILE = 'file';
    const TYPE_ENTITY = 'entity';

    const TYPES = [
        self::TYPE_FILE,
        self::TYPE_ENTITY,
    ];

    /** @var ContaoFramework */
    protected $framework;
    /** @var DataContainerChoice */
    protected $dataContainerChoice;
    /** @var ModelUtil */
    protected $modelUtil;
    /** @var ModelInstanceChoice */
    protected $modelInstanceChoice;

    public function __construct(
        private readonly Utils $utils,
        ContaoFramework $framework
    )
    {
        $this->framework = $framework;
    }

    /**
     * @Callback(table="tl_watchlist_item", target="fields.entityTable.options")
     */
    public function getDataContainers()
    {
        $arrTables = Database::getInstance()->listTables();
        return array_values($arrTables);
    }

    /**
     * @Callback(table="tl_watchlist_item", target="fields.entity.options")
     */
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
