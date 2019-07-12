<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Manager;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use HeimrichHannot\WatchlistBundle\Event\WatchlistBeforeSendNotificationEvent;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\Module\DownloadLinkSubmission;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class WatchlistActionManager
{
    const MESSAGE_STATUS_ERROR = 'watchlist-message-error';
    const MESSAGE_STATUS_SUCCESS = 'watchlist-message-success';

    /**
     * for tracking iterations.
     *
     * @var int
     */
    protected $position = 0;

    /**
     * @var ContaoFramework
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

    public function __construct(ContainerInterface $container, ContaoFramework $framework, TranslatorInterface $translator)
    {
        $this->framework = $framework;
        $this->translator = $translator;
        $this->container = $container;
    }

    /**
     * @param $name
     * @param $durability
     *
     * @return WatchlistModel
     */
    public function addMultipleWatchlist(string $name, string $durability)
    {
        return $this->createWatchlist($name, null, $durability);
    }

    /**
     * delete a watchlist.
     *
     * @param int $id
     *
     * @return string
     */
    public function deleteWatchlistItem(int $id)
    {
        if (null === ($watchlistItem = $this->framework->getAdapter(WatchlistItemModel::class)->findInstanceByPk($id))) {
            $message = $this->translator->trans('huh.watchlist.item.delete.error');

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        $message = $this->translator->trans('huh.watchlist.item.delete.success', ['%item%' => $watchlistItem->title]);
        $watchlistItem->delete();

        return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
    }

    /**
     * delete all items from specific watchlist.
     *
     * @param int $watchlistId
     *
     * @return string
     */
    public function deleteWatchlistItemFromWatchlist(int $watchlistId)
    {
        if (null === ($watchlistItems = $this->framework->getAdapter(WatchlistItemModel::class)->findByPid($watchlistId))) {
            $message = $this->translator->trans('huh.watchlist.item.delete.error');

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        $this->deleteItems($watchlistItems);

        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedById($watchlistId))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_delete_all_items']);

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
        }

        $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_delete_all_items_from_watchlist'], $watchlist->name);

        return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
    }

    /**
     * @param int $watchlistId
     *
     * @return string
     */
    public function emptyWatchlist(int $watchlistId)
    {
        if (null === ($watchlistItems = $this->framework->getAdapter(WatchlistItemModel::class)->findByPid($watchlistId))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_delete_all_error']);

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        foreach ($watchlistItems as $item) {
            $item->delete();
        }

        $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_empty_watchlist'];

        return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
    }

    /**
     * delete watchlist and its watchlistItems.
     *
     * @param int $watchlistId
     *
     * @return string
     */
    public function deleteWatchlist(int $watchlistId)
    {
        if (null !== ($watchlistItems = $this->framework->getAdapter(WatchlistItemModel::class)->findByPid($watchlistId))) {
            // delete all items from watchlist
            foreach ($watchlistItems as $item) {
                $item->delete();
            }
        }

        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedById($watchlistId))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_delete_all_error']);

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        // delete the watchlist
        $watchlist->delete();

        $this->container->get('session')->set(WatchlistModel::WATCHLIST_SELECT, null);

        $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_delete_all']);

        return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
    }

    /**
     * generate link to public list of watchlist items.
     *
     * @param     $module
     * @param int $watchlistId
     *
     * @return array
     */
    public function generateDownloadLink($module, int $watchlistId)
    {
        if (!$module instanceof ModuleModel) {
            $module = $this->framework->getAdapter(ModuleModel::class)->findByPk($module);
        }

        if (null === $module) {
            $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_watchlist_download_link_error'];

            return [false, $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR)];
        }

        if (null === ($page = $this->framework->getAdapter(PageModel::class)->findByPk($module->downloadLink))) {
            $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_watchlist_download_link_page_error'];

            return [false, $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR)];
        }

        if (null === ($watchlist = $this->container->get('huh.watchlist.watchlist_manager')->getWatchlistModel(null, $watchlistId))) {
            $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_no_watchlist_found'];

            return [false, $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR)];
        }

        // start lifecylce of download link when it is generated
        $watchlist->startShare = time();
        $watchlist->save();

        return [urldecode($page->getAbsoluteUrl('?watchlist='.$watchlist->uuid)), false];
    }

    /**
     * get the status message.
     *
     * @param string $message
     * @param string $status
     *
     * @return string
     */
    public function getStatusMessage(string $message, string $status)
    {
        $template = new FrontendTemplate('watchlist_message');
        $template->message = $message;
        $template->cssClass = static::MESSAGE_STATUS_SUCCESS == $status ? 'bg-success' : 'bg-danger';

        return $template->parse();
    }

    /**
     * create a new watchlist.
     *
     * @param string      $name
     * @param string|null $hash
     * @param null        $durability
     *
     * @return WatchlistModel
     */
    public function createWatchlist(string $name, string $hash = null, $durability = null)
    {
        if (null === ($userId = $this->framework->getAdapter(FrontendUser::class)->getInstance()->id)) {
            $userId = 0;
        }

        $watchlist = new WatchlistModel();
        $watchlist->pid = $userId;
        $watchlist->name = $name;

        $watchlist->uuid =
            $this->framework->getAdapter(StringUtil::class)->binToUuid($this->framework->getAdapter(Database::class)->getInstance()->getUuid());
        $watchlist->ip =
            (!$this->framework->getAdapter(Config::class)->get('disableIpCheck') ? $this->framework->getAdapter(Environment::class)->get('ip') : '');
        $watchlist->sessionID = session_id();
        $watchlist->tstamp = time();
        $watchlist->published = 1;
        $watchlist->hash = isset($hash) ? $hash : sha1(session_id().$watchlist->ip.$name);

        if ($GLOBALS['TL_LANG']['WATCHLIST']['durability']['default'] == $durability) {
            $watchlist->start = strtotime('today');
            //add 29 days to timestamp to receive a different of 30 days
            $watchlist->stop = strtotime('tomorrow') + 60 * 60 * 24 * 29;
        }

        return $watchlist->save();
    }

    /**
     * add a item to a watchlist.
     *
     * @param int    $watchlistId
     * @param string $type
     * @param        $itemData
     *
     * @return string
     */
    public function addItemToWatchlist(int $watchlistId, string $type, $itemData)
    {
        // check if needed itemData was submitted (`uuid` for type `file`, `ptable` and `ptableId` for `entity`)
        if (!isset($itemData->uuid) && !isset($itemData->ptable) && !isset($itemData->ptableId)) {
            $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_no_data'];

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        // check if watchlist to which the item should be added exists
        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedById($watchlistId))) {
            $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_no_watchlist_found'];

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        // check if item is already in this watchlist
        if (isset($itemData->uuid) && true !== ($response = $this->checkFile($watchlist, $itemData->uuid))) {
            return $response;
        }

        if ($itemData->ptable && $itemData->ptableId
            && true !== ($response = $this->checkEntity($watchlist, $itemData->ptable, $itemData->ptableId))) {
            return $response;
        }

        $item = new WatchlistItemModel();
        $item->pid = $watchlist->id;
        $item->pageID = $itemData->pageId;
        $item->type = $type;
        $item->tstamp = time();

        $item->title = $itemData->title ? html_entity_decode($itemData->title) : '';
        $item->uuid = $itemData->uuid ? StringUtil::uuidToBin(is_array($itemData->uuid) ? $itemData->uuid['uuid'] : $itemData->uuid) : null;
        $item->ptable = $itemData->ptable ? $itemData->ptable : '';
        $item->ptableId = $itemData->ptableId ? $itemData->ptableId : '';
        $item->downloadable = $itemData->downloadable;

        $item->save();

        $message = $this->translator->trans('huh.watchlist.item.add.success',['%item%' => $item->title]);

        return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function sendDownloadLinkNotification($data)
    {
        if (null === ($module = $this->container->get('huh.utils.model')->findModelInstanceByPk('tl_module', $data->moduleId))) {
            return $this->getStatusMessage($GLOBALS['TL_LANG']['WATCHLIST']['message_no_module'], static::MESSAGE_STATUS_ERROR);
        }

        if (!$module->downloadLinkUseNotification) {
            return $this->getStatusMessage($GLOBALS['TL_LANG']['WATCHLIST']['message_config_error'], static::MESSAGE_STATUS_ERROR);
        }

        $submissionData = $this->getSubmissionData($data);

        if (!$module->downloadLinkFormConfigModule) {
            if (null === ($user = FrontendUser::getInstance())) {
                return $this->getStatusMessage($GLOBALS['TL_LANG']['WATCHLIST']['message_no_user'], static::MESSAGE_STATUS_ERROR);
            }

            $submissionData = array_merge($submissionData, $this->getSubmissionDataFromFrontendUser($user));
        }

        if (empty($submissionData)) {
            return $this->getStatusMessage($GLOBALS['TL_LANG']['WATCHLIST']['message_no_user'], static::MESSAGE_STATUS_ERROR);
        }

        // add downloadLink to submissionData
        list($downloadLink, $error) = $this->generateDownloadLink($module, $data->watchlistId);

        if ($error) {
            return $error;
        }

        $submissionData['downloadLink'] = $downloadLink;

        $this->container->get('event_dispatcher')
            ->dispatch(WatchlistBeforeSendNotificationEvent::NAME, new WatchlistBeforeSendNotificationEvent($submissionData, $module));

        if ($module->downloadLinkUseConfirmationNotification) {
            $activation = md5(uniqid(mt_rand(), true));

            if (!$this->setWatchlistActivation($data->watchlistId, $activation)) {
                return $this->getStatusMessage($GLOBALS['TL_LANG']['WATCHLIST']['message_no_watchlist_found'], static::MESSAGE_STATUS_ERROR);
            }

            $submissionData['downloadLink'] .= '&activation='.$activation;
        }

        return $this->sendDownloadLinkAsNotification($module, $submissionData);
    }

    /**
     * @param ModuleModel $module
     * @param array       $submissionData
     *
     * @return string
     */
    public function sendDownloadLinkAsNotification(ModuleModel $module, array $submissionData)
    {
        if (null === ($notification = $this->container
                ->get('huh.utils.model')
                ->findModelInstanceByPk('tl_nc_notification', $module->downloadLinkNotification))) {
            return $this->getStatusMessage($GLOBALS['TL_LANG']['WATCHLIST']['message_notofication_error'], static::MESSAGE_STATUS_ERROR);
        }

        $token = $this->getTokens($submissionData);

        $notification->send($token, $GLOBALS['TL_LANGUAGE']);

        return $this->getStatusMessage($this->translator->trans('huh.watchlist.download_link.success'), static::MESSAGE_STATUS_SUCCESS);
    }

    /**
     * @param int $moduleId
     * @param int $watchlistId
     *
     * @return string
     */
    public function watchlistLoadDownloadLinkForm(int $moduleId, int $watchlistId)
    {
        if (null === ($module = $this->container->get('huh.utils.model')->findModelInstanceByPk('tl_module', $moduleId))) {
            return $this->getStatusMessage($GLOBALS['TL_LANG']['WATCHLIST']['message_no_module'], static::MESSAGE_STATUS_ERROR);
        }

        if (null === ($configModule =
                $this->container->get('huh.utils.model')->findModelInstanceByPk('tl_module', $module->downloadLinkFormConfigModule))) {
            return $this->getStatusMessage($GLOBALS['TL_LANG']['WATCHLIST']['message_config_error'], static::MESSAGE_STATUS_ERROR);
        }

        $form = new DownloadLinkSubmission($configModule);

//        $this->beforeFormGeneration($form, $moduleId, $watchlistId);
        $watchlistTemplateManager = $this->container->get('huh.watchlist.template_manager');
        $watchlistManager = $this->container->get('huh.watchlist.watchlist_manager');

        $config = [
            'headline' => $watchlistManager->getWatchlistName($module, $watchlistManager->getWatchlistModel(null, $watchlistId)),
        ];

        return $watchlistTemplateManager->generateWatchlistWindow($form->generate(), $config);
    }

    /**
     * @param int    $watchlistId
     * @param string $activation
     *
     * @return bool
     */
    protected function setWatchlistActivation(int $watchlistId, string $activation)
    {
        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findModelInstanceByPk($watchlistId))) {
            return false;
        }

        $watchlist->activation = $activation;
        $watchlist->save();

        return true;
    }

    /**
     * @param $data
     *
     * @return array
     */
    protected function getSubmissionData($data)
    {
        if (!is_array($data)) {
            $data = (array) $data;
        }

        return $data;
    }

    /**
     * @param FrontendUser $user
     *
     * @return null|array
     */
    protected function getSubmissionDataFromFrontendUser(FrontendUser $user)
    {
        $data = null;
        if (null === ($member = $this->container->get('huh.utils.member')->findByPk($user->id))) {
            return $data;
        }

        $data = $member->row();

        return $data;
    }

    /**
     * @param array  $submission
     * @param string $prefix
     *
     * @return array
     */
    protected function getTokens(array $submission, $prefix = 'form')
    {
        $token = [];

        foreach ($submission as $key => $value) {
            if ('downloadLink' == $key) {
                $token[$key] = $value;
                continue;
            }

            $token[$prefix.'_'.$key] = $value;
        }

        return $token;
    }

    /**
     * @param DownloadLinkSubmission $form
     * @param int                    $moduleId
     * @param int                    $watchlistId
     */
    protected function beforeFormGeneration(DownloadLinkSubmission $form, int $moduleId, int $watchlistId)
    {
        $defaultData = [
            ['field' => 'module', 'value' => $moduleId, 'label' => ''],
            ['field' => 'watchlistId', 'value' => $watchlistId, 'label' => ''],
        ];

        $form->addDefaultValues($defaultData);
    }

    /**
     * check if uuid is valid and if file is already in watchlist.
     *
     * @param $watchlist
     * @param $uuid
     *
     * @return bool|string
     */
    protected function checkFile($watchlist, $uuid)
    {
        if (is_array($uuid)) {
            $uuid = $uuid['uuid'];
        }

        $uuid = hex2bin($uuid);

        if (null === $this->container->get('huh.utils.file')->getFileFromUuid($uuid)) {
            $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_invalid_file'];

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        if (false !== ($watchlistItem =
                $this->container->get('huh.watchlist.watchlist_item_manager')->isItemInWatchlist($watchlist->id, $uuid))) {
            $message = $this->translator->trans('huh.watchlist.item.already_in_watchlist');


            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        return true;
    }

    /**
     * check if file is already in watchlist.
     *
     * @param $watchlist
     * @param $ptable
     * @param $ptableId
     *
     * @return bool|string
     */
    protected function checkEntity($watchlist, $ptable, $ptableId)
    {
        if (null !== ($watchlistItem =
                $this->container->get('huh.watchlist.watchlist_item_manager')->isItemInWatchlist($watchlist->id, null, $ptable, $ptableId))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_in_watchlist'], $watchlistItem->title, $watchlist->name);

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        return true;
    }
}
