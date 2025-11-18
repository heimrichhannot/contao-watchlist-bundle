<?php

namespace HeimrichHannot\WatchlistBundle\InsertTag;

use Contao\CoreBundle\DependencyInjection\Attribute\AsInsertTag;
use Contao\CoreBundle\InsertTag\InsertTagResult;
use Contao\CoreBundle\InsertTag\OutputType;
use Contao\CoreBundle\InsertTag\ResolvedInsertTag;
use Contao\CoreBundle\InsertTag\Resolver\InsertTagResolverNestedResolvedInterface;
use HeimrichHannot\WatchlistBundle\Generator\WatchlistLinkGenerator;

#[AsInsertTag('watchlist_add_item_link')]
class AddItemLinkInsertTag implements InsertTagResolverNestedResolvedInterface
{
    public function __construct(
        private readonly WatchlistLinkGenerator $watchlistLinkGenerator,
    )
    {
    }

    public function __invoke(ResolvedInsertTag $insertTag): InsertTagResult
    {
//        {{watchlist_add_item_link::file::<file uuid (string)>::<optional: title>::<optional: watch list uuid>}}
//        {{watchlist_add_item_link::entity::<entity table>::<entity id>::<title>::<optional: entity url>::<optional: preview file uuid (string)>::<optional: watch list uuid>}}

        $params = $insertTag->getParameters();
        $type = $params->getScalar(0);

        if (!in_array($type, ['file', 'entity'])) {
            return new InsertTagResult('', OutputType::text);
        }


        $file = $params->getScalar(1);
        $title = $params->getScalar(2);
        $watchlist = $params->getScalar(3);
        return new InsertTagResult(
            $this->watchlistLinkGenerator->generateAddFileLink($file, $title, $watchlist),
            OutputType::html
        );



        return  new InsertTagResult('', OutputType::text);

    }
}