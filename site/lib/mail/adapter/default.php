<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

class VisformsMailadapterDefault extends VisformsMailadapter
{
	public function receipt() {
		$emailsettings = $this->formreceiptmailsettings;
		if (isset($emailsettings['emailreceipt'])) {
			$this->receiptmailsettings[$this->prefix . 'emailreceipt'] = $emailsettings['emailreceipt'];
		}
		if (isset($emailsettings['emailreceiptsubject'])) {
			$this->receiptmailsettings['emailreceiptsubject'] = $emailsettings['emailreceiptsubject'];
		}
		if (isset($emailsettings['emailreceiptfrom'])) {
			$this->receiptmailsettings['emailreceiptfrom'] = $emailsettings['emailreceiptfrom'];
		}
		if (isset($emailsettings['emailreceiptfromname'])) {
			$this->receiptmailsettings['emailreceiptfromname'] = $emailsettings['emailreceiptfromname'];
		}
		if (isset($emailsettings['emailreceipttext'])) {
			$this->receiptmailsettings['emailreceipttext'] = $emailsettings['emailreceipttext'];
		}
		if (isset($emailsettings['emailreceiptincfield'])) {
			$this->receiptmailsettings['emailreceiptincfield'] = $emailsettings['emailreceiptincfield'];
		}
		if (isset($emailsettings['emailreceipthideemptyfields'])) {
			$this->receiptmailsettings['emailreceipthideemptyfields'] = $emailsettings['emailreceipthideemptyfields'];
		}
		if (isset($emailsettings['emailreceiptemptycaliszero'])) {
			$this->receiptmailsettings['emailreceiptemptycaliszero'] = $emailsettings['emailreceiptemptycaliszero'];
		}
		if (isset($emailsettings['emailreceiptincdatarecordid'])) {
			$this->receiptmailsettings['emailreceiptincdatarecordid'] = $emailsettings['emailreceiptincdatarecordid'];
		}
		if (isset($emailsettings['emailreceiptinccreated'])) {
			$this->receiptmailsettings['emailreceiptinccreated'] = $emailsettings['emailreceiptinccreated'];
		}
		if (isset($emailsettings['emailreceiptincformtitle'])) {
			$this->receiptmailsettings['emailreceiptincformtitle'] = $emailsettings['emailreceiptincformtitle'];
		}
		if (isset($emailsettings['emailreceiptincip'])) {
			$this->receiptmailsettings['emailreceiptincip'] = $emailsettings['emailreceiptincip'];
		}
		if (isset($emailsettings['emailrecipientincfilepath'])) {
			$this->receiptmailsettings['emailrecipientincfilepath'] = $emailsettings['emailrecipientincfilepath'];
		}
		if (isset($emailsettings['emailreceiptincfile'])) {
			$this->receiptmailsettings['emailreceiptincfile'] = $emailsettings['emailreceiptincfile'];
		}
		return array_merge($this->receiptmailsettings, $this->customreceiptmailsettings);
	}

