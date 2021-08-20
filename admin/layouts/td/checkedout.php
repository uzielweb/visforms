<?php
/**
 * Visforms
 *
 * @author       Ingmar Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2017 vi-solutions
 * @since        Joomla 3.0.0
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<td class="has-context">
    <div class="pull-left"><?php
        if ($displayData['item']->checked_out) {
            echo JHtml::_('jgrid.checkedout',
                $displayData['data']->i,
                $displayData['view']->user->name,
                $displayData['item']->checked_out_time,
                $displayData['view']->viewName. '.',
                $displayData['data']->canCheckin);
        }
        if ($displayData['data']->canEdit || $displayData['data']->canEditOwn) { ?>
            <a href="<?php echo $displayData['data']->linkEdit; ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>"><?php echo $displayData['view']->escape($displayData['item']->title); ?></a>
            <p class="small"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $displayData['view']->escape($displayData['item']->name)); ?></p><?php
        }
        else {
            echo $displayData['view']->escape($displayData['item']->title); ?>
            <p class="small"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $displayData['view']->escape($displayData['item']->name)); ?></p><?php
        } ?>
    </div>
</td>