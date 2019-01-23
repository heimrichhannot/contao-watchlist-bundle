<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\ListItem;

use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ListBundle\Item\DefaultItem;

class WatchlistDownloadListItem extends DefaultItem
{
    public function getDownloadLink()
    {
        $filesModel = System::getContainer()->get('contao.framework')->getAdapter(FilesModel::class)->findByUuid($this->_raw['uuid']);

        return System::getContainer()->get('huh.utils.url')->addQueryString('file='.$filesModel->path);
    }

    public function getImage()
    {
        $image = [];

        $filesModel = System::getContainer()->get('contao.framework')->getAdapter(FilesModel::class)->findByUuid($this->_raw['uuid']);

        $imageArray['imageTitle'] = $this->_raw['title'];
        $imageArray['image'] = $filesModel->path;

        // Override the default image size
        if ('' !== ($rawSize = $this->getModule()['imgSize'])) {
            $size = StringUtil::deserialize($rawSize, true);

            if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                $imageArray['size'] = $rawSize;
            }
        }

        System::getContainer()->get('huh.utils.image')->addToTemplateData('image', 'imageSelectorField', $image, $imageArray, null, null, null, $filesModel);

        return $image;
    }
}
