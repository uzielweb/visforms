<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

class VisformsMailadapterVfedit extends VisformsMailadapter
{
	public function receipt() {
		$emailsettings = $this->formreceiptmailsettings;
		if (isset($emailsettings['editemailreceipt'])) {
			$this->receiptmailsettings[$this->prefix . 'emailreceipt'] = $emailsettings['editemailreceipt'];
		}
		if (isset($emailsettings['editemailreceiptsubject'])) {
			$this->receiptmailsettings['emailreceiptsubject'] = $emailsettings['editemailreceiptsubject'];
		}
		if (isset($emailsettings['editemailreceiptfrom'])) {
			$this->receiptmailsettings['emailreceiptfrom'] = $emailsettings['editemailreceiptfrom'];
		}
		if (isset($emailsettings['editemailreceiptfromname'])) {
			$this->receiptmailsettings['emailreceiptfromname'] = $emailsettings['editemailreceiptfromname'];
		}
		if (isset($emailsettings['editemailreceipttext'])) {
			$this->receiptmailsettings['emailreceipttext'] = $emailsettings['editemailreceipttext'];
		}
		if (isset($emailsettings['editemailreceiptincfield'])) {
			$this->receiptmailsettings['emailreceiptincfield'] = $emailsettings['editemailreceiptincfield'];
		}
		if (isset($emailsettings['editemailreceipthideemptyfields'])) {
			$this->receiptmailsettings['emailreceipthideemptyfields'] = $emailsettings['editemailreceipthideemptyfields'];
		}
		if (isset($emailsettings['editemailreceiptemptycaliszero'])) {
			$this->receiptmailsettings['emailreceiptemptycaliszero'] = $emailsettings['editemailreceiptemptycaliszero'];
		}
		if (isset($emailsettings['editemailreceiptincdatarecordid'])) {
			$this->receiptmailsettings['emailreceiptincdatarecordid'] = $emailsettings['editemailreceiptincdatarecordid'];
		}
		if (isset($emailsettings['editemailreceiptinccreated'])) {
			$this->receiptmailsettings['emailreceiptinccreated'] = $emailsettings['editemailreceiptinccreated'];
		}
		if (isset($emailsettings['editemailreceiptincformtitle'])) {
			$this->receiptmailsettings['emailreceiptincformtitle'] = $emailsettings['editemailreceiptincformtitle'];
		}
		if (isset($emailsettings['editemailreceiptincip'])) {
			$this->receiptmailsettings['emailreceiptincip'] = $emailsettings['editemailreceiptincip'];
		}
		if (isset($emailsettings['editemailrecipientincfilepath'])) {
			$this->receiptmailsettings['emailrecipientincfilepath'] = $emailsettings['editemailrecipientincfilepath'];
		}
		if (isset($emailsettings['editemailreceiptincfile'])) {
			$this->receiptmailsettings['emailreceiptincfile'] = $emailsettings['editemailreceiptincfile'];
		}
		$result = array_merge($this->receiptmailsettings, $this->customreceiptmailsettings);
		return $result;
	}

