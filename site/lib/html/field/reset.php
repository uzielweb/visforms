<?php
/**
 * Visforms HTML class for reset button
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
require_once(__DIR__ . '/submit.php');

/**
 * Create HTML of a reset button according to it's type
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsHtmlReset extends VisformsHtmlSubmit
{     
    /**
     * 
     * Constructor
     * 
     * @param object $field field object as extracted from database
     */
    public function __construct($field, $decorable, $attribute_type)
    {
        $attribute_type = "reset";
        parent::__construct($field, $decorable, $attribute_type);
    }

	public function setControlHtmlClasses($field) {
		$field->attribute_class = (!empty($this->field->fieldCSSclass)) ? ' btn ' : ' btn btn-danger';
		return $field;
	}

	public function setUikit3ControlHtmlClasses($field) {
		$field->attribute_class = (!empty($this->field->fieldCSSclass)) ? ' btn ' : ' btn uk-button-danger';
		return $field;
	}

	public function setUikit2ControlHtmlClasses($field) {
		$field->attribute_class = (!empty($this->field->fieldCSSclass)) ? '' : ' uk-button uk-button-danger';
		return $field;
	}
}