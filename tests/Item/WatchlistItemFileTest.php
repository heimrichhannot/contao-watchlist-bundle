<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Test\Item;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\WatchlistBundle\Item\WatchlistItemFile;

class WatchlistItemFileTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();

        $container = $this->mockContainer();

        $fileUtil = $this->createMock(FileUtil::class);
        $fileUtil->method('getPathFromUuid')->willReturn('path/to/file');

        $container->set('huh.utils.file', $fileUtil);

        System::setContainer($container);
    }

    public function testSetFile()
    {
        $item = new WatchlistItemFile(['uuid' => 'uuid']);
        $item->setFile();
        $this->assertSame('path/to/file', $item->getFile());

        $container = $this->mockContainer();

        $fileUtil = $this->createMock(FileUtil::class);
        $fileUtil->method('getPathFromUuid')->willReturn(null);

        $container->set('huh.utils.file', $fileUtil);

        System::setContainer($container);

        $item = new WatchlistItemFile(['uuid' => 'uuid']);
        $item->setFile();

        $this->assertNull($item->getFile());
    }
}
