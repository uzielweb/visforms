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

if ($this->visforms->published != '1')
{
    return;
}
?>
<div class="visforms-form<?php echo $this->menu_params->get('pageclass_sfx'); ?>" id="visformcontainer">
<?php
if ($this->menu_params->get('show_page_heading') == 1)
{
    if (!$this->menu_params->get('page_heading') == "")
    { ?>
        <h1><?php echo $this->menu_params->get('page_heading'); ?></h1>
        <?php
    }
    else
    { ?>
        <h1><?php echo $this->visforms->title; ?></h1>
        <?php
    }
} ?>

<?php if (strcmp($this->visforms->description, "") != 0)
{ ?>
    <div class="category-desc"><?php
        JPluginHelper::importPlugin('content');
        echo JHtml::_('content.prepare', $this->visforms->description);
        ?>
    </div>
<?php } ?>

        <p>
        <?php
            echo JText::_('COM_VISFORMS_REDIRECT_TO_EDIT_VIEW_TEXT');
        ?>
        </p>

            <?php
            foreach ($this->editLinks as $recordId)
            {
                $redirectUri = '&return=' . $this->return;
                $link = JUri::base() .'index.php?option=com_visforms&view=edit&layout=edit&task=edit.editdata&id=' . (int)$this->visforms->id . '&cid=' . (int)$recordId . $redirectUri . '&Itemid=' . $this->visforms->dataEditMenuExists;
            ?>
                <p></p><a href="<?php echo htmlspecialchars($link, ENT_COMPAT, 'UTF-8'); ?>"><?php echo JText::_('COM_VISFORMS_EDIT_LINK_TEXT'); ?><?php echo $recordId; ?></a></p>
            <?php
            }
            ?>
</div>