<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\DataContainer;

use Contao\System;
use HeimrichHannot\Submissions\Creator\SubmissionCreator;

class ModuleContainer
{
    public function getFromConfigModules()
    {
        $options = [];

        if (null === ($modules = System::getContainer()->get('huh.utils.model')->findModelInstancesBy('tl_module', ['tl_module.type=?'], [SubmissionCreator::MODULE_SUBMISSION_READER]))) {
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

        if (null === ($post = System::getContainer()->get('huh.request')->getPost('data'))) {
            return $data;
        }

        return json_decode($post);
    }
}
