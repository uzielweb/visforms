<?php
/**
 * Vistools editcss view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.modal');
JHtml::_('formbehavior.chosen', 'select');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');

$input = JFactory::getApplication()->input; ?>

<script type="text/javascript">
	jQuery(document).ready(function($){
        // Hide all the folder when the page loads
		$('.folder ul, .component-folder ul').hide();
		// Display the tree after loading
		$('.directory-tree').removeClass("directory-tree");
		// Show all the lists in the path of an open file
		$('.show > ul').show();
		// Stop the default action of anchor tag on a click event
		$('.folder-url, .component-folder-url').click(function(event){ event.preventDefault(); });
		// Prevent the click event from proliferating
		$('.file, .component-file-url').bind('click',function(e){ e.stopPropagation(); });
		// Toggle the child indented list on a click event
		$('.folder, .component-folder').bind('click',function(e){
			$(this).children('ul').toggle();
			e.stopPropagation();
		});
        // new file tree
        $('#fileModal .folder-url').bind('click',function(e){
            $('.folder-url').removeClass('selected');
            e.stopPropagation();
            $('#fileModal input.address').val($(this).attr('data-id'));
            $(this).addClass('selected');
        });
	});
</script>
<style>
	/* styles for modals */
	.selected {
		background: #08c;
		color: #fff;
	}
	.selected:hover {
		background: #08c !important;
		color: #fff;
	}
	.modal-body .column {
		width: 50%;
        float: left;
	}
	.directory-tree {
		display: none;
	}
	.tree-holder{
		overflow-x: auto;
	}
</style>
<div class="row-fluid">
	<div class="span12"><?php
        if($this->type == 'file') { ?>
			<p class="well well-small lead"><?php echo JText::sprintf('COM_VISFORMS_CSS_FILENAME', $this->source->filename); ?></p><?php
        } ?>
	</div>
</div>
<div class="row-fluid">
	<div class="span3 tree-holder"><?php echo $this->loadTemplate('tree');?></div>
	<div class="span9"><?php
        if($this->type == 'home') { ?>
			<form action="<?php echo JRoute::_('index.php?option=com_visforms&view=vistools&layout=default&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal"><?php
                $layout = new JLayoutFile('div.form_hidden_inputs');
                echo $layout->render(); ?>
			</form><?php
        }
        if($this->type == 'file'){ ?>
			<form action="<?php echo JRoute::_('index.php?option=com_visforms&view=vistools&layout=default&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
				<p class="label"><?php echo JText::_('COM_VISFORMS_TOGGLE_FULL_SCREEN'); ?></p>
				<div class="clr"></div>
				<div class="editor-border"><?php echo $this->form->getInput('source'); ?></div><?php
                $layout = new JLayoutFile('div.form_hidden_inputs');
                echo $layout->render();
                echo $this->form->getInput('filename'); ?>
			</form><?php
        } ?>
	</div>
</div><?php
if ($this->type != 'home') { ?>
	<div  id="deleteModal" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><?php echo JText::_('COM_VISFORMS_ARE_YOU_SURE');?></h3>
		</div>
		<div class="modal-body">
			<p><?php echo JText::sprintf('COM_VISFORMS_MODAL_FILE_DELETE', $this->fileName); ?></p>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('COM_VISFORMS_CLOSE'); ?></a><?php
				$token = JSession::getFormToken() . '=1';
				$deleteLinkUrl = 'index.php?option=com_visforms&view=vistools&layout=default&task=vistools.delete' . '&file=' . $this->file . '&' . $token;
				$deleteLink = JRoute::_($deleteLinkUrl); ?>
			<a href="<?php echo $deleteLink; ?>" class="btn btn-danger"><?php echo JText::_('COM_VISFORMS_DELETE');?></a>
		</div>
	</div><?php
} ?>
<div  id="fileModal" class="modal hide fade">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3><?php echo JText::_('COM_VISFORMS_NEW_FILE_HEADER');?></h3>
	</div>
	<div class="modal-body">
		<div class="column">
			<form method="post" action="<?php echo JRoute::_("index.php?option=com_visforms&view=vistools&layout=default&task=vistools.createFile&file=$this->file"); ?>" class="well">
				<fieldset>
					<label><?php echo JText::_('COM_VISFORMS_NEW_FILE_TYPE');?></label>
					<select name="type" required >
						<option value="null">- <?php echo JText::_('COM_VISFORMS_NEW_FILE_SELECT');?> -</option>
						<option value="css">css</option>
					</select>
					<label><?php echo JText::_('COM_VISFORMS_NEW_FILE_NAME');?></label>
					<input type="text" name="name" required />
					<input type="hidden" class="address" name="address" /><?php
                    echo JHtml::_('form.token'); ?>
					<input type="submit" value="<?php echo JText::_('COM_VISFORMS_BUTTON_CREATE');?>" class="btn btn-primary" />
				</fieldset>
			</form>
            <form method="post" action="<?php echo JRoute::_("index.php?option=com_visforms&view=vistools&layout=default&task=vistools.uploadFile&file=$this->file"); ?>" class="well" enctype="multipart/form-data">
				<fieldset>
					<input type="hidden" class="address" name="address" />
					<input type="file" name="files" required /><?php
                    echo JHtml::_('form.token'); ?>
					<input type="submit" value="<?php echo JText::_('COM_VISFORMS_BUTTON_UPLOAD');?>" class="btn btn-primary" />
				</fieldset>
			</form>
		</div>
	</div>
	<div class="modal-footer">
		<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('COM_VISFORMS_CLOSE'); ?></a>
	</div>
</div><?php
if ($this->type != 'home') { ?>
	<form action="<?php echo JRoute::_('index.php?option=com_visforms&view=vistools&layout=default&task=vistools.renameFile&file=' . $this->file); ?>" method="post">
		<div  id="renameModal" class="modal hide fade">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3><?php echo JText::sprintf('COM_VISFORMS_RENAME_FILE', $this->fileName); ?></h3>
			</div>
			<div class="modal-body">
				<div id="template-manager-css" class="form-horizontal">
					<div class="control-group">
						<label for="new_name" class="control-label hasTooltip" title="<?php echo JHtml::tooltipText(JText::_('COM_VISFORMS_NEW_FILE_NAME_DESC')); ?>"><?php echo JText::_('COM_VISFORMS_NEW_FILE_NAME')?></label>
						<div class="controls">
							<input class="input-xlarge" type="text" name="new_name" required />
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('COM_VISFORMS_CLOSE'); ?></a>
				<button class="btn btn-primary" type="submit"><?php echo JText::_('COM_VISFORMS_BUTTON_RENAME'); ?></button>
			</div>
		</div><?php
        echo JHtml::_('form.token'); ?>
	</form><?php
} ?>