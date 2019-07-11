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


use HeimrichHannot\WatchlistBundle\FrontendFramework\WatchlistFrameworkInterface;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use Twig\Error\LoaderError;

abstract class AbstractPartialTemplate implements PartialTemplateInterface
{
    const TEMPLATE_WATCHLIST_WINDOW      = 'watchlist_window';
    const TEMPLATE_WATCHLIST_ITEM        = 'watchlist_item';
    const TEMPLATE_OPEN_WATCHLIST_WINDOW = 'open_watchlist_window';
    const TEMPLATE_ADD_TO_WATCHLIST      = 'add_to_watchlist';
    const TEMPLATE_ACTION                = 'watchlist_action';

    const ACTION_TYPE_TOGGLE = 'toggle';
    const ACTION_TYPE_UPDATE = 'update';
    const ACTION_TYPE_DOWNLOAD = 'download';
    const ACTION_TYPE_NONE   = 'none';

    /**
     * @var PartialTemplateBuilder
     */
    protected $builder;

    /**
     * Return the template name without framework suffix and file extension,
     * e.g. 'watchlist_window'
     *
     * @return string
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
     * @return string
     * @throws \Twig_Error_Loader
     */
    protected function getTemplate(WatchlistFrameworkInterface $frontendFramework): string
    {
        try
        {
            $template = $this->builder->getContainer()->get('huh.utils.template')->getTemplate(
                $frontendFramework->getTemplate($this->getTemplateName()),
                $frontendFramework->getTemplateFormat()
            );
        } catch (LoaderError $e)
        {
            $template = $this->builder->getContainer()->get('huh.utils.template')->getTemplate(
                $this->builder->getFrameworksManager()->getFrameworkByType('base')->getTemplate($this->getTemplateName()),
                $this->builder->getFrameworksManager()->getFrameworkByType('base')->getTemplateFormat()
            );
        }
        return $template;
    }

    /**
     * Create the data attributes array with default setup
     *
     * @param WatchlistConfigModel $configuration
     * @param string $actionUrl
     * @param string $actionType
     * @return array
     */
    protected function createDefaultActionAttributes(WatchlistConfigModel $configuration, string $actionUrl, string $actionType): array
    {
        return [
            'action'          => $this->getTemplateName(),
            'watchlistConfig' => $configuration->id,
            'requestToken'    => $this->builder->getCsrfToken(),
            'actionUrl'       => $actionUrl,
            'actionType'      => $actionType,
            'frontend'        => $this->builder->getFrontendFramework($configuration)->getType(),
        ];
    }

    /**
     * Create the context array with default setup
     *
     * @param array $dataAttributes
     * @param WatchlistModel|null $watchlistModel
     * @return array
     */
    protected function createDefaultActionContext(array $dataAttributes, ?WatchlistModel $watchlistModel = null)
    {
        $context                   = [];
        $context['dataAttributes'] = $this->generateDataAttributes($dataAttributes);
        $context['cssClass']       = 'huh_watchlist_action';
        $context['cssCountClass']  = 'huh_watchlist_item_count';
        if ($watchlistModel)
        {
            $context['cssClass']      .= ' watchlist-' . $watchlistModel->id;
            $context['cssCountClass'] .= ' watchlist-' . $watchlistModel->id;
        }
        return $context;
    }

    /**
     * Generate a data-attributes-string out of an array
     *
     * @param array $attributes
     * @return string
     */
    protected function generateDataAttributes(array $attributes): string
    {
        $dataAttributes = '';
        array_walk($attributes, function ($value, $key) use (&$dataAttributes) {
            $key            = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $key));
            $dataAttributes .= 'data-'.$key.'="'.$value.'" ';
        });
        return $dataAttributes;
    }

    /**
     * Prepare the content data.
     *
     * @param array $context
     * @return array
     */
    protected function prepareContext(array $context): array
    {
        $context['id'] = $this->getTemplateName() . '_' . rand(0, 99999);
        return $context;
    }
}