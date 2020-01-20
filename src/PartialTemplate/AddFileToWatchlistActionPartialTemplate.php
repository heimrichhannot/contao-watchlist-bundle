<?php


namespace HeimrichHannot\WatchlistBundle\PartialTemplate;


use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;

class AddFileToWatchlistActionPartialTemplate extends AbstractAddToWatchlistActionPartialTemplate
{
    protected function getAttributes(array $attributes = []): array
    {
        $attributes['uuid'] = $this->uuid;

        return $attributes;
    }
}