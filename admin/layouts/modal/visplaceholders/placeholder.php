<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2018 vi-solutions
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
extract($displayData);
if ($displayData['placeholder']) {
	$placeholder = $displayData['placeholder'];
	$view = $displayData['view'];
?>

<tr class="row<?php echo $placeholder->counter % 2; ?>">
    <td class="center">
        <?php echo (int) $placeholder->id; ?>
    </td>
    <td class="center">
        <a href="javascript:void(0)" onclick="if (window.parent) window.<?php echo $view->escape($placeholder->function)?>('<?php echo $view->escape(addslashes($placeholder->name)); ?>','<?php echo $view->escape(addslashes($placeholder->editor)); ?>');">
            <?php echo  $view->escape($placeholder->label); ?></a>
    </td>
    <td class="center nowrap">
        <?php echo $view->escape($placeholder->typefield); ?>
    </td>
</tr>

<?php } ?>