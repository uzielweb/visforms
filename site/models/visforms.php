<?php
/**
 * Visforms model for Visforms
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
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

use Joomla\Utilities\ArrayHelper;

if (!class_exists('JHtmlVisformsselect')) {
	JLoader::register('JHtmlVisformsselect', JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/html/visformsselect.php');
}
if (!class_exists('JHtmlVisformscalendar')) {
	JLoader::register('JHtmlVisformscalendar', JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/html/visformscalendar.php');
}
if (!class_exists('VisformsmediaHelper')) {
	JLoader::register('VisformsmediaHelper', JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/visformsmedia.php');
}

require_once JPATH_ROOT . '/components/com_visforms/lib/layout/helper/formlayout.php';

/**
 * Visforms modell
 *
 * @package        Joomla.Site
 * @subpackage     com_visforms
 * @since          1.6
 */
class VisformsModelVisforms extends JModelLegacy
{
	/**
	 * The form id.
	 *
	 * @var    int
	 * @since  11.1
	 */
	protected $_id;
	/**
	 * Input from request.
	 *
	 * @var    int
	 * @since  11.1
	 */
	protected $input;
	/**
	 * The fields object or null.
	 *
	 * @var    mixed
	 * @since  11.1
	 */
	protected $fields;
	/**
	 * The form object or null.
	 *
	 * @var    mixed
	 * @since  11.1
	 */
	private $form;
	public static $newSubmission = 0;
	public static $editSubmission = 1;
	public static $displayStateIsNew = 1;
	public static $displayStateIsRedisplay = 2;
	public static $displayStateIsNewEditData = 3;
	public static $displayStateIsRedisplayEditData = 4;
	protected $aefList;
	protected $supportedFieldTypes;
	protected $context;
	protected $caller;
	protected $hasBt3Layouts;
	protected $hasBt4Layouts;
	protected $hasUikit3Layouts;
	protected $dataEditMenuExists = false;
	protected $breakPoints = array('Sm', 'Md', 'Lg', 'Xl');

	/**
	 * Constructor.
	 *
	 * @param array $config An optional associative array of configuration settings.
	 *
	 * @see     JModel
	 * @since   11.1
	 */
	public function __construct($config = array()) {
		$config['id'] = (!empty($config['id'])) ? $config['id'] : null;
		$config['context'] = (!empty($config['context'])) ? $config['context'] : '';
		$this->input = JFactory::getApplication()->input;
		$this->aefList = VisformsAEF::getAefList();
		$this->setId($config['id']);
		$this->setCaller($config['context']);
		$this->setContext($config['context']);
		$this->setSupportedFieldTypes();
		$this->setHasBt3Layouts();
		$this->setHasBt4Layouts();
		$this->setHasUikit3Layouts();
		$this->setDataEditMenuExists();
		$language = JFactory::getLanguage();
		$language->load('com_visforms', JPATH_ROOT . '/components/com_visforms', 'en-GB', true);
		$language->load('com_visforms', JPATH_ROOT . '/components/com_visforms', null, true);
		$language->load('com_visforms', JPATH_ROOT, 'en-GB', true);
		$language->load('com_visforms', JPATH_ROOT, null, true);
		parent::__construct($config);
	}

	/**
	 * Method store the form id in _id.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function setId($id = null) {
		if (is_null($id)) {
			$id = $this->input->getInt('id', 0);
		}
		$this->_id = $id;
	}

	protected function setCaller($context = '') {
		$this->caller = '';
		if ((!empty($context)) && (strpos($context, 'plgvfformview_') !== false)) {
			$this->caller = 'plgvfformview';
		}
		if ((!empty($context)) && (strpos($context, 'vfedit') !== false)) {
			$this->caller = 'vfedit';
		}
		if ((!empty($context)) && (strpos($context, 'modvisform') !== false)) {
			$this->caller = 'modvisform';
		}
	}

	protected function setHasBt3Layouts() {
		if (!empty($this->aefList[VisformsAEF::$bootStrap3Layouts])) {
			$this->hasBt3Layouts = true;
		}
		else {
			$this->hasBt3Layouts = false;
		}
		//check support in paid extensions
		if ((!empty($this->caller)) && ($this->caller == 'plgvfformview')) {
			$version = JHTMLVisforms::getPluginFormViewVersion();
			if ((empty($version)) || (version_compare($version, '1.5.2', 'le'))) {
				$this->hasBt3Layouts = false;
			}
		}
		if ((!empty($this->caller)) && ($this->caller == 'vfedit')) {
			$version = JHTMLVisforms::getFrontendDataEditVersion();
			if ((empty($version)) || (version_compare($version, '1.4.1', 'le'))) {
				$this->hasBt3Layouts = false;
			}
		}
	}

	protected function setHasBt4Layouts() {
		if (!empty($this->aefList[VisformsAEF::$bootStrap4Layouts])) {
			$this->hasBt4Layouts = true;
		}
		else {
			$this->hasBt4Layouts = false;
		}
	}

	protected function setHasUikit3Layouts() {
		if (!empty($this->aefList[VisformsAEF::$uikit3Layouts])) {
			$this->hasUikit3Layouts = true;
		}
		else {
			$this->hasUikit3Layouts = false;
		}
	}

	protected function setContext($context = '') {
		//plugin visforms form view < 1.5.0 set config['context'] but does not submit the context with the form
		//thus if php validation fails, user inputs and error messages are stored in the wrong context and will not be found to display again
		//set context = '' in this case
		if ((!empty($context)) && (strpos($context, 'plgvfformview_') !== false)) {
			$version = JHTMLVisforms::getPluginFormViewVersion();
			if ((empty($version)) || (version_compare($version, '1.5.0', 'lt'))) {
				$context = '';
			}
		}
		//we may come from the controller, context is transported as post value then
		if (empty($context)) {
			$context = $this->input->getCmd('context', '');
		}
		//we deal with form displayed by a menu item and have no specific context
		//we fall back to our old standard 'context'
		if (empty($context)) {
			$context = 'form' . $this->_id;
		}
		$this->context = $context;
	}

	public function getContext() {
		return $this->context;
	}

	protected function setSupportedFieldTypes() {
		$this->supportedFieldTypes = array(
			'text', 'password', 'email', 'date', 'number', 'url', 'hidden', 'checkbox', 'multicheckbox', 'radio', 'select', 'file', 'image', 'submit', 'reset', 'fieldsep', 'textarea'
		);
		if (!empty($this->aefList[VisformsAEF::$customFieldTypeCalculation])) {
			$this->supportedFieldTypes[] = 'calculation';
		}
		if (!empty($this->aefList[VisformsAEF::$customFieldTypeLocation])) {
			$this->supportedFieldTypes[] = 'location';
		}
		if (!empty($this->aefList[VisformsAEF::$customFieldTypeSignature])) {
			$this->supportedFieldTypes[] = 'signature';
		}
		if (!empty($this->aefList[VisformsAEF::$subscription])) {
			$this->supportedFieldTypes[] = 'radiosql';
			$this->supportedFieldTypes[] = 'selectsql';
			$this->supportedFieldTypes[] = 'multicheckboxsql';
		}
	}

	/**
	 * Handel compatibility of Visforms extensions with field types added to Visforms core
	 * only necessary of a new field type needs adaptations in the view files
	 * Visforms component and module set supported field types as well, then
	 *
	 * @param string or array $fieldType additional supported field types
	 */
	public function addSupportedFieldType($fieldType = null) {
		if (empty($fieldType)) {
			return;
		}
		if (is_array($fieldType)) {
			foreach ($fieldType as $add) {
				$this->addSupportedFieldType($add);
			}
		}
		else {
			switch ($fieldType) {
				case 'pagebreak' :
					if (!empty($this->aefList[VisformsAEF::$multiPageForms])) {
						$this->supportedFieldTypes[] = $fieldType;
					}
					break;
				default :
					$this->supportedFieldTypes[] = $fieldType;
					break;
			}
		}
	}