	public function result() {
		$emailsettings = $this->formresultmailsettings;
		if (isset($emailsettings['editemailresult'])) {
			$this->resultmailsettings[$this->prefix . 'emailresult'] = $emailsettings['editemailresult'];
		}
		if (isset($emailsettings['editemailfrom'])) {
			$this->resultmailsettings['emailfrom'] = $emailsettings['editemailfrom'];
		}
		if (isset($emailsettings['editemailfromname'])) {
			$this->resultmailsettings['emailfromname'] = $emailsettings['editemailfromname'];
		}
		if (isset($emailsettings['editemailto'])) {
			$this->resultmailsettings['emailto'] = $emailsettings['editemailto'];
		}
		if (isset($emailsettings['editemailcc'])) {
			$this->resultmailsettings['emailcc'] = $emailsettings['editemailcc'];
		}
		if (isset($emailsettings['editemailbcc'])) {
			$this->resultmailsettings['emailbcc'] = $emailsettings['editemailbcc'];
		}
		if (isset($emailsettings['editsubject'])) {
			$this->resultmailsettings['subject'] = $emailsettings['editsubject'];
		}
		if (isset($emailsettings['editemailresulttext'])) {
			$this->resultmailsettings['emailresulttext'] = $emailsettings['editemailresulttext'];
		}
		if (isset($emailsettings['editemailresultincfield'])) {
			$this->resultmailsettings['emailresultincfield'] = $emailsettings['editemailresultincfield'];
		}
		if (isset($emailsettings['editemailresulthideemptyfields'])) {
			$this->resultmailsettings['emailresulthideemptyfields'] = $emailsettings['editemailresulthideemptyfields'];
		}
		if (isset($emailsettings['editemailresultemptycaliszero'])) {
			$this->resultmailsettings['emailresultemptycaliszero'] = $emailsettings['editemailresultemptycaliszero'];
		}
		if (isset($emailsettings['editemailresultincdatarecordid'])) {
			$this->resultmailsettings['emailresultincdatarecordid'] = $emailsettings['editemailresultincdatarecordid'];
		}
		if (isset($emailsettings['editemailresultinccreated'])) {
			$this->resultmailsettings['emailresultinccreated'] = $emailsettings['editemailresultinccreated'];
		}
		if (isset($emailsettings['editemailresultincformtitle'])) {
			$this->resultmailsettings['emailresultincformtitle'] = $emailsettings['editemailresultincformtitle'];
		}
		if (isset($emailsettings['editemailresultincip'])) {
			$this->resultmailsettings['emailresultincip'] = $emailsettings['editemailresultincip'];
		}
		if (isset($emailsettings['editreceiptmailaslink'])) {
			$this->resultmailsettings['receiptmailaslink'] = $emailsettings['editreceiptmailaslink'];
		}
		if (isset($emailsettings['editemailresultincfilepath'])) {
			$this->resultmailsettings['emailresultincfilepath'] = $emailsettings['editemailresultincfilepath'];
		}
		if (isset($emailsettings['editemailresultincfile'])) {
			$this->resultmailsettings['emailresultincfile'] = $emailsettings['editemailresultincfile'];
		}
		$result = array_merge($this->resultmailsettings, $this->customresultmailsettings);
		return $result;
	}

	protected function getPrefix() {
		return 'edit';
	}

	protected function getCustomreceiptmailsettings() {
		parent::getCustomreceiptmailsettings();
		$emailsettings = $this->formreceiptmailsettings;
		if (isset($emailsettings['editemailreceiptmodifiedonly'])) {
			$this->customreceiptmailsettings['editemailreceiptmodifiedonly'] = $emailsettings['editemailreceiptmodifiedonly'];
		}
		if (!empty($emailsettings['editemailreceipt']) && ($emailsettings['editemailreceipt'] == "2")) {
			$userdeciscionreceiptmail = JFactory::getApplication()->input->post->get('editemailreceiptuserdecision', '', 'STRING');
			if ($userdeciscionreceiptmail === "1") {
				$this->customreceiptmailsettings['userdeciscionreceiptmail'] = $userdeciscionreceiptmail;
			}
		}
	}

	protected function getCustomresultmailsettings() {
		parent::getCustomresultmailsettings();
		$emailsettings = $this->formresultmailsettings;
		if (isset($emailsettings['editemailresultmodifiedonly'])) {
			$this->customresultmailsettings['editemailresultmodifiedonly'] = $emailsettings['editemailresultmodifiedonly'];
		}
		if (!empty($emailsettings['editemailresult']) && ($emailsettings['editemailresult'] == "2")) {
			$userdeciscionresultmail = JFactory::getApplication()->input->post->get('editemailresultuserdecision', '', 'STRING');
			if ($userdeciscionresultmail === "1") {
				$this->customresultmailsettings['userdeciscionresultmail'] = $userdeciscionresultmail;
			}
		}
	}

	protected function setFormReceiptMailSettings() {
		$formreceiptmailsettings = $this->form->editemailreceiptsettings;
		$registry = new JRegistry;
		//Convert to an array
		$registry->loadString($formreceiptmailsettings);
		$this->formreceiptmailsettings = $registry->toArray();
	}

	protected function setFormResultMailSettings() {
		$formresultmailsettings = $this->form->editemailresultsettings;
		$registry = new JRegistry;
		//Convert to an array
		$registry->loadString($formresultmailsettings);
		$this->formresultmailsettings = $registry->toArray();
	}
}