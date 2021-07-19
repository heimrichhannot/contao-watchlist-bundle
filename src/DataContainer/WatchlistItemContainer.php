<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\DataContainer;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\UtilsBundle\Choice\DataContainerChoice;
use HeimrichHannot\UtilsBundle\Choice\ModelInstanceChoice;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class WatchlistItemContainer
{
    const TYPE_FILE = 'file';
    const TYPE_ENTITY = 'entity';

    const TYPES = [
        self::TYPE_FILE,
        self::TYPE_ENTITY,
    ];

    protected ContaoFramework     $framework;
    protected DcaUtil             $dcaUtil;
    protected DataContainerChoice $dataContainerChoice;
    protected ModelUtil           $modelUtil;
    protected ModelInstanceChoice $modelInstanceChoice;
    protected FileUtil            $fileUtil;

    public function __construct(ContaoFramework $framework, DataContainerChoice $dataContainerChoice, ModelInstanceChoice $modelInstanceChoice, DcaUtil $dcaUtil, ModelUtil $modelUtil, FileUtil $fileUtil)
    {
        $this->framework = $framework;
        $this->dataContainerChoice = $dataContainerChoice;
        $this->modelInstanceChoice = $modelInstanceChoice;
        $this->dcaUtil = $dcaUtil;
        $this->modelUtil = $modelUtil;
        $this->fileUtil = $fileUtil;
    }

    /**
     * @Callback(table="tl_watchlist_item", target="list.sorting.child_record")
     */
    public function listChildren(array $row)
    {
        $label = $row['title'];

        switch ($row['type']) {
            case static::TYPE_FILE:
                if (null !== ($path = $this->fileUtil->getPathFromUuid($row['file']))) {
                    $label .= " ($path)";
                }

                break;

            case static::TYPE_ENTITY:
                $this->framework->getAdapter(System::class)->loadLanguageFile($row['entityTable']);

                if (null !== ($entity = $this->modelUtil->findOneModelInstanceBy($row['entityTable'], [$row['entityTable'].'.id=?'], [$row['entity']]))) {
                    foreach (ModelInstanceChoice::TITLE_FIELDS as $titleField) {
                        if (isset($GLOBALS['TL_DCA'][$row['entityTable']]['fields'][$titleField])) {
                            $label .= ' ('.($entity->{$titleField} ? $entity->{$titleField}.', ' : '').'ID '.$entity->id.')';

                            break;
                        }
                    }
                }

                break;
        }

        return '<div class="tl_content_left">'.$label.' <span style="color:#b3b3b3; padding-left:3px">['.
            \Date::parse(\Contao\Config::get('datimFormat'), trim($row['dateAdded'])).']</span></div>';
    }

    /**
     * @Callback(table="tl_watchlist_item", target="config.onsubmit")
     */
    public function setDateAdded(DataContainer $dc)
    {
        $this->dcaUtil->setDateAdded($dc);
    }

    /**
     * @Callback(table="tl_watchlist_item", target="config.oncopy")
     */
    public function setDateAddedOnCopy($insertId, DataContainer $dc)
    {
        $this->dcaUtil->setDateAddedOnCopy($insertId, $dc);
    }

    /**
     * @Callback(table="tl_watchlist_item", target="fields.entityTable.options")
     */
    public function getDataContainers()
    {
        return $this->dataContainerChoice->getCachedChoices();
    }

    /**
     * @Callback(table="tl_watchlist_item", target="fields.entity.options")
     */
    public function getEntities(DataContainer $dc)
    {
        if (null === ($item = $this->modelUtil->findModelInstanceByPk('tl_watchlist_item', $dc->id)) || !$item->entityTable) {
            return [];
        }

        return $this->modelInstanceChoice->getChoices([
            'dataContainer' => $item->entityTable,
        ]);
    }
}
