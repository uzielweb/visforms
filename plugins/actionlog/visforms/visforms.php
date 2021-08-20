<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Utilities\ArrayHelper;

JLoader::register('ActionLogPlugin', JPATH_ADMINISTRATOR . '/components/com_actionlogs/libraries/actionlogplugin.php');
JLoader::register('ActionlogsHelper', JPATH_ADMINISTRATOR . '/components/com_actionlogs/helpers/actionlogs.php');

/**
 * Visforms Users Actions Logging Plugin.
 *
 * @since  3.9.0
 */
class PlgActionlogVisforms extends ActionLogPlugin
{
	protected $loggableExtensions = array();

	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		$params = ComponentHelper::getComponent('com_actionlogs')->getParams();
		$this->loggableExtensions = $params->get('loggable_extensions', array());
	}

	public function onVisformsAfterJFormSave($context, $article, $isNew) {
		if (isset($this->contextAliases[$context])) {
			$context = $this->contextAliases[$context];
		}
		$option = $this->app->input->getCmd('option');
		if (!$this->checkLoggable($option)) {
			return;
		}
		$params = ActionlogsHelper::getLogContentTypeParams($context);
		// Not found a valid content type, don't process further
		if ($params === null) {
			return;
		}
		list(, $contentType) = explode('.', $params->type_alias);
		if ($isNew) {
			$messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_ADDED';
			$defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_ADDED';
		}
		else {
			$messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_UPDATED';
			$defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_UPDATED';
		}
		// If the content type doesn't has it own language key, use default language key
		if (!$this->app->getLanguage()->hasKey($messageLanguageKey)) {
			$messageLanguageKey = $defaultLanguageKey;
		}
		$id = empty($params->id_holder) ? 0 : $article->get($params->id_holder);
		$message = array(
			'action' => $isNew ? 'add' : 'update',
			'type' => $params->text_prefix . '_TYPE_' . $params->type_title,
			'id' => $id,
			'title' => $article->get($params->title_holder),
			'itemlink' => ActionlogsHelper::getContentTypeLink($option, $contentType, $id)
		);
		$this->addLog(array($message), $messageLanguageKey, $context);
	}

	public function onVisformsAfterJFormDelete($context, $article) {
		$option = $this->app->input->get('option');
		if (!$this->checkLoggable($option)) {
			return;
		}
		$params = ActionlogsHelper::getLogContentTypeParams($context);
		// Not found a valid content type, don't process further
		if ($params === null) {
			return;
		}
		// If the content type has it own language key, use it, otherwise, use default language key
		if ($this->app->getLanguage()->hasKey(strtoupper($params->text_prefix . '_' . $params->type_title . '_DELETED'))) {
			$messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_DELETED';
		}
		else {
			$messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_DELETED';
		}
		$id = empty($params->id_holder) ? 0 : $article->get($params->id_holder);
		$message = array(
			'action' => 'delete',
			'type' => $params->text_prefix . '_TYPE_' . $params->type_title,
			'id' => $id,
			'title' => $article->get($params->title_holder)
		);
		$this->addLog(array($message), $messageLanguageKey, $context);
	}

	public function onVisformsJFormChangeState($context, $pks, $value) {
		$option = $this->app->input->getCmd('option');
		if (!$this->checkLoggable($option)) {
			return;
		}
		$params = ActionlogsHelper::getLogContentTypeParams($context);
		// Not found a valid content type, don't process further
		if ($params === null) {
			return;
		}
		list(, $contentType) = explode('.', $params->type_alias);
		switch ($value) {
			case 0:
				$messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_UNPUBLISHED';
				$defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_UNPUBLISHED';
				$action = 'unpublish';
				break;
			case 1:
				$messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_PUBLISHED';
				$defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_PUBLISHED';
				$action = 'publish';
				break;
			default:
				$messageLanguageKey = '';
				$defaultLanguageKey = '';
				$action = '';
				break;
		}
		// If the content type doesn't has it own language key, use default language key
		if (!$this->app->getLanguage()->hasKey($messageLanguageKey)) {
			$messageLanguageKey = $defaultLanguageKey;
		}
		$db = $this->db;
		$query = $db->getQuery(true)
			->select($db->quoteName(array($params->title_holder, $params->id_holder)))
			->from($db->quoteName($params->table_name))
			->where($db->quoteName($params->id_holder) . ' IN (' . implode(',', ArrayHelper::toInteger($pks)) . ')');
		try {
			$db->setQuery($query);
			$items = $db->loadObjectList($params->id_holder);
		}
		catch (RuntimeException $e) {
			$items = array();
		}
		$messages = array();
		foreach ($pks as $pk) {
			$message = array(
				'action' => $action,
				'type' => $params->text_prefix . '_TYPE_' . $params->type_title,
				'id' => $pk,
				'title' => $items[$pk]->{$params->title_holder},
				'itemlink' => ActionlogsHelper::getContentTypeLink($option, $contentType, $pk)
			);
			$messages[] = $message;
		}
		$this->addLog($messages, $messageLanguageKey, $context);
	}

	public function onVisformsdataAfterJFormSave($context, $article, $isNew, $data = array()) {
		if (isset($this->contextAliases[$context])) {
			$context = $this->contextAliases[$context];
		}
		$option = $this->app->input->getCmd('option');
		if (!$this->checkLoggable($option)) {
			return;
		}
		$params = ActionlogsHelper::getLogContentTypeParams($context);
		// Not found a valid content type, don't process further
		if ($params === null) {
			return;
		}
		$fid = $this->getFid();
		if ($isNew) {
			$messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_ADDED';
			$defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_ADDED';
		}
		else {
			$messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_UPDATED';
			$defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_UPDATED';
		}
		// If the content type doesn't has it own language key, use default language key
		if (!$this->app->getLanguage()->hasKey($messageLanguageKey)) {
			$messageLanguageKey = $defaultLanguageKey;
		}
		$id = empty($params->id_holder) ? 0 : $article->{$params->id_holder};
		$message = array(
			'action' => $isNew ? 'add' : 'update',
			'type' => $params->text_prefix . '_TYPE_' . $params->type_title,
			'id' => $id,
			'title' => $id . ' '. JText::_('PLG_ACTIONLOG_VISFORMS_DATA_ADDITIONAL_LINK_TEXT') . ' ' . $fid,
			'itemlink' => $this->getDataItemLink($id, $fid)
		);
		$this->addLog(array($message), $messageLanguageKey, $context);
	}

	public function onVisformsdataAfterJFormDelete($context, $article) {
		$option = $this->app->input->get('option');
		if (!$this->checkLoggable($option)) {
			return;
		}
		$params = ActionlogsHelper::getLogContentTypeParams($context);
		// Not found a valid content type, don't process further
		if ($params === null) {
			return;
		}
		$fid = $this->getFid();
		// If the content type has it own language key, use it, otherwise, use default language key
		if ($this->app->getLanguage()->hasKey(strtoupper($params->text_prefix . '_' . $params->type_title . '_DELETED'))) {
			$messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_DELETED';
		}
		else {
			$messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_DELETED';
		}
		$id = empty($params->id_holder) ? 0 : $article->get($params->id_holder);
		$message = array(
			'action' => 'delete',
			'type' => $params->text_prefix . '_TYPE_' . $params->type_title,
			'id' => $id,
			'title' => $id . ' '. JText::_('PLG_ACTIONLOG_VISFORMS_DATA_ADDITIONAL_LINK_TEXT') . ' ' . $fid,
		);
		$this->addLog(array($message), $messageLanguageKey, $context);
	}

	public function onVisformsdataJFormChangeState($context, $pks, $value) {
		$option = $this->app->input->getCmd('option');
		if (!$this->checkLoggable($option)) {
			return;
		}
		$params = ActionlogsHelper::getLogContentTypeParams($context);
		// Not found a valid content type, don't process further
		if ($params === null) {
			return;
		}
		$fid = $this->getFid();
		switch ($value) {
			case 0:
				$messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_UNPUBLISHED';
				$defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_UNPUBLISHED';
				$action = 'unpublish';
				break;
			case 1:
				$messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_PUBLISHED';
				$defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_PUBLISHED';
				$action = 'publish';
				break;
			default:
				$messageLanguageKey = '';
				$defaultLanguageKey = '';
				$action = '';
				break;
		}
		// If the content type doesn't has it own language key, use default language key
		if (!$this->app->getLanguage()->hasKey($messageLanguageKey)) {
			$messageLanguageKey = $defaultLanguageKey;
		}
		$messages = array();
		foreach ($pks as $pk) {
			$message = array(
				'action' => $action,
				'type' => $params->text_prefix . '_TYPE_' . $params->type_title,
				'id' => $pk,
				'title' => $pk . ' '. JText::_('PLG_ACTIONLOG_VISFORMS_DATA_ADDITIONAL_LINK_TEXT') . ' ' . $fid,
				'itemlink' => $this->getDataItemLink($pk, $fid)
			);
			$messages[] = $message;
		}
		$this->addLog($messages, $messageLanguageKey, $context);
	}

	protected function checkLoggable($extension) {
		return in_array($extension, $this->loggableExtensions);
	}

	protected function getDataItemLink($pk, $fid) {
		return 'index.php?option=com_visforms&view=visdatas&fid=' . $fid;
	}

	protected function getFid() {
		$fid = $this->app->input->getCmd('fid');
		if (empty($fid)) {
			$fid = $this->app->input->getCmd('id');
		}
		return $fid;
	}
}
