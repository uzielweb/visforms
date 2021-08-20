<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;
JFormHelper::loadFieldClass('hidden');
require_once JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/aef/aef.php';

class JFormFieldAef extends JFormFieldHidden
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Aef';

	public function renderField($options = array()) {
		return $this->getInput();
	}

	protected function getInput() {
		$feature = $this->getAttribute('feature', 8);
		$minversion = $this->getAttribute('version', '');
		if (empty($minversion)) {
			$featureexists = VisformsAEF::checkAEF($feature);
			if (!empty($featureexists)) {
				$this->value = "1";
			} 
			else {
				$this->value = "0";
			}
		} 
		else {
			$installedversion = VisformsAEF::getVersion($feature);
			if (!empty($installedversion) && (version_compare($installedversion, $minversion, 'ge'))) {
				$this->value = "1";
			} 
			else {
				$this->value = "0";
			}
		}
		return parent::getInput();
	}

	protected function getLabel() {
		return '';
	}
}
