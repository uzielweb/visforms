<?php
/**
 * Visforms field file class
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

class VisformsFieldFile extends VisformsField
{
	public function __construct($field, $form) {
		parent::__construct($field, $form);
		//file inputs are not sumitted with the post
		$this->postValue = "";
		//no edit or queryValue for file upload fields
	}

	protected function setField() {
		//preprocessing field
		$this->extractDefaultValueParams();
		$this->extractGridSizesParams();
		$this->extractRestrictions();
		$this->mendBooleanAttribs();
		$this->addValidationAttribsForUpload();
		$this->setIsConditional();
		$this->setFieldDefaultValue();
		$this->setCustomJs();
		$this->setFieldsetCounter();
		$this->setShowRequiredAsterix();
	}

	protected function setFieldDefaultValue() {

		//file upload fields do not use the post value but the $_FILES var, but if we have a POST Value, we set dataSource property
		if ((count($_POST) > 0) && isset($_POST['postid']) && ($_POST['postid'] == $this->form->id)) {
			$this->field->dataSource = 'post';
			return;
		}
		//Nothing to do
		return;
	}

	protected function setDbValue() {
		return;
	}

	protected function setRedirectParam() {
		return;
	}

	protected function addValidationAttribsForUpload() {
		$uploadMaxFileSize = VisformsmediaHelper::toBytes(ini_get('upload_max_filesize'));
		$maxfilesize = (((int) $uploadMaxFileSize > 0) && (((int) $this->form->maxfilesize === 0) || ($this->form->maxfilesize * 1024) > $uploadMaxFileSize)) ? $uploadMaxFileSize : $this->form->maxfilesize * 1024;
		$allowedExtensions = (!empty($this->field->allowedextensions)) ? $this->field->allowedextensions : $this->form->allowedextensions;
		$this->field->validate_filesize = $maxfilesize;
		$this->field->validate_fileextension = "'" . $allowedExtensions . "'";
	}
}