<?php

namespace HeimrichHannot\WatchlistBundle\Routing;

use Contao\Date;
use Contao\Model\Collection;
use Contao\PageModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;

class PageFinder
{
    public function findByConfig(WatchlistConfigModel $config, int $limit = 0, bool $onlyPublished = true, array $options = []): Collection|PageModel|null
    {
        $t = PageModel::getTable();
        $time = Date::floorToMinute();

        if ($limit > 0) {
            $options['limit'] = $limit;
        }

        if ($limit === 1) {
            $options['return'] = 'Model';
        }

        $columns = [
            "$t.watchlistConfig=?",
        ];

        if ($onlyPublished) {
            $columns[] = "$t.published=1 AND ($t.start='' OR $t.start<=$time) AND ($t.stop='' OR $t.stop>$time)";
        }

        return PageModel::findBy(
            $columns,
            [$config->id],
            $options
        );
    }
}