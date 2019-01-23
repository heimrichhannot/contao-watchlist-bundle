<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Test\Item;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\WatchlistBundle\Item\DownloadItemFile;

class DownloadItemFileTest extends ContaoTestCase
{
    public function testSetFile()
    {
        $container = $this->mockContainer();

        $fileUtil = $this->createMock(FileUtil::class);
        $fileUtil->method('getPathFromUuid')->willReturn('path/to/file');

        $container->set('huh.utils.file', $fileUtil);
        System::setContainer($container);

        $item = new DownloadItemFile(['uuid' => 'uuid']);
        $item->setFile();
        $this->assertSame('path/to/file', $item->getFile());
    }

    public function testRetrieveItem()
    {
        $container = $this->mockContainer();

        $fileUtil = $this->createMock(FileUtil::class);
        $fileUtil->method('getPathFromUuid')->willReturn(null);

        $container->set('huh.utils.file', $fileUtil);
        System::setContainer($container);

        $item = new DownloadItemFile(['uuid' => 'uuid']);
        $this->assertNull($item->retrieveItem());

        $container = $this->mockContainer();

        $fileUtil = $this->createMock(FileUtil::class);
        $fileUtil->method('getPathFromUuid')->willReturn('path/to/file');

        $container->set('huh.utils.file', $fileUtil);
        System::setContainer($container);

        $item2 = new DownloadItemFile(['uuid' => 'uuid']);
        $retrievedItem2 = $item2->retrieveItem();

        $this->assertNotNull($retrievedItem2);
        $this->assertNull($item2->getTitle());

        $item3 = new DownloadItemFile(['uuid' => 'uuid', 'title' => 'title']);

        $retrievedItem3 = $item3->retrieveItem();

        $this->assertNotNull($retrievedItem3);
        $this->assertSame('title', $item3->getTitle());

        $container = $this->mockContainer();

        $fileUtil = $this->createMock(FileUtil::class);
        $fileUtil->method('getPathFromUuid')->willReturn('');

        $container->set('huh.utils.file', $fileUtil);
        System::setContainer($container);

        $item4 = new DownloadItemFile(['uuid' => 'uuid', 'title' => '']);
        $retrievedItem4 = $item4->retrieveItem();

        $this->assertNull($retrievedItem4);
    }
}
