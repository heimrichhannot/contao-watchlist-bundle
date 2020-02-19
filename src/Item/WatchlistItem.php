<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Item;

use Contao\Controller;
use Contao\FrontendTemplate;
use Contao\System;
use HeimrichHannot\WatchlistBundle\Event\WatchlistModifyEditActionsForWatchlistItemEvent;
use HeimrichHannot\WatchlistBundle\Manager\AjaxManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;

class WatchlistItem implements WatchlistItemInterface
{
    protected $_raw = [];

    protected $_title;

    protected $_file = null;

    protected $_type;

    public function __construct(array $data = [])
    {
        $this->setRaw($data);
    }

    public function getRaw(): array
    {
        return $this->_raw;
    }

    public function setRaw(array $data = []): void
    {
        $this->_raw = $data;
    }

    public function setType(string $type = '')
    {
        $this->_type = $type;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setTitle(string $title = '')
    {
        $this->_title = $title;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function getFile()
    {
        return $this->_file;
    }

    public function setFile()
    {
    }

    public function getEditActions(WatchlistConfigModel $configuration, int $watchlistId): string
    {
        $container              = System::getContainer();
        $request = $container->get('request_stack')->getCurrentRequest();
        $url     = null;
        if ($request->isXmlHttpRequest())
        {
            $url = $request->headers->get('referer');
        }
        $translator             = $container->get('translator');
        $template               = new FrontendTemplate('watchlist_edit_actions');
        $template->id           = $this->_raw['id'];
        $template->deleteAction = $container->get('huh.ajax.action')->generateUrl(
            AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_DELETE_ITEM_ACTION, [], true, $url
        );
        $template->delTitle     = $translator->trans('huh.watchlist.item.delete.label');
        $template->delLink      = $translator->trans('huh.watchlist.item.delete.link');
        $template->moduleId     = $configuration->id;
        $template->watchlistId  = $watchlistId;

        if (null !== ($file = $this->getFile())) {
            if (!$url) {
                $url = $container->get('huh.utils.url')->getCurrentUrl(['skipParams' => true]);
            }
            else {
                $url = parse_url($url, PHP_URL_PATH);
            }
            $template->downloadAction = $url.'?file=' . $file;
            $template->downloadTitle  = $translator->trans('huh.watchlist.item.download.title', [
                '%item%' => $this->getTitle()
            ]);
            $template->downloadLink  = $translator->trans('huh.watchlist.item.download.link');
        }


        $event = System::getContainer()->get('event_dispatcher')->dispatch(WatchlistModifyEditActionsForWatchlistItemEvent::NAME, new WatchlistModifyEditActionsForWatchlistItemEvent($template, $configuration, $watchlistId, $this->getRaw()));
        $template = $event->getTemplate();


        return $template->parse();
    }

    public function getDetailsUrl(WatchlistConfigModel $configuration): ?string
    {
        return null;
    }
}
