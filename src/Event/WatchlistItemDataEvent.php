<?php

namespace HeimrichHannot\WatchlistBundle\Event;

use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use Symfony\Component\EventDispatcher\Event;

class WatchlistItemDataEvent extends Event
{

    /**
     * @var array
     */
    private $item;
    /**
     * @var WatchlistConfigModel
     */
    private $configModel;

    public function __construct(array $item, WatchlistConfigModel $configModel)
    {
        $this->item = $item;
        $this->configModel = $configModel;
    }

    /**
     * @return array
     */
    public function getItem(): array
    {
        return $this->item;
    }

    /**
     * @param array $item
     */
    public function setItem(array $item): void
    {
        $this->item = $item;
    }

    /**
     * @return WatchlistConfigModel
     */
    public function getConfigModel(): WatchlistConfigModel
    {
        return $this->configModel;
    }
}