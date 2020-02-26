<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\PartialTemplate;

use HeimrichHannot\WatchlistBundle\FrontendFramework\WatchlistFrameworkInterface;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use Twig\Error\LoaderError;

abstract class AbstractPartialTemplate implements PartialTemplateInterface
{
    const TEMPLATE_WATCHLIST_WINDOW = 'watchlist_window';
    const TEMPLATE_WATCHLIST_ITEM = 'watchlist_item';
    const TEMPLATE_OPEN_WATCHLIST_WINDOW = 'open_watchlist_window';
    const TEMPLATE_ACTION = 'watchlist_action';
    const TEMPLATE_ITEM_PARENT_LIST = 'watchlist_item_parent_list';

    const ACTION_TYPE_TOGGLE = 'toggle';
    const ACTION_TYPE_UPDATE = 'update';
    const ACTION_TYPE_DOWNLOAD = 'download';
    const ACTION_TYPE_NONE = 'none';

    /**
     * @var PartialTemplateBuilder
     */
    protected $builder;

    /**
     * Return the template name without framework suffix and file extension,
     * e.g. 'watchlist_window'.
     */
    abstract public function getTemplateName(): string;

    public function setBuilder(PartialTemplateBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Get the template path with fallback to base template if template does not exist.
     *
     * @param string $templateType
     *
     * @throws \Twig_Error_Loader
     */
    protected function getTemplate(WatchlistFrameworkInterface $frontendFramework): string
    {
        try {
            $template = $this->builder->getContainer()->get('huh.utils.template')->getTemplate(
                $frontendFramework->getTemplate($this->getTemplateName()),
                $frontendFramework->getTemplateFormat()
            );
        } catch (LoaderError $e) {
            $template = $this->builder->getContainer()->get('huh.utils.template')->getTemplate(
                $this->builder->getFrameworksManager()->getFrameworkByType('base')->getTemplate($this->getTemplateName()),
                $this->builder->getFrameworksManager()->getFrameworkByType('base')->getTemplateFormat()
            );
        }

        return $template;
    }

    /**
     * Create the data attributes array with default setup.
     */
    protected function createDefaultActionAttributes(WatchlistConfigModel $configuration, string $actionUrl, string $actionType): array
    {
        return [
            'action' => $this->getTemplateName(),
            'watchlistConfig' => $configuration->id,
            'requestToken' => $this->builder->getCsrfToken(),
            'actionUrl' => $actionUrl,
            'actionType' => $actionType,
            'frontend' => $this->builder->getFrontendFramework($configuration)->getType(),
        ];
    }

    /**
     * Create the context array with default setup.
     *
     * @return array
     */
    protected function createDefaultActionContext(array $dataAttributes, WatchlistConfigModel $configuration, ?WatchlistModel $watchlistModel = null)
    {
        $dataAttributes = $this->builder->getFrontendFramework($configuration)->prepareDataAttributes($dataAttributes, $this);
        $context = [];
        $context['dataAttributes'] = $this->generateDataAttributes($dataAttributes);
        $context['cssClass'] = 'huh_watchlist_action';
        $context['cssCountClass'] = 'huh_watchlist_item_count';
        if ($watchlistModel) {
            $context['cssClass'] .= ' watchlist-'.$watchlistModel->id;
            $context['cssCountClass'] .= ' watchlist-'.$watchlistModel->id;
        }

        return $context;
    }

    /**
     * Generate a data-attributes-string out of an array.
     */
    protected function generateDataAttributes(array $attributes): string
    {
        $dataAttributes = '';
        array_walk($attributes, function ($value, $key) use (&$dataAttributes) {
            $key = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $key));
            $dataAttributes .= 'data-'.$key.'="'.$value.'" ';
        });

        return $dataAttributes;
    }

    /**
     * Prepare the content data.
     */
    protected function prepareContext(array $context): array
    {
        $context['id'] = $this->getTemplateName().'_'.rand(0, 99999);

        return $context;
    }
}
