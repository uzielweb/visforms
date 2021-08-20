<?php
/**
 * Visforms message class
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

abstract class VisformsMessage
{
	protected $name;
	protected $type;
	protected $text;
	protected $messageType;

	public function __construct($name, $args, $messageType = 'warning') {
		$this->name = $name;
		$this->messageType = $messageType;
	}

	public static function getMessage($name, $type, $args = array()) {
		$classname = get_called_class() . ucfirst($type);
		if (!class_exists($classname)) {
			//try to register it
			JLoader::register($classname, dirname(__FILE__) . '/message/' . $type . '.php');
			if (!class_exists($classname)) {
				//return a default class?
				return false;
			}
		}
		//Get message from the appropriate subclass
		$message = new $classname($name, $args);
		return $message->setMessage();
	}

	abstract protected function setMessage();
}