<?php 
/**
 * Visforms message view for Visforms
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
defined('_JEXEC') or die('Restricted access'); ?>


<div class="item-page">
	<?php if (isset($this->successMessage) && ($this->successMessage != "")) {
		echo $this->successMessage;
	 } ?>
</div>
