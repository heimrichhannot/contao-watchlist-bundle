<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_watchlist_item'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ptable' => 'tl_watchlist',
        'enableVersioning' => false,
        'onload_callback' => [
            ['tl_watchlist_item', 'checkPermission'],
        ],
        'onsubmit_callback' => [
            [\HeimrichHannot\UtilsBundle\Dca\DcaUtil::class, 'setDateAdded'],
        ],
        'oncopy_callback' => [
            [\HeimrichHannot\UtilsBundle\Dca\DcaUtil::class, 'setDateAddedOnCopy'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',            ],
        ],
    ],
    'list' => [
        'label' => [
            'fields' => ['id'],
            'format' => '%s',
        ],
        'sorting' => [
            'mode' => 1,
            'fields' => ['dateAdded'],
            'panelLayout' => 'filter;sort,search,limit',
            'child_record_callback' => ['tl_watchlist_item', 'listChildren'],
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => [],
        'default' => '',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_watchlist.id',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'tstamp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['tstamp'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag' => 6,
            'eval' => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
    ],
];

class tl_watchlist_item extends \Contao\Backend
{
    public function listChildren($arrRow)
    {
        return '<div class="tl_content_left">'.($arrRow['title'] ?: $arrRow['id']).' <span style="color:#b3b3b3; padding-left:3px">['.
                \Date::parse(\Contao\Config::get('datimFormat'), trim($arrRow['dateAdded'])).']</span></div>';
    }

    public function checkPermission()
    {
        $user = \Contao\BackendUser::getInstance();
        $database = \Contao\Database::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Set the root IDs
        if (!is_array($user->contao_watchlist_bundles) || empty($user->contao_watchlist_bundles)) {
            $root = [0];
        } else {
            $root = $user->contao_watchlist_bundles;
        }

        $id = strlen(\Contao\Input::get('id')) ? \Contao\Input::get('id') : CURRENT_ID;

        // Check current action
        switch (\Contao\Input::get('act')) {
            case 'paste':
            // Allow
            break;

            case 'create':
            if (!strlen(\Contao\Input::get('pid')) || !in_array(\Contao\Input::get('pid'), $root)) {
                throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to create watchlist_item items in watchlist_item archive ID '.\Contao\Input::get('pid').'.');
            }

            break;

            case 'cut':
            case 'copy':
            if (!in_array(\Contao\Input::get('pid'), $root)) {
                throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to '.\Contao\Input::get('act').' watchlist_item item ID '.$id.' to watchlist_item archive ID '.\Contao\Input::get('pid').'.');
            }
            // no break STATEMENT HERE

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
            $objArchive = $database->prepare('SELECT pid FROM tl_watchlist_item WHERE id=?')
            ->limit(1)
            ->execute($id);

            if ($objArchive->numRows < 1) {
                throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid watchlist_item item ID '.$id.'.');
            }

            if (!in_array($objArchive->pid, $root)) {
                throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to '.\Contao\Input::get('act').' watchlist_item item ID '.$id.' of watchlist_item archive ID '.$objArchive->pid.'.');
            }

            break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
            if (!in_array($id, $root)) {
                throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access watchlist_item archive ID '.$id.'.');
            }

            $objArchive = $database->prepare('SELECT id FROM tl_watchlist_item WHERE pid=?')
            ->execute($id);

            if ($objArchive->numRows < 1) {
                throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid watchlist_item archive ID '.$id.'.');
            }

            /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session */
            $session = \Contao\System::getContainer()->get('session');

            $sessionData = $session->all();
            $sessionData['CURRENT']['IDS'] = array_intersect($sessionData['CURRENT']['IDS'], $objArchive->fetchEach('id'));
            $session->replace($sessionData);

            break;

            default:
            if (strlen(\Contao\Input::get('act'))) {
                throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid command "'.\Contao\Input::get('act').'".');
            } elseif (!in_array($id, $root)) {
                throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access watchlist_item archive ID '.$id.'.');
            }

            break;
        }
    }
}
