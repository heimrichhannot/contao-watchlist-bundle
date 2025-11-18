<?php

namespace HeimrichHannot\WatchlistBundle\InsertTag;

use Contao\CoreBundle\DependencyInjection\Attribute\AsInsertTag;
use Contao\CoreBundle\InsertTag\InsertTagResult;
use Contao\CoreBundle\InsertTag\OutputType;
use Contao\CoreBundle\InsertTag\ResolvedInsertTag;
use Contao\CoreBundle\InsertTag\ResolvedParameters;
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
        $params = $insertTag->getParameters();
        $type = $params->getScalar(0);

        if (!in_array($type, ['file', 'entity'])) {
            return new InsertTagResult('', OutputType::text);
        }

        return match ($type) {
            'file' => $this->fileInsertTag($params),
            'entity' => $this->entityInsertTag($params),
        };
    }

    /**
     * @param ResolvedParameters $params
     * @return InsertTagResult
     */
    private function fileInsertTag(ResolvedParameters $params): InsertTagResult
    {
        $file = $params->getScalar(1);
        $title = $params->getScalar(2);
        $watchlist = $params->getScalar(3);
        return new InsertTagResult(
            $this->watchlistLinkGenerator->generateAddFileLink($file, $title, $watchlist),
            OutputType::html
        );
    }

    private function entityInsertTag(ResolvedParameters $params): InsertTagResult
    {
        $table = $params->getScalar(1);
        $id = $params->getScalar(2);
        $title = $params->getScalar(3);
        $url = $params->getScalar(4);
        $preview = $params->getScalar(5);
        $watchlist = $params->getScalar(6);

        return new InsertTagResult(
            $this->watchlistLinkGenerator->generateEntityLink($table, $id, $title, $url, $preview, $watchlist),
            OutputType::html
        );

    }
}