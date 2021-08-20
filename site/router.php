<?php
/**
 * @package        Joomla.Site
 * @copyright      Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Component\Router\RouterBase;

class VisformsRouter extends RouterView
{
	protected $noIDs = false;

	public function __construct($app = null, $menu = null) {
		$visforms = new RouterViewConfiguration('visforms');
		$visforms->setKey('id');
		$visforms->addLayout('message');
		$this->registerView($visforms);
		$visformsdata = new RouterViewConfiguration('visformsdata');
		$visformsdata->setKey('id');
		$visformsdata->removeLayout('default');
		$visformsdata->addLayout('data');
		$visformsdata->addLayout('detail');
		$visformsdata->addLayout('dataeditlist');
		$visformsdata->addLayout('detailedit');
		$visformsdata->addLayout('detailitem');
		$this->registerView($visformsdata);
		$mysubmissions = new RouterViewConfiguration('mysubmissions');
		$mysubmissions->setKey('id');
		$this->registerView($mysubmissions);
		$edit = new RouterViewConfiguration('edit');
		$edit->setKey('id');
		$this->registerView($edit);
		parent::__construct($app, $menu);
		// rules are processed for build and parse in the order they are attached here
		JLoader::register('VisformsRouterRulesMenu', __DIR__ . '/helpers/route/MenuRules.php');
		$this->attachRule(new VisformsRouterRulesMenu($this));
		// will remove view and id from query
		$this->attachRule(new StandardRules($this));
		JLoader::register('VisformsRouterRulesVisforms', __DIR__ . '/helpers/route/VisformsRules.php');
		$this->attachRule(new VisformsRouterRulesVisforms($this));
		// nomenuRules replaces all segments (3 and 4 in URL created by search plugin
		$this->attachRule(new NomenuRules($this));
		$test = true;
	}

	// add form as id-alias to segments
	public function getVisformsSegment($id, $query) {
		if (!strpos($id, ':')) {
			$db = \JFactory::getDbo();
			$dbQuery = $db->getQuery(true)
				->select($db->qn('name'))
				->from($db->qn('#__visforms'))
				->where($db->qn('id') . ' = ' . (int) $query['id']);
			$db->setQuery($dbQuery);
			$id .= ':' . $db->loadResult();
		}
		return array((int) $id => $id);
	}

	public function getVisformsdataSegment($id, $query) {
		// view visformsdata layout detailitem has its own menu type
		// this is specific for one form (query parameter id) and one record set (query parameter cid)
		// MenuRules router does only accept one key-url parameter, in order to determine whether a menu item exists (the id parameter)
		// this results in wrong urls
		// if we have a url with query parameters detailitem and cid, we do not return the Segment (does not match a menu item)
		// this only happens if the query does not have an Itemid parameter which actually resolves to a detailitem menu item
		// parse route of the no menu router will remove the cid from the query
		// so the no menu router will resolve to a full sef url
		if (isset($query['cid'])) {
			return array();
		}
		return $this->getVisformsSegment($id, $query);
	}

	public function getMysubmissionsSegment($id, $query) {
		return $this->getVisformsSegment($id, $query);
	}

	public function getEditSegment($id, $query) {
		return $this->getVisformsSegment($id, $query);
	}

	public function getVisformsId($segment, $query) {
		return (int) $segment;
	}

	public function getVisformsdataId($segment, $query) {
		return (int) $segment;
	}

	public function getMysubmissionsId($segment, $query) {
		return (int) $segment;
	}

	public function getEditId($segment, $query) {
		return (int) $segment;
	}

	/*public function getPath() {

	}*/
}