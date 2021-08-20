<?php
/**
 * Visforms
 *
 * @author       Ingmar Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2018 vi-solutions
 * @since        Joomla 3.0.0
 */

defined('_JEXEC') or die('Restricted access');

?>
<button title="open the new created form field list" onclick="visHelper.open(event, '<?php echo JRoute::_("index.php?option=com_visforms&view=visfields&fid=__fid__"); ?>')" class="btn btn-small btn-primary btn-open-fields" disabled>Open Field List</button>
<button title="open the new created form in edit mode" onclick="visHelper.open(event, '<?php echo JRoute::_("index.php?option=com_visforms&task=visform.edit&id=__fid__"); ?>')" class="btn btn-small btn-primary btn-open-form" disabled>Open Form</button>
<button title="navigate to user menu manager to create a form main menu entry" onclick="visHelper.open(event, '<?php echo JRoute::_("index.php?option=com_menus&view=item&layout=edit&menutype=mainmenu"); ?>')" class="btn btn-small btn-primary btn-create-main-menu" disabled>Open Create Main Menu</button>
<button title="navigate to user menu manager to create a form user menu entry" onclick="visHelper.open(event, '<?php echo JRoute::_("index.php?option=com_menus&view=item&client_id=0&menutype=usermenu&layout=edit"); ?>')" class="btn btn-small btn-primary btn-create-user-menu" disabled>Open Create User Menu</button>
<a title="all settings will get lost" href="<?php echo JRoute::_("index.php?option=com_visforms&view=viscreator"); ?>" class="btn btn-small btn-danger" role="button">Reset</a>