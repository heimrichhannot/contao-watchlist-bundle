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
use HeimrichHannot\WatchlistBundle\Event\WatchlistPrepareElementEvent;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistManager;
use HeimrichHannot\WatchlistBundle\PartialTemplate\AddToWatchlistActionPartialTemplate;
use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateBuilder;

class WatchlistPrepareElementListener
{
    /**
     * @var WatchlistManager
     */
    private $watchlistManager;
    /**
     * @var PartialTemplateBuilder
     */
    private $templateBuilder;


    /**
     * WatchlistPrepareElementListener constructor.
     */
    public function __construct(WatchlistManager $watchlistManager, PartialTemplateBuilder $templateBuilder)
    {
        $this->watchlistManager = $watchlistManager;
        $this->templateBuilder = $templateBuilder;
    }

    public function onHuhWatchlistEventPrepareElement(WatchlistPrepareElementEvent $event)
    {
        $template = $event->getTemplate();
        $configuration = $event->getConfiguration();

        switch ($template->type)
        {
            case 'download':
                $template->addToWatchlistButton = ['html' => ''];
                $fileModel = FilesModel::findByPath($template->singleSRC);
                if ($fileModel)
                {
                    $template->addToWatchlistButton = [
                        'html' => $this->templateBuilder->generate(new AddToWatchlistActionPartialTemplate(
                            $configuration,
                            'tl_content',
                            $fileModel->uuid,
                            $template->link ?: ($template->title ?: $template->name),
                            $template->options ?: []
                        ))
                    ];
                }
                $event->stopPropagation();
                return;
            case 'downloads':
                if (empty($template->files)) {
                    break;
                }
                $files = [];
                foreach ($template->files as $file)
                {
                    $button = $this->templateBuilder->generate(new AddToWatchlistActionPartialTemplate(
                        $configuration,
                        'tl_content',
                        $file['uuid'],
                        isset($file['link']) ? $file['link'] : (isset($file['title']) ?  $file['title'] : $file['name']),
                        $template->options ?: []
                    ));
                    $file['addToWatchlistButton']['html'] = $button;
                    $files[] = $file;
                }
                $template->files = $files;
                $event->stopPropagation();
                return;
        }
        return;
    }
}