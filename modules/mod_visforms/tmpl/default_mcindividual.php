<?php
/**
 * Visforms bootstrap default view for Visforms
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
	
if ($visforms->published != '1') {
	return;
}

$gridClass= (!empty($visforms->hasBt3Layout)) ? 'row' : 'row-fluid';

JHtmlVisforms::includeScriptsOnlyOnce(array('visforms.default.min' => false, 'bootstrapform' => $visforms->usebootstrapcss)); ?>
<form action="<?php echo Juri::base(true) . '/' . htmlspecialchars($formLink, ENT_COMPAT, 'UTF-8'); ?>" method="post" name="visform"
	id="<?php echo $visforms->parentFormId; ?>" 
	class="mcindividual visform <?php echo $visforms->formCSSclass; ?>"<?php if($upload == true) { ?> enctype="multipart/form-data"<?php } ?> role="form"> <?php
//add a progressbar
	if (((!empty($visforms->displaysummarypage)) || ($steps > 1)) && (!empty($visforms->displayprogress))) {
		echo JLayoutHelper::render('visforms.progress.default', array('parentFormId' => $visforms->parentFormId, 'steps' => $steps, 'displaysmallbadges' => $visforms->displaysmallbadges, 'displaysummary' => $visforms->displaysummarypage), null, array('component' => 'com_visforms'));
	}
	for ($f = 1; $f < $steps + 1; $f++) {
    $active = ($f === 1) ? ' active' : '';
    echo '<fieldset class="fieldset-' . $f . $active . '">';
		if ($f === 1) {
        //Explantion for * if at least one field is requiered at the top of the form
			if ($required == true && $visforms->required == 'top') {
				echo JLayoutHelper::render('visforms.requiredtext.btdefault', array('form' => $visforms), null, array('component' => 'com_visforms'));
			}

        //first hidden fields at the top of the form
			for ($i = 0; $i < $nbFields; $i++) {
            $field = $visforms->fields[$i];
				if ($field->typefield == "hidden") {
                echo $field->controlHtml;
            }
        }
    }
	//then inputs, textareas, selects and fieldseparators
    $counter = 0;
    echo '<div class="'.$gridClass.'">';
	for ($i=0;$i < $nbFields; $i++) {
        $field = $visforms->fields[$i];
        $bt_size = (isset($field->bootstrap_size) && ($field->bootstrap_size > 0)) ? $field->bootstrap_size : 6;
			if ($field->typefield != "hidden" && empty($field->sig_in_footer) && !isset($field->isButton) && ($field->fieldsetcounter === $f)) {
            //set focus to first visible field
				if ((!empty($setFocus)) && ($firstControl == true) && ((!(isset($field->isDisabled))) || ($field->isDisabled == false))) {
                $script = '';
                $script .= 'jQuery(document).ready( function(){';
                $script .= 'jQuery("#' . $field->errorId . '").focus();';
                $script .= '});';
                $doc = JFactory::getDocument();
                $doc->addScriptDeclaration($script);
                $firstControl = false;
            }
            if ((!empty($visforms->mpdisplaytype)) && ($visforms->mpdisplaytype == 1) && ($field->typefield == 'pagebreak')) {
                //we need to reset counter inside the accordion
                $counter = 0;
            }
            else {
                //don't add size of accordionheader field to counter
                $counter += $bt_size;
            }
            if ($counter > 12) {
                echo '</div>';
                $counter = $bt_size;
                echo '<div class="'.$gridClass.'">';
            }
            echo $field->controlHtml;
        }   	
    }
		if ($f === $steps) {
        echo '</div>';
        //no summary page
			if (empty($visforms->displaysummarypage)) {
				echo JLayoutHelper::render('visforms.footers.mcindividual.nosummary', array('form' => $visforms, 'nbFields' => $nbFields, 'hasRequired' => $required), null, array('component' => 'com_visforms'));
			} //with summary page
			else {
				echo JLayoutHelper::render('visforms.footers.mcindividual.withsummary', array('form' => $visforms, 'nbFields' => $nbFields, 'hasRequired' => $required, 'summarypageid' => $visforms->parentFormId), null, array('component' => 'com_visforms'));
			}
		}
    echo '</fieldset>';
	} ?>
    <input type="hidden" name="return" value="<?php echo $return; ?>" />
    <input type="hidden" value="<?php echo $visforms->id; ?>" name="postid" />
    <input type="hidden" value="<?php echo $context; ?>" name="context" />
    <input type="hidden" value="pagebreak" name="addSupportedFieldType[]" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
<script type="text/javascript">
    window["setFormPosition<?php echo $context?>"] = function()  {
        jQuery("#<?php echo $visforms->parentFormId; ?> div[class^='fc-tbxfield']").each( function (i, el) {
            var errorDivClass = jQuery(el).attr('class');
            var fieldid = errorDivClass.replace('fc-tbx', '');
            if (fieldid.indexOf('_') > 0)
            {
                var split = fieldid.split('_')
                if ((jQuery.isArray(split)) && (split.length > 0))
                {
                    fieldid = split[0];
                }
            }
            jQuery("." + fieldid).each(function(idx, control) {
                var position = jQuery(control).position();
                
            jQuery(el).css({
                "position" : "absolute",
                "bottom": "100%",
                <?php  if ($visforms->formlayout == 'bt3mcindividual') { ?>
                "left" : 0
                <?php } else { ?>
                "left" : position.left
                <?php } ?>
             });
            });
        });
    }
    jQuery( window ).resize(function() {
        window["setFormPosition<?php echo $context?>"] ();
    });
    jQuery(document).ready(function () {

        window["setFormPosition<?php echo $context?>"] ();
        jQuery(".displayChanger").on('change', function (e) {
            window["setFormPosition<?php echo $context?>"] ();
        });
        jQuery(".next_btn, .back_btn").on('click', function (e) {
            window["setFormPosition<?php echo $context?>"] ();
        });
        jQuery(".next_btn, .summary_btn").on('click', function (e) {
            window["setFormPosition<?php echo $context?>"] ();
        });
    });
</script>