<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Test\Item;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\Ajax\AjaxAction;
use HeimrichHannot\WatchlistBundle\Item\WatchlistItem;

class WatchlistItemTest extends ContaoTestCase
{
    public function testCanBeInstantiated()
    {
        $this->assertInstanceOf(WatchlistItem::class, new WatchlistItem());
    }

    public function testGetRaw()
    {
        $item = new WatchlistItem();

        $this->assertSame([], $item->getRaw());
    }

    public function testSetRaw()
    {
        $item = new WatchlistItem();

        $this->assertSame([], $item->getRaw());

        $item->setRaw(['title' => 'title']);
        $this->assertSame(['title' => 'title'], $item->getRaw());
    }

    public function testGetType()
    {
        $item = new WatchlistItem();
        $this->assertNull($item->getType());

        $item2 = new WatchlistItem(['type' => 'type']);
        $this->assertNull($item2->getType());
    }

    public function testSetType()
    {
        $item = new WatchlistItem();
        $this->assertNull($item->getType());

        $item->setType('type');
        $this->assertSame('type', $item->getType());
    }

    public function testGetTitle()
    {
        $item = new WatchlistItem();
        $this->assertNull($item->getTitle());

        $item2 = new WatchlistItem(['title' => 'title']);
        $this->assertNull($item2->getTitle());
    }

    public function testSetTitle()
    {
        $item = new WatchlistItem();
        $this->assertNull($item->getType());

        $item->setTitle('title');
        $this->assertSame('title', $item->getTitle());
    }

    public function testGetEditActions()
    {
        $this->markTestSkipped();

        $container = $this->mockContainer();

        $ajaxAction = $this->createMock(AjaxAction::class);
        $ajaxAction->method('generateUrl')->willReturn('ajaxAction');
    }
}
