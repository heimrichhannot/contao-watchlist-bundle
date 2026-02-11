<?php

namespace HeimrichHannot\WatchlistBundle\Watchlist;

use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\FrontendTemplate;
use Contao\PageModel;
use HeimrichHannot\WatchlistBundle\Event\WatchlistItemDataEvent;
use HeimrichHannot\WatchlistBundle\Item\WatchlistItem;
use HeimrichHannot\WatchlistBundle\Item\WatchlistItemType;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class WatchlistContent implements \Stringable
{
    /**
     * @internal Use factory to create instance of this class
     */
    public function __construct(
        public readonly WatchlistConfigModel      $config,
        /**
         * @var array<WatchlistItem> $items
         */
        public array                              $items,
        private readonly InsertTagParser          $insertTagParser,
        private readonly EventDispatcherInterface $eventDispatcher,
        public ?string                            $watchlistUrl = null,
        public ?string                            $itemUrl = null,
        public ?string                            $downloadAllUrl = null,
        private ?FrontendTemplate                 $legacyTemplate = null,
        public ?string                            $shareUrl = null,
        public ?PageModel                         $rootPage = null,

    ) {}

    public function __toString()
    {
        if (!$this->legacyTemplate) {
            $this->legacyTemplate = new FrontendTemplate('watchlist_content_default');
        }

        $this->legacyTemplate->watchlistUrl = $this->watchlistUrl;
        $this->legacyTemplate->itemUrl = $this->itemUrl;
        $this->legacyTemplate->watchlistDownloadAllUrl = $this->downloadAllUrl;
        $this->legacyTemplate->shareUrl = $this->shareUrl;
        $this->legacyTemplate->config = $this->config;


        $this->applyItemDataToLegacyTemplate($this->legacyTemplate);

        return $this->insertTagParser->replace($this->legacyTemplate->parse());
    }

    private function applyItemDataToLegacyTemplate(FrontendTemplate $template)
    {
        $items = [];
        foreach ($this->items as $wlItem) {

            // clean items for frontend (don't pass internal info to outside for security reasons)
            $cleanedItem = [
                'rootPage' => $this->rootPage?->id ?: 0,
                'type' => $wlItem->getType()->value,
                'title' => $wlItem->getModel()->title,
            ];

            $cleanedItem = $wlItem->applyToTemplateData($cleanedItem);

            if ($wlItem->getType() === WatchlistItemType::ENTITY && $wlItem->fileExist()) {
                $template->hasDownloadableFiles = true;
            }

            $cleanedItem['postData'] = htmlspecialchars(
                json_encode(array_filter($cleanedItem, 'is_scalar')),
                \ENT_QUOTES,
                'UTF-8'
            );

            $event = $this->eventDispatcher->dispatch(
                new WatchlistItemDataEvent($cleanedItem, $this->config, $wlItem),
                WatchlistItemDataEvent::class
            );

            $items[] = $event->itemData;
        }

        $template->items = $items;
    }
}