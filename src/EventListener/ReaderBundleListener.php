<?php

namespace HeimrichHannot\WatchlistBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use HeimrichHannot\ReaderBundle\Backend\ReaderConfig;
use HeimrichHannot\ReaderBundle\HeimrichHannotContaoReaderBundle;

class ReaderBundleListener
{
    #[AsHook('loadDataContainer')]
    public function onLoadDataContainer(string $table): void
    {
        if ('tl_reader_config' !== $table || !class_exists(HeimrichHannotContaoReaderBundle::class)) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_reader_config'];

        PaletteManipulator::create()
            ->addField('actAsWatchlistShareTarget', 'misc_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', 'tl_reader_config');

        $dca['fields']['actAsWatchlistShareTarget'] = [
            'label'     => &$GLOBALS['TL_LANG']['tl_reader_config']['actAsWatchlistShareTarget'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ];

        ReaderConfig::addOverridableFields();
    }
}