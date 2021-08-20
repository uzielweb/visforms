<?php
/**
 * Visform form view for Visforms
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

jimport( 'joomla.html.editor' );
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

// Check if TinyMCE editor is enable. If not we have to hide the editor buttons
$db = JFactory::getDbo();
$query = $db->getQuery(true)
    ->select($db->qn('element'))
    ->from($db->qn('#__extensions'))
    ->where($db->qn('element') .' = ' . $db->quote('tinymce'))
    ->where($db->qn('folder') .' = ' . $db->quote('editors'))
    ->where($db->qn('enabled') . ' = 1');
$db->setQuery($query, 0, 1);
$editor = $db->loadResult();
$hasSub = VisformsAEF::checkAEF(VisformsAEF::$subscription);

// if no editor is found stop tinyMCE is disabled
if (!$editor) {
    // hide editor button div
    $css = '#editor-xtd-buttons {display: none;}';
    $doc = JFactory::getDocument();
    $doc->addStyleDeclaration($css);
}
$hasFrontEdit = VisformsAEF::checkAEF(VisformsAEF::$allowFrontEndDataEdit);
$jVersion = new JVersion(); ?>

<script type="text/javascript">
	Joomla.submitbutton = function(task) {
        if (task == 'visform.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
            jQuery('#permissions-sliders select').attr('disabled', 'disabled');
            if (task !='visform.cancel') {
                if (typeof jQuery('#jform_visformsmailattachments_params_f_attachment_list_hidden').storeVisformsOptionCreatorData !== "undefined") {
                    jQuery('#jform_visformsmailattachments_params_f_attachment_list_hidden').storeVisformsOptionCreatorData();
                }
                if (typeof jQuery('#jform_visformseditmailattachments_params_f_editattachment_list_hidden').storeVisformsOptionCreatorData !== "undefined") {
                    jQuery('#jform_visformseditmailattachments_params_f_editattachment_list_hidden').storeVisformsOptionCreatorData();
                }
            }
            Joomla.submitform(task, document.getElementById('item-form'));
        }
        else {
            alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
        }
    }
    var googleReCaptchaListBoxSelection = function () {
        var layout = document.getElementById('jform_layoutsettings_formlayout');
        var layoutVal = layout.options[layout.selectedIndex].value;
        var subLayout = document.getElementById('jform_layoutsettings_displaysublayout');
        var subLayoutVal = subLayout.options[subLayout.selectedIndex].value;
        var captcha = document.getElementById('jform_captcha');
        var captchaval = captcha.options[captcha.selectedIndex].value;
        if (layoutVal == 'btdefault' && subLayoutVal == 'individual') {
            if (captchaval == 2) {
                document.getElementById('jform_captchaoptions_grecaptcha2label_bootstrap_size').parentNode.parentNode.style.display='block';
            }
            else {
                document.getElementById('jform_captchaoptions_grecaptcha2label_bootstrap_size').parentNode.parentNode.style.display='none';
            }
        }
        else {
            document.getElementById('jform_captchaoptions_grecaptcha2label_bootstrap_size').parentNode.parentNode.style.display='none';
        }
    }
    var googleReCaptchaBt4ListBoxSelection = function () {
        var layout = document.getElementById('jform_layoutsettings_formlayout');
        var layoutVal = layout.options[layout.selectedIndex].value;
        var subLayout = document.getElementById('jform_layoutsettings_displaysublayout');
        var subLayoutVal = subLayout.options[subLayout.selectedIndex].value;
        if (layoutVal == 'bt4mcindividual'  && subLayoutVal == 'individual') {
            document.getElementById('jform_captchaoptions_captchaLabelBootstrapWidth').parentNode.parentNode.style.display='block';
            document.getElementById('jform_captchaoptions_captchaLabelBootstrapWidthSm').parentNode.parentNode.style.display='block';
            document.getElementById('jform_captchaoptions_captchaLabelBootstrapWidthMd').parentNode.parentNode.style.display='block';
            document.getElementById('jform_captchaoptions_captchaLabelBootstrapWidthLg').parentNode.parentNode.style.display='block';
            document.getElementById('jform_captchaoptions_captchaLabelBootstrapWidthXl').parentNode.parentNode.style.display='block';
            document.getElementById('jform_captchaoptions_btLabelWidthDesc-lbl').parentNode.parentNode.style.display='block';
        } else {
            document.getElementById('jform_captchaoptions_captchaLabelBootstrapWidth').parentNode.parentNode.style.display='none';
            document.getElementById('jform_captchaoptions_captchaLabelBootstrapWidthSm').parentNode.parentNode.style.display='none';
            document.getElementById('jform_captchaoptions_captchaLabelBootstrapWidthMd').parentNode.parentNode.style.display='none';
            document.getElementById('jform_captchaoptions_captchaLabelBootstrapWidthLg').parentNode.parentNode.style.display='none';
            document.getElementById('jform_captchaoptions_captchaLabelBootstrapWidthXl').parentNode.parentNode.style.display='none';
            document.getElementById('jform_captchaoptions_btLabelWidthDesc-lbl').parentNode.parentNode.style.display='none';
        }
        if (layoutVal == 'uikit3' && subLayoutVal == 'individual') {
            document.getElementById('jform_captchaoptions_captchaLabelUikit3Width').parentNode.parentNode.style.display='block';
            document.getElementById('jform_captchaoptions_captchaLabelUikit3WidthSm').parentNode.parentNode.style.display='block';
            document.getElementById('jform_captchaoptions_captchaLabelUikit3WidthMd').parentNode.parentNode.style.display='block';
            document.getElementById('jform_captchaoptions_captchaLabelUikit3WidthLg').parentNode.parentNode.style.display='block';
            document.getElementById('jform_captchaoptions_captchaLabelUikit3WidthXl').parentNode.parentNode.style.display='block';
            document.getElementById('jform_captchaoptions_uikit3LabelWidthDesc-lbl').parentNode.parentNode.style.display='block';
        } else {
            document.getElementById('jform_captchaoptions_captchaLabelUikit3Width').parentNode.parentNode.style.display='none';
            document.getElementById('jform_captchaoptions_captchaLabelUikit3WidthSm').parentNode.parentNode.style.display='none';
            document.getElementById('jform_captchaoptions_captchaLabelUikit3WidthMd').parentNode.parentNode.style.display='none';
            document.getElementById('jform_captchaoptions_captchaLabelUikit3WidthLg').parentNode.parentNode.style.display='none';
            document.getElementById('jform_captchaoptions_captchaLabelUikit3WidthXl').parentNode.parentNode.style.display='none';
            document.getElementById('jform_captchaoptions_uikit3LabelWidthDesc-lbl').parentNode.parentNode.style.display = 'none';
        }
        if (layoutVal == 'uikit2' && subLayoutVal == 'individual') {
            document.getElementById('jform_captchaoptions_captchaLabelUikit2Width').parentNode.parentNode.style.display='block';
            document.getElementById('jform_captchaoptions_captchaLabelUikit2WidthSm').parentNode.parentNode.style.display='block';
            document.getElementById('jform_captchaoptions_captchaLabelUikit2WidthMd').parentNode.parentNode.style.display='block';
            document.getElementById('jform_captchaoptions_captchaLabelUikit2WidthLg').parentNode.parentNode.style.display='block';
            document.getElementById('jform_captchaoptions_uikit2LabelWidthDesc-lbl').parentNode.parentNode.style.display='block';
        } else {
            document.getElementById('jform_captchaoptions_captchaLabelUikit2Width').parentNode.parentNode.style.display='none';
            document.getElementById('jform_captchaoptions_captchaLabelUikit2WidthSm').parentNode.parentNode.style.display='none';
            document.getElementById('jform_captchaoptions_captchaLabelUikit2WidthMd').parentNode.parentNode.style.display='none';
            document.getElementById('jform_captchaoptions_captchaLabelUikit2WidthLg').parentNode.parentNode.style.display='none';
            document.getElementById('jform_captchaoptions_uikit2LabelWidthDesc-lbl').parentNode.parentNode.style.display = 'none';
        }
    }
</script>

<form id="item-form" class="form-validate" action="<?php echo JRoute::_("$this->baseUrl&view=$this->editViewName&layout=edit&id=$this->id"); ?>" method="post" name="adminForm">
    <div id="j-main-container">
    <div class="form-inline form-inline-header"><?php
	    // Todo remove quick start help step or complete it
	    //echo (new JLayoutFile('div.quickstart_help_element'))->render(array('step' => 1, 'description' => 'COM_VISFORMS_CREATOR_QUICKSTART_STEP1'));
        echo $this->form->getControlGroup('title');
        echo $this->form->getControlGroup('name'); ?>
    </div>
    <div class="form-horizontal"><?php
    $formFieldSets = $this->form->getFieldsets();
    // we are done with form title
    unset($formFieldSets['form_title']);
    // access rules placed at the very end
    if((isset($formFieldSets[$name = 'access-rules']))) {
        unset($formFieldSets[$name]);
    }

    echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'visform-basic-details'));

    //custom layout for first tab
    if((isset($formFieldSets[$name = 'visform-basic-details']))) {
        $fieldSet = $formFieldSets[$name];
        echo JHtml::_('bootstrap.addTab', 'myTab', $name, JText::_($fieldSet->label)); ?>
        <div class="row-fluid">
            <div class="span12">
                <fieldset class="adminform"><?php
                    foreach ($this->form->getFieldset($name) as $field) {
                        echo $field->getControlGroup();
                    } ?>
                </fieldset>
            </div>
        </div><?php
        unset($formFieldSets[$name]);
        echo JHtml::_('bootstrap.endTab');
    }

    //layout for all other tabs except the permissions tab
    foreach ($formFieldSets as $name => $fieldSet) {
        if ($hasFrontEdit || $name !== 'visform-edit-email-details') {
            echo JHtml::_('bootstrap.addTab', 'myTab', $name, JText::_($fieldSet->label)); ?>
            <div class="row-fluid form-horizontal-desktop"><?php
            //custom layout for plugin mailattachments tab display attachment selection on top with full width; display pdf and csv attachment options below in two colums
            if ($name === 'visforms-extension-mailattachments') { ?>
                <div class="span12" style="padding-right: 20px;"> <?php
                    foreach ($this->form->getFieldset($name) as $field) {
                        if (!($field->group == 'visformsmailattachments_params.exportsettings')) {
                            if (($field->fieldname == 'f_attachment_list_hidden') || ($field->fieldname == 'visformsmailattachemtsspacer')) {
                                echo $field->getControlGroup();
                            }
                        }
                    } ?>
                </div>
            </div>
            <div class="row-fluid form-horizontal-desktop">
                <div class="span6"><?php
                foreach ($this->form->getFieldset($name) as $field) {
                    if (!($field->group == 'visformsmailattachments_params.exportsettings')) {
                        if ((!($field->fieldname == 'f_attachment_list_hidden')) && (!($field->fieldname == 'visformsmailattachemtsspacer'))) {
                            echo $field->getControlGroup();
                        }
                    }
                } ?>
                </div>
                <div class="span6"><?php
                foreach ($this->form->getFieldset($name) as $field) {
                    if ($field->group == 'visformsmailattachments_params.exportsettings') {
                        echo $field->getControlGroup();
                    }
                } ?>
                </div><?php
            }
	        else if ($name === 'visforms-extension-editmailattachments') { ?>
                <div class="span12" style="padding-right: 20px;"> <?php
			        foreach ($this->form->getFieldset($name) as $field) {
				        if (!($field->group == 'visformseditmailattachments_params.exportsettings')) {
					        if (($field->fieldname == 'f_editattachment_list_hidden') || ($field->fieldname == 'visformseditmailattachemtsspacer')) {
						        echo $field->getControlGroup();
					        }
				        }
			        } ?>
                </div>
                </div>
                <div class="row-fluid form-horizontal-desktop">
                <div class="span6"><?php
			        foreach ($this->form->getFieldset($name) as $field) {
				        if (!($field->group == 'visformseditmailattachments_params.exportsettings')) {
					        if ((!($field->fieldname == 'f_editattachment_list_hidden')) && (!($field->fieldname == 'visformseditmailattachemtsspacer'))) {
						        echo $field->getControlGroup();
					        }
				        }
			        } ?>
                </div>
                <div class="span6"><?php
		        foreach ($this->form->getFieldset($name) as $field) {
			        if ($field->group == 'visformseditmailattachments_params.exportsettings') {
				        echo $field->getControlGroup();
			        }
		        } ?>
                </div><?php
            }
            else {
                //all the other tabs ?>
                <div class="span12"><?php
                foreach ($this->form->getFieldset($name) as $field) {
                    echo $field->getControlGroup();
                } ?>
                </div><?php
            } ?>
            </div><?php
            echo JHtml::_('bootstrap.endTab');
        }
    } ?>
    <div class="clr"></div><?php
    // layout for permissions tab
    if ($this->canDo->get('core.admin')) {
        echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_VISFORMS_FIELDSET_FORM_RULES', true));
        echo $this->form->getInput('rules');
        echo JHtml::_('bootstrap.endTab');
    }
    echo JHtml::_('bootstrap.endTabSet'); ?>
	</div><?php
    $layout = new JLayoutFile('div.form_hidden_inputs');
    echo $layout->render(); ?>
    </div>
    </div>
</form>
<script type="text/javascript">
    googleReCaptchaListBoxSelection();
    googleReCaptchaBt4ListBoxSelection();
</script>