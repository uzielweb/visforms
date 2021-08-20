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
	
if ($this->visforms->published != '1') {
	return;
}
$gridClass= (!empty($this->visforms->hasBt3Layout)) ? 'row' : 'row-fluid';

JHtmlVisforms::includeScriptsOnlyOnce(array('visforms.default.min' => false,  'bootstrapform' => $this->visforms->usebootstrapcss)); ?>

<form action="<?php echo JRoute::_($this->formLink) ; ?>" method="post" name="visform" 
	id="<?php echo$this->visforms->parentFormId; ?>" 
	class="mcindividual visform <?php echo $this->visforms->formCSSclass;?>"<?php if($this->upload == true) { ?> enctype="multipart/form-data"<?php } ?> role="form"> <?php
//add a progressbar
	if (((!empty($this->visforms->displaysummarypage)) || ($this->steps > 1)) && (!empty($this->visforms->displayprogress))) {
		echo JLayoutHelper::render('visforms.progress.default', array('parentFormId' => $this->visforms->parentFormId, 'steps' => $this->steps, 'displaysmallbadges' => $this->visforms->displaysmallbadges, 'displaysummary' => $this->visforms->displaysummarypage));
	}
	for ($f = 1; $f < $this->steps + 1; $f++) {
    $active = ($f === 1) ? ' active' : '';
    echo '<fieldset class="fieldset-' . $f . $active . '">';
		if ($f === 1) {
        //Explantion for * if at least one field is requiered at the top of the form
		if ($this->required == true && $this->visforms->required == 'top') {
			echo JLayoutHelper::render('visforms.requiredtext.btdefault', array('form' => $this->visforms));
        }

        //first hidden fields at the top of the form
			for ($i = 0; $i < $this->nbFields; $i++) {
            $field = $this->visforms->fields[$i];
				if ($field->typefield == "hidden") {
                echo $field->controlHtml;
            }
        }
    }
	//then inputs, textareas, selects and fieldseparators
    $counter = 0;
    echo '<div class="'.$gridClass.'">';
		for ($i = 0; $i < $this->nbFields; $i++) {
        $field = $this->visforms->fields[$i];
        $bt_size = (isset($field->bootstrap_size) && ($field->bootstrap_size > 0)) ? $field->bootstrap_size : 6;
			if ($field->typefield != "hidden" && empty($field->sig_in_footer) && !isset($field->isButton) && ($field->fieldsetcounter === $f)) {
            //set focus to first visible field
				if ((!empty($this->setFocus)) && ($this->firstControl == true) && ((!(isset($field->isDisabled))) || ($field->isDisabled == false))) {
                $script= '';
                $script .= 'jQuery(document).ready( function(){';
                $script .= 'jQuery("#'. $field->errorId.'").focus();';
                $script .= '});';
                $doc = JFactory::getDocument();
                $doc->addScriptDeclaration($script);
                $this->firstControl = false;
            }
            if ((!empty($this->visforms->mpdisplaytype)) && ($this->visforms->mpdisplaytype == 1) && ($field->typefield == 'pagebreak')) {
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
		if ($f === $this->steps) {
        echo '</div>';
        //no summary page
			if (empty($this->visforms->displaysummarypage)) {
				echo JLayoutHelper::render('visforms.footers.mcindividual.nosummary', array('form' => $this->visforms, 'nbFields' => $this->nbFields, 'hasRequired' => $this->required));
			} //with summary page
			else {
				echo JLayoutHelper::render('visforms.footers.mcindividual.withsummary', array('form' => $this->visforms, 'nbFields' => $this->nbFields, 'hasRequired' => $this->required, 'summarypageid' => $this->visforms->parentFormId));
			}
		}
    echo '</fieldset>';
} ?>
    <input type="hidden" value="<?php echo $this->visforms->id; ?>" name="postid" />
    <input type="hidden" value="pagebreak" name="addSupportedFieldType[]" /> <?php
        $input = JFactory::getApplication()->input;
        $tmpl = $input->get('tmpl');
	if (isset($tmpl)) {
           echo '<input type="hidden" value="' . $tmpl . '" name="tmpl" />';
        }
		$creturn = $input->get('creturn');
	if (isset($creturn)) {
           echo '<input type="hidden" value="' . $creturn . '" name="creturn" />';
        }
	if (!empty($this->return)) {
           echo '<input type="hidden" value="' . $this->return . '" name="return" />';
        }
	echo JHtml::_('form.token'); ?>
</form>
<script type="text/javascript">
    //Window resize
    jQuery( window ).resize(function() {
        setFormPosition();
    });

    function setFormPosition () {
        jQuery("#<?php echo $this->visforms->parentFormId; ?> div[class^='fc-tbxfield']").each( function (i, el) {
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
                <?php  if ($this->visforms->formlayout == 'bt3mcindividual') { ?>
                "left" : 0
                <?php } else { ?>
                "left" : position.left
                <?php } ?>
             });
            });
        });
    }
            
        
    jQuery(document).ready(function () {
        setFormPosition();
        jQuery(".displayChanger").on('change', function (e) {
            setFormPosition();
        });
        jQuery(".next_btn, .summary_btn").on('click', function (e) {
            setFormPosition();
        });
    });
</script>