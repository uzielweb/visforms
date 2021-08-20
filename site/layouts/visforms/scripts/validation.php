<?php
/**
 * Visforms default view for Visforms
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
JLoader::register('JHtmlVisforms', JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/html/visforms.php');

if (!empty($displayData)) :
    if ((isset($displayData['visforms'])) && (isset($displayData['textareaRequired'])) && (isset($displayData['hasHTMLEditor'])) && (isset($displayData['parentFormId'])) && (isset($displayData['steps']))) :
        $visforms = $displayData['visforms'];
        $vfObjForJs = new stdClass();
        $vfObjForJs->fid = $visforms->id;
        $textareaRequired = $displayData['textareaRequired'];
        $hasHTMLEditor = $displayData['hasHTMLEditor'];
        $vfObjForJs->initEditor = ($displayData['textareaRequired'] == true || $displayData['hasHTMLEditor'] == true) ? true : false;
        $parentFormId = $displayData['parentFormId'];
        $vfObjForJs->parentFormId = $displayData['parentFormId'];
        $steps = $displayData['steps'];
        $vfObjForJs->steps = $displayData['steps'];
        $nbFields = count($visforms->fields);
        $vfObjForJs->nbFields = count($visforms->fields);
        $vfObjForJs->summaryLayout = (!empty($visforms->summarytag)) ? $visforms->summarytag: 'table' ;
		$vfObjForJs->summaryLayoutClass = (!empty($visforms->summarylayoutclass)) ? (($vfObjForJs->summaryLayout == 'table') ? $visforms->summarylayoutclass . ' table' : $visforms->summarylayoutclass) : (($vfObjForJs->summaryLayout == 'table') ? 'table' : '');
        $summaryRowLayout = 'tr';
        $oSummaryFirstElementLayout = '<td>';
        $cSummaryFirstElementLayout = '</td>';
        $oSummarySecondElementLayout = '<td>';
        $cSummarySecondElementLayout = '</td>';


		if ((!empty($visforms->summarytag))) {
			switch ($visforms->summarytag) {
                case 'dl' :
                    $summaryRowLayout = '';
                    $oSummaryFirstElementLayout = '<dt>';
                    $cSummaryFirstElementLayout = '</dt>';
                    $oSummarySecondElementLayout = '<dd>';
                    $cSummarySecondElementLayout = '</dd>';
                    break;
                case 'ul' :
                case 'ol' :
                    $summaryRowLayout = 'li';
                    $oSummaryFirstElementLayout = '';
                    $cSummaryFirstElementLayout = '';
                    $oSummarySecondElementLayout = '';
                    $cSummarySecondElementLayout = '';
                    break;
                case 'div' :
                    $summaryRowLayout = 'p';
                    $oSummaryFirstElementLayout = '<span>';
                    $cSummaryFirstElementLayout = '</span>';
                    $oSummarySecondElementLayout = '<span>';
                    $cSummarySecondElementLayout = '</span>';
                    break;
                default :
                    break;
            }
        }
        $vfObjForJs->summaryRowLayout = $summaryRowLayout;
        $vfObjForJs->oSummaryFirstElementLayout = $oSummaryFirstElementLayout;
        $vfObjForJs->cSummaryFirstElementLayout = $cSummaryFirstElementLayout;
        $vfObjForJs->oSummarySecondElementLayout = $oSummarySecondElementLayout;
        $vfObjForJs->cSummarySecondElementLayout = $cSummarySecondElementLayout;
        $vfObjForJs->displaysummarypage = (!empty($visforms->displaysummarypage)) ? true : false;
        $vfObjForJs->hideemptyfieldsinsummary = (!empty($visforms->hideemptyfieldsinsummary)) ? true : false;
	    $vfObjForJs->summaryemptycaliszero = (!empty($visforms->summaryemptycaliszero) && (!empty($visforms->hideemptyfieldsinsummary))) ? true : false;
        $fieldsForJsa = array();
        $userinputsa = array();
        $restrictData = array();
		for ($i = 0; $i < $nbFields; $i++) {
            $field = $visforms->fields[$i];
			if (isset($field->userInput)) {
				$userinputsa[] = json_encode(array(
					'type' => $field->typefield,
					'label' =>'field'. $field->id,
					// value can either be a string or an array (select, checkbox group), therefore use JSON_FORCE_OBJECT on json_encode
					'value' => $field->userInput,
					'isDisabled' => ((!empty($field->isDisabled)) ? true : false),
					'isForbidden' => ((!empty($field->isForbidden)) ? true : false)), JSON_FORCE_OBJECT);
            }
			if (isset($field->showWhenForForm) && (is_array($field->showWhenForForm))) {
                $restrictData[] = 'field' . $field->id . ' : ' . '"' . implode(', ', $field->showWhenForForm) . '"';
            }
            //enclose all keys in "" if converted to JSON-String with registry later
			$summaryLabel = (empty($field->customlabelforsummarypage)) ? $field->label : $field->customlabelforsummarypage;
			$fieldsForJsa[] = array('id' =>  (int) $field->id, 'type' => $field->typefield ,'label'=> $summaryLabel);
        }
        $userinputss = implode(',', $userinputsa);
        $userinputs = "[" . $userinputss . "]";
        $restrictDataString = "{" . implode(", ", $restrictData) . "}";
	    $vfObjForJs->fields =  $fieldsForJsa;
        $jsonform = json_encode($vfObjForJs, JSON_FORCE_OBJECT);

		if ($textareaRequired == true || $hasHTMLEditor == true) {
            //we need an editor and create a simple tinyMCE editor
            VisformsEditorHelper::initEditor();
        }
?>
    <script type="text/javascript">
        jQuery(document).ready(function () {
                jQuery('#<?php echo $parentFormId; ?>').validate({
                    submitHandler: function (form) {
                        var returnVal = true;
                        if (window["<?php echo $parentFormId; ?>SubmitAction"] && typeof window["<?php echo $parentFormId; ?>SubmitAction"] !== "undefined") {
                            returnVal = window["<?php echo $parentFormId; ?>SubmitAction"](this);
                        }
                        if (!returnVal) {
                            return false;
                        }
                        form.submit();
                        jQuery(form).find('input[type="submit"]').prop("disabled", true);
                        jQuery(form).find('input[type="reset"]').prop("disabled", true);
                        jQuery(form).find('input[type="image"]').prop("disabled", true);
                        <?php if (!empty($visforms->showmessageformprocessing)) { ?>
                        var div = jQuery("#<?php echo $parentFormId; ?>_processform");
                        if (div.length) {
                            jQuery("vispoweredby").hide();
                            jQuery("#<?php echo $parentFormId; ?>").hide();
                            div.show();
                            var elOffset = div.offset().top;
                            var elHeight = div.height();
                            var windowHeight = jQuery(window).height();
                            var offset;

                            if (elHeight < windowHeight) {
                                offset = elOffset - ((windowHeight / 2) - (elHeight / 2));
                            }
                            else {
                                offset = elOffset;
                            }

                            var speed = 700;
                            jQuery('html, body').animate({scrollTop: offset}, speed);
                        }
                        <?php } ?>
                    },
                    wrapper: "p",
                    //absolutly necessary when working with tinymce!
                    ignore: ".ignore",
                    rules: { <?php
                        //insert rules that we cannot put into html attributes because they are no valid attributs or valid attribute values
                        for ($i = 0; $i < $nbFields; $i++) {
                            $field = $visforms->fields[$i];
                            if (isset($field->validateArray)) {
	                            if (isset($field->typefield) && ($field->typefield == "select" || $field->typefield == "selectsql" || $field->typefield == "multicheckbox") || $field->typefield == "multicheckboxsql" || $field->typefield == 'location') {
                                    echo "\"" . $field->name . "[]\": {";
                                }
                                else if ( $field->typefield == 'email' && array_key_exists('mailExists', $field->validateArray)) {
	                                echo "\"" . $field->name . "_code\" : {";
                                }
                                else {
                                    echo "\"" . $field->name . "\" : {";
                                }
                                foreach ($field->validateArray as $n => $v) {
                                    if ($n == 'geolocation') {
                                        //geolocation is a dummy value used to get a value in validateArray for location fields
                                        echo "ispair: \"field" . $field->id."\",";
                                    }
                                    else if ($n == 'mailExists') {
	                                    echo "  \"remote\": { url : \"". Juri::root(true) ."/index.php?option=com_visforms&task=visforms.checkVerificationCode\", type: \"post\", data : {verificationAddr: function () {return document.getElementById(\"field" . $field->id."\").value;}, code : function () {return document.getElementById(\"field" . $field->id."_code\").value;}, fid: ".$visforms->id. ", \"".JSession::getFormToken()."\" : 1}, dataFilter: function (data) {if (data === \"1\") {return true;} else {return false}}}";
                                    }
                                    else if (($n == "equalTo") || ($n == "remote")) {
                                        echo $n . ": \"" . $v . "\",";
                                    }
                                    else {
                                        echo $n . ": " . $v . ",";
                                    }
                                }

                                echo "},";
                                unset($n);
                                unset($v);
                            }
                        }
                        //add required for captcha
						if (isset($visforms->captcha)) {
							if ($visforms->captcha == 2) {
								echo '"g-recaptcha-response" : { required : true},';
							}
						} ?>
                    },
                    messages: { <?php
                        //Include custom error messages
						for ($i = 0; $i < $nbFields; $i++) {
                            $field = $visforms->fields[$i];
                            //Custom Error Messages for date fields
							if (isset($field->typefield) && $field->typefield == "date" && !(isset($field->customErrorMsgArray))) {
								if (isset($field->dateFormatJs)) {
                                    echo "\"" . $field->name . "\" : {";
									switch ($field->dateFormatJs) {
                                        case "%d.%m.%Y":
                                             echo " dateDMY: \"" . JText::sprintf('COM_VISFORMS_ENTER_VALID_DATE_FORMAT', 'dd.mm.YYYY') . "\", ";
                                            break;
                                        case "%m/%d/%Y":
                                            echo " dateMDY: \"" . JText::sprintf('COM_VISFORMS_ENTER_VALID_DATE_FORMAT', 'mm/dd/YYYY') . "\", ";
                                            break;
                                        case "%Y-%m-%d":
                                            echo " dateYMD: \"" . JText::sprintf('COM_VISFORMS_ENTER_VALID_DATE_FORMAT', 'YYYY-mm-dd') . "\", ";
                                            break;
                                    }
	                                echo " },";
                                }
                            }
							else if (isset($field->typefield) && $field->typefield == "file" && !(isset($field->customErrorMsgArray))) {
                                echo "\"" . $field->name . "\" : { filesize: \"" . JText::sprintf('COM_VISFORMS_JS_ERROR_WARNFILETOOLARGE', ($visforms->maxfilesize)) . "\" ,";
                                echo " fileextension: \"" . JText::_('COM_VISFORMS_JS_ERROR_WARNFILETYPE') . "\" },";
                            }
							else if (isset($field->typefield) && $field->typefield == "email" && isset($field->validateArray) && array_key_exists('mailExists', $field->validateArray) && !(isset($field->customErrorMsgArray))) {
	                            echo "\"" . $field->name . "_code\" : { remote: \"" . JText::_('COM_VISFORMS_VALIDATION_CODE_INVALID') . "\",";
	                            echo " required : \"". JText::_('COM_VISFORMS_ENTER_VALIDATION_CODE') ."\"} ,";
                            }
                            //Custom Error Messages
							if (isset($field->customErrorMsgArray)) {
								//Custom Error Messages for Selects and multicheckboxes
                                if (isset($field->typefield) && ($field->typefield == "select" || $field->typefield == "selectsql" || $field->typefield == "multicheckbox" || $field->typefield == "multicheckboxsql")) {
                                    echo "\"" . $field->name . "[]\": {";
									foreach ($field->customErrorMsgArray as $n => $v) {
                                        echo $n . ": \"" .  addslashes($v) . "\",";
                                    }
                                    echo "},";
                                }
								else {
                                    //Custom Error Messages for 'normal' fields
                                    echo "\"" . $field->name . "\": {";
									foreach ($field->customErrorMsgArray as $n => $v) {
										if ($n === "date" && (isset($field->dateFormatJs))) {
											switch ($field->dateFormatJs) {
                                                case "%d.%m.%Y":
                                                    echo " dateDMY:  \"" . addslashes($v) . "\",";
                                                    break;
                                                case "%m/%d/%Y":
                                                    echo " dateMDY:  \"" . addslashes($v) . "\",";
                                                    break;
                                                case "%Y-%m-%d":
                                                    echo " dateYMD:  \"" . addslashes($v) . "\",";
                                                    break;
                                            }
                                        }
										else {
                                            echo $n . ": \"" . addslashes($v) . "\",";
                                        }
                                    }
                                    echo "},";
                                }
                            }
							else {
                                //Adapat Error message for multicheckbox minlength, maxlength if we use the default message texts
								if (isset($field->typefield) && (($field->typefield == "multicheckbox") || ($field->typefield == "multicheckboxsql"))) {
                                    echo "\"" . $field->name . "[]\": {";
                                    echo "minlength: jQuery.validator.format('" . JText::_('COM_VISFORMS_ENTER_VAILD_MINLENGTH_MULTICHECKBOX') . "'),";
                                    echo "maxlength: jQuery.validator.format('" . JText::_('COM_VISFORMS_ENTER_VAILD_MAXLENGTH_MULTICHECKBOX') . "')";
                                    echo "},";
                                }
                            }
                        }
                        //Custom Captcha Error Message
						if (isset($visforms->captchacustomerror) && $visforms->captchacustomerror != "") {
							echo "\"" . $visforms->context . "viscaptcha_response\": {";
							echo "required" . ": \"" . addslashes($visforms->captchacustomerror) . "\",";
							echo "},";
							echo "\"g-recaptcha-response\": {";
							echo "required" . ": \"" . addslashes($visforms->captchacustomerror) . "\",";
							echo "},";
                        }

                        ?>
                    },
                    //in accordion view, display a summary message, that form contains errors
                    <?php if (!empty($visforms->mpdisplaytype)) { ?>
                        <?php if ($visforms->mpdisplaytype == 1) { ?>
                        showErrors: function(errorMap, errorList) {
                            var errorNoteDiv = jQuery("#<?php echo $parentFormId; ?>").closest('.visforms-form').find(".error-note");
                            errorNoteDiv.html("<?php echo JText::_('COM_VISFORMS_VALIDATOR_ERROR_COUNT_MESSAGE1'); ?>"
                                + this.numberOfInvalids()
                                + "<?php echo JText::_('COM_VISFORMS_VALIDATOR_ERROR_COUNT_MESSAGE2'); ?>");
                            this.defaultShowErrors();
                        if (!this.numberOfInvalids()) {
                            errorNoteDiv.hide();
                        }
                        else {
                            errorNoteDiv.show();
                        }
                        },
                    <?php  } ?>
                <?php  } ?>
                    errorPlacement: function (error, element) {
                        var errorfieldid = element.attr("data-error-container-id");
                        if (!errorfieldid && element.attr("id") === "g-recaptcha-response") {
                            errorfieldid = 'fc-tbxrecaptcha_response_field';
                        }
                        jQuery('#<?php echo $parentFormId; ?>' + ' div.' + errorfieldid).html('');
                        error.appendTo('#<?php echo $parentFormId; ?>' + ' div.' + errorfieldid);
                        error.addClass("errorcontainer");
                    },
                });

            jQuery('.captcharefresh<?php echo $visforms->id; ?>').on(
                'click', function () {
                    if (jQuery('#captchacode<?php echo $visforms->id; ?>')) {
                        jQuery('#captchacode<?php echo $visforms->id; ?>').attr('src', '<?php echo Juri::root(true); ?>/index.php?option=com_visforms&task=visforms.captcha&sid=' + Math.random() + '&id=<?php echo $visforms->id; ?>');
                    }
                });
                jQuery('#<?php echo $parentFormId; ?>').initVisform({
                    visform: <?php echo $jsonform; ?>,
                    restrictData: <?php echo $restrictDataString; ?>,
                    userInputs:  <?php echo $userinputs; ?>});
        });
    </script>
 <?php
    endif;
endif; ?>