	/**
	 * Method to get the form dataset
	 *
	 * @return  object with form data
	 *
	 * @since   11.1
	 */
	public function getForm() {
		// ToDo set this form earlier move code to subfunctions
		$app = JFactory::getApplication();
		$form = $app->getUserState('com_visforms.' . $this->context);
		$storedFormIsValid = $this->validateCachedFormSettings($form);
		//only use stored form if it's settings are valid
		if (empty($storedFormIsValid)) {
			$this->cleanUserState();
			$db = $this->_db;
			$query = $db->getQuery(true);
			$query->select('*')
				->from($db->qn('#__visforms'))
				->where($db->qn('id') . ' = ' . $this->_id);
			try {
				$db->setQuery($query);
				$form = $db->loadObject();
			}
			catch (RuntimeException $e) {
				$form = false;
			}
			$this->form = $form;
			if (empty($form)) {
				return $form;
			}
			$mailadapter = VisformsMailadapter::getInstance($this->form, $this->caller);
			if (is_object($mailadapter)) {
				$this->form->emailreceiptsettings = $mailadapter->receipt();
				$this->makePropertyNameShort('emailreceiptsettings');
				$this->form->emailresultsettings = $mailadapter->result();
				$this->makePropertyNameShort('emailresultsettings');
			}
			$this->form->savesettings = VisformsHelper::registryArrayFromString($this->form->savesettings);
			$this->makePropertyNameShort('savesettings', true);
			$this->form->subredirectsettings = VisformsHelper::registryArrayFromString($this->form->subredirectsettings);
			$this->makePropertyNameShort('subredirectsettings', true);
			$this->form->frontendsettings = VisformsHelper::registryArrayFromString($this->form->frontendsettings);
			$this->makePropertyNameShort('frontendsettings');
			$this->form->layoutsettings = VisformsHelper::registryArrayFromString($this->form->layoutsettings);
			$this->makePropertyNameShort('layoutsettings');
			$this->form->captchaoptions = VisformsHelper::registryArrayFromString($this->form->captchaoptions);
			$this->makePropertyNameShort('captchaoptions');
			$this->form->viscaptchaoptions = VisformsHelper::registryArrayFromString($this->form->viscaptchaoptions);
			if (empty($this->aefList[VisformsAEF::$subscription])) {
				$this->form->preventsubmitonenter = 0;
				$this->form->defaultresponsive = 0;
			}
			$formLayout = new VisformsFormLayout($this->form);
			$this->form = $formLayout->fixInvalidLayoutSelection();
			if (!isset($this->form->displaysublayout)) {
			    //set to default
                $this->form->displaysublayout = 'horizontal';
            }
			//set a flag, if we use a bootstrap 3 Layout
			$this->form->hasBt3Layout = (in_array($this->form->formlayout, array('bt3default', 'editbt3default', 'bt3horizontal', 'editbt3horizontal', 'bt3mcindividual', 'editbt3mcindividual'))) ? true : false;
			//Never include bootstrap 2.3.2 css, if layout is bootstrap 3
			if (!empty($this->form->hasBt3Layout)) {
				$this->form->usebootstrapcss = false;
			}
			$this->setDisplayState();
			// initialize helper properties
			$this->form->errors = array();
			$this->form->steps = 1;
			$this->form->accordioncounter = (int) 0;
			$this->form->mapCounter = (int) 0;
			$subFileVersion = VisformsAEF::getVersion(VisformsAEF::$subFiles);
			$this->form->canHideSummaryOnMultiPageForms = (empty($subFileVersion) || (version_compare($subFileVersion, '1.2.3', 'le'))) ? false : true;
			$this->form->mpdisplaytype = (empty($this->form->mpdisplaytype) || empty($subFileVersion) || (version_compare($subFileVersion, '1.2.3', 'le'))) ? 0 : $form->mpdisplaytype;
			$this->form->firstpanelcollapsed = (empty($this->form->mpdisplaytype) || empty($this->form->firstpanelcollapsed)) ? 0 : $this->form->firstpanelcollapsed;
			$this->form->context = $this->context;
			if (empty($this->aefList[VisformsAEF::$allowFrontEndDataEdit])) {
				$this->form->redirecttoeditview = false;
			}
			$this->form->dataEditMenuExists = $this->dataEditMenuExists;
			if (empty($this->form->formprocessingmessage)) {
				$this->form->formprocessingmessage = JText::_('COM_VISFORMS_FORM_PROCESSING_DEFAULT_MESSAGE');
			}
			//we cannot use recaptcha plugin if it is not enabled!
			if (!empty($this->form->captcha) && ($this->form->captcha == "2") && (!JPluginHelper::isEnabled('captcha', 'recaptcha'))) {
				$this->form->captcha = "0";
			}
			$this->form = $formLayout->setLayoutOptions();
			$app->setUserState('com_visforms.' . $this->context, $this->form);
		}
		else {
			$this->form = $form;
		}
		return $this->form;
	}

