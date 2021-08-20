<?php 
/**
 * Visdata detail view for Visforms
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

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation'); ?>

<script type="text/javascript">
Joomla.submitbutton = function(task) {
    if (task == 'visdata.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
        Joomla.submitform(task, document.getElementById('item-form'));
    }
    else {
        alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
    }
}
</script>

<form action="<?php echo JRoute::_("$this->baseUrl&view=visdatas&fid=$this->fid&id=$this->id");?>" method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
    <div id="j-main-container">
    <div class="form-horizontal">
    <div class="row-fluid">
    <div class="span12">
        <fieldset class="adminform">
            <legend><?php echo JText::_('COM_VISFORMS_DATA_DETAIL'); ?></legend> <?php
            echo $this->form->renderFieldset('basic');
                foreach ($this->fields as $field) {
                    if ($field->typefield == 'file') {
                        $key = "F".$field->id;
                        $displayDimension = "";
                        $file = New JObject();
                        $file->name = JHtml::_('visforms.getUploadFileName', $this->item->$key);
                        $file->path = JHtml::_('visforms.getUploadFilePath', $this->item->$key);
                        $file->link = JHtml::_('visforms.getUploadFileLink', $this->item->$key);
                        $file->isimage = VisformsmediaHelper::isImage($file->name);
                        if (!empty($file->isimage)) {
                            $info = @getimagesize(JPATH_SITE . '/' . $file->path);
                            $file->width  = @$info[0];
                            $file->height = @$info[1];
                            if (($info[0] > 60) || ($info[1] > 60)) {
                                $dimensions = JHelperMedia::imageResize($file->width, $file->height, 60);
                                $file->width = $dimensions[0];
                                $file->height = $dimensions[1];
                            }
                            if ((!empty($file->width)) && (!empty($file->height))) {
                                $displayDimension = 'width="' . $file->width .'" height="' . $file->height .'" ';
                            }
                        }
                        if ((!empty($file->name)) && (!empty($file->path))) { ?>
                        <ul id="jform_F<?php echo $field->id; ?>-fileimg" class="thumbnails">
                            <li class="imgOutline thumbnail height-100 width-100 center"><?php
                                echo JText::_('COM_VISFORMS_DEL'); ?>
                                <input type="checkbox" class="deleteFile" id="jform_F<?php echo $field->id; ?>-filedelete" name="jform[F<?php echo $field->id; ?>-filedelete]" value="delete"/>
                                <div class="clearfix"></div>
                                <img src="<?php echo (!empty($file->isimage)) ? JUri::root() . $file->path : JUri::root() . 'media/com_visforms/img/icon-48-generic.png'; ?>" <?php echo $displayDimension; ?>/>
                                <div class="clearfix"></div>
                            </li>
                            <div class="clearfix"></div>
                            <li><small><?php echo $file->name; ?></small></li>
                        </ul><?php
                        }
                    }
                    echo $this->form->renderField("F".$field->id);
                } ?>
        </fieldset>
    </div>
    </div>
    </div><?php
    $layout = new JLayoutFile('div.form_hidden_inputs');
    echo $layout->render(); ?>
    </div>
</form>
<script>
    jQuery(document).ready(function(e){
        var files = jQuery('.hiddenFileUpload');
        files.each(function(index) {
            var id = jQuery(this).attr('id');
            var fileimg = jQuery("#" + id + "-fileimg")
            if (fileimg.length) {
                fileimg.detach();
                fileimg.prependTo(jQuery(this).parent());
            }
            else {
                jQuery(this).attr('disabled', false);
            }
        })
        jQuery(".deleteFile").on("change", function (e) {
            var ischecked = jQuery(this).is(':checked');
            var upload = jQuery(this).parents('.thumbnails').parent().find('.hiddenFileUpload');
            if (ischecked) {
                upload.attr('disabled', false);
            }
            else {
                upload.attr('disabled', true);
            }            
        });
    });
</script>