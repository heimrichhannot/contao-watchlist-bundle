<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Filter\Type;

use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\Type\TextType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;

class WatchlistDownloadType extends TextType
{
    const TYPE = 'watchlistDownload';

    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        if (empty($ids = $this->getSourceItemIds($element->watchlistConfig))) {
            return;
        }

        $filter = $this->config->getFilter();

        $builder->andWhere($filter['dataContainer'] . '.id ' . $this->getDefaultOperator($element) . ' (' . implode(',',
                $ids) . ')');
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultName(FilterConfigElementModel $element)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOperator(FilterConfigElementModel $element)
    {
        return DatabaseUtil::OPERATOR_IN;
    }

    /**
     * get an array of ids of items in watchlist
     * @param int|null $watchlistConfig
     * @return array
     */
    protected function getSourceItemIds(int $watchlistConfig = null): array
    {
        $uuid = System::getContainer()->get('huh.request')->getGet('watchlist');
        $ids  = [];

        if (!$uuid) {
            return $ids;
        }

        if (null === ($watchlist = System::getContainer()->get('huh.utils.model')->findOneModelInstanceBy('tl_watchlist',
                ['tl_watchlist.uuid=?'], [$uuid]))) {
            return $ids;
        }

        list($table, $columns, $values) = $this->getQuery($watchlist, $watchlistConfig);

        if (null === ($watchlistItems = System::getContainer()->get('huh.utils.model')->findModelInstancesBy($table, $columns, $values))) {
            return $ids;
        }

        return $watchlistItems->fetchEach('ptableId');
    }

    protected function getQuery(WatchlistModel $watchlist, int $watchlistConfigId = null): array
    {
        $table   = 'tl_watchlist_item';
        $columns = [$table . '.pid=?'];
        $values  = [$watchlist->id];


        if(null === ($watchlistConfig = System::getContainer()->get('huh.utils.model')->findOneModelInstanceBy('tl_watchlist_config', ['tl_watchlist_config.id=?'],[$watchlistConfigId]))) {
            return [$table, $columns, $values];
        }

        if(!$watchlistConfig->skipItemsForDownloadList) {
            return [$table, $columns, $values];
        }

        if(empty($skipConfig = StringUtil::deserialize($watchlistConfig->skipItemsForDownloadListConfig, true))) {
            return [$table, $columns, $values];
        }

        if(null === ($watchlistItems = System::getContainer()->get('huh.utils.model')->findModelInstancesBy($table, $columns, $values))) {
            return [$table, $columns, $values];
        }


        $allowedIds = [];
        foreach($watchlistItems as $item) {
            $itemColumns = [];
            $itemValues  = [];

            if('entity' == $item->type) {
                $itemColumns[] = $item->ptable . '.id=?';
                $itemValues[]  = $item->ptableId;
            }

            foreach($skipConfig as $condition) {
                list($itemColumns[], $conditionValues) = System::getContainer()->get('huh.utils.database')->computeCondition($condition['field'], $condition['operator'], $condition['value'], $item->ptable);
                $itemValues[] = reset($conditionValues);
            }

            if(null === ($retrievedItem = System::getContainer()->get('huh.utils.model')->findOneModelInstanceBy($item->ptable, $itemColumns, $itemValues))) {
                continue;
            }

            $allowedIds[] = $item->id;
        }

        if(empty($allowedIds)) {
            return [$table, $columns, $values];
        }

        $ids = implode(',', $allowedIds);
        list($cols, $vals) = System::getContainer()->get('huh.utils.database')->computeCondition('id', DatabaseUtil::OPERATOR_IN, $ids, $table);


        return [$table, [$cols], $vals];
    }
}
