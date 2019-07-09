<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\DataContainer;

use Contao\DataContainer;
use HeimrichHannot\Submissions\Creator\SubmissionCreator;
use HeimrichHannot\WatchlistBundle\Module\ModuleWatchlist;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ModuleContainer
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getFormConfigModules(DataContainer $dc)
    {
        $options = [];

        if (null === ($modules = $this->container->get('huh.utils.model')->findModelInstancesBy('tl_module', ['tl_module.type=?'], [SubmissionCreator::MODULE_SUBMISSION_READER]))) {
            return $options;
        }

        foreach ($modules as $module) {
            $options[$module->id] = $module->name;
        }

        return $options;
    }

    public function getModuleId()
    {
        return $this->getDataValue('moduleId');
    }

    public function getWatchlistId()
    {
        return $this->getDataValue('watchlistId');
    }

    public function getDataValue(string $field)
    {
        $data = $this->getData();

        if (!$data->{$field}) {
            return null;
        }

        return $data->{$field};
    }

    public function getData()
    {
        $data = null;

        if (null === ($post = $this->container->get('huh.request')->getPost('data'))) {
            return $data;
        }

        return json_decode($post);
    }

    public function getWatchlistModules()
    {

        if (null === ($modules = $this->container->get('huh.utils.model')->findModelInstancesBy(
            'tl_module', ['tl_module.type=?'], [ModuleWatchlist::MODULE_WATCHLIST]
        ))) {
            return [];
        }

        $options = [];
        while ($modules->next()) {
            $options[$modules->id] = $modules->name;
        }

        return $options;
    }

    /**
     * Get frontend framework types as selection
     *
     * @return array
     */
    public function getWatchlistFrontendFrameworks()
    {
        $frameworks = $this->container->get('huh.watchlist.manager.frontend_frameworks')->getAllFrameworks();
        return array_keys($frameworks);
    }

    /**
     * Get frontend framework types as selection
     *
     * @param DataContainer $dc
     * @return string
     */
    public function editWatchlistWizard(DataContainer $dc)
    {
        return '';
//        return ($dc->value < 1)
//            ? ''
//            : ' <a href="contao?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $dc->value . '&amp;rt=' . REQUEST_TOKEN . '" title="'
//            . sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $dc->value) . '" style="padding-left:3px">'
//            . $this->generateImage('alias.gif', $GLOBALS['TL_LANG']['tl_content']['editalias'][0], 'style="vertical-align:top"') . '</a>';
    }

//    public function editModule(\DataContainer $dc)
//    {
//        return ($dc->value < 1)
//            ? ''
//            : ' <a href="contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $dc->value . '&amp;rt=' . REQUEST_TOKEN . '" title="'
//            . sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $dc->value) . '" style="padding-left:3px">'
//            . $this->generateImage('alias.gif', $GLOBALS['TL_LANG']['tl_content']['editalias'][0], 'style="vertical-align:top"') . '</a>';
//    }
}