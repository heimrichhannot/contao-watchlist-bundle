<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\WatchlistBundle\PartialTemplate;


use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;

class DownloadAllActionPartialTemplate extends AbstractPartialTemplate
{
    /**
     * @var WatchlistConfigModel
     */
    private $configuration;
    /**
     * @var WatchlistModel
     */
    private $watchlist;


    /**
     * DownloadAllPartialTemplate constructor.
     */
    public function __construct(WatchlistConfigModel $configuration, WatchlistModel $watchlist)
    {
        $this->configuration = $configuration;
        $this->watchlist = $watchlist;
    }

    public function getTemplateType(): string
    {
        return static::TEMPLATE_ACTION;
    }

    /**
     * Generate the template
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig_Error_Loader
     */
    public function generate(): string
    {
        $url = $this->builder->getRouter()->generate('huh_watchlist_download_all');

        $dataAttributes              = $this->createDefaultActionAttributes($this->configuration, $url, static::ACTION_TYPE_DOWNLOAD);
        $dataAttributes['watchlist'] = $this->watchlist->id;

        $context              = $this->createDefaultActionContext($dataAttributes, $this->watchlist);
        $context['id']        = '';
        $context['linkText']  = $this->builder->getTranslator()->trans('huh.watchlist.list.download.text');
        $context['linkTitle'] = $this->builder->getTranslator()->trans('huh.watchlist.list.download.title');

        $template = $this->getTemplate($this->builder->getFrontendFramework($this->configuration));
        return $this->builder->getTwig()->render($template, $context);
//
//
//        $url = $this->getOriginalRouteIfAjaxRequest();
//        $downloadAllTemplate = new FrontendTemplate('watchlist_download_all_action');
//        $downloadAllTemplate->action = $this->container->get('huh.ajax.action')->generateUrl(
//            AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_DOWNLOAD_ALL_ACTION, [], true, $url
//        );
//        $downloadAllTemplate->downloadAllLink = $this->builder->getTranslator()->trans('huh.watchlist.list.download.text');
//        $downloadAllTemplate->downloadAllTitle = $this->translator->trans('huh.watchlist.list.download.title');
//        $downloadAllTemplate->watchlistId = $watchlistId;
//        $downloadAllTemplate->moduleId = $configuration->id;
//
//        return $downloadAllTemplate->parse();
    }
}