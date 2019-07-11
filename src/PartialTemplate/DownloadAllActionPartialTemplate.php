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

    public function getTemplateName(): string
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

        $context              = $this->createDefaultActionContext($dataAttributes, $this->configuration, $this->watchlist);
        $context['id']        = '';
        $context['cssClass'] .= ' huh_watchlist_download_all';
        $context['linkText']  = $this->builder->getTranslator()->trans('huh.watchlist.list.download.text');
        $context['linkTitle'] = $this->builder->getTranslator()->trans('huh.watchlist.list.download.title');

        $watchlistFramework = $this->builder->getFrontendFramework($this->configuration);
        $context            = $watchlistFramework->prepareContext($context, $this);
        $template           = $this->getTemplate($watchlistFramework);
        return $this->builder->getTwig()->render($template, $context);
    }
}