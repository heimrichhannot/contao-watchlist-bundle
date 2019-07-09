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
use HeimrichHannot\WatchlistBundle\Manager\AjaxManager;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\PartialTemplate\AddToWatchlistPartialTemplate;
use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HookListener
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var PartialTemplateBuilder
     */
    private $templateBuilder;
    /**
     * @var WatchlistManager
     */
    private $watchlistManager;

    public function __construct(ContainerInterface $container, PartialTemplateBuilder $templateBuilder, WatchlistManager $watchlistManager)
    {
        $this->container = $container;
        $this->templateBuilder = $templateBuilder;
        $this->watchlistManager = $watchlistManager;
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
                /** @var WatchlistConfigModel $configuration */
                $configuration = WatchlistConfigModel::findAll()->current();
                $watchlist = $this->watchlistManager->getWatchlistModel($configuration);
                $template->addToWatchlistButton = ['html' => ''];
                $fileModel = FilesModel::findByPath($template->singleSRC);
                if ($fileModel)
                {
                    $data                           = $template->getData();
                    $data['watchlistTitle']         = isset($data['link']) ? $data['link'] : (isset($data['title']) ? $data['title'] : $data['name']);
                    $data['uuid']                   = $fileModel->uuid;
                    $template->addToWatchlistButton = [
                        'html' => $this->templateBuilder->generate(new AddToWatchlistPartialTemplate(
                            $configuration,
                            $data,
                            'tl_content'
                        ))
                    ];


//                        [
//                        'html' => $this->container->get('huh.watchlist.template_manager')->generateAddToWatchlistButtonForContentElement(
//                            $template->getData(),
//                            $fileModel->uuid
//                        )
//                    ];
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
                    $entryData['watchlistTitle'] = isset($file['link']) ? $file['link'] : (isset($file['title']) ?  $file['title'] : $file['name']);
                    $entryData['uuid'] = $file['uuid'];
                    /** @var WatchlistConfigModel $configuration */
                    $configuration = WatchlistConfigModel::findAll()->current();
                    $button = $this->templateBuilder->generate(new AddToWatchlistPartialTemplate(
                        $configuration,
                        $entryData,
                        'tl_content'
                    ));
//                    $button = $this->container->get('huh.watchlist.template_manager')->generateAddToWatchlistButtonForContentElement(
//                        $entryData,
//                        $file['uuid']
//                    );
                    $file['addToWatchlistButton']['html'] = $button;
                    $files[] = $file;
                }
                $template->files = $files;
                break;
        }
        return;
    }
}
