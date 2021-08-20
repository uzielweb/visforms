<?php
/**
 * Sql Class for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6
 */
defined('_JEXEC') or die( 'Direct Access to this location is not allowed.' );

class visformsSql
{
	private $sql;
	private $input;
	private $user;
	private $frameworkObjects;
	private $filters;
	private $inputContext;

	public function __construct($sql, $inputContext = '') {
		$this->sql = $sql;
		$this->input = JFactory::getApplication()->input;
		$this->user = JFactory::getUser();
		$this->frameworkObjects = array('user', 'input');
		$this->filters = array('user' => array('password'));
		$this->inputContext = $inputContext;
	}

	public function getItemsFromSQL() {
		$this->sql = $this->processFrameworkObjects($this->sql);
		// create a shortcut
		$sql = $this->sql;
		if ($this->checkIsSelectSqlOnly()) {
			try {
				$db = JFactory::getDbo();
				$db->setQuery($sql);
				$items = $db->loadObjectList();
				return $items;
			}
			catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
		}
		else {
			throw  new Exception(JText::_("COM_VISFORMS_CREATE_SQL_BAD_SQL"));
		}
	}

	// ToDo use different implementations for user and input object
	private function processFrameworkObjects($text) {
		// replace placeholder for frameworkobjects with value
		foreach ($this->frameworkObjects as $fObj) {
			$filter = (array_key_exists($fObj, $this->filters)) ? $this->filters[$fObj] : array();
			$placeholders = new VisformsPlaceholder($text);
			while ($placeholders->hasNext()) {
				$placeholders->getNext();
				$pName = $placeholders->getPlaceholderPart('name');
				if ($fObj == $placeholders->getPlaceholderPart('context') && !(in_array($pName, $filter))) {
					$obj = ('user' == $fObj) ? $this->user : $this->input;
					$replace = $obj->get($pName, null, 'string');
					if (is_null($replace) && 'input' == $fObj) {
						$replace = $obj->get($this->inputContext . $pName, null, 'string');
					}
					// plugin form view sets paremaeters in input get
					if (is_null($replace) && 'input' == $fObj) {
						$replace = $obj->get->get($pName, null, 'string');
					}
					if (is_null($replace) && 'input' == $fObj) {
						$replace = $obj->get->get($this->inputContext . $pName, null, 'string');
					}
					//custom replace function for framework objects
					$placeholders->replaceForSql($replace);
				}
			}
			$text = $placeholders->getText();
		}
		return $placeholders->getText();
	}

	public function checkIsSelectSqlOnly() {
		$forbiddenKeywords = array('insert', 'update', 'delete', 'create', 'set', 'drop');
		try {
			$parsed = $this->parseSql();
			if ($parsed === false) {
				// no query elements found, i.e. where without where
				return true;
			}
			if (is_null($parsed)) {
				return true;
			}
			// find sql keywords recursively in $parsed
			$foundKeyWords = $this->array_keys_multi($parsed);
			foreach ($foundKeyWords as $keyword) {
				if (in_array(strtolower($keyword), $forbiddenKeywords)) {
					return false;
				}
			}
		}
		catch (Exception $e) {
			return false;
		}
		return true;
	}

	protected function parseSql() {
		require_once JPATH_ROOT .'/libraries/visolutions/php_sql_parser/PHPSQLParser.php';
		try {
			// calcPosition throws really fast errors and it's use it not necessary
			$parser = new PHPSQLParser($this->sql, false);
			return $parser->parsed;
		}
		catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	// recursively find all sql keywords in parsed sql tree
	protected function array_keys_multi(array $array) {
		$keys = array();
		foreach ($array as $key => $value) {
			// all keys which are sql key words are upper case words
			if ($key === strtoupper($key)) {
				$keys[] = $key;
			}
			if (is_array($value)) {
				$keys = array_merge($keys, $this->array_keys_multi($value));
			}
		}

		return $keys;
	}
}