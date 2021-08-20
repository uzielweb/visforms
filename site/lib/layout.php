<?php
/**
 * Visforms Layout class
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

abstract class VisformsLayout
{
	protected $type;
	protected $options;

	public function __construct($type, $options) {
		$this->type = $type;
		$this->showRequiredAsterix = true;
		$this->parentFormId = "";
		$this->errormessagenopopup = 0;
		$this->defaultresponsive = 0;
		//get additional options from $options
		if (!(is_null($options))) {
			if (isset($options['showRequiredAsterix'])) {
				$this->showRequiredAsterix = $options['showRequiredAsterix'];
			}
			if (isset($options['parentFormId'])) {
				$this->parentFormId = $options['parentFormId'];
			}
			if (isset($options['errormessagenopopup'])) {
				$this->errormessagenopopup = $options['errormessagenopopup'];
			}
			if (isset($options['defaultresponsive'])) {
				$this->defaultresponsive = $options['defaultresponsive'];
			}
		}
	}

	public static function getInstance($type = 'visforms', $options = null) {
		if ($type == '') {
			$type = 'visforms';
		}
		$classname = get_called_class() . ucfirst($type);
		if (!class_exists($classname)) {
			//try to register it
			JLoader::register($classname, dirname(__FILE__) . '/layout/' . $type . '.php');
			if (!class_exists($classname)) {
				//return a default class?
				return false;
			}
		}
		//delegate to the appropriate subclass
		return new $classname($type, $options);
	}


	//Method to add layout specific custom css to the view.
	public function addCss() {
		$css = "";
		$doc = JFactory::getDocument();
		if ($this->showRequiredAsterix == true) {
			$css .= $this->getCustomRequiredCss($this->parentFormId);
		}
		if ($this->errormessagenopopup == true && (method_exists($this, 'errorMessageCss'))) {
			$css .= $this->errorMessageCss($this->parentFormId);
		}
		if ($this->defaultresponsive == true && (method_exists($this, 'addDefaultResponsiveCss'))) {
			$css .= $this->addDefaultResponsiveCss($this->parentFormId);
		}
		if (method_exists($this, 'addCustomCss')) {
			$css .= $this->addCustomCss($this->parentFormId);
		}
		if ($css != "") {
			$doc->addStyleDeclaration($css);
		}
	}

	abstract protected function getCustomRequiredCss($parent);
}