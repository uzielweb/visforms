<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2018 vi-solutions
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class VisformsMailVerification {

	private $code;
	private $verificationMailAddr;
	private $formId;
	private $mailFrom;
	private $mailFromName;
	private $db;
	private $storedCode;

	public function __construct($mailAddr = null) {
		$verificationMail = (is_null($mailAddr)) ? JFactory::getApplication()->input->post->get('verificationAddr', '', 'STRING') : $mailAddr;
		$valide = VisformsValidate::validate('email', array('value' => $verificationMail));
		$this->verificationMailAddr = (empty($valide)) ? '' : $verificationMail;
		// Ajax Validation => fid, Form Submit => postid
		$this->formId = JFactory::getApplication()->input->post->get('fid', JFactory::getApplication()->input->post->get('postid', 0, 'int'), 'int');
		$config = JFactory::getConfig();
		$this->mailFrom = $config->get('mailfrom', '');
		$this->mailFromName = $config->get('fromname', '');
		$this->code = $this->createVerificationCode();
		$this->db = JFactory::getDbo();
	}

	public function sendVerificationMail() {
		if (empty($this->verificationMailAddr)) {
			return JText::_('COM_VISFORMS_EMAIL_VERIFICATION_EMAIL_REQUIRED');
		}
		if (empty($this->code)) {
			return JText::_('COM_VISFORMS_EMAIL_VERIFICATION_MAIL_NO_CODE');
		}
		if (empty($this->mailFrom)) {
			return JText::_('COM_VISFORMS_EMAIL_VERIFICATION_MAIL_NO_SENDER');
		}
		$codeStored = $this->storeVerificationCode();
		if (empty($codeStored)) {
			return JText::_('COM_VISFORMS_EMAIL_VERIFICATION_CANNOT_STORE_CODE');
		}
		$mail = JFactory::getMailer();
		$mail->CharSet = "utf-8";
		$mail->addRecipient(explode(",", $this->verificationMailAddr));
		$mail->setSender($this->mailFrom);
		$mail->setSubject(JText::_('COM_VISFORMS_VERIFICATIONCODE') . ' ' . $this->mailFromName);
		$mail->IsHTML(true);
		$mail->Encoding = 'base64';
		$mail->setBody(JText::_('COM_VISFORMS_VERIFICATIONCODE_IS') . ' ' . $this->code);
		$sent = $mail->Send();
		if (!empty($sent)) {
			$this->removeExpiredCodes();
			return JText::_('COM_VISFORMS_EMAIL_VERIFICATION_MAIL_SEND');
		}
		else {
			$this->removeExpiredCodes();
			$this->removeVerifactionCode($codeStored);
			return JText::_('COM_VISFORMS_EMAIL_VERIFICATION_MAIL_SEND_PROBLEMS');
		}
	}

	protected function createVerificationCode() {
		if (empty($this->verificationMailAddr) || strlen($this->verificationMailAddr) < 4) {
			return '';
		}
		$code = str_replace('@', '', $this->verificationMailAddr);
		$code = str_replace('.', '', $code);
		$code = str_shuffle($code);
		$code = strtoupper(substr($code, 0, min(strlen($code), 4)));
		return $code;
	}

	public function getStoredCodes() {
		$db = $this->db;
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->qn('#__visverificationcodes'))
			->where($db->qn('email') . ' = ' . $db->q($this->verificationMailAddr))
			->where($db->qn('fid') . ' = ' . $db->q($this->formId));
		try {
			$db->setQuery($query);
			return $db->loadObjectList();
		}
		catch (RuntimeException $e) {
			return false;
		}
	}

	protected function storeVerificationCode() {
		$return = true;
		// check if there are already codes stored for this mail address
		$storedCodes = $this->getStoredCodes();
		if (!empty($storedCodes) && is_array($storedCodes)) {
			// get first element and update it with new code
			$storedCode = array_shift($storedCodes);
			$storedCode->code = $this->code;
			$storedCode->created = JFactory::getDate()->toSql();
			try {
				$this->db->updateObject('#__visverificationcodes', $storedCode, 'id');
				$return = $storedCode->id;
			}
			catch (RuntimeException $e) {
				$return = false;
			}
		}
		else {
			$storedCode = new stdClass();
			$storedCode->fid = $this->formId;
			$storedCode->email = $this->verificationMailAddr;
			$storedCode->code = $this->code;
			$storedCode->created = JFactory::getDate()->toSql();
			try {
				$this->db->insertObject('#__visverificationcodes', $storedCode);
				$storedCode->id = $this->db->insertid();
				$return = $storedCode->id;
			}
			catch (RuntimeException $e) {
				$return = false;
			}
		}
		$this->storedCode = $storedCode;
		unset($storedCode);
		if (!empty($storedCodes) && is_array($storedCodes)) {
			// database is not clean: there was more than one code stored for the mail address: remove them
			foreach ($storedCodes as $storedCode) {
				$this->removeVerifactionCode($storedCode->id);
			}
		}
		return $return;
	}

	protected function removeVerifactionCode($id) {
		$db = $this->db;
		$query = $db->getQuery(true);
		$query->delete($db->qn('#__visverificationcodes'))
			->where($db->qn('id') . ' = ' . $id);
		try {
			$db->setQuery($query);
			$db->execute();
			return true;
		}
		catch (RuntimeException $e) {
			return false;
		}
	}

	protected function removeExpiredCodes() {
		$db = $this->db;
		$query = $db->getQuery(true);
		$query->delete($db->qn('#__visverificationcodes'))
			->where($db->qn('created') . ' < NOW() - INTERVAL 1 WEEK ');
		try {
			$db->setQuery($query);
			return $db->execute();
		}
		catch (RuntimeException $e) {
			return false;
		}
	}
}