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


abstract class AbstractPartialTemplate implements PartialTemplateInterface
{
    /**
     * @var PartialTemplateBuilder
     */
    protected $builder;

    abstract public function generate(): string;

    public function setBuilder(PartialTemplateBuilder $builder)
    {
        $this->builder = $builder;
    }
}