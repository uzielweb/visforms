<?php
/**
 * visfields controller for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */
 
defined('_JEXEC') or die( 'Restricted access' );
use Joomla\Utilities\ArrayHelper;

class VisformsControllerVisfields extends JControllerAdmin
{
	public function __construct($config = array()) {
		parent::__construct($config);
		$fid = JFactory::getApplication()->input->getInt( 'fid', 0 );
		$this->view_list = 'visfields&fid=' . $fid;
		$this->text_prefix = 'COM_VISFORMS_FIELD';
	}
	
	public function getModel($name = 'Visfield', $prefix = 'VisformsModel', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

    public function forms() {
        $this->setRedirect('index.php?option=com_visforms&view=visforms');
    }

    public function form() {
        $fid = JFactory::getApplication()->input->getInt('fid', 0);
        $app = JFactory::getApplication();
        $context = "com_visforms.edit.visform.id";
        if ($fid != 0) {
            $app->setUserState($context, (array) $fid);
        }
        $this->setRedirect('index.php?option=com_visforms&view=visform&layout=edit&id=' . $fid);
    }

	public function datas() {
		$fid = $this->input->getInt('fid', -1);
		$this->setRedirect( "index.php?option=com_visforms&view=visdatas&fid=".$fid);
		return true;
	}

	public function saveOrderAjaxData() {
		$pks = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');
		$pks = ArrayHelper::toInteger($pks);
		$order = ArrayHelper::toInteger($order);
		$model = $this->getModel();
		$return = $model->saveorderDataDetail($pks, $order);
		if ($return) {
			echo '1';
		}
		\JFactory::getApplication()->close();
	}
}