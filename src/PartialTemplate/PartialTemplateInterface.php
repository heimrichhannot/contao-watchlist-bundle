<?php


namespace HeimrichHannot\WatchlistBundle\PartialTemplate;


interface PartialTemplateInterface
{
    /**
     * Generate the template
     *
     * @return string
     */
    public function generate(): string;

    /**
     * Set the template builder to get the dependencies from the getter methods.
     *
     * @param PartialTemplateBuilder $builder
     * @return mixed
     */
    public function setBuilder(PartialTemplateBuilder $builder);
}