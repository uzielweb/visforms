<?php
/**
 * Visforms decorator class for HTML controls
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
require_once JPATH_ROOT . '/components/com_visforms/lib/layout/helper/uikit3field.php';
require_once JPATH_ROOT . '/components/com_visforms/lib/layout/helper/uikit2field.php';

abstract class VisformsHtmlControl
{
	protected $field;
	protected $layout;
	protected $breakPoints;
	protected $fieldHelper;

	public function __construct($field, $layout) {
		$this->field = $field;
		$this->layout = $layout;
		$this->breakPoints = array('Sm', 'Md', 'Lg', 'Xl');
		$this->setFieldHelper();
	}

    public static function getInstance($field, $layout)
    {
        $fieldtype = $field->getField()->typefield;
        $classname = get_called_class() . ucfirst($layout) . ucfirst($fieldtype);
        if (!class_exists($classname))
        {
        	// bt4 Layout? We have an control implementation for each field type in bt4mcindividual
	        if ($layout === 'editbt4mcindividual') {
		        $classname = get_called_class() . ucfirst('bt4mcindividual') . ucfirst($fieldtype);
	        }
	        // uikit3 Layout? We have an control implementation for each field type in bt4mcindividual
	        else if ($layout === 'edituikit3') {
		        $classname = get_called_class() . ucfirst('uikit3') . ucfirst($fieldtype);
	        }
	        // uikit3 Layout? We have an control implementation for each field type in bt4mcindividual
	        else if ($layout === 'edituikit2') {
		        $classname = get_called_class() . ucfirst('uikit2') . ucfirst($fieldtype);
	        }
            //check if we have an implemantation in btdefault
	        else if (in_array($layout, array('editbthorizontal', 'editbtdefault', 'editmcindividual', 'bthorizontal', 'mcindividual'))) {
                $classname = get_called_class() . ucfirst('btdefault') . ucfirst($fieldtype);
            }
            else {
                if (in_array($layout, array('editbt3horizontal', 'editbt3default', 'editbt3mcindividual', 'bt3horizontal', 'bt3mcindividual'))) {
                    $classname = get_called_class() . ucfirst('bt3default') . ucfirst($fieldtype);
                }
                else {
                    $classname = get_called_class() . ucfirst('visforms') . ucfirst($fieldtype);
                }
            }
        }

        if (!class_exists($classname)) {
            //fall back on the visform default
            $classname = get_called_class() . ucfirst('visforms') . ucfirst($fieldtype);
        }

        //delegate to the appropriate subclass
        return new $classname($field, $layout);
    }

    abstract public function getControlHtml();

    /**
     * Method to create label html string
     * @return string label html or ''
     */
    public function createLabel()
    {
        return '';
    }

    /**
     * Method to create class attribute value for label tag according to layout
     * @return string class attribute value
     */
    protected function getLabelClass()
    {
        $labelClass = '';
        switch ($this->layout) {
            case 'bthorizontal' :
            case 'editbthorizontal' :
                $labelClass = ' control-label ';
                break;
            case 'bt3horizontal' :
            case 'editbt3horizontal' :
                $labelClass = 'col-sm-3 control-label ';
                break;
            case 'bt3mcindividual' :
            case 'editbt3mcindividual' :
            case 'btdefault' :
            case 'editbtdefault' :
            case 'bt3default' :
            case 'edit3btdefault' :
                break;
	        case 'bt4mcindividual' :
	        case 'editbt4mcindividual' :
		        $field = $this->field->getField();
		        $labelClass = 'col-' . $field->labelBootstrapWidth;
		        foreach ($this->breakPoints as $breakPoint) {
			        $name = 'labelBootstrapWidth' . $breakPoint;
			        $lcBreakPoint = lcfirst($breakPoint);
			        $labelClass .= ($field->$name != "12") ? ' col-' . $lcBreakPoint . '-' . $field->$name : '';
		        }
		        $labelClass .= (!empty($field->show_label)) ? ' col-form-label sr-only' : ' col-form-label';
	        break;
	        case 'uikit2' :
	        case 'edituikit2' :
	        case 'uikit3' :
	        case 'edituikit3' :
				$labelClass = $this->fieldHelper->getLabelClass();
		        break;
            default :
                $labelClass = ' visCSSlabel ';
                break;
        }
        return $labelClass;
    }

    /**
     * Method to create html for field custom text
     * @return string custom text or ''
     */
    public function getCustomText()
    {
    	switch ($this->layout) {
		    case 'bt4mcindividual' :
		    case 'editbt4mcindividual' :
			    $class = $this->getCtClasses();
			    break;
		    case 'uikit3' :
		    case 'edituikit3' :
			    $class = $this->getUikit3CtClasses();
			    break;
		    case 'uikit2' :
		    case 'edituikit2' :
			    $class = $this->getUikit2CtClasses();
			    break;
		    default :
			    $class = (in_array($this->layout, array('bt3default', 'editbt3default', 'bt3horizontal', 'editbt3horizontal', 'bt3mcindividual', 'editbt3mcindividual'))) ? 'help-block' : 'visCustomText';
			    $class .= (in_array($this->layout, array('bt3horizontal', 'editbt3horizontal'))) ? ' col-sm-offset-3 col-sm-9' : '';
			    break;
	    }
        $field = $this->field->getField();
        //input
        $html = '';
        if (isset($field->customtext) && ($field->customtext != ''))
        {
            JPluginHelper::importPlugin('content');
            $customtext = JHtml::_('content.prepare', $field->customtext);
            if (in_array($this->layout, array('uikit3', 'edituikit3', 'uikit2', 'edituikit2'))) {
            	$html .= '<label class="'.$this->getLabelClass().'"></label>';
            }
            $html .= '<div class="'.$class.' ">' . $customtext . '</div>';
        }
        //Trigger onVisformsAfterCustomtextPrepare event to allow changes on field properties before control html is created
        JPluginHelper::importPlugin('visforms');
		//make custom adjustments to the custom text html
		JFactory::getApplication()->triggerEvent('onVisformsAfterCustomtextPrepare', array('com_visforms.field', &$html, $this->layout));
        return $html;
    }

	//Override in control/layoutname/fieldtypename.php for custom classes if necessary
	//proper indentation with regards to label width for elements that do not have a label in front of them, i.e. custom text, error div, checkbox control...
	protected function getCtClasses() {
		$field = $this->field->getField();
		// width always 12 in total
		$ctClasses = ($field->labelBootstrapWidth != "12") ? 'offset-' . $field->labelBootstrapWidth . ' col-' . (12 - $field->labelBootstrapWidth) : 'col-12';
		foreach ($this->breakPoints as $breakPoint) {
			$name = 'labelBootstrapWidth' . $breakPoint;
			$lcBreakPoint = lcfirst($breakPoint);
			$ctClasses .= ($field->$name != "12") ? ' offset-' . $lcBreakPoint . '-' . $field->$name . ' col-' . $lcBreakPoint . '-' . (12 - $field->$name) : '';
		}
		return $ctClasses;
	}

	protected function getUikit3CtClasses() {
		return  $this->fieldHelper->getCtrlGroupUikit3Classes();
	}

	protected function getUikit2CtClasses() {
		return  $this->fieldHelper->getCtrlGroupUikit2Classes();
	}

	// Todo Refactor: Field helper is used in uikit specific code only (base class and implementation for each layout???
	protected function setFieldHelper() {
    	switch ($this->layout) {
		    case 'uikit2' :
		    case 'edituikit2' :
			    $this->fieldHelper = new VisformsUikit2FieldHelper();
		        $this->fieldHelper->setField($this->field->getField());
			    break;
		    case 'uikit3' :
		    case 'edituikit3' :
			    $this->fieldHelper = new VisformsUikit3FieldHelper();
		        $this->fieldHelper->setField($this->field->getField());
			    break;
	    }
	}
}