<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\WatchlistBundle\EventListener;

use Contao\FilesModel;
use Contao\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HookListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onGetPageLayout()
    {
        // Register and check for ajax actions
        $this->container->get('huh.watchlist.ajax_manager')->ajaxActions();
    }

    /**
     * Hook: parseTemplate
     *
     * @param Template $template
     */
    public function onParseTemplate(Template $template)
    {
        switch ($template->type)
        {
            case 'download':
                $template->addToWatchlistButton = ['html' => ''];
                $fileModel = FilesModel::findByPath($template->singleSRC);
                if ($fileModel)
                {
                    $template->addToWatchlistButton = [
                        'html' => $this->container->get('huh.watchlist.template_manager')->generateAddToWatchlistButtonForContentElement($template->getData(), $fileModel->uuid)
                    ];
                }
                break;
            case 'downloads':
                if (empty($template->files)) {
                    break;
                }
                $entryData = $template->getData();
                $files = [];
                foreach ($template->files as $file)
                {
                    $entryData['title'] = isset($file['link']) ? $file['link'] : (isset($file['title']) ?  $file['title'] : $file['name']);
                    $button = $this->container->get('huh.watchlist.template_manager')->generateAddToWatchlistButtonForContentElement($entryData, $file['uuid']);
                    $file['addToWatchlistButton']['html'] = $button;
                    $files[] = $file;
                }
                $template->files = $files;
                break;
        }
        return;
    }
}