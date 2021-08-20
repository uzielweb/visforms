<?php
/**
 * Visforms validate email class
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
require_once 'components/com_visforms/lib/mail/verification.php';

/**
 * Visforms validate email
 *
 * @package        Joomla.Site
 * @subpackage     com_visforms
 * @since          1.6
 */
class VisformsValidateVerificationcode extends VisformsValidate
{

	protected $value;
	protected $verificationAddr;

	/**
	 *
	 * Constructor
	 *
	 * @param string $type control type
	 * @param array  $args params for validate
	 */

	public function __construct($type, $args) {
		parent::__construct($type, $args);
		$this->value = isset($args['value']) ? $args['value'] : "";
		$this->verificationAddr = isset($args['verificationAddr']) ? $args['verificationAddr'] : "";
	}

	/**
	 * Method that performs the validation
	 * @return boolean
	 */
	protected function test() {
		$helper = new VisformsMailVerification($this->verificationAddr);
		$storedCodes = $helper->getStoredCodes();
		foreach ($storedCodes as $storedCode) {
			if ($this->value === $storedCode->code) {
				return true;
			}
		}
		return false;
	}
}