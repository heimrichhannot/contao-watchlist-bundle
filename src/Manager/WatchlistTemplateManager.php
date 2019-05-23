<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Model;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\Ajax\Response\ResponseError;
use HeimrichHannot\WatchlistBundle\Manager\AjaxManager;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistManager;
use Psr\Cache\InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Error\LoaderError;

class WatchlistTemplateManager
{
    const WATCHLIST_SELECT_WATCHLIST_OPTIONS = 'watchlist-options';
    const WATCHLIST_SELECT_ITEM_OPTIONS = 'item-options';
    const WATCHLIST_NAME_SUBMISSION = 'WATCHLIST_SESSION_BE';

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var TranslatorInterface
     */
    protected $translator;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container, TranslatorInterface $translator)
    {
        $this->framework = $container->get('contao.framework');
        $this->translator = $translator;
        $this->container = $container;
    }

    /**
     * @param      $module
     * @param      $items
     * @param bool $grouped
     *
     * @return string
     */
    public function getWatchlist($module, $items, $watchlistId, $grouped = true)
    {
        $template = new FrontendTemplate('watchlist');

        $preparedWatchlistItems = [];
        if (!empty($items)) {
            $preparedWatchlistItems = $this->prepareWatchlistItems($items, $module, $grouped);
        }

        if (!empty($preparedWatchlistItems['parents'])) {
            $template->pids = array_keys($preparedWatchlistItems['parents']);
        }

        if (!empty($preparedWatchlistItems['items'])) {
            $template->items = $preparedWatchlistItems['items'];
        }

        // get download link action
        if (!empty($items) && $module->useDownloadLink) {
            $template->actions = true;
            $template->downloadLinkAction = $this->getDownloadLinkAction($module, $watchlistId);
        }

        // get delete watchlist action
        if ($module->useMultipleWatchlist) {
            $template->actions = true;
            $template->deleteWatchlistAction = $this->getDeleteWatchlistAction($watchlistId, $module->id);

            $template->selectWatchlist =
                $this->getOptionsSelectTemplate(System::getContainer()->get('huh.watchlist.watchlist_manager')->getWatchlistOptions($module),
                    static::WATCHLIST_SELECT_WATCHLIST_OPTIONS, $watchlistId, $module->id, System::getContainer()
                        ->get('huh.ajax.action')
                        ->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_UPDATE_WATCHLIST_ACTION));
        } // get empty watchlist action
        elseif (!empty($items)) {
            $template->actions = true;
            $template->emptyWatchlistAction = $this->getEmptyWatchlistAction($watchlistId, $module->id);
        }

        // get download all action
        if (!$module->disableDownloadAll && count($preparedWatchlistItems) > 1) {
            $template->actions = true;
            $template->downloadAllAction = $this->getDownloadAllAction($watchlistId, $module->id);
        }

        if (empty($items)) {
            $template->empty = $this->translator->trans('huh.watchlist.empty_list');
        }

        $template->grouped = $grouped;

        return $template->parse();
    }

    /**
     * @param      $moduleId
     * @param null $watchlistId
     *
     * @return string
     * @throws InvalidArgumentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function getWatchlistWindow($moduleId, $watchlistId = null)
    {
        $module = $this->framework->getAdapter(ModuleModel::class)->findByPk($moduleId);
        $config = ['template' => $module->watchlistWindowTemplate];


        if (null === $module) {
            return $this->generateWatchlistWindow($GLOBALS['TL_LANG']['WATCHLIST']['empty'], $config);
        }

        $watchlistManager = $this->container->get('huh.watchlist.watchlist_manager');

        if (null === ($watchlist = $watchlistManager->getWatchlistModel($moduleId, $watchlistId))) {
            return $this->generateWatchlistWindow($GLOBALS['TL_LANG']['WATCHLIST']['empty'], $config);
        }

        // if no watchlistId is given overwrite by found watchlist
        $watchlistId = $watchlist->id;
        $watchlistItems = $watchlistManager->getCurrentWatchlistItems($module, $watchlist->id);

        $watchlistName = $watchlistManager->getWatchlistName($module, $watchlist);

        $config['headline'] = $watchlistName;
        $config['class'] = 'large';

        return $this->generateWatchlistWindow($this->getWatchlist($module, $watchlistItems, $watchlistId), $config);
    }

    /**
     * @param $watchlistId
     * @param $moduleId
     *
     * @return string
     */
    public function getDeleteWatchlistAction($watchlistId, $moduleId)
    {
        $template = new FrontendTemplate('watchlist_delete_watchlist_action');
        $template->watchlistId = $watchlistId;
        $template->moduleId = $moduleId;
        $template->action =
            System::getContainer()->get('huh.ajax.action')->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_DELETE_WATCHLIST_ACTION);
        $template->deleteWatchlistLink = $GLOBALS['TL_LANG']['WATCHLIST']['delWatchlistLink'];
        $template->deleteWatchlistTitle = $GLOBALS['TL_LANG']['WATCHLIST']['delWatchlistTitle'];

        return $template->parse();
    }

    /**
     * @param $watchlistId
     * @param $moduleId
     *
     * @return string
     */
    public function getEmptyWatchlistAction($watchlistId, $moduleId)
    {
        $template = new FrontendTemplate('watchlist_empty_watchlist_action');
        $template->watchlistId = $watchlistId;
        $template->moduleId = $moduleId;
        $template->action =
            System::getContainer()->get('huh.ajax.action')->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_EMPTY_WATCHLIST_ACTION);
        $template->emptyWatchlistLink = $this->translator->trans('huh.watchlist.empty_watchlist');
        $template->emptyWatchlistTitle = $this->translator->trans('huh.watchlist.remove_all_from_list');

        return $template->parse();
    }

    /**
     * @param      $items
     * @param bool $grouped
     * @param      $module
     *
     * @return array
     */
    public function prepareWatchlistItems($items, $module, $grouped)
    {
        $totalCount = $items->count();

        $parsedItems = [];
        $parents = [];

        foreach ($items as $key => $item) {
            $cssClass = trim((0 == $key ? 'first ' : '').($key == $totalCount ? 'last ' : '').(0 == ($key + 1) % 2 ? 'odd ' : 'even '));

            $parsedItem = $this->parseItem($item, $module, $cssClass);

            if ($grouped) {
                $parsedItems[$item->pageID]['page'] = $this->framework->getAdapter(PageModel::class)->findByPk($item->pageID)->title;
                $parsedItems[$item->pageID]['items'][$item->id] = $parsedItem;
            } else {
                $arrPids[$item->pageID] = $parents[$item->pageID];
                $parsedItems[$item->id] = $parsedItem;
            }
        }

        return ['items' => $parsedItems, 'parents' => $parents];
    }

    /**
     * @param int $moduleId
     * @param int $watchlistId
     *
     * @return string
     */
    public function getDownloadLinkAction($module, int $watchlistId)
    {
        $template = new FrontendTemplate('watchlist_downloadLink_action');
        $action = null;

        $template->moduleId = $module->id;
        $template->watchlistId = $watchlistId;
        $template->downloadLinkTitle = $this->translator->trans('huh.watchlist.download_link.title');

        if (!$module->downloadLinkUseNotification) {
            $action = System::getContainer()
                ->get('huh.ajax.action')
                ->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_DOWNLOAD_LINK_ACTION);
        } else {
            $action = System::getContainer()
                ->get('huh.ajax.action')
                ->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_SEND_DOWNLOAD_LINK_NOTIFICATION);
        }

        if ($module->downloadLinkFormConfigModule) {
            $action = System::getContainer()
                ->get('huh.ajax.action')
                ->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_LOAD_DOWNLOAD_LINK_FORM);
        }

        $template->action = $action;

        return $template->parse();
    }

    /**
     * @param $watchlistId
     * @param $moduleId
     *
     * @return string
     */
    public function getDownloadAllAction($watchlistId, $moduleId)
    {
        $downloadAllTemplate = new FrontendTemplate('watchlist_download_all_action');
        $downloadAllTemplate->action =
            System::getContainer()->get('huh.ajax.action')->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_DOWNLOAD_ALL_ACTION);
        $downloadAllTemplate->downloadAllLink = $GLOBALS['TL_LANG']['WATCHLIST']['downloadAllLink'];
        $downloadAllTemplate->downloadAllTitle = $GLOBALS['TL_LANG']['WATCHLIST']['downloadAllTitle'];
        $downloadAllTemplate->watchlistId = $watchlistId;
        $downloadAllTemplate->moduleId = $moduleId;

        return $downloadAllTemplate->parse();
    }

    /**
     * generate the add-to-watchlist button.
     *
     * @param array  $data
     * @param string $dataContainer
     * @param int    $watchlistConfig
     * @param bool   $downloadable
     *
     * @return string
     */
    public function getAddToWatchlistButton(
        array $data,
        string $dataContainer,
        int $watchlistConfig,
        $downloadable = true,
        string $fileField = 'uuid',
        string $template = 'watchlist_add_action'
    ) {
        $template = new FrontendTemplate($template);
        $template->added = false;

        if (null === ($file = StringUtil::deserialize($data[$fileField], true)[0])) {
            return '';
        }

        if ($this->container->get('huh.watchlist.watchlist_item_manager')->isItemInWatchlist($watchlistConfig, $data[$fileField])) {
            $template->added = true;
        }

        $template->type = WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE;
        $template->id = $data['id'];
        $template->options = json_encode($data['options']);
        $template->moduleId = $watchlistConfig;
        $template->dataContainer = $dataContainer;
        $template->downloadable = $downloadable;
        $template->itemTitle = $data['title'];
        $template->uuid = bin2hex($file);
        $template->action = $this->container->get('huh.ajax.action')->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_ADD_ACTION);
        $template->title = $this->translator->trans('huh.watchlist.item.add.title', ['%item%' => $data['title']]);
        $template->link = $data['linkTitle'] ?: $this->translator->trans('huh.watchlist.item.add.link');

        return $template->parse();
    }


    public function generateAddToWatchlistButtonForContentElement(array $data, ?string $uuid = null)
    {
        return $this->generateAddToWatchlistButtonForTemplate($data, 'tl_content', $uuid);
    }

    /**
     *  Returns an add to watchlist button for the given entity.
     *
     * @param array $data
     * @param string $dataContainer
     * @param string|null $uuid
     * @return string
     */
    public function generateAddToWatchlistButtonForTemplate(array $data, string $dataContainer, ?string $uuid = null)
    {
        if ($data['addAddToWatchlistButton']) {
            if ($uuid)
            {
                $data['uuid'] = $uuid;
            }
            return $this->getAddToWatchlistButton($data, $dataContainer, $data['watchlistConfiguration']);
        }
    }


    /**
     * get add modal.
     *
     * @param int    $moduleId
     * @param string $type
     * @param        $itemData
     *
     * @return array|ResponseError|null
     */
    public function getWatchlistAddModal(int $moduleId, string $type, $itemData)
    {
        if (null === ($module = $this->framework->getAdapter(ModuleModel::class)->findByPk($moduleId))) {
            return null;
        }

        // if multiple watchlists are not allowed add the item to the watchlist and return the message

        if (!$module->useMultipleWatchlist) {
            return $this->getSimpleWatchlistAddModal($moduleId, $type, $itemData);
        }

        $template = new FrontendTemplate('watchlist_add_modal');

        $template->addTitle = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['addTitle'], $itemData['title']);
        $template->addLink = $GLOBALS['TL_LANG']['WATCHLIST']['addLink'];
        $template->abort = $GLOBALS['TL_LANG']['WATCHLIST']['abort'];
        $template->type = WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE;

        $template->newWatchlistTitle = $GLOBALS['TL_LANG']['WATCHLIST']['newWatchlist'];
        $template->selectWatchlistTitle = $GLOBALS['TL_LANG']['WATCHLIST']['selectWatchlist'];
        $template->addItemToSelectedWatchlistAction = System::getContainer()
            ->get('huh.ajax.action')
            ->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_ADD_ITEM_TO_SELECTED_WATCHLIST);
        $template->downloadable = $itemData['downloadable'];

        $template->moduleId = $moduleId;

        $template->newWatchlistAction = System::getContainer()
            ->get('huh.ajax.action')
            ->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_NEW_WATCHLIST_ADD_ITEM_ACTION);

        if ($module->useWatchlistDurability) {
            $template->useWatchlistDurability = $module->useWatchlistDurability;
            $template->durabilityLabel = $GLOBALS['TL_LANG']['WATCHLIST']['durability']['label'];
            $template->durability = [
                $module->watchlistDurability.$GLOBALS['TL_LANG']['WATCHLIST']['durability']['days'],
                $GLOBALS['TL_LANG']['WATCHLIST']['durability']['immortal'],
            ];
        }

        if (!empty($options = System::getContainer()->get('huh.watchlist.watchlist_manager')->getWatchlistOptions($module))) {
            $template->watchlistOptions = $this->getOptionsSelectTemplate($options, static::WATCHLIST_SELECT_WATCHLIST_OPTIONS);
        }

        if ($itemData['options']) {
            $template->itemOptions = $this->getOptionsSelectTemplate($itemData['options'], static::WATCHLIST_SELECT_ITEM_OPTIONS);
        }

        if ($itemData['uuid']) {
            $template->uuid = $itemData['uuid'];
            $template->itemTitle = $itemData['title'];
        }

        $config = ['headline' => sprintf($GLOBALS['TL_LANG']['WATCHLIST']['addTitle'], $itemData['title'])];

        return [null, $this->generateWatchlistWindow($template->parse(), $config), null];
    }

    /**
     * @param array $options
     *
     * @return string
     */
    public function getOptionsSelectTemplate(array $options, string $class, $currentOption = null, int $moduleId = null, string $action = null)
    {
        $template = new FrontendTemplate('watchlist_select_actions');

        $template->label = $GLOBALS['TL_LANG']['WATCHLIST']['selectOption'][$class];
        $template->select = $options;
        $template->currentOption = $currentOption;
        $template->class = $class;
//        $template->action        = $action;
//        $template->moduleId      = $moduleId;

        return $template->parse();
    }

    /**
     * get the template for options of item.
     *
     * @param int    $moduleId
     * @param string $type
     * @param array  $options
     *
     * @return string
     */
    public function getWatchlistItemOptions(int $moduleId, string $type, array $options)
    {
        $template = new FrontendTemplate('watchlist_add_option_modal');

        $template->options = $this->getOptionsSelectTemplate($options, static::WATCHLIST_SELECT_ITEM_OPTIONS);
        $template->abort = $GLOBALS['TL_LANG']['WATCHLIST']['abort'];
        $template->addTitle = $GLOBALS['TL_LANG']['WATCHLIST']['addTitle'];
        $template->addLink = $GLOBALS['TL_LANG']['WATCHLIST']['addLink'];
        $template->moduleId = $moduleId;
        $template->type = $type;
        $template->action =
            System::getContainer()->get('huh.ajax.action')->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_ADD_ACTION);

        return $template->parse();
    }

    /**
     * @param $moduleId
     * @param $watchlistId
     *
     * @return array
     */
    public function getUpdatedWatchlist(int $moduleId, int $watchlistId = null)
    {
        if (null === ($module = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_module', $moduleId))) {
            $template = new FrontendTemplate('watchlist');
            $template->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];

            return [$template->parse(), '', 0];
        }

        $watchlistManager = System::getContainer()->get('huh.watchlist.watchlist_manager');

        if (!$watchlistId) {
            $watchlistId = $this->getRandomWatchlist($module);
        }

        if (null === ($watchlist = $watchlistManager->getWatchlistModel(null, $watchlistId))) {
            $template = new FrontendTemplate('watchlist');
            $template->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];

            return [$template->parse(), '', 0];
        }

        $watchlistName = $watchlistManager->getWatchlistName($module, $watchlist);
        $watchlistItems = $watchlistManager->getCurrentWatchlistItems($moduleId, $watchlistId);

        return [$this->getWatchlist($module, $watchlistItems, $watchlistId), $watchlistName, $watchlistItems ? $watchlistItems->count() : 0];
    }

    /**
     * add content to the modal wrapper.
     *
     * @param string $content
     *
     * @param array $config
     * @return string
     * @throws InvalidArgumentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function generateWatchlistWindow(string $content, array $config = [])
    {
        $template = 'watchlist_window_default';
        if ($config['template']) {
            $template = $config['template'];
        }

        $context = [
            'content' => $content,
            'headline' => isset($config['headline']) ? $config['headline'] : $GLOBALS['TL_LANG']['WATCHLIST']['modalHeadline'],
            'class' => isset($config['class']) ? $config['class'] : '',
        ];

        return $this->container->get('huh.utils.template')->renderTwigTemplate($template, $context);


//        $template = new FrontendTemplate('watchlist_modal_wrapper');
//        $template->content = $content;
//
//        $template->headline = $config['headline'] ? $config['headline'] : $GLOBALS['TL_LANG']['WATCHLIST']['modalHeadline'];
//
//        if ($config['class']) {
//            $template->class = $config['class'];
//        }
//
//        return $template->parse();
    }

    /**
     * add the image to the template.
     *
     * @param FrontendTemplate $template
     * @param string           $path
     */
    public function addImageToTemplate(FrontendTemplate $template, string $path, $module)
    {
        if (!isset($path)) {
            return;
        }

        $template->image = $path;

        // resize image if set
        if ('' != $module->imgSize) {
            $image = [];

            $size = StringUtil::deserialize($module->imgSize, true);

            if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                $image['size'] = $module->imgSize;
            }

            $image['singleSRC'] = $path;
            Controller::addImageToTemplate($template, $image);
        }
    }

    /**
     * return unparsed toggler template
     * -> do not parse it yet since we want to access some properties in `WatchlistModule`.
     *
     * @param int $moduleId
     *
     * @return array
     */
    public function getWatchlistToggler(int $moduleId)
    {
        $watchlist = System::getContainer()->get('huh.watchlist.watchlist_manager')->getWatchlistModel($moduleId);

        $template = new FrontendTemplate('watchlist_toggler');
        $count = 0;

        if (null !== ($watchlistItems = System::getContainer()->get('huh.watchlist.watchlist_manager')->getItemsFromWatchlist($watchlist->id))) {
            $count = $watchlistItems->count();
        }

        $template->toggleLink = $this->getTogglerTitle($moduleId);
        $template->moduleId = $moduleId;
        $template->watchlistId = $watchlist->id;
        $template->action =
            System::getContainer()->get('huh.ajax.action')->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_SHOW_MODAL_ACTION);
        $template->itemCount = $count;

        return [$watchlist->id, $template->parse()];
    }

    /**
     * return a random watchlist id from either the user groups set at module
     * or from user id.
     *
     * @param $module
     *
     * @return int
     */
    protected function getRandomWatchlist($module)
    {
        $watchlists = $module->useGroupWatchlist
            ? System::getContainer()->get('huh.watchlist.watchlist_manager')->getWatchlistByGroups($module)
            : System::getContainer()->get('huh.watchlist.watchlist_manager')->getWatchlistByCurrentUser();

        if (null === $watchlists) {
            return 0;
        }

        $ids = $watchlists->fetchEach('id');

        return $ids[array_rand($ids)];
    }

    /**
     * @param int $pageId
     * @param     $module
     *
     * @return array
     */
    protected function getParentList(int $pageId, $module)
    {
        $page = $this->framework->getAdapter(PageModel::class)->findByPk($pageId);
        $type = null;
        $pageId = $page->id;
        $pages = [$page->row()];
        $items = [];

        // Get all pages up to the root page
        $pages = $this->framework->getAdapter(PageModel::class)->findParentsById($page->pid);

        if (null !== $pages) {
            while ($pageId > 0 && 'root' != $type && $pages->next()) {
                $type = $pages->type;
                $pageId = $pages->pid;
                $pages[] = $pages->row();
            }
        }

        // Get the first active regular page and display it instead of the root page
        if ('root' == $type) {
            $firstPage = $this->framework->getAdapter(PageModel::class)->findFirstPublishedByPid($pages->id);

            $items[] = [
                'isRoot' => true,
                'isActive' => false,
                'href' => ((null !== $firstPage) ? $this->framework->getAdapter(Controller::class)
                    ->generateFrontendUrl($firstPage->row()) : Environment::get('base')),
                'title' => specialchars($pages->pageTitle ?: $pages->title, true),
                'link' => $pages->title,
                'data' => $firstPage->row(),
                'class' => '',
            ];

            array_pop($pages);
        }

        // Build the breadcrumb menu
        for ($i = (count($pages) - 1); $i > 0; --$i) {
            if (($pages[$i]['hide'] && !$module->showHidden) || (!$pages[$i]['published'] && !BE_USER_LOGGED_IN)) {
                continue;
            }

            // Get href
            switch ($pages[$i]['type']) {
                case 'redirect':
                    $href = $pages[$i]['url'];

                    if (0 === strncasecmp($href, 'mailto:', 7)) {
                        $href = $this->framework->getAdapter(StringUtil::class)->encodeEmail($href);
                    }
                    break;

                case 'forward':
                    $objNext = $this->framework->getAdapter(PageModel::class)->findPublishedById($pages[$i]['jumpTo']);

                    if (null !== $objNext) {
                        $href = $this->framework->getAdapter(Controller::class)->generateFrontendUrl($objNext->row());
                        break;
                    }
                // DO NOT ADD A break; STATEMENT

                // no break
                default:
                    $href = $this->framework->getAdapter(Controller::class)->generateFrontendUrl($pages[$i]);
                    break;
            }

            $items[] = [
                'isRoot' => false,
                'isActive' => false,
                'href' => $href,
                'title' => specialchars($pages[$i]['pageTitle'] ?: $pages[$i]['title'], true),
                'link' => $pages[$i]['title'],
                'data' => $pages[$i],
                'class' => '',
            ];
        }

        // Active page
        $items[] = [
            'isRoot' => false,
            'isActive' => true,
            'href' => $this->framework->getAdapter(Controller::class)->generateFrontendUrl($pages[0]),
            'title' => specialchars($pages[0]['pageTitle'] ?: $pages[0]['title']),
            'link' => $pages[0]['title'],
            'data' => $pages[0],
            'class' => 'last',
        ];

        $items[0]['class'] = 'first';

        return $items;
    }

    /**
     * @param WatchlistItemModel $item
     * @param                    $module
     * @param                    $cssClass
     *
     * @return string
     */
    protected function parseItem(WatchlistItemModel $item, $module, $cssClass)
    {
        $template = new FrontendTemplate('watchlist_item');

        $class = '';
        switch ($item->type) {
            case WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE:
                $class = System::getContainer()
                    ->get('huh.watchlist.watchlist_manager')
                    ->getClassByName($module->watchlistItemFile, WatchlistManager::WATCHLIST_ITEM_FILE_GROUP);
                break;
            case WatchlistItemModel::WATCHLIST_ITEM_TYPE_ENTITY:
                $class = System::getContainer()
                    ->get('huh.watchlist.watchlist_manager')
                    ->getClassByName($module->watchlistItemEntity, WatchlistManager::WATCHLIST_ITEM_ENTITY_GROUP);
        }

        if ('' == $class) {
            return '';
        }

        if (null === ($watchlistItem = new $class($item->row()))) {
            return '';
        }

        if (null !== ($file = $watchlistItem->getFile())) {
            $template->image = $watchlistItem->getFile();
            $this->addImageToTemplate($template, $file, $module);
        }

        $template->title = $watchlistItem->getTitle();
        $template->actions = $watchlistItem->getEditActions($module);
        $template->cssClass = $watchlistItem->getType();
        $template->id = $item->id;
        $template->type = $watchlistItem->getType();

        return $template->parse();
    }

    /**
     * return simple add modal where you only can select options of a item but no options of watchlist.
     *
     * @param int    $moduleId
     * @param string $type
     * @param        $itemData
     *
     * @return array|ResponseError
     */
    protected function getSimpleWatchlistAddModal(int $moduleId, string $type, $itemData)
    {
        if (null === ($watchlist = System::getContainer()->get('huh.watchlist.watchlist_manager')->getWatchlistModel($moduleId))) {
            return new ResponseError();
        }

        if (!$itemData['options']) {
            $message = System::getContainer()->get('huh.watchlist.action_manager')->addItemToWatchlist($watchlist->id, $type, $itemData);
            $watchlistItems = System::getContainer()->get('huh.watchlist.watchlist_manager')->getItemsFromWatchlist($watchlist->id);

            return [$message, null, $watchlistItems->count()];
        }

        $template = new FrontendTemplate('watchlist_add_option_modal');

        $template->options = $this->getOptionsSelectTemplate($itemData['options'], static::WATCHLIST_SELECT_ITEM_OPTIONS);
        $template->action =
            System::getContainer()->get('huh.ajax.action')->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_ADD_ACTION);
        $template->moduleId = $moduleId;
        $template->type = WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE;
        $template->watchlistId = $watchlist->id;
        $template->addTitle = $GLOBALS['TL_LANG']['WATCHLIST']['addTitle'];
        $template->addLink = $GLOBALS['TL_LANG']['WATCHLIST']['addLink'];
        $template->abort = $GLOBALS['TL_LANG']['WATCHLIST']['abort'];

        return [null, $this->generateWatchlistWindow($template->parse()), null];
    }

    /**
     * @param int $moduleId
     *
     * @return string
     */
    protected function getTogglerTitle(int $moduleId)
    {
        $title = $GLOBALS['TL_LANG']['WATCHLIST']['toggleLink'];

        if (null === ($module = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_module', $moduleId))) {
            return $title;
        }

        if (!$module->overrideTogglerTitle) {
            return $title;
        }

        return $this->translator->trans($module->togglerTitle);
    }
}
