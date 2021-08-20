<?php
/**
 * Mod_Visforms Form
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   mod_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;
	
if ($visforms->published != '1') {
    return;
}
HTMLHelper::_('bootstrap.framework');

//retrieve helper variables from params
$nbFields=$params->get('nbFields');
$required = $params->get('required');
$upload = $params->get('upload');
$textareaRequired = $params->get('textareaRequired');
$hasHTMLEditor = $params->get('hasHTMLEditor');
$return = HTMLHelper::_('visforms.base64_url_encode', Uri::getInstance()->toString());
$firstControl = $params->get('firstControl');
$setFocus = $params->get('setFocus');
$steps = $params->get('steps');
$context = $params->get('context');
$successMessage = $params->get('successMessage');
echo LayoutHelper::render('visforms.custom.noscript', array(), null, array('component' => 'com_visforms'));
?>

<div class="visforms visforms-form"><?php
	echo (new JLayoutFile('div.ajax_modal_error_dialog', JPATH_ROOT .'/administrator/components/com_visforms/layouts'))->render();
    if (isset($visforms->errors) && is_array($visforms->errors) && count($visforms->errors) > 0) {
	    echo LayoutHelper::render('visforms.error.messageblock', array('errormessages' => $visforms->errors, 'context' => 'form'), null, array('component' => 'com_visforms'));
    }

    if ($menu_params->get('show_title') == 1) {?>
		<h1><?php echo $visforms->title; ?></h1><?php
	}

	echo LayoutHelper::render('visforms.success.messageblock', array('message' => $successMessage, 'parentFormId' => $visforms->parentFormId), null, array('component' => 'com_visforms'));?>

    <div class="alert alert-danger error-note" style="display: none;"></div><?php
	echo LayoutHelper::render('visforms.scripts.validation', array('visforms' => $visforms, 'textareaRequired' => $textareaRequired, 'hasHTMLEditor' => $hasHTMLEditor, 'parentFormId' => $visforms->parentFormId, 'steps' => $steps), null, array('component' => 'com_visforms'));
    if (strcmp ( $visforms->description , "" ) != 0) { ?>
        <div class="category-desc"><?php
            PluginHelper::importPlugin('content');
            echo HTMLHelper::_('content.prepare', $visforms->description); ?>
        </div><?php
    }

    //display form with appropriate layout
	switch($visforms->formlayout) {
		case 'btdefault' :
		case 'bthorizontal' :
		case 'bt3default' :
		case 'bt3horizontal' :
			require ModuleHelper::getLayoutPath('mod_visforms', $params->get('layout', 'default') . '_btdefault');
			break;
		case  'mcindividual' :
		case  'bt3mcindividual' :
			require ModuleHelper::getLayoutPath('mod_visforms', $params->get('layout', 'default') . '_mcindividual');
			break;
		case  'bt4mcindividual' :
			require ModuleHelper::getLayoutPath('mod_visforms', $params->get('layout', 'default') . '_bt4mcindividual');
			break;
		case  'uikit2' :
			require ModuleHelper::getLayoutPath('mod_visforms', $params->get('layout', 'default') . '_uikit2');
			break;
		case  'uikit3' :
			require ModuleHelper::getLayoutPath('mod_visforms', $params->get('layout', 'default') . '_uikit3');
			break;
		default :
			require ModuleHelper::getLayoutPath('mod_visforms', $params->get('layout', 'default') . '_visforms');
			break;
	}

    if ($visforms->poweredby == '1') {
        echo HTMLHelper::_('visforms.creditsFrontend');
    }
    if (!empty($visforms->showmessageformprocessing)) { ?>
        <div id="<?php echo $visforms->parentFormId; ?>_processform" style="display:none"><div class="processformmessage"><?php
                echo $visforms->formprocessingmessage; ?>
            </div></div><?php
    }
    echo LayoutHelper::render('visforms.scripts.map', array('form' => $visforms), null, array('component' => 'com_visforms'));
	echo LayoutHelper::render('visforms.scripts.searchableselect', array('form' => $visforms), null, array('component' => 'com_visforms'));?>
</div>
