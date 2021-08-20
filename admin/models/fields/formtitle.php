<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.form.formfield.text');


class JFormFieldFormtitle extends JFormFieldText
{
	public $type = 'Formtitle';

	protected function getInput() {
        $model = JModelLegacy::getInstance('Visfields', 'VisformsModel');	
        $formtitle = $model->getFormtitle();
        $this->value = $formtitle;
        $this->readonly = true;
        return parent::getInput();
	}
}
