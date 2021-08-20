<?php
/**
 * Visforms success message html
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

if (!empty($displayData)) : 
    if (!empty($displayData['message'])) :
        $successMessage = $displayData['message'];
        echo '<div class="alert alert-success">';
	    echo '<button class="close successMessage" type="button">×</button>';
        echo $successMessage;
        echo '</div>';
    endif;  
endif; ?>


        