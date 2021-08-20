<?php
/**
 * Visforms decorator class for HTML controls
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

/**
 * Decorate HTML control for Bootstrap horizontal layout
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsHtmlControlDecoratorBtHorizontal  extends VisformsHtmlControlDecorator
{
      
   /**
    * Decorate (wrap) html code with bootstrap horizontal html code
    * @param object $field visforms form field
    * @return type
    */
   protected function decorate ()
   {
        $control = $this->control;
        $field = $control->field->getField();
        $clabel = $control->createlabel();
        $ccontrol = $control->getControlHtml();
        $ccustomtext = $control->getCustomText();
        $html = '';
        $layout = new JLayoutFile('visforms.decorators.bthorizontal', null);
        $layout->setOptions(array('component' => 'com_visforms'));
        $html .= $layout->render(array('field' => $field, 'clabel' => $clabel, 'ccontrol' => $ccontrol, 'ccustomtext' => $ccustomtext));
        return $html;
    }
}
?>