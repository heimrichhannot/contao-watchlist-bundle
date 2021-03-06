<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Test\Choice;

use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\WatchlistBundle\Choice\WatchlistItemEntityChoice;
use Symfony\Component\HttpKernel\Kernel;

class WatchlistItemEntityChoiceTest extends ContaoTestCase
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

        $container->set('huh.watchlist.choice.watchlist_entity', new WatchlistItemEntityChoice($this->framework));

        $container->setParameter('huh.watchlist', [
            'watchlist' => [
                'watchlistFileItems' => [
                    [
                        'name' => 'default',
                        'class' => 'WatchlistFileClass',
                    ],
                ],
                'watchlistEntityItems' => [
                    [
                        'name' => 'default',
                        'class' => 'WatchlistEntityClass',
                    ],
                ],
            ],
        ]);

        System::setContainer($container);
    }

    public function testGetChoices()
    {
        $choices = System::getContainer()->get('huh.watchlist.choice.watchlist_entity')->getChoices();

        $this->assertCount(1, $choices);
        $this->assertSame('default', key($choices));
        $this->assertSame('WatchlistEntityClass', $choices['default']);
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
