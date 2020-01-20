<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\WatchlistBundle\PartialTemplate;

use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;

/**
 * Class AddToWatchlistPartialTemplate
 * @package HeimrichHannot\WatchlistBundle\PartialTemplate
 *
 * @property PartialTemplateBuilder $builder
 */
abstract class AbstractAddToWatchlistActionPartialTemplate extends AbstractPartialTemplate
{
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
     * @var array
     */
    private $context = [];

    /**
     * AddToWatchlistPartialTemplate constructor.
     * @param WatchlistConfigModel $configuration
     * @param WatchlistModel $watchlist
     * @param array $buttonData
     * @param array $options
     * @param string|null $uuid
     * @param string|null $ptable
     * @param int|null $ptableId
     */
    public function __construct(
        WatchlistConfigModel $configuration,
        WatchlistModel $watchlist,
        array $buttonData,
        string $uuid = null,
        string $ptable = null,
        int $ptableId = null
    ) {
        $this->configuration  = $configuration;
        $this->watchlistModel = $watchlist;
        $this->buttonData     = $buttonData;
        $this->uuid           = $uuid;
        $this->ptable         = $ptable;
        $this->ptableId       = $ptableId;
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

    /**
     *
     */
    public function setButtonContext(): void
    {
        $defaultAttributes = $this->getDefaultAttributes();
        $attributes        = $this->getAttributes($defaultAttributes);

        $context              = $this->createDefaultActionContext($attributes, $this->configuration);
        $context['cssClass']  .= ' huh_watchlist_add_to_watchlist' . ($this->buttonData['added'] ? ' added' : '');
        $context['linkText']  = $this->buttonData['label'];
        $context['linkTitle'] = $this->buttonData['linkTitle'];

        if($this->buttonData['added']) {
            $context['disabled'] = true;
        }

        $this->setContext($this->prepareContext($context));
    }

    /**
     * @return string
     */
    protected function getUrl(): string
    {
        return $this->builder->getRouter()->generate('huh_watchlist_add_to_watchlist');
    }

    /**
     * @return array
     */
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
     * get action attributes
     *
     * @return array
     */
    abstract protected function getAttributes(array $attributes = []): array;

    /**
     * @param array $context
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }
}