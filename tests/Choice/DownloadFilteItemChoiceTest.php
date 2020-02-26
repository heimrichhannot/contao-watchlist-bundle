<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Test\Choice;

use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\WatchlistBundle\Choice\DownloadItemFileChoice;
use Symfony\Component\HttpKernel\Kernel;

class DownloadFilteItemChoiceTest extends ContaoTestCase
{
    protected $framework;

    public function setUp()
    {
        parent::setUp();
        $this->framework = $this->mockContaoFramework($this->createMockAdapter());

        $container = $this->mockContainer();

        $kernel = $this->createMock(Kernel::class);
        $kernel->method('getCacheDir')->willReturn('system/cache');

        $container->set('kernel', $kernel);
        System::setContainer($container);

        $container->set('huh.watchlist.choice.download_file', new DownloadItemFileChoice($this->framework));

        $container->setParameter('huh.watchlist', [
            'watchlist' => [
                'downloadFileItems' => [
                    [
                        'name' => 'default',
                        'class' => 'DownloadFileClass',
                    ],
                ],
                'downloadEntityItems' => [
                    [
                        'name' => 'default',
                        'class' => 'DownloadEntityClass',
                    ],
                ],
            ],
        ]);

        System::setContainer($container);
    }

    public function testGetChoices()
    {
        $choices = System::getContainer()->get('huh.watchlist.choice.download_file')->getChoices();

        $this->assertCount(1, $choices);
        $this->assertSame('default', key($choices));
        $this->assertSame('DownloadFileClass', $choices['default']);
    }

    public function createMockAdapter()
    {
        $systemAdapter = $this->mockAdapter(['loadLanguageFile']);
        $modelAdapter = $this->mockAdapter(['getClassFromTable']);

        return [
            Model::class => $modelAdapter,
            System::class => $systemAdapter,
        ];
    }
}
