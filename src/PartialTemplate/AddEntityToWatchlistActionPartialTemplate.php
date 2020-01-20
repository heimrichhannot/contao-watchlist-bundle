<?php


namespace HeimrichHannot\WatchlistBundle\PartialTemplate;


class AddEntityToWatchlistActionPartialTemplate extends AbstractAddToWatchlistActionPartialTemplate
{
    protected function getAttributes(array $attributes = []): array
    {
        $attributes['ptable']   = $this->ptable;
        $attributes['ptableId'] = $this->ptableId;

        return $attributes;
    }
}