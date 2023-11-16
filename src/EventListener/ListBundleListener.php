<?php

namespace HeimrichHannot\WatchlistBundle\EventListener;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\ListBundle\Backend\ListConfig;
use HeimrichHannot\ListBundle\HeimrichHannotContaoListBundle;

class ListBundleListener
{
    /**
     * @Hook("loadDataContainer")
     */
    public function onLoadDataContainer(string $table): void
    {
        if ('tl_list_config' !== $table || !class_exists(HeimrichHannotContaoListBundle::class)) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_list_config'];

        PaletteManipulator::create()
            ->addField('actAsWatchlistShareTarget', 'misc_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', 'tl_list_config');

        $dca['fields']['actAsWatchlistShareTarget'] = [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['actAsWatchlistShareTarget'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ];

        ListConfig::addOverridableFields();
    }
}