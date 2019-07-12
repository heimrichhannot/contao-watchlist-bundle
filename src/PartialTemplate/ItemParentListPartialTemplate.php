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


use Contao\Controller;
use Contao\Environment;
use Contao\PageModel;
use Contao\StringUtil;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;

class ItemParentListPartialTemplate extends AbstractPartialTemplate
{
    /**
     * @var PageModel
     */
    private $page;
    /**
     * @var WatchlistConfigModel
     */
    private $configuration;

    /**
     * ItemParentListPartialTemplate constructor.
     */
    public function __construct(WatchlistConfigModel $configuration, PageModel $page)
    {
        $this->page = $page;
        $this->configuration = $configuration;
    }


    /**
     * Return the template name without framework suffix and file extension,
     * e.g. 'watchlist_window'
     *
     * @return string
     */
    public function getTemplateName(): string
    {
        return static::TEMPLATE_ITEM_PARENT_LIST;
    }

    /**
     * Generate the template
     *
     * @return string
     */
    public function generate(): string
    {
        $type   = null;
        $pageId = $this->page->id;
        $pages  = [$this->page->row()];
        $items  = [];

        // Get all pages up to the root page
        $objPages = PageModel::findParentsById($this->page->pid);

        if ($objPages !== null) {
            while ($pageId > 0 && $type != 'root' && $objPages->next()) {
                $type    = $objPages->type;
                $pageId  = $objPages->pid;
                $pages[] = $objPages->row();
            }
        }

        // Get the first active regular page and display it instead of the root page
        if ($type == 'root') {
            $objFirstPage = PageModel::findFirstPublishedByPid($objPages->id);

            $items[] = array
            (
                'isRoot'   => true,
                'isActive' => false,
                'href'     => (($objFirstPage !== null) ? Controller::generateFrontendUrl($objFirstPage->row()) : Environment::get('base')),
                'title'    => StringUtil::specialchars($objPages->pageTitle ? : $objPages->title, true),
                'link'     => $objPages->title,
                'data'     => $objFirstPage->row(),
                'class'    => ''
            );

            array_pop($pages);
        }

        // Build the breadcrumb menu
        for ($i = (count($pages) - 1); $i > 0; $i--) {
            if (($pages[$i]['hide'] && !$this->showHidden) || (!$pages[$i]['published'] && !BE_USER_LOGGED_IN)) {
                continue;
            }

            // Get href
            switch ($pages[$i]['type']) {
                case 'redirect':
                    $href = $pages[$i]['url'];

                    if (strncasecmp($href, 'mailto:', 7) === 0) {
                        $href = StringUtil::encodeEmail($href);
                    }
                    break;
                case 'forward':
                    $objNext = PageModel::findPublishedById($pages[$i]['jumpTo']);

                    if ($objNext !== null) {
                        $href = Controller::generateFrontendUrl($objNext->row());
                        break;
                    }
                // DO NOT ADD A break; STATEMENT

                default:
                    $href = Controller::generateFrontendUrl($pages[$i]);
                    break;
            }

            $items[] = array
            (
                'isRoot'   => false,
                'isActive' => false,
                'href'     => $href,
                'title'    => StringUtil::specialchars($pages[$i]['pageTitle'] ? : $pages[$i]['title'], true),
                'link'     => $pages[$i]['title'],
                'data'     => $pages[$i],
                'class'    => ''
            );
        }

        // Active page
        $items[] = array
        (
            'isRoot'   => false,
            'isActive' => true,
            'href'     => Controller::generateFrontendUrl($pages[0]),
            'title'    => StringUtil::specialchars($pages[0]['pageTitle'] ? : $pages[0]['title']),
            'link'     => $pages[0]['title'],
            'data'     => $pages[0],
            'class'    => 'last'
        );

        $items[0]['class'] = 'first';

        $context = [];
        $context['items'] = $items;

        $template           = $this->getTemplate($this->builder->getFrontendFramework($this->configuration));
        return $this->builder->getTwig()->render($template, $context);
    }
}