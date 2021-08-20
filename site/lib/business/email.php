<?php
/**
 * Visforms field email business class
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

class VisformsBusinessEmail extends VisformsBusinessText
{

	protected function setField() {
		$this->setIsDisabled();
		$this->setCustomJs();
		if (isset($this->field->dataSource) && $this->field->dataSource == 'post')
		{
			$this->validatePostValue();
		}
		$this->addShowWhenForForm();
	}
	
    protected function setCustomJs() {
    	if (!isset($this->field->validate_mailExists)) {
    		return;

	    }
	    $field = $this->field;
    	$extraAttribs = ' placeholder=\"'. JText::_('COM_VISFORMS_ENTER_VERIFICATION_CODE_PLACEHOLDER') .'\"';
    	$extraAttribs .= (!empty($field->attribute_value)) ? ' required = \"required\" aria-required=\"true\" ' : '';
    	$extraAttribs .= (isset($field->isDisabled) && ($field->isDisabled == true)) ? ' disabled=\"disabled\"' : '';
	    $extraAttribs .= ' data-error-container-id=\"fc-tbxfield'.$field->id.'_code\"';
    	$extraClass = (isset($field->isDisabled) && ($field->isDisabled == true)) ? ' ignore' : '';
    	// There is no email exists validation in edit views!
	    switch ($this->form->formlayout) {
		    case 'bt4mcindividual' :
			    $script = '
			        jQuery(document).ready( function() {
			            var parent =  jQuery("#field'.$field->id.'").parent("div");
			            jQuery("#field'.$field->id.'").wrapAll("<div class=\"input-group mb-0\"></div>");
			            parent.append("<input type=\"text\" id=\"field'.$field->id.'_code\" name=\"'.$field->name.'_code\" data-error-container-id=\"fc-tbxfield'.$field->id.'\"  autocomplete=\"nope\" class=\"form-control verificationCode'.$extraClass.'\"'. $extraAttribs .' />");	        
				        jQuery("<span class=\"input-group-btn\"><btn type=\"button\" class=\"btn btn-secondary verifyMailBtn\" onclick=\"verifyMail(\'field'.$field->id.'\',\''.$field->fid.'\',\''.JSession::getFormToken().'\',\''.Juri::base(true).'\'); return false;\">'. JText::_('COM_VISFORMS_VERIFY') .'</btn></div>").insertAfter("#field'.$field->id.'");
				        
				        jQuery("#field'.$field->id.'").on("change", function () {
				            if (jQuery(this).val()) {
				                jQuery("#field'.$field->id.'_code").prop("required", true);
				            }
				            else {
				                jQuery("#field'.$field->id.'_code"  ).prop("required", false);
				            }
				        });
			        });
			    ';
			    break;
		    case 'btdefault' :
		    case 'bthorizontal' :
			    $script = '
			        jQuery(document).ready( function() {	        
				        jQuery("<div class=\"fc-tbxfield' . $field->id . '_code\"></div><div class=\"input-prepend\"><span class=\"btn add-on verifyMailBtn\" onclick=\"verifyMail(\'field' . $field->id . '\',\'' . $field->fid . '\',\'' . JSession::getFormToken() . '\',\''.Juri::base(true).'\'); return false;\">' . JText::_('COM_VISFORMS_VERIFY') . '</span><input type=\"text\" id=\"field' . $field->id . '_code\" name=\"' . $field->name . '_code\" class=\"form-control verificationCode' . $extraClass . '\"' . $extraAttribs . ' /></div>").insertAfter("#field' . $field->id . '");
				        jQuery("#field' . $field->id . '").on("change", function () {
				            if (jQuery(this).val()) {
				                jQuery("#field' . $field->id . '_code").prop("required", true);
				            }
				            else {
				                jQuery("#field' . $field->id . '_code"  ).prop("required", false);
				            }
				        });
			        });
			    ';
			    break;
		    case 'mcindividual' :
			    $script = '
			        jQuery(document).ready( function() {	        
				        jQuery("<div class=\"input-prepend\"><span class=\"btn add-on verifyMailBtn\" onclick=\"verifyMail(\'field' . $field->id . '\',\'' . $field->fid . '\',\'' . JSession::getFormToken() . '\',\''.Juri::base(true).'\'); return false;\">' . JText::_('COM_VISFORMS_VERIFY') . '</span><input type=\"text\" id=\"field' . $field->id . '_code\" name=\"' . $field->name . '_code\" class=\"form-control verificationCode' . $extraClass . '\"' . $extraAttribs . ' /></div><div class=\"fc-tbxfield' . $field->id . '_code\"></div>").insertAfter("#field' . $field->id . '");
				        jQuery("#field' . $field->id . '").on("change", function () {
				            if (jQuery(this).val()) {
				                jQuery("#field' . $field->id . '_code").prop("required", true);
				            }
				            else {
				                jQuery("#field' . $field->id . '_code"  ).prop("required", false);
				            }
				        });
			        });
			    ';
			    break;
		    case 'bt3default' :
		    case 'bt3horizontal' :
			    $script = '
			        jQuery(document).ready( function() {	        
				        jQuery("<div class=\"fc-tbxfield' . $field->id . '_code\"></div><div class=\"input-group\"><span class=\"btn input-group-addon verifyMailBtn\" onclick=\"verifyMail(\'field' . $field->id . '\',\'' . $field->fid . '\',\'' . JSession::getFormToken() . '\',\''.Juri::base(true).'\'); return false;\">' . JText::_('COM_VISFORMS_VERIFY') . '</span><input type=\"text\" id=\"field' . $field->id . '_code\" name=\"' . $field->name . '_code\" class=\"form-control verificationCode' . $extraClass . '\"' . $extraAttribs . ' /></div>").insertAfter("#field' . $field->id . '");
				        jQuery("#field' . $field->id . '").on("change", function () {
				            if (jQuery(this).val()) {
				                jQuery("#field' . $field->id . '_code").prop("required", true);
				            }
				            else {
				                jQuery("#field' . $field->id . '_code"  ).prop("required", false);
				            }
				        });
			        });
			    ';
			    break;
		    case 'bt3mcindividual' :
			    $script = '
			        jQuery(document).ready( function() {	        
				        jQuery("<div class=\"input-group\"><span class=\"btn input-group-addon verifyMailBtn\" onclick=\"verifyMail(\'field' . $field->id . '\',\'' . $field->fid . '\',\'' . JSession::getFormToken() . '\',\''.Juri::base(true).'\'); return false;\">' . JText::_('COM_VISFORMS_VERIFY') . '</span><input type=\"text\" id=\"field' . $field->id . '_code\" name=\"' . $field->name . '_code\" class=\"form-control verificationCode' . $extraClass . '\"' . $extraAttribs . ' /></div><div class=\"fc-tbxfield' . $field->id . '_code\"></div>").insertAfter("#field' . $field->id . '");
				        jQuery("#field' . $field->id . '").on("change", function () {
				            if (jQuery(this).val()) {
				                jQuery("#field' . $field->id . '_code").prop("required", true);
				            }
				            else {
				                jQuery("#field' . $field->id . '_code"  ).prop("required", false);
				            }
				        });
			        });
			    ';
			    break;
		    default :
			    $script = '
			        jQuery(document).ready( function() {	        
				        jQuery("<div class=\"fc-tbxfield' . $field->id . '_code\"></div><span class=\"btn verifyMailBtn\" onclick=\"verifyMail(\'field' . $field->id . '\',\'' . $field->fid . '\',\'' . JSession::getFormToken() . '\',\''.Juri::base(true).'\'); return false;\">' . JText::_('COM_VISFORMS_VERIFY') . '</span><input type=\"text\" id=\"field' . $field->id . '_code\" name=\"' . $field->name . '_code\" class=\"form-control verificationCode' . $extraClass . '\"' . $extraAttribs . ' />").insertAfter("#field' . $field->id . '");
				        jQuery("#field' . $field->id . '").on("change", function () {
				            if (jQuery(this).val()) {
				                jQuery("#field' . $field->id . '_code").prop("required", true);
				            }
				            else {
				                jQuery("#field' . $field->id . '_code"  ).prop("required", false);
				            }
				        });
			        });
			    ';
			    break;
	    }
	    $this->field->customJs[] = $script;
    }

	protected function validatePostValue() {
		parent::validatePostValue();
		// addtional validation of email verification code
		if ($this->field->attribute_value != "" && isset($this->field->validate_mailExists)) {
			$code = JFactory::getApplication()->input->post->get($this->field->name . '_code', '', 'STRING');
			if (VisformsValidate::validate('verificationcode', array('value' => $code, 'verificationAddr' => $this->field->attribute_value)) !== true) {
				$error = JText::sprintf('COM_VISFORMS_POST_VALIDATION_CODE_INVALID', $this->field->label);
				$this->field->isValid = false;
				$this->setErrors($error);
			}
			return;
		}
	}
}