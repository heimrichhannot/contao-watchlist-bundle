<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Test\Manager;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\AjaxBundle\Manager\AjaxTokenManager;
use HeimrichHannot\AjaxBundle\Response\ResponseError;
use HeimrichHannot\AjaxBundle\Response\ResponseSuccess;
use HeimrichHannot\WatchlistBundle\Manager\AjaxManager;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistActionManager;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistTemplateManager;

class AjaxManagerTest extends ContaoTestCase
{
    protected $framework;

    protected $watchlistTemplate;

    protected $actionManager;

    protected $watchlistManager;

    public function setUp()
    {
        parent::setUp();

        $this->framework = $this->mockContaoFramework();
        $this->watchlistTemplate = $this->createMock(WatchlistTemplateManager::class);
    }

    public function testCanBeInstantiated()
    {
        $framework = $this->mockContaoFramework();
        $watchlistTemplate = $this->createMock(WatchlistTemplateManager::class);
        $actionManager = $this->createMock(WatchlistActionManager::class);
        $watchlistManager = $this->createMock(WatchlistManager::class);

        $this->assertInstanceOf(AjaxManager::class, new AjaxManager($framework, $watchlistTemplate, $actionManager, $watchlistManager));
    }

    public function testWatchlistShowModalAction()
    {
        $container = $this->mockContainer();

        $container->set('huh.ajax.token', $this->createMock(AjaxTokenManager::class));

        System::setContainer($container);

        $framework = $this->mockContaoFramework();
        $watchlistTemplate = $this->createMock(WatchlistTemplateManager::class);
        $watchlistTemplate->method('getWatchlistModal')->willReturn('modal');

        $actionManager = $this->createMock(WatchlistActionManager::class);
        $watchlistManager = $this->createMock(WatchlistManager::class);

        $ajaxManager = new AjaxManager($framework, $watchlistTemplate, $actionManager, $watchlistManager);

        $response = $ajaxManager->watchlistShowModalAction(null, null);
        $this->assertInstanceOf(ResponseError::class, $response);

        $response2 = $ajaxManager->watchlistShowModalAction(1, null);
        $this->assertInstanceOf(ResponseSuccess::class, $response2);

        $response3 = $ajaxManager->watchlistShowModalAction(1, 1);
        $this->assertInstanceOf(ResponseSuccess::class, $response3);
    }

    public function testWatchlistAddAction()
    {
    }
}
