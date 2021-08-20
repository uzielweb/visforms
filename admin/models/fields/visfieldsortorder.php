<?php
/**
 * Visform field Visdatasortorder
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

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for Visforms.
 * Supports list Visforms fields.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldVisFieldSortOrder extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'VisFieldSortOrder';


	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array();
		$subVer = VisformsAEF::getVersion(VisformsAEF::$subscription);
		if (version_compare($subVer, '3.2.1', 'ge')) {
        $options[] = JHtml::_(
                    'select.option', 'a.dataordering ASC',
                    JText::_('COM_VISFORMS_GRID_HEADING_ORDERING_DATA_VIEW_ASC'), 'value', 'text',
                    false
                );
		$options[] = JHtml::_(
                    'select.option', 'a.dataordering DESC',
                    JText::_('COM_VISFORMS_GRID_HEADING_ORDERING_DATA_VIEW_DESC'), 'value', 'text',
                    false
                );
		}
        // Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
