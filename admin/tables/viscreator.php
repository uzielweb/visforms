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
 * @since        Joomla 3.6.2
 */

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/tables/tablebase.php');

class TableVisCreator extends VisFormsTableBase
{
    public function __construct(\JDatabaseDriver $db) {
        parent::__construct('#__viscreator', 'id', $db);
    }

    protected function _getAssetName() {
        return 'com_visforms.visform.'. $this->fid . '.viscreator.'.$this->id;
    }

    protected function _getAssetTitle() {
        return $this->label;
    }

    protected function _getAssetParentId(JTable $table = null, $id = null) {
        return $this->_getAssetFormId($table, $id);
    }

    public function bind($array, $ignore = '') {
        // bind the rules
        if (isset($array['rules'])) {
            $rules = new JAccessRules($array['rules']);
            $this->setRules($rules);
        }
        return parent::bind($array, $ignore);
    }

    public function store($updateNulls = false) {
        $this->addCreatedByFields();
        return parent::store($updateNulls);
    }
}