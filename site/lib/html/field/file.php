<?php
/**
 * Visforms HTML class for file fields
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
require_once(__DIR__ . '/text.php');

/**
 * Create HTML of a file field according to it's type
 *
 * @package        Joomla.Site
 * @subpackage     com_visforms
 * @since          1.6
 */
class VisformsHtmlFile extends VisformsHtmlText
{

	public function __construct($field, $decorable, $attribute_type) {
		$attribute_type = "file";
		parent::__construct($field, $decorable, $attribute_type);
	}

	public function removeNoBootstrapClasses($field) {
		if ((isset($field->attribute_class))) {
			$field->attribute_class = '';
		}
		return $field;
	}

	// only called on bootstrap 4 layouts
	public function setControlHtmlClasses($field) {
		if (!empty($field->custominfo)) {
			$field->attribute_title = htmlspecialchars($field->custominfo, ENT_COMPAT, 'UTF-8');
			$field->attribute_class .= ' visToolTip';
			JHtmlVisforms::visformsTooltip();
		}
		return $field;
	}

	public function setUikit3ControlHtmlClasses($field) {
		return $this->setControlHtmlClasses($field);
	}

	public function setUikit2ControlHtmlClasses($field) {
		return $this->setControlHtmlClasses($field);
	}

	public function removeUnsupportedShowLabel($field) {
		unset($field->show_label);
		return $field;
	}
}