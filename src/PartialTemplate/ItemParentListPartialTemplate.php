<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
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
     * e.g. 'watchlist_window'.
     */
    public function getTemplateName(): string
    {
        return static::TEMPLATE_ITEM_PARENT_LIST;
    }

    /**
     * Generate the template.
     */
    public function generate(): string
    {
        $type = null;
        $pageId = $this->page->id;
        $pages = [$this->page->row()];
        $items = [];

        // Get all pages up to the root page
        $objPages = PageModel::findParentsById($this->page->pid);

        if (null !== $objPages) {
            while ($pageId > 0 && 'root' != $type && $objPages->next()) {
                $type = $objPages->type;
                $pageId = $objPages->pid;
                $pages[] = $objPages->row();
            }
        }

        // Get the first active regular page and display it instead of the root page
        if ('root' == $type) {
            $objFirstPage = PageModel::findFirstPublishedByPid($objPages->id);

            $items[] = [
                'isRoot' => true,
                'isActive' => false,
                'href' => ((null !== $objFirstPage) ? Controller::generateFrontendUrl($objFirstPage->row()) : Environment::get('base')),
                'title' => StringUtil::specialchars($objPages->pageTitle ?: $objPages->title, true),
                'link' => $objPages->title,
                'data' => $objFirstPage->row(),
                'class' => '',
            ];

            array_pop($pages);
        }

        // Build the breadcrumb menu
        for ($i = (\count($pages) - 1); $i > 0; --$i) {
            if (($pages[$i]['hide'] && !$this->showHidden) || (!$pages[$i]['published'] && !BE_USER_LOGGED_IN)) {
                continue;
            }

            // Get href
            switch ($pages[$i]['type']) {
                case 'redirect':
                    $href = $pages[$i]['url'];

                    if (0 === strncasecmp($href, 'mailto:', 7)) {
                        $href = StringUtil::encodeEmail($href);
                    }
                    break;
                case 'forward':
                    $objNext = PageModel::findPublishedById($pages[$i]['jumpTo']);

                    if (null !== $objNext) {
                        $href = Controller::generateFrontendUrl($objNext->row());
                        break;
                    }
                // DO NOT ADD A break; STATEMENT

                // no break
                default:
                    $href = Controller::generateFrontendUrl($pages[$i]);
                    break;
            }

            $items[] = [
                'isRoot' => false,
                'isActive' => false,
                'href' => $href,
                'title' => StringUtil::specialchars($pages[$i]['pageTitle'] ?: $pages[$i]['title'], true),
                'link' => $pages[$i]['title'],
                'data' => $pages[$i],
                'class' => '',
            ];
        }

        // Active page
        $items[] = [
            'isRoot' => false,
            'isActive' => true,
            'href' => Controller::generateFrontendUrl($pages[0]),
            'title' => StringUtil::specialchars($pages[0]['pageTitle'] ?: $pages[0]['title']),
            'link' => $pages[0]['title'],
            'data' => $pages[0],
            'class' => 'last',
        ];

        $items[0]['class'] = 'first';

        $context = [];
        $context['items'] = $items;

        $template = $this->getTemplate($this->builder->getFrontendFramework($this->configuration));

        return $this->builder->getTwig()->render($template, $context);
    }
}
