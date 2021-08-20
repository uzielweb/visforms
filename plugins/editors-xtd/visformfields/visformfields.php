<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.visfields
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Editor Visfields buton
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.visfields
 * @since       1.5
 */
class PlgButtonVisformfields extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the button
	 *
	 * @param   string  $name  The name of the button to add
	 *
	 * @return array A four element array of (field-id, field-title, field-type)
	 */
	public function onDisplay($name)
	{
        $app = JFactory::getApplication();
        $o = $app->input->get('option');
        $v = $app->input->get('view');
        if ($o == 'com_visforms' && ($v == 'visform' || $v == 'vispdf'))
        {
            $fid = $app->input->getCmd('fid', 0);
            $id = $fid > 0 ? $fid : $app->input->getCmd('id', 0);
            /*
             * Javascript to insert the link
             * View element calls jSelectVisformsfield when an field is clicked
             * jSelectVisformsfield creates the Placeholder for the field, sends it to the editor,
             * and closes the select frame.
             */
            $jversion = new JVersion();
            $linkeditorname = '';
            if (version_compare($jversion->getShortVersion(), '3.7.0', 'lt')) {
                $js = "
                function jSelectVisformfield(field)
                {
                    var tag = '${' + field + '}';
                    jInsertEditorText(tag, '" . $name . "');
                    SqueezeBox.close();
                }";

                $doc = JFactory::getDocument();
                $doc->addScriptDeclaration($js);
                JHtml::_('behavior.modal');
            }
            else {
                $linkeditorname = '&amp;editor=' . $name;
            }
            /*
             * Use the built-in element view to select the field.
             * Currently uses blank class.
             */
            $link = 'index.php?option=com_visforms&amp;view=visplaceholders&amp;fid=' . $id . '&amp;layout=modal&amp;tmpl=component&amp;'
                . JSession::getFormToken() . '=1' . $linkeditorname;

            $button = new JObject;
            $button->modal = true;
            $button->class = 'btn';
            $button->link = $link;
            $button->text = JText::_('PLG_VISFORMFIELDS_BUTTON_VISFORMFIELDS');
            $button->name = 'file-add';
            $button->options = "{handler: 'iframe', size: {x: 800, y: 500}}";

            return $button;
        }
    }
}
