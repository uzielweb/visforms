<?php 
/**
 * Visfield field view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

defined('_JEXEC') or die('Restricted access');
    
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
$jVersion = new JVersion();
$fieldsetsWithOptionlist = array('visf_select_options', 'visf_radio_options', 'visf_multicheckbox_options');
$sqlOptionListfieldNames = array('f_selectsql_sql', 'f_radiosql_sql', 'f_multicheckboxsql_sql');

?>

<script type="text/javascript">
    jQuery(document).ready(function() {
        visHelperAsync.stopWaitDonut();
    });
    var visField = {
        testSqlStatement: function (event, button) {
            event.preventDefault();
            var element = jQuery(button).parents('.sql-edit-controls');
            var sql = jQuery(element).find('[id$=_sql]').val();
            // escape quotes
            sql = sql.replace(/"/g, '\\\"');
            // escape special characters
            sql = encodeURIComponent(sql);
            var messageDiv = jQuery(button).siblings('.sql-message-field');
            if(sql) {
                var waitDonut = jQuery(button).siblings('.div_ajax-call-wait').find('.icon_ajax-call-wait');
                var data = 'data=' + JSON.stringify({ statement: sql, "<?php echo JSession::getFormToken();?>" : 1 });
                // show the waiting icon during the request
                visHelperAsync.startWaitDonut(waitDonut);
                jQuery.ajax({
                    type: 'POST',
                    url: 'index.php?option=com_visforms&task=visfield.testSqlStatement',
                    data: data,
                    success: function(data, textStatus, jqXHR) {
                        visHelperAsync.stopWaitDonut();
                        jQuery(messageDiv).text(data.message);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        visHelperAsync.stopWaitDonut();
                        // give error feedback
                        jQuery(messageDiv).text('error');
                    },
                    dataType: 'json',
                    async: true
                });
            }
            else {
                jQuery(messageDiv).text('<?php echo JText::_('COM_VISFORMS_EMPTY_SQL_STATEMENT'); ?>');
            }
        },
    }

    var visHelperAsync = {
        startWaitDonut: function (waitDonut) {
            jQuery(waitDonut).show();
        },
        stopWaitDonut: function (waitDonut) {
            if(null == waitDonut) {
                jQuery('.icon_ajax-call-wait').hide();
            }
            else {
                jQuery(waitDonut).hide();
            }
        }
    };
    function removeUnused(selected) {
        var fieldType = ['text', 'email', 'date', 'url', 'number', 'password', 'hidden', 'textarea', 'checkbox', 'multicheckbox', 'radio', 'select', 'file', 'image', 'reset', 'submit', 'fieldsep', 'pagebreak', 'calculation', 'location', 'signature', 'multicheckboxsql', 'radiosql', 'selectsql'];
        var fieldTypesWithOptionlist = ['multicheckbox', 'radio', 'select'];
        for (var i in fieldType) {
            if (selected != fieldType[i]) {
                try {
                    var elname = 'visf_' + fieldType[i];
                    var el = document.getElementById(elname);
                    el.parentNode.removeChild(el);
                }
                catch (e) { }
            }
            for (var j in fieldTypesWithOptionlist) {
                if (selected != fieldTypesWithOptionlist[j]) {
                    try {
                        var elname = 'visf_' + fieldTypesWithOptionlist[j] + '_options';
                        var el = document.getElementById(elname);
                        el.parentNode.removeChild(el);
                    }
                    catch (e) { }
                }
            }
        }
    }
	Joomla.submitbutton = function(task) {
		if (task == 'visfield.cancel') {
            jQuery('#permissions-sliders select').attr('disabled', 'disabled');
            Joomla.submitform(task, document.getElementById('item-form'));
		}
		else if (document.formvalidator.isValid(document.getElementById('item-form'))) {
            Joomla.removeMessages();
            jQuery('#permissions-sliders select').attr('disabled', 'disabled');
            //make sure the typefield has a selected value
            var ft = document.getElementById('jform_typefield');
            var idx = ft.selectedIndex;
            var sel = ft[idx].value;
            switch (sel) {
                case '0' :
                    alert('<?php echo $this->escape(JText::_('COM_VISFORMS_TYPE_FIELD_REQUIRED'));?>');
                    break;
                case 'checkbox' :
                    var cbval = document.getElementById('jform_defaultvalue_f_checkbox_attribute_value');
                    if (cbval.value == "") {
                        alert('<?php echo $this->escape(JText::_('COM_VISFORMS_CHECKBOX_VALUE_REQUIRED'));?>');
                    }
                    else {
                        removeUnused(sel);
                        Joomla.submitform(task, document.getElementById('item-form'));
                    }
                    break;
                case 'multicheckbox' :
                case 'radio' :
                    jQuery('#jform_defaultvalue_f_' + sel + '_list_hidden').storeVisformsOptionCreatorData();
                    var grpel = document.getElementById('jform_defaultvalue_f_' + sel + '_list_hidden');
                    var countDefOpts = document.getElementById('jform_defaultvalue_f_' + sel + '_countDefaultOpts').value;
                    if (grpel.value == "" || grpel.value == "{}") {
                        alert('<?php echo $this->escape(JText::_('COM_VISFORMS_OPTIONS_REQUIRED'));?>');
                    }
                    else if (countDefOpts > 1) {
                        alert('<?php echo $this->escape(JText::_('COM_VISFORMS_ONLY_ONE_DEFAULT_OPTION_POSSIBLE'));?>');
                    }
                    else {
                        removeUnused(sel);
                        Joomla.submitform(task, document.getElementById('item-form'));
                    }
                    break;
                case 'select' :
                    jQuery('#jform_defaultvalue_f_' + sel + '_list_hidden').storeVisformsOptionCreatorData();
                    var grpel = document.getElementById('jform_defaultvalue_f_' + sel + '_list_hidden');
                    var countDefOpts = document.getElementById('jform_defaultvalue_f_' + sel + '_countDefaultOpts').value;
                    var isMultiple = document.getElementById('jform_defaultvalue_f_' + sel + '_attribute_multiple').checked;
                    if (grpel.value == "" || grpel.value == "{}") {
                        alert('<?php echo $this->escape(JText::_('COM_VISFORMS_OPTIONS_REQUIRED'));?>');
                    }
                    else if ((countDefOpts > 1) && (isMultiple == false)) {
                        alert('<?php echo $this->escape(JText::_('COM_VISFORMS_ONLY_ONE_DEFAULT_OPTION_POSSIBLE'));?>');
                    }
                    else {
                        removeUnused(sel);
                        Joomla.submitform(task, document.getElementById('item-form'));
                    }
                    break;
                case 'radiosql':
                case 'multicheckboxsql':
                case 'selectsql':
                    var sql = document.getElementById('jform_defaultvalue_f_' + sel + '_sql').value;
                    if (sql == "") {
                        alert('<?php echo $this->escape(JText::_('COM_VISFORMS_SQL_REQUIRED'));?>');
                    }
                    else {
                        removeUnused(sel);
                        Joomla.submitform(task, document.getElementById('item-form'));
                    }
                    break;
                case 'image':
                        var altt = document.getElementById('jform_defaultvalue_f_image_attribute_alt');
                        var image = document.getElementById('jform_defaultvalue_f_image_attribute_src');
                        if ((altt.value == "") || (image.value == "")) {
                            if (altt.value == "") {
                                alert('<?php echo $this->escape(JText::_('COM_VISFORMS_ALT_TEXT_REQUIRED'));?>');
                            }
                            else {
                                alert('<?php echo $this->escape(JText::_('COM_VISOFORMS_FIELD_IMAGE_IMAGE_REQUIRED'));?>');
                            }
                        }
                        else {
                            removeUnused(sel);
                            Joomla.submitform(task, document.getElementById('item-form'));
                        }
                    break;
                case 'location' :
                    var reLat = /^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/;
                    var reLng = /^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/;
                    var latCenter = document.getElementById('jform_defaultvalue_f_location_defaultMapCenter_lat').value;
                    var lngCenter = document.getElementById('jform_defaultvalue_f_location_defaultMapCenter_lng').value;
                    var validLatCenter = reLat.test(latCenter) && latCenter !== "";
                    var validLngCenter = reLng.test(lngCenter) && lngCenter !== "";
                    var latPos = document.getElementById('jform_defaultvalue_f_location_attribute_value_lat').value;
                    var lngPos = document.getElementById('jform_defaultvalue_f_location_attribute_value_lng').value;
                    var validLatPos = (reLat.test(latPos) || (latPos === "" && lngPos === ""));
                    var validLngPos = (reLng.test(lngPos) || (latPos === "" && lngPos === ""));
                    if (!validLatCenter || !validLngCenter) {
                        alert('<?php echo $this->escape(JText::_('COM_VISFORMS_LOCATION_DEFAULT_CENTER_VALUES_REQUIRED'));?>');
                    } else if (!validLatPos || !validLngPos) {
                        alert('<?php echo $this->escape(JText::_('COM_VISFORMS_LOCATION_DEFAULT_POSITION_VALUES_INVALID_FORMAT'));?>');
                    }
                    else {
                        removeUnused(sel);
                        Joomla.submitform(task, document.getElementById('item-form'));
                    }
                    break;
                default :
                    removeUnused(sel);
                    Joomla.submitform(task, document.getElementById('item-form'));
                    break;
            }
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_("$this->baseUrl&view=$this->editViewName&layout=edit&id=$this->id&fid=$this->fid"); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
    <input type="hidden" name="option" value="com_visforms" />
    <input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
    <input type="hidden" name="fid" value="<?php echo $this->fid; ?>" />
    <input type="hidden" name="ordering" value="<?php echo $this->item->ordering; ?>" />
    <input type="hidden" name="controller" value="visfields" /><?php
    $layout = new JLayoutFile('div.form_hidden_inputs');
    echo $layout->render(); ?>
	<div class="form-inline form-inline-header"><?php
        echo $this->form->getControlGroup('label');
        echo $this->form->getControlGroup('name'); ?>
    </div>
    <div class="form-horizontal"><?php
        echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'basicfieldinfo'));
        echo JHtml::_('bootstrap.addTab', 'myTab', 'basicfieldinfo', JText::_('COM_VISFORMS_FIELD_BASIC_INFO')); ?>
        <div class="row-fluid form-horizontal-desktop">
            <div class="span6"><?php
                foreach ($this->form->getFieldset('basicfieldinfo') as $field) {
                    if($field->fieldname != 'ordering') {
                        echo $field->renderField();
                    }
                } ?>
            </div>
            <div class="span6"><?php
                $groupFieldSets = $this->form->getFieldsets('defaultvalue');
                foreach ($groupFieldSets as $name => $fieldSet) {
                    if (in_array($name, $fieldsetsWithOptionlist)) {continue;}?>
                    <div id="<?php echo $name; ?>"><?php
                        foreach ($this->form->getFieldset($name) as $field) {
	                        if (in_array($field->fieldname, $sqlOptionListfieldNames)) {
		                        $statement = $this->form->getField($field->fieldname, 'defaultvalue');
		                        $renderData  = array("input" => $statement->__get('input'), "view" => $this, "task" => 'visField.testSqlStatement');
		                        $label = $statement->__get('label');
		                        $input = (new JLayoutFile('renderpdf.fields.sql_statement_selection'))->render($renderData);
		                        echo $statement->render('joomla.form.renderfield', array("label" => $label, "input" => $input));
		                        continue;
	                        }
                           //if we have a date field we have to set default dateformat for the calendar
                           if ($field->fieldname === "f_date_attribute_value") {
                               $dateFormatField = $this->form->getField('f_date_format', 'defaultvalue');
                               if ($dateFormatField->value != "") {
                                   // get date format for javascript
                                   $dFormat = explode(";", $dateFormatField->value);
                                   if (isset($dFormat[1])) {
                                       $this->form->setFieldAttribute("f_date_attribute_value", "format", $dFormat[1], 'defaultvalue');
                                   }
                               }
                           }
                           echo $field->renderField();
                        } ?>
                    </div> <?php
                } ?>
            </div>
        </div>
        <div class="row-fluid form-horizontal-desktop">
            <div class="span12"><?php
                foreach ($fieldsetsWithOptionlist as $name) {
                    $groupFieldSet = $this->form->getFieldset($name, 'defaultvalue'); ?>
                    <div id="<?php echo $name; ?>"><?php
                    foreach ($groupFieldSet as $field) {
	                    echo $field->renderField();
                    }
                    ?>
                    </div><?php
                } ?>
            </div>
        </div><?php
        echo JHtml::_('bootstrap.endTab');
        echo JHtml::_('bootstrap.addTab', 'myTab', 'visfield-advanced-detailso', JText::_('COM_VISFORMS_TAB_ADVANCED')); ?>
        <div class="row-fluid form-horizontal-desktop">
            <div class="span6">
                <h3><?php echo JText::_('COM_VISFORMS_TAB_LAYOUT'); ?></h3><?php
	            $btgridLayout = $this->form->getFieldset('visfield-bootstrap-grid');
	            if (!empty($btgridLayout)) {
		            echo '<div id="bootstrapGridSizes">';
		            foreach ($btgridLayout as $field) {
			            echo $field->renderField();
		            }
		            echo '</div>';
	            }
                $fsLayout = $this->form->getFieldset('visfield-layout-details');
                foreach ($fsLayout as $field) {
                    echo $field->renderField();
                } ?>
            </div>
            <div class="span6">
                <h3><?php echo JText::_('COM_VISFORMS_HEADER_USAGE'); ?></h3><?php
	            $fsAdvanced = $this->form->getFieldset('visfield-advanced-details');
	            foreach ($fsAdvanced as $field) {
		            echo $field->renderField();
	            }
	            $fslayoutcustomtext= $this->form->getFieldset('layout-custom-text');
	            foreach ($fslayoutcustomtext as $field) {
		            echo $field->renderField();
	            } ?>
            </div>
        </div><?php
        echo JHtml::_('bootstrap.endTab');

        if ($this->canDo->get('core.admin')) {
            echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_VISFORMS_FIELDSET_FIELD_RULES', true));
            echo $this->form->getInput('rules');
            JHtml::_('bootstrap.endTab');
        }

        echo JHtml::_('bootstrap.endTabSet'); ?>
    </div>
</form>