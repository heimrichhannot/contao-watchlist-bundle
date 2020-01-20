<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Filter\Type;

use Contao\System;
use HeimrichHannot\FilterBundle\Filter\Type\TextType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;

class WatchlistDownloadType extends TextType
{
    const TYPE = 'watchlistDownload';

    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        if (empty($ids = $this->getSourceItemIds())) {
            return;
        }

        $filter = $this->config->getFilter();

        $builder->andWhere($filter['dataContainer'] . '.id ' . $this->getDefaultOperator($element) . ' (' . implode(',', $ids) . ')');
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
     *
     * @return array
     */
    protected function getSourceItemIds(): array
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

        if (null === ($watchlistItems = System::getContainer()->get('huh.utils.model')->findModelInstancesBy('tl_watchlist_item',
                ['tl_watchlist_item.pid=?'], [$watchlist->id]))) {
            return $ids;
        }

        return $watchlistItems->fetchEach('ptableId');
    }
}
