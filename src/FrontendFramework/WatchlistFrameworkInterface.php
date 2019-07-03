<?php


namespace HeimrichHannot\WatchlistBundle\FrontendFramework;


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
     * Returns the twig template path.
     *
     * @return string
     */
    public function getWindowTemplate(): string;

    public function compile(array $context): array;

}