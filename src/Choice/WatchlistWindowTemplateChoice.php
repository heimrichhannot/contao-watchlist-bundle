<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\WatchlistBundle\Choice;


use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WatchlistWindowTemplateChoice extends AbstractChoice
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container->get('contao.framework'));
        $this->container = $container;
    }


    /**
     * @return array
     */
    protected function collect()
    {
        $templates = $this->container->get('huh.utils.template')->getTemplateGroup('watchlist_window');
        return $templates;
    }
}