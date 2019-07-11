<?php


namespace HeimrichHannot\WatchlistBundle\FrontendFramework;


use Contao\Module;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateInterface;

interface WatchlistFrameworkInterface
{
    /**
     * Return an alias for the framework. Example bs4 for Bootstrap 4.
     * Only lowercase letters a-z, numbers and underscores are allowed (regexp: ^[a-z0-9_]+$).
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Return the twig template format, e.g. 'html.twig'
     *
     * @return string
     */
    public function getTemplateFormat(): string;

    /**
     * Returns the twig template name for the given action.
     *
     * Override this method for custom template names.
     *
     * @param string $action
     * @return string
     */
    public function getTemplate(string $action): string;

    /**
     * Add or edit the module template variables
     *
     * @param array $context
     * @param Module $template
     * @return array
     */
    public function prepareModuleTemplate(array $context, Module $template): array;

    /**
     * Add or edit data attributes
     *
     * @param array $attributes
     * @param PartialTemplateInterface $template
     * @return array
     */
    public function prepareDataAttributes(array $attributes, PartialTemplateInterface $template): array;

    /**
     * Prepare the template context.
     *
     * @param array $context
     * @param PartialTemplateInterface $template
     * @return array
     */
    public function prepareContext(array $context, PartialTemplateInterface $template): array;
}