<?php

namespace HeimrichHannot\WatchlistBundle\ListItem;


use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ListBundle\Item\DefaultItem;

class WatchlistDownloadListItem extends DefaultItem
{
    /**
     * {@inheritdoc}
     */
    public function setRaw(array $data = []): void
    {
        if($data['ptable'] && $data['ptableId'] && (null !== $item = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($data['ptable'], $data['ptableId']))) {
            $data = $item->row();
            $data['uuid'] = StringUtil::deserialize($data['file'], true)[0];
        }

        parent::setRaw($data);
    }
}