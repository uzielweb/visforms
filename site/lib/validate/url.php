<?php
/**
 * Visforms validate Url class
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

class VisformsValidateUrl extends VisformsValidate
{
	protected $value;
	protected $regex;

	public function __construct($type, $args) {
		parent::__construct($type, $args);
		$this->regex = '/^(http|https|ftp)\:\/\/[a-z0-9\-\.]+\.[a-z]{2,3}(:[a-z0-9]*)?\/?([a-z0-9\-\._\?\,\'\/\\\+&amp;%\$#\=~])*$/i';
		//allow localhost, too
		//$this->regex = '/^(http|https|ftp)\:\/\/(localhost|[a-z0-9\-\.]+\.[a-z]{2,3})(:[a-z0-9]*)?\/?([a-z0-9\-\._\?\,\'\/\\\+&amp;%\$#\=~])*$/i';
		//we expect an item with key 'value' in $args
		$this->value = isset($args['value']) ? $args['value'] : "";
	}

	protected function test() {
		if (!(preg_match($this->regex, $this->value) == true)) {
			return false;

		} 
		else {
			return true;
		}
	}
}