	public function result() {
		$emailsettings = $this->formresultmailsettings;
		if (isset($emailsettings['emailresult'])) {
			$this->resultmailsettings[$this->prefix . 'emailresult'] = $emailsettings['emailresult'];
		}
		if (isset($emailsettings['emailfrom'])) {
			$this->resultmailsettings['emailfrom'] = $emailsettings['emailfrom'];
		}
		if (isset($emailsettings['emailfromname'])) {
			$this->resultmailsettings['emailfromname'] = $emailsettings['emailfromname'];
		}
		if (isset($emailsettings['emailto'])) {
			$this->resultmailsettings['emailto'] = $emailsettings['emailto'];
		}
		if (isset($emailsettings['emailcc'])) {
			$this->resultmailsettings['emailcc'] = $emailsettings['emailcc'];
		}
		if (isset($emailsettings['emailbcc'])) {
			$this->resultmailsettings['emailbcc'] = $emailsettings['emailbcc'];
		}
		if (isset($emailsettings['subject'])) {
			$this->resultmailsettings['subject'] = $emailsettings['subject'];
		}
		if (isset($emailsettings['emailresulttext'])) {
			$this->resultmailsettings['emailresulttext'] = $emailsettings['emailresulttext'];
		}
		if (isset($emailsettings['emailresultincfield'])) {
			$this->resultmailsettings['emailresultincfield'] = $emailsettings['emailresultincfield'];
		}
		if (isset($emailsettings['emailresulthideemptyfields'])) {
			$this->resultmailsettings['emailresulthideemptyfields'] = $emailsettings['emailresulthideemptyfields'];
		}
		if (isset($emailsettings['emailresultemptycaliszero'])) {
			$this->resultmailsettings['emailresultemptycaliszero'] = $emailsettings['emailresultemptycaliszero'];
		}
		if (isset($emailsettings['emailresultincdatarecordid'])) {
			$this->resultmailsettings['emailresultincdatarecordid'] = $emailsettings['emailresultincdatarecordid'];
		}
		if (isset($emailsettings['emailresultinccreated'])) {
			$this->resultmailsettings['emailresultinccreated'] = $emailsettings['emailresultinccreated'];
		}
		if (isset($emailsettings['emailresultincformtitle'])) {
			$this->resultmailsettings['emailresultincformtitle'] = $emailsettings['emailresultincformtitle'];
		}
		if (isset($emailsettings['emailresultincip'])) {
			$this->resultmailsettings['emailresultincip'] = $emailsettings['emailresultincip'];
		}
		if (isset($emailsettings['receiptmailaslink'])) {
			$this->resultmailsettings['receiptmailaslink'] = $emailsettings['receiptmailaslink'];
		}
		if (isset($emailsettings['emailresultincfilepath'])) {
			$this->resultmailsettings['emailemailresultincfilepath'] = $emailsettings['emailresultincfilepath'];
		}
		if (isset($emailsettings['emailresultincfile'])) {
			$this->resultmailsettings['emailresultincfile'] = $emailsettings['emailresultincfile'];
		}
		return array_merge($this->resultmailsettings, $this->customresultmailsettings);
	}

	protected function getPrefix() {
		return '';
	}

	protected function setFormReceiptMailSettings() {
		$formreceiptmailsettings = $this->form->emailreceiptsettings;
		$registry = new JRegistry;
		//Convert to an array
		$registry->loadString($formreceiptmailsettings);
		$this->formreceiptmailsettings = $registry->toArray();
		$this->formreceiptmailsettings['emailreceipt'] = $this->form->emailreceipt;
		$this->formreceiptmailsettings['emailreceiptsubject'] = $this->form->emailreceiptsubject;
		$this->formreceiptmailsettings['emailreceiptfrom'] = $this->form->emailreceiptfrom;
		$this->formreceiptmailsettings['emailreceiptfromname'] = $this->form->emailreceiptfromname;
		$this->formreceiptmailsettings['emailreceipttext'] = $this->form->emailreceipttext;
	}

	protected function setFormResultMailSettings() {
		$formresultmailsettings = $this->form->emailresultsettings;
		$registry = new JRegistry;
		//Convert to an array
		$registry->loadString($formresultmailsettings);
		$this->formresultmailsettings = $registry->toArray();
		$this->formresultmailsettings['emailresult'] = $this->form->emailresult;
		$this->formresultmailsettings['emailfrom'] = $this->form->emailfrom;
		$this->formresultmailsettings['emailfromname'] = $this->form->emailfromname;
		$this->formresultmailsettings['emailto'] = $this->form->emailto;
		$this->formresultmailsettings['emailcc'] = $this->form->emailcc;
		$this->formresultmailsettings['emailbcc'] = $this->form->emailbcc;
		$this->formresultmailsettings['subject'] = $this->form->subject;
		$this->formresultmailsettings['emailresulttext'] = $this->form->emailresulttext;
	}
}