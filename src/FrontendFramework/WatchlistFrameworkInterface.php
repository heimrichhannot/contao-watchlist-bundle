<?php


namespace HeimrichHannot\WatchlistBundle\FrontendFramework;


use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;

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
     * Prepare the template.
     *
     * @param array $context
     * @return array
     */
    public function compile(array $context): array;
}