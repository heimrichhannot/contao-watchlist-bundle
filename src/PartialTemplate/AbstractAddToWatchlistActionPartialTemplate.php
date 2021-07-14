<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\PartialTemplate;

use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;

/**
 * Class AddToWatchlistPartialTemplate.
 *
 * @property PartialTemplateBuilder $builder
 */
abstract class AbstractAddToWatchlistActionPartialTemplate extends AbstractPartialTemplate
{
    /**
     * @var array
     */
    protected $buttonData;
    /**
     * @var string
     */
    protected $uuid;
    /**
     * @var string
     */
    protected $ptable;
    /**
     * @var int
     */
    protected $ptableId;
    /**
     * @var WatchlistConfigModel
     */
    private $configuration;
    /**
     * @var WatchlistModel
     */
    private $watchlist;
    /**
     * @var array
     */
    private $context = [];

    /**
     * AddToWatchlistPartialTemplate constructor.
     *
     * @param array $options
     */
    public function __construct(
        WatchlistConfigModel $configuration,
        WatchlistModel $watchlist,
        array $buttonData,
        string $uuid = null,
        string $ptable = null,
        int $ptableId = null
    ) {
        $this->configuration = $configuration;
        $this->watchlistModel = $watchlist;
        $this->buttonData = $buttonData;
        $this->uuid = $uuid;
        $this->ptable = $ptable;
        $this->ptableId = $ptableId;
    }

    public function getTemplateName(): string
    {
        return static::TEMPLATE_ACTION;
    }

    public function generate(): string
    {
        $this->setButtonContext();

        $template = $this->getTemplate($this->builder->getFrontendFramework($this->configuration));

        return $this->builder->getTwig()->render($template, $this->getContext());
    }

    public function setButtonContext(): void
    {
        $defaultAttributes = $this->getDefaultAttributes();
        $attributes = $this->getAttributes($defaultAttributes);

        $context = $this->createDefaultActionContext($attributes, $this->configuration);
        $context['cssClass'] .= ' huh_watchlist_add_to_watchlist'.($this->buttonData['added'] ? ' added' : '');
        $context['linkText'] = $this->buttonData['label'];
        $context['linkTitle'] = $this->buttonData['linkTitle'];

        if ($this->buttonData['added']) {
            $context['disabled'] = true;
        }

        $this->setContext($this->prepareContext($context));
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    protected function getUrl(): string
    {
        return $this->builder->getRouter()->generate('huh_watchlist_add_to_watchlist');
    }

    protected function getDefaultAttributes(): array
    {
        $attributes = $this->createDefaultActionAttributes($this->configuration, $this->getUrl(),
            static::ACTION_TYPE_UPDATE);

        foreach ($this->buttonData as $key => $value) {
            $attributes[$key] = $value;
        }

        return $attributes;
    }

    /**
     * get action attributes.
     */
    abstract protected function getAttributes(array $attributes = []): array;
}