	protected function cleanUserState() {
		$app = JFactory::getApplication();
		$task = $app->input->getCmd('task', '');
		//urlparams are stored in the session, so that we can use them to reset invalid user inputs and/or disabled fields to the proper default value, which may be set as an url param
		//stored url params may be junk, for example if a user has just left the form (clicked to another menu item...)
		//these stored url params are only no junk if the task is send, else they must be removed
		if ($task !== 'send') {
			$urlparams = $app->getUserState('com_visforms.urlparams.' . $this->context, null);
			if (isset($urlparams)) {
				$app->setUserState('com_visforms.urlparams.' . $this->context, null);
			}
			unset($urlparams);
		}
		//we stored the disabled stated that results from the stored user inputs in the session
		//these stored disabeld states are only no junk, if the task is saveedit
		if ($task !== 'saveedit') {
			$fieldsdisabledstate = $app->getUserState('com_visforms.fieldsdisabledstate.' . $this->context, null);
			if (isset($fieldsdisabledstate)) {
				$app->setUserState('com_visforms.fieldsdisabledstate.' . $this->context, null);
			}
			unset($fieldsdisabledstate);
		}
	}

	protected function setDisplayState() {
		// the display state is used in the field.php function setQueryValue in order to decide if url params from a get request should be stored in the session
		// url params (from get) are only stored if $displayStateIsNew and we are not in an edit view task
		// the display state is also used in the plugin mail attachments and visdoubleoptin in order to decide, if we send an mail after form submit or after form edit
		// the display state is used in business.php function setOrgDisabledStateFromStoredDataInUserState()
		$app = JFactory::getApplication();
		$task = $app->input->getCmd('task', '');
		if ($task === "editdata") {
			$this->form->displayState = self::$displayStateIsNewEditData;
		}
		else if ($task === "saveedit") {
			$this->form->displayState = self::$displayStateIsRedisplayEditData;
		}
		else {
			$this->form->displayState = self::$displayStateIsNew;
		}
	}

	protected function makePropertyNameShort($propertiesName, $subOnly = false) {
		foreach ($this->form->$propertiesName as $name => $value) {
			if ($subOnly && empty($this->aefList[VisformsAEF::$subscription])) {
				$value = false;
			}
			//make names shorter and set all properties as properties of form object
			$this->form->$name = $value;
		}
	}

	public function getItems() {
		//make sure the form is created
		$this->getForm();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$supportedFieldTypes = implode("','", $this->supportedFieldTypes);
		$query->select('*')
			->from($db->qn('#__visfields'))
			->where($db->qn('fid') . ' = ' . $this->_id)
			->where($db->qn('published') . " = " . 1)
			->where('not ' . $db->qn('editonlyfield') . ' = ' . 1)
			->where($db->qn('typefield') . ' in (\'' . $supportedFieldTypes . '\')')
			->order($db->qn('ordering') . ' asc');
		$items = $this->_getList($query);
		return $items;
	}

