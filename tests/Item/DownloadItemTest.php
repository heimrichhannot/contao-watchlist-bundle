<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Test\Item;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\WatchlistBundle\Item\DownloadItem;

class DownloadItemTest extends ContaoTestCase
{
    public function testCanBeInstantiated()
    {
        $item = new DownloadItem();

        $this->assertInstanceOf(DownloadItem::class, $item);
    }

    public function testGetRaw()
    {
        $item = new DownloadItem();
        $this->assertSame([], $item->getRaw());

        $item2 = new DownloadItem(['title' => 'title']);
        $this->assertSame(['title' => 'title'], $item2->getRaw());
    }

    public function testSetRaw()
    {
        $item = new DownloadItem();
        $this->assertSame([], $item->getRaw());

        $item->setRaw(['title' => 'title']);
        $this->assertSame(['title' => 'title'], $item->getRaw());
    }

    public function testGetTitle()
    {
        $item = new DownloadItem();
        $this->assertNull($item->getTitle());

        $item2 = new DownloadItem(['title' => 'title']);
        $this->assertNull($item->getTitle());
    }

    public function testSetTitle()
    {
        $item = new DownloadItem();
        $this->assertNull($item->getTitle());

        $item->setTitle('title');
        $this->assertSame('title', $item->getTitle());
    }
}
