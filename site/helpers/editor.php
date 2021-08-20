<?php

/**
 * editor class	for visforms
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */
// no direct access
defined('_JEXEC') or die('Restricted access');


class VisformsEditorHelper
{
	public static function initEditor() {
    	$basePath = 'media/editors/tinymce';
		$app = JFactory::getApplication();
		$language = JFactory::getLanguage();
		$text_direction = 'ltr';
		if ($language->isRTL()) {
			$text_direction = 'rtl';
		}
		$entity_encoding = 'raw';

		$langMode = 1;
		$langPrefix = 'en';

		if ($langMode) {
			$langPrefix = substr($language->getTag(), 0, strpos($language->getTag(), '-'));
		}
		$newlines = 1;

		if ($newlines) {
			// br
			$forcenewline = "force_br_newlines : true, force_p_newlines : false, forced_root_block : '',";
		} else {
			// p
			$forcenewline = "force_br_newlines : false, force_p_newlines : true, forced_root_block : 'p',";
		}

		$invalid_elements = 'script,applet,iframe';
		$skin = 'skin : "lightgray",';
		$relative_urls = "false";
		$load = "\t<script type=\"text/javascript\" src=\"" .
			JUri::root() . $basePath .
			"/tinymce.min.js\"></script>\n";

		$return = $load .
			"\t<script type=\"text/javascript\">
            tinyMCE.init({
                    // General
                    directionality: \"$text_direction\",
                    selector: \"textarea.mce_editable\",
                    plugins : \"-test, charmap\",
                    language : \"" . $langPrefix . "\",
                    mode : \"specific_textareas\",
                    $skin
                    schema: \"html5\",
                    theme : \"modern\",
                    // Cleanup/Output
                    inline_styles : true,
                    gecko_spellcheck : true,
                    entity_encoding : \"$entity_encoding\",
                    $forcenewline
                    // URL
                    relative_urls : $relative_urls,
                    remove_script_host : false,
                    // Layout                  
                    document_base_url : \"" . JUri::root() . "\",
            });
            </script>";
		$doc = JFactory::getDocument();
		$doc->addCustomTag($return);
		return true;
	}
}