	public function getEditOnlyItems() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->qn('#__visfields'))
			->where($db->qn('fid') . ' = ' . $this->_id)
			->where($db->qn('published') . " = " . 1)
			->where($db->qn('editonlyfield') . ' = ' . 1)
			->order($db->qn('ordering') . ' asc');
		$items = $this->_getList($query);
		return $items;
	}

	public function getValidatedFields($submissionType = 0) {
		$visform = $this->getForm();
		$app = JFactory::getApplication();
		$this->fields = $app->getUserState('com_visforms.' . $this->context . '.fields');
		if (!is_array($this->fields)) {
			$fields = $this->getItems();
			$n = count($fields);
			for ($i = 0; $i < $n; $i++) {
				//remove unsupported field types
				if (!in_array($fields[$i]->typefield, $this->supportedFieldTypes)) {
					unset($fields[$i]);
					continue;
				}
			}
			//reset keys
			$fields = array_values($fields);
			//get new count
			$n = count($fields);
			//get basic field definition
			for ($i = 0; $i < $n; $i++) {
				$ofield = VisformsField::getInstance($fields[$i], $visform);
				if (is_object($ofield)) {
					if ($submissionType == self::$editSubmission) {
						$cid = $this->input->get('cid', 0, 'int');
						if (!empty($cid)) {
							$ofield->setRecordId($cid);
						}
					}
					$fields[$i] = $ofield->getField();
				}
			}
			//get new count
			$n = count($fields);
			// perform business logic
			for ($i = 0; $i < $n; $i++) {
				$ofield = VisformsBusiness::getInstance($fields[$i], $visform, $fields);
				if (is_object($ofield)) {
					if (isset($fields[$i]->typefield) && ($fields[$i]->typefield !== "calculation")) {
						//as there may be interactions between the field processed and the rest of the form fields we always return the fields array
						$fields = $ofield->getFields();
					}
				}
			}
			//only after we have performed the business logic on all fields we know which fields are disabled
			//we can do some further fieldspecific business stuff only now
			//reset default values of disabled fields
			//validate the "required" - omit the required validation for disabled fields!
			//we use the business class for this as well
			for ($i = 0; $i < $n; $i++) {
				$ofield = VisformsBusiness::getInstance($fields[$i], $visform, $fields);
				if (is_object($ofield)) {
					if (isset($fields[$i]->typefield) && ($fields[$i]->typefield !== "calculation")) {
						$ofield->setOrgDisabledStateFromStoredDataInUserState();
						$fields[$i] = $ofield->validateRequired();
						$fields[$i] = $ofield->setFieldValueProperties();
					}
				}
			}
			//fields of type calculation only set the custom javascript that calculates the fields in the form view and calculates the dbValue if we are in a "send" task
			//both functions need all other fields to be completely processed so that the calcualtion uses the correct values
			//if the form is displayed, values of calculation fields are calculated with javascript according to all field settings
			//if the submittes data are stored, values are calculated completely independently
			//the value only depends of the values of all other fields but it does not matter if a calculation field is disabled itself or not
			//having a conditional calculation field would only mean, that a user can see the field or not
			for ($i = 0; $i < $n; $i++) {
				$ofield = VisformsBusiness::getInstance($fields[$i], $visform, $fields);
				if (is_object($ofield)) {
					if (isset($fields[$i]->typefield) && ($fields[$i]->typefield === "calculation")) {
						//as there may be interactions between the field processed and the rest of the form fields we always return the fields array
						$fields = $ofield->getFields();
					}
				}
			}
			$this->fields = $fields;
		}
		$app->setUserState('com_visforms.' . $this->context . '.fields', $this->fields);
		return $this->fields;
	}

	/**
	 * @return array|mixed
	 */
	public function reloadFields() {
		//this function is called, when user task send is preform and user inputs were valid but an error occured on storing data in db
		//this is usually the case, when an unique value validation was no longer "valid" because another recordset was stored in the meantime
		//the form is invalid then and we are on the way to re-display the form
		//so we assume that the user inputs are still valide expect the value of the field with the failed unigue value validation
		//basically this functions makes sure, that all already used options of radios, selects and checkboxgroups are disabeled and that text inputs with invalide doublicate values are marked as invalide
		//if the invalid field is disabled the default values are restored according to the implementation of the resetDisabledFieldToDefaultvalue() function in the fields business class
		$visform = $this->getForm();
		$visform->steps = (int) 1;
		$visform->accordioncounter = (int) 0;
		$visform->mapCounter = (int) 0;
		$subFileVersion = VisformsAEF::getVersion(VisformsAEF::$subFiles);
		$visform->canHideSummaryOnMultiPageForms = (empty($subFileVersion) || (version_compare($subFileVersion, '1.2.3', 'le'))) ? false : true;
		$visform->mpdisplaytype = (empty($visform->mpdisplaytype) || empty($subFileVersion) || (version_compare($subFileVersion, '1.2.3', 'le'))) ? 0 : $visform->mpdisplaytype;
		$app = JFactory::getApplication();
		$this->fields = $app->getUserState('com_visforms.' . $this->context . '.fields');
		if (!is_array($this->fields)) {
			//should not happen because wie have already stored fields in user state
			//but if, the field should be completely correct
			$fields = $this->getValidatedFields();
		}
		else {
			$fields = $this->getItems();
			$n = count($fields);
			//get basic field definition
			for ($i = 0; $i < $n; $i++) {
				$ofield = VisformsField::getInstance($fields[$i], $visform);
				if (is_object($ofield)) {
					$fields[$i] = $ofield->getField();
				}
			}
			// perform business logic
			for ($i = 0; $i < $n; $i++) {
				//no fieldtype specific process order necessery on reload field
				//if we reload the fields we know, that we will display the form
				//at this point it does not matter which value is calculated for a field of type calculation because it is recalculated by javascript on document.ready
				//as the custom javascript only replaces fieldnames with html id attributes, no special treatment of these fields is necessary for this task either
				$ofield = VisformsBusiness::getInstance($fields[$i], $visform, $fields);
				if (is_object($ofield)) {
					//as there may be interactions between the field processed and the rest of the form fields we always return the fields array
					$fields = $ofield->getFields();
				}
			}
			//only after we have performed the business logic on all fields we know which fields are disabled
			//we can validate the "required" only then, because we have to omit the required validation for disabled fields!
			//we use the business class for this as well
			for ($i = 0; $i < $n; $i++) {
				$ofield = VisformsBusiness::getInstance($fields[$i], $visform, $fields);
				if (is_object($ofield)) {
					//we don't need to set the orgDisabledStates here because they only needs to be set once and that is already done!
					$fields[$i] = $ofield->validateRequired();
					$fields[$i] = $ofield->setFieldValueProperties();
				}
			}
		}
		$this->fields = $fields;
		$app->setUserState('com_visforms.' . $this->context . '.fields', $this->fields);
		return $this->fields;
	}

	//called from view
	public function getFields() {
		$visform = $this->getForm();
		$app = JFactory::getApplication();
		$this->fields = $app->getUserState('com_visforms.' . $this->context . '.fields');
		if (!is_array($this->fields)) {
			$fields = $this->getValidatedFields();
		}
		else {
			$fields = $this->fields;
		}
		$n = count($fields);
		//prepare HTML
		for ($i = 0; $i < $n; $i++) {
			$html = VisformsHtml::getInstance($fields[$i]);
			if (is_object($html)) {
				$ofield = VisformsHtmllayout::getInstance($visform->formlayout, $html, $visform->displaysublayout);
				if (is_object($ofield)) {
					$fields[$i] = $ofield->prepareHtml();
				}
			}
		}
		$this->fields = $fields;
		return $this->fields;
	}

	function addHits() {
		$db = JFactory::getDbo();
		$visform = $this->getForm();
		if (isset($visform->id)) {
			$query = $db->getQuery(true);
			$hits = $visform->hits + 1;
			$updatefields = array($db->quoteName('hits') . ' = ' . $hits);
			$conditions = array($db->quoteName('id') . ' = ' . $visform->id);
			$query->update($db->quoteName('#__visforms'))->set($updatefields)->where($conditions);
			$db->setQuery($query);
			$db->execute();
		}
	}

	function saveData() {
		//Form and Field structure and info from db
		$visform = $this->getForm();
		$fields = $this->getValidatedFields();
		$visform->fields = $fields;
		$folder = $visform->uploadpath;
		if (VisformsmediaHelper::uploadFiles($visform) === false) {
			return false;
		}
		if ($visform->saveresult == 1) {
			if ($this->storeData($visform) === false) {
				return false;
			}
		}
		if ($visform->emailreceipt == 1) {
			$this->sendReceiptMail($visform);
		}
		if ($visform->emailresult == 1) {
			$this->sendResultMail($visform);
		}
		return true;
	}

	function getMenuparams() {
		$app = JFactory::getApplication();
		$menu_params = $app->getParams();
		$this->setState('menu_params', $menu_params);
		return $menu_params;
	}

	// no longer used
	public function cleanLineBreak($formId, $fields, $id = null) {
		$db = JFactory::getDbo();
		$id = (is_null($id)) ? $db->insertid() : $id;
		$query = $db->getQuery(true);
		$updatefields = array();
		for ($i = 0; $i < count($fields); $i++) {
			$updatefields[] = $db->quoteName('F' . $fields[$i]->id) . ' = replace (F' . $fields[$i]->id . ', CHAR(13,10), \' \')';
		}
		$conditions = array($db->quoteName('id') . ' = ' . $id);
		$query->update($db->quoteName('#__visforms_' . $formId))->set($updatefields)->where($conditions);
		try {
			$db->setQuery($query);
			$result = $db->execute();
		}
		catch (RuntimeException $e) {
		}
	}

	private function storeData($visform) {
		$folder = $visform->uploadpath;
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$lockValidationFields = array();
		$datas = new stdClass();
		$datas->created = JFactory::getDate()->toSql();
		if (!empty($visform->save_exclude_ip) && !empty($this->aefList[VisformsAEF::$subscription])) {
			$datas->ipaddress = '';
		}
		else {
			$datas->ipaddress = $_SERVER['REMOTE_ADDR'];
		}
		$datas->published = ($visform->autopublish == 1) ? 1 : 0;
		$datas->created_by = (isset($user->id)) ? $user->id : 0;
		$n = count($visform->fields);
		for ($i = 0; $i < $n; $i++) {
			$field = $visform->fields[$i];
			//buttons, pagebreaks, fieldseps are not stored, therefore they have a value of NULL in the db
			if ((empty($field->isButton)) && ($field->typefield != 'pagebreak') && ($field->typefield != 'fieldsep')) {
				//make sure that dbValue of disabled fields is set to ''
				if (!empty($field->isDisabled)) {
					//all disabled fields are stored with empty value in db
					$dbfieldvalue = "";
				}
				else {
					if ($field->typefield == 'file' && isset($_FILES[$field->name]['name']) && $_FILES[$field->name]['name'] != '') {
						//save folder and filename
						$file = new stdClass();
						$file->folder = $folder;
						$file->file = $field->file['new_name'];
						$registry = new JRegistry($file);
						$dbfieldvalue = $registry->toString();
					}
					else {
						if (isset($field->dbValue) && $field->typefield != 'file') {
							$dbfieldvalue = $field->dbValue;
						}
						else {
							//Todo Check, if we should throw an error here and redisplay the form, because this should actually never be the case
							$dbfieldvalue = '';
						}
					}
				}
				$dbfieldname = 'F' . $field->id;
				//Add field to insert object
				$datas->$dbfieldname = $dbfieldvalue;
				if ((!empty($field->uniquevaluesonly)) && (isset($dbfieldvalue)) && (empty($field->isDisabled)) && ($dbfieldvalue !== '')) {
					$validation = new stdClass();
					$validation->id = $field->id;
					$validation->name = $dbfieldname;
					$validation->value = $dbfieldvalue;
					//parameter uniguepublishedvaluesonly does not yet exist, therfore results always in 0
					$validation->publishedonly = (!empty($field->uniquepublishedvaluesonly)) ? 1 : 0;
					$validation->label = $field->label;
					$validation->typefield = $field->typefield;
					$lockValidationFields[] = $validation;
				}
				unset($file);
				unset($dbfieldvalue);
				unset($dbfieldname);
				unset($validation);
			}
		}
		try {
			// check if value of field with unique value validation is already stored in the database
			foreach ($lockValidationFields as $test) {
				if ((!empty($test)) && (is_object($test))) {
					$query = $db->getQuery(true);
					$query->select($db->qn('id'))
						->from($db->qn('#__visforms_' . $visform->id));
					if (in_array($test->typefield, array('select', 'multicheckbox', 'selectsql', 'multicheckboxsql'))) {
						$formSelections = JHtmlVisformsselect::explodeMsDbValue($test->value);
						$storedSelections = $query->concatenate(array($db->q(JHtmlVisformsselect::$msdbseparator), $db->quoteName($test->name), $db->q(JHtmlVisformsselect::$msdbseparator)));
						foreach ($formSelections as $formselection) {
							$formselection = '%' . JHtmlVisformsselect::$msdbseparator . $formselection . JHtmlVisformsselect::$msdbseparator . '%';
							$query->where('(' . $storedSelections . ' like ' . $db->q($formselection) . ')');
						}
					}
					else {
						// radios and radiosql are handled here
						$query->where($db->qn($test->name) . ' = ' . $db->q($test->value));
					}
					if (!empty($test->publishedonly)) {
						$query->where($db->qn('published') . ' = ' . 1);
					}
					try {
						$db->setQuery($query);
						$valueExistes = $db->loadResult();
					}
					catch (RuntimeException $e) {
					}
					if (!empty($valueExistes)) {
						throw New RuntimeException('');
					}
				}
			}
			// if not, store the record set in the database
			$result = $db->insertObject('#__visforms_' . $visform->id, $datas);
		}
		catch (RuntimeException $e) {
			$message = $e->getMessage();
			if (!empty($message)) {
				throw new RuntimeException(JText::_('COM_VISFORMS_SAVING_DATA_FAILED') . ' ' . $message);
			}
			else {
				throw New RuntimeException('');
			}
		}
		$visform->dataRecordId = $db->insertid();
		// store default values of edit only fields in db
		$editOnlyFields = $this->getEditOnlyItems();
		if (!empty($editOnlyFields)) {
			$editOnlyFieldData = new stdClass();
			$editOnlyFieldData->id = $visform->dataRecordId;
			for ($i = 0; $i < count($editOnlyFields); $i++) {
				$ofield = VisformsField::getInstance($editOnlyFields[$i], $visform);
				if (is_object($ofield)) {
					$editOnlyFields[$i] = $ofield->getField();
					// empty string is not a default value but "0" would be
					if (isset($editOnlyFields[$i]->editOnlyFieldDbValue) && $editOnlyFields[$i]->editOnlyFieldDbValue !== "") {
						$dbfieldname = 'F' . $editOnlyFields[$i]->id;
						//Add field to insert object
						$editOnlyFieldData->$dbfieldname = $editOnlyFields[$i]->editOnlyFieldDbValue;
					}
				}
			}
			$db->updateObject('#__visforms_' . $visform->id, $editOnlyFieldData, 'id');
		}

		// check whether someone has submitted and stored a form with the same unique value in the meantime (simulaniously)
		// if there are duplicate values for unique value fields in the db
		// if so, we check, if our recordset has the highest id in the group of record sets with duplicate values
		// if so, we delete the record set and throw an error
		foreach ($lockValidationFields as $test) {
			if ((!empty($test)) && (is_object($test))) {
				$query = $db->getQuery(true);
				$query->select($db->qn('id'))
					->from($db->qn('#__visforms_' . $visform->id));
				if (in_array($test->typefield, array('select', 'multicheckbox', 'selectsql', 'multicheckboxsql'))) {
					$formSelections = JHtmlVisformsselect::explodeMsDbValue($test->value);
					$storedSelections = $query->concatenate(array($db->q(JHtmlVisformsselect::$msdbseparator), $db->quoteName($test->name), $db->q(JHtmlVisformsselect::$msdbseparator)));
					foreach ($formSelections as $formselection) {
						$formselection = '%' . JHtmlVisformsselect::$msdbseparator . $formselection . JHtmlVisformsselect::$msdbseparator . '%';
						$query->where('(' . $storedSelections . ' like ' . $db->q($formselection) . ')');
					}
				}
				else {
					// radios and radiosql are handled here
					$query->where($db->qn($test->name) . ' = ' . $db->q($test->value));
				}
				if (!empty($test->publishedonly)) {
					$query->where($db->qn('published') . ' = ' . 1);
				}
				$query->order($db->qn('id'));
				try {
					$db->setQuery($query);
					$checkValueExistes = $db->loadColumn();
				}
				catch (RuntimeException $e) {
				}
				if ((!empty($checkValueExistes)) && (count($checkValueExistes) > 1) && (is_array($checkValueExistes))) {
					//remove the first element
					array_shift($checkValueExistes);
					//we are not the first recordset stored and have to delete ourselves and throw an error
					if (in_array($visform->dataRecordId, $checkValueExistes)) {
						$query = $db->getQuery(true);
						$query->delete($db->qn('#__visforms_' . $visform->id));
						$query->where($db->qn('id') . ' = ' . $visform->dataRecordId . ' LIMIT 1');
						try {
							$db->setQuery($query);
							$db->execute();
						}
						catch (RuntimeException $e) {
						}
						throw New RuntimeException('');
					}
				}
			}
		}
		return true;
	}

	/**
	 * Send Receipt Mail
	 *
	 * @param object $visform Form Object with attached field information
	 */
	protected function sendReceiptMail($visform) {
		//we can only send a mail, if the form has a field of type email, that contains an email
		$isSendMail = false;
		$emailReceiptTo = '';
		$mailBody = '';
		$n = count($visform->fields);
		//Do we have an e-mail field with value? Then get to mail address to which to send the mail to
		for ($i = 0; $i < $n; $i++) {
			$field = $visform->fields[$i];
			if ($field->typefield == 'email') {
				if ($field->dbValue) {
					$isSendMail = true;
					$emailReceiptTo = $field->dbValue;
					break;
				}
			}
		}
		if (empty($isSendMail) || empty($emailReceiptTo)) {
			return;
		}
		JPluginHelper::importPlugin('visforms');
		$app = JFactory::getApplication();
		if (!empty($visform->emailreceipttext)) {
			//Do some replacements in email text
			$text = $visform->emailreceipttext;
			// allow plugins to modify the receipt mail text
			$app->triggerEvent('onVisformsPrepareDoiMailText', array('com_visforms.form.receiptmail', &$text, $visform));
			$visform->emailreceipttext = $text;
		}
		$app->triggerEvent('onVisformsBeforeEmailPrepare', array('com_visforms.form.receiptmail', $visform));
		if (!empty($visform->emailreceipttext)) {
			$replacedText = JHtmlVisforms::replacePlaceholder($visform, $visform->emailreceipttext);
			$mailBody .= JHtmlVisforms::fixLinksInMail($replacedText);
		}
		$bodyData = array();
		if ($visform->emailreceiptincformtitle == 1) {
			$bodyData[] = JText::_('COM_VISFORMS_FORM') . " : " . $visform->title;
		}
		if ($visform->emailreceiptinccreated == 1) {
			$bodyData[] = JText::_('COM_VISFORMS_REGISTERED_AT') . " " . VisformsHelper::getFormattedServerDateTime('now');
		}

		//Include user inputs if parameter is set to true
		if ($visform->emailreceiptincfield == 1) {
			$bodyData[] = $this->getMailIncludeData($visform, 'receipt');
		}
		if ((!(empty($visform->dataRecordId))) && isset($visform->emailreceiptincdatarecordid) && ($visform->emailreceiptincdatarecordid == 1)) {
			$bodyData[] = JText::_('COM_VISFORMS_RECORD_SET_ID') . " : " . $visform->dataRecordId;
		}
		if (!isset($visform->emailreceiptincip) || (isset($visform->emailreceiptincip) && ($visform->emailreceiptincip == 1))) {
			$bodyData[] = JText::_('COM_VISFORMS_IP_ADDRESS') . " : " . $_SERVER['REMOTE_ADDR'];
		}
		$bodyData = implode('<br />', $bodyData);
		if (!empty($bodyData)) {
			$mailBody .= '<p>' . $bodyData . '</p>';
		}
		$mail = JFactory::getMailer();
		$mail->CharSet = "utf-8";
		//Attach files to email
		if (!empty($visform->emailreceiptincfile)) {
			for ($i = 0; $i < $n; $i++) {
				$field = $visform->fields[$i];
				if ($field->typefield == 'file' && isset($_FILES[$field->name]['name']) && $_FILES[$field->name]['name'] != '') {
					if ($field->file['filepath'] != '') {
						$attachmentName = $field->file['name'];
						if (!empty($field->fileattachmentname)) {
							$attachmentName = $this->getAttachementName($visform, $field->fileattachmentname, $attachmentName);
						}
						$mail->addAttachment($field->file['filepath'], $attachmentName);
					}
				}
				else if ($field->typefield == 'file' && ($visform->emailreceiptincfile == "2") && !empty($field->orgfile->filepath)) {
					if (!empty($field->fileattachmentname)) {
						$attachmentName = $this->getAttachementName($visform, $field->fileattachmentname, $field->orgfile->filepath);
						$mail->addAttachment($field->orgfile->filepath, $attachmentName);
					}
					else {
						$mail->addAttachment($field->orgfile->filepath);
					}
				}
			}
		}
		//send the mail
		$emailreceiptsubject = JHtmlVisforms::replacePlaceholder($visform, $visform->emailreceiptsubject);
		$mail->addRecipient($emailReceiptTo);
		if (!empty($visform->emailreceiptfrom)) {
			$mail->setSender(array($visform->emailreceiptfrom, $visform->emailreceiptfromname));
		}
		$mail->setSubject($emailreceiptsubject);
		$mail->IsHTML(true);
		$mail->Encoding = 'base64';
		$mail->setBody($mailBody);
		$app->triggerEvent('onVisformsEmailPrepare', array('com_visforms.form.receiptmail', &$mail, $visform));
		$mail->Send();
	}

	/**
	 * Send Result Mail
	 */
	protected function sendResultMail($visform) {
		JPluginHelper::importPlugin('visforms');
		$app = JFactory::getApplication();
		$app->triggerEvent('onVisformsBeforeEmailPrepare', array('com_visforms.form.resultmail', $visform));
		$emailSender = "";
		//get reply to mail
		$n = count($visform->fields);
		for ($i = 0; $i < $n; $i++) {
			$field = $visform->fields[$i];
			if ($field->typefield == 'email') {
				if (($field->dbValue)) {
					$emailSender = $field->dbValue;
					break;
				}
			}
		}
		$mailBody = '';
		if ((!empty($visform->emailresulttext))) {
			$replacedText = JHtmlVisforms::replacePlaceholder($visform, $visform->emailresulttext);
			$mailBody .= JHtmlVisforms::fixLinksInMail($replacedText);
		}
		$bodyData = array();
		if ((!isset($visform->emailresultincformtitle)) || (isset($visform->emailresultincformtitle) && $visform->emailresultincformtitle == 1)) {
			$bodyData[] = JText::_('COM_VISFORMS_FORM') . " : " . $visform->title;
		}
		if ((!isset($visform->emailresultinccreated)) || (isset($visform->emailresultinccreated) && $visform->emailresultinccreated == 1)) {
			$bodyData[] = JText::_('COM_VISFORMS_REGISTERED_AT') . " " . VisformsHelper::getFormattedServerDateTime('now');
		}
		//Include user inputs if parameter is set to true
		if ($visform->emailresultincfield == 1) {
			$bodyData[] = $this->getMailIncludeData($visform, 'result');
		}
		if ((!(empty($visform->dataRecordId))) && isset($visform->emailresultincdatarecordid) && ($visform->emailresultincdatarecordid == 1)) {
			$bodyData[] = JText::_('COM_VISFORMS_RECORD_SET_ID') . " : " . $visform->dataRecordId;
		}
		if (!isset($visform->emailresultincip) || (isset($visform->emailresultincip) && ($visform->emailresultincip == 1))) {
			$bodyData[] = JText::_('COM_VISFORMS_IP_ADDRESS') . " : " . $_SERVER['REMOTE_ADDR'];
		}
		$bodyData = implode('<br />', $bodyData);
		if (!empty($bodyData)) {
			$mailBody .= '<p>' . $bodyData . '</p>';
		}
		$mail = JFactory::getMailer();
		$mail->CharSet = "utf-8";
		//Attach files to email
		if (!empty($visform->emailresultincfile)) {
			for ($i = 0; $i < $n; $i++) {
				$field = $visform->fields[$i];
				if ($field->typefield == 'file' && isset($_FILES[$field->name]['name']) && $_FILES[$field->name]['name'] != '') {
					if (!empty($field->file['filepath']) && !empty($field->file['name'])) {
						$attachmentName = $field->file['name'];
						if (!empty($field->fileattachmentname)) {
							$attachmentName = $this->getAttachementName($visform, $field->fileattachmentname, $attachmentName);
						}
						$mail->addAttachment($field->file['filepath'], $attachmentName);
					}
				}
				else if ($field->typefield == 'file' && ($visform->emailresultincfile == "2") && !empty($field->orgfile->filepath)) {
					if (!empty($field->fileattachmentname)) {
						$attachmentName = $this->getAttachementName($visform, $field->fileattachmentname, $field->orgfile->filepath);
						$mail->addAttachment($field->orgfile->filepath, $attachmentName);
					}
					else {
						$mail->addAttachment($field->orgfile->filepath);
					}
				}
			}
		}
		if (strcmp($visform->emailto, "") != 0) {
			$mail->addRecipient(explode(",", $visform->emailto));
		}
		if (strcmp($visform->emailcc, "") != 0) {
			$mail->addCC(explode(",", $visform->emailcc));
		}
		if (strcmp($visform->emailbcc, "") != 0) {
			$mail->addBCC(explode(",", $visform->emailbcc));
		}
		if (!empty($visform->emailfrom)) {
			$mail->setSender(array($visform->emailfrom, $visform->emailfromname));
		}
		$subject = JHtmlVisforms::replacePlaceholder($visform, $visform->subject);
		$mail->setSubject($subject);
		if ($emailSender != "") {
			$mail->addReplyTo($emailSender);
		}
		$mail->IsHTML(true);
		$mail->Encoding = 'base64';
		$mail->setBody($mailBody);
		$app->triggerEvent('onVisformsEmailPrepare', array('com_visforms.form.resultmail', &$mail, $visform));
		$sent = $mail->Send();
	}

	protected function getAttachementName($visform , $atmName, $uploadName) {
		if (empty($atmName)) {
			return $atmName;
		}
		$fileExt = JFile::getExt($uploadName);
		$attachmentName = JHtmlVisforms::replacePlaceholder($visform, $atmName) . '.' . $fileExt;
		return JFile::makeSafe($attachmentName);
	}

	protected function validateCachedFormSettings($form) {
		if (empty($form)) {
			return false;
		}
		if (!is_object($form)) {
			return false;
		}
		if (empty($form->formlayout)) {
			return false;
		}
		return true;
	}

	public function getRedirectParams($fields, $query = array(), $formcontext = '') {
		if (empty($fields)) {
			return $query;
		}
		foreach ($fields as $field) {
			//setting this param is handled by the field
			//only set, if field option addtoredirecturl is enabled
			if (isset($field->redirectParam)) {
				$contextfreefieldname = (empty($formcontext)) ? ($field->name) : substr($field->name, strlen($formcontext));
				$rdtparamname = (!empty($field->rdtparamname)) ? $field->rdtparamname : $contextfreefieldname;
				switch ($field->typefield) {
					//just make sure that values of this field types are not added accidentally
					case 'file' :
					case 'image' :
					case 'submit' :
					case 'reset' :
						break;
					case 'select' :
					case 'multicheckbox' :
						$query[$rdtparamname] = array();
						foreach ($field->redirectParam as $value) {
							$query[$rdtparamname][] = $value;
						}
						break;
					default :
						$query[$rdtparamname] = $field->redirectParam;
						break;
				}
			}
		}
		return $query;
	}

	protected function getMailIncludeData($visform, $type) {
		$data = array();
		foreach ($visform->fields as $field) {
			$fieldValue = '';
			$label = (!empty($this->aefList[VisformsAEF::$subscription]) && !empty($field->customlabelformail)) ? $field->customlabelformail : $field->label;
			if (!empty($field->isButton)) {
				continue;
			}
			if ($field->typefield == 'pagebreak') {
				continue;
			}
			if ($field->typefield == 'fieldsep') {
				continue;
			}
			if (!empty($field->isDisabled)) {
				continue;
			}
			switch ($type) {
				case 'result' :
					if (empty($field->includeinresultmail)) {
						continue 2;
					}
					break;
				default :
					if (empty($field->includeinreceiptmail)) {
						continue 2;
					}
					break;
			}
			if ($field->typefield == 'file' && isset($_FILES[$field->name]['name']) && $_FILES[$field->name]['name'] != '') {
				switch ($type) {
					case 'result' :
						if ((!empty($visform->emailresultincfilepath)) && (isset($field->file['filelink']))) {
							$fieldValue = $field->file['filelink'];
						}
						else {
							$fieldValue = (isset($field->file['new_name'])) ? $field->file['new_name'] : '';
						}
						break;
					default :
						if ((!empty($visform->emailrecipientincfilepath)) && (isset($field->file['filelink']))) {
							$fieldValue = $field->file['filelink'];
						}
						else {
							$fieldValue = (isset($field->file['name_org'])) ? $field->file['name_org'] : '';
						}
						break;
				}
			}
			else if ($field->typefield == 'signature') {
				//embed binary png
				$layout = new JLayoutFile('visforms.datas.fields.signature', null);
				$layout->setOptions(array('component' => 'com_visforms'));
				$fieldValue = $layout->render(array('field' => $field, 'data' => $field->dbValue));
			}
			else if ($field->typefield == 'textarea') {
				if (!empty($field->keepBr)) {
					$fieldValue = JHtmlVisforms::replaceLinebreaks($field->dbValue, "<br />");
				}
				else {
					$fieldValue = JHtmlVisforms::replaceLinebreaks($field->dbValue, " ");
				}
			}
			else {
				if (isset($field->dbValue)) {
					$fieldValue = JHtmlVisformsselect::removeNullbyte($field->dbValue);
				}
				else {
					//fallback set to ""
					$fieldValue = "";
				}
			}
			//stop execution for this field if fieldvalue is empty and form option is set to hide empty fields in data included in mail
			switch ($type) {
				case 'result' :
					if (!empty($visform->emailresulthideemptyfields)) {
						if (($field->typefield !== 'calculation') && ($fieldValue === '')) {
							continue 2;
						}
						if (($field->typefield === 'calculation') && (!empty($visform->emailresultemptycaliszero)) && (VisformsHelper::checkNumberValueIsZero($fieldValue))) {
							continue 2;
						}
					}
					break;
				default :
					if (!empty($visform->emailreceipthideemptyfields)) {
						if (($field->typefield !== 'calculation') && ($fieldValue === '')) {
							continue 2;
						}
						if (($field->typefield === 'calculation') && (!empty($visform->emailreceiptemptycaliszero)) && (VisformsHelper::checkNumberValueIsZero($fieldValue))) {
							continue 2;
						}
					}
					break;
			}
			if (($type == 'result') && (!empty($visform->receiptmailaslink)) && ($field->typefield == 'email')) {
				$fieldValue = '<a href="mailto:' . $fieldValue . '">' . $fieldValue . '</a>';
			}
			$data[] = $label . " : " . $fieldValue;
		}
		return implode("<br />", $data);
	}

	protected function setDataEditMenuExists() {
		// default value of $this->dataEditMenuExists is false
		if (empty($this->aefList[VisformsAEF::$allowFrontEndDataEdit])) {
			return;
		}
		$dataViewMenuItemExists = JHtmlVisforms::checkDataViewMenuItemExists($this->_id);
		$mysubmenuexists = JHtmlVisforms::checkMySubmissionsMenuItemExists();
		$this->dataEditMenuExists = $dataViewMenuItemExists ? $dataViewMenuItemExists : $mysubmenuexists;
	}

	public function getRecords() {
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$db = JFactory::getDbO();
		$query = $db->getQuery(true);
		$query->select(array('id', 'published'))
			->from($db->quoteName('#__visforms_' . $this->_id))
			->where($db->quoteName('created_by') . " = " . $userId);
		$db->setQuery($query);
		try {
			$details = $db->loadObjectList();
			return $details;
		}
		catch (Exception $ex) {
			return false;
		}
		return false;
	}

	protected function removeForbiddenLayouts() {
	}
	public function checkFormViewMenuItemExists($id = null) {
		$app = JFactory::getApplication();
		$id = (is_null($id)) ? $this->id : (int) $id;
		$menuitems = $app->getMenu()->getItems('link', 'index.php?option=com_visforms&view=visforms&id=' . $id);
		if ((!(empty($menuitems))) && (is_array($menuitems)) && (!empty($menuitems[0]->id))) {
			return $menuitems[0]->id;
		}
		return false;
	}
}
