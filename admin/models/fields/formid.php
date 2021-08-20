<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.form.formfield.text');


class JFormFieldFormId extends JFormFieldText{

	public $type = 'FormId';

	protected function getInput()
	{
		$fid = JFactory::getApplication()->input->getInt('fid', 0);
		$this->value = $fid;
		$this->readonly = true;
		return parent::getInput();
	}
}
