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
 * Decorate HTML control for Bootstrap default layout
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsHtmlControlDecoratorBtDefault  extends VisformsHtmlControlDecorator
{
   /**
    * Decorate (wrap) html code with bootstrap default html code
    * @param object $field visforms form field
    * @return type
    */
   protected function decorate ()
    {
        //we wrap the control in a div if the field isCondtional, so that we can easily hide the whole control
        //the div class=control is part of the control because it's position divers, depending on the field type
        $control = $this->control;
        $field = $control->field->getField();
        $clabel = $control->createlabel();
        $ccontrol = $control->getControlHtml();
        $ccustomtext = $control->getCustomText();
        $html = '';
        $layout = new JLayoutFile('visforms.decorators.btdefault', null);
        $layout->setOptions(array('component' => 'com_visforms'));
        $html .= $layout->render(array('field' => $field, 'clabel' => $clabel, 'ccontrol' => $ccontrol, 'ccustomtext' => $ccustomtext));
        return $html;
    }
}
?>