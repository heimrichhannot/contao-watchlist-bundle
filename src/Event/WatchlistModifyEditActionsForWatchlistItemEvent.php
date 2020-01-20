<?php


namespace HeimrichHannot\WatchlistBundle\Event;


use Contao\FrontendTemplate;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use Symfony\Component\EventDispatcher\Event;

class WatchlistModifyEditActionsForWatchlistItemEvent extends Event
{
    const NAME = 'huh.watchlist.event.watchlist_modify_edit_actions_for_watchlist_item';

    /**
     * @var FrontendTemplate
     */
    protected $template;

    /**
     * @var WatchlistConfigModel
     */
    protected $config;

    /**
     * @var int
     */
    protected $watchlist;

    /**
     * @var array
     */
    protected $item;


    public function __construct(FrontendTemplate $template, WatchlistConfigModel $config, int $watchlist, array $item)
    {
        $this->template  = $template;
        $this->config    = $config;
        $this->watchlist = $watchlist;
        $this->item      = $item;
    }

    /**
     * @return FrontendTemplate
     */
    public function getTemplate(): FrontendTemplate
    {
        return $this->template;
    }

    /**
     * @param FrontendTemplate $template
     */
    public function setTemplate(FrontendTemplate $template): void
    {
        $this->template = $template;
    }

    /**
     * @return WatchlistConfigModel
     */
    public function getConfig(): WatchlistConfigModel
    {
        return $this->config;
    }

    /**
     * @param WatchlistConfigModel $config
     */
    public function setConfig(WatchlistConfigModel $config): void
    {
        $this->config = $config;
    }

    /**
     * @return int
     */
    public function getWatchlist(): int
    {
        return $this->watchlist;
    }

    /**
     * @param int $watchlist
     */
    public function setWatchlist(int $watchlist): void
    {
        $this->watchlist = $watchlist;
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

}