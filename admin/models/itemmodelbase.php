<?php
/**
 * Visforms
 *
 * @author       Ingmar Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2017 vi-solutions
 * @since        Joomla 3.0.0
 */

defined('_JEXEC') or die('Restricted access');

class VisFormsItemModelBase extends JModelAdmin
{
    protected $item;

    public function __construct($config = array()) {
        parent::__construct($config);
    }

    public function getForm($data = array(), $loadData = true) { }

    public function getItem($pk = null)
    {
        if(isset($this->item)) {
            // item already loaded and processed
            return $this->item;
        }
        if($this->item = parent::getItem($pk)) {
            // sub class: format the fields parameters
            $this->loadFormFieldsParameters();
        }
        return $this->item;
    }

    protected function loadFormFieldsParameters() { }
}