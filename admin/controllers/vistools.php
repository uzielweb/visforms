<?php
/**
 * vistools controller for Visforms
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

class VisformsControllerVisTools extends JControllerLegacy
{
    protected $protectedFiles;

    public function __construct($config = array()) {
		parent::__construct($config);
		$this->registerTask('apply', 'save');
        $this->protectedFiles = array ('bootstrapform.css', 'visdata.css', 'visdata.min.css', 'visforms.bootstrap4.css', 'visforms.bootstrap4.min.css',
	        'visforms.min.css', 'viforms.css', 'visforms.default.css', 'visforms.default.min.css', 'visforms.full.bootstrap4.css', 'visformssearchtools.css');
	}

    public function cancel() {
		$this->setRedirect(JRoute::_('index.php?option=com_visforms', false));
	}

	public function close() {
        $this->setRedirect(JRoute::_('index.php?option=com_visforms&view=vistools', false));
	}
    
    public function getModel($name = 'Vistools', $prefix = 'VisformsModel', $config = array()) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	protected function allowEdit() {
		return JFactory::getUser()->authorise('core.edit.css', 'com_visforms');
	}


	protected function allowSave() {
		return $this->allowEdit();
	}

	function editCSS() {
        // access check
		$app = JFactory::getApplication();
		$context = 'com_visforms.edit.css';		
		$model = $this->getModel();
        $this->setRedirect("index.php?option=com_visforms&view=vistools&layout=default");
	}

	function save() {
		// check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app	 	  = JFactory::getApplication();
        $data         = $this->input->post->get('jform', array(), 'array');
		$task	 	  = $this->getTask();
		$model		  = $this->getModel();
        $fileName     = $app->input->get('file');
		$explodeArray = explode(':', base64_decode($fileName));

		// access check
		if (!$this->allowSave()) {
			$app->enqueueMessage(JText::_('JERROR_SAVE_NOT_PERMITTED'), 'error');
			return false;
		}

		// match the stored id's with the submitted
        if (empty($data['filename'])) {
			$app->enqueueMessage(JText::_('COM_VISFORMS_ERROR_SOURCE_ID_FILENAME_MISMATCH'), 'error');
            return false;
		}
		elseif ($data['filename'] != end($explodeArray)) {
			$app->enqueueMessage(JText::_('COM_VISFORMS_ERROR_SOURCE_ID_FILENAME_MISMATCH'), 'error');
            return false;
		}
        
        // validate the posted data
		$form = $model->getForm();

		if (!$form) {
			$app->enqueueMessage($model->getError(), 'error');
			return false;
		}

        // check for validation errors
        $data = $model->validate($form, $data);
		if ($data === false) {
			// get the validation messages
			$errors	= $model->getErrors();
			// push up to three validation messages out to the user
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}
			// redirect back to the edit screen
            $url = 'index.php?option=com_visforms&view=vistools&layout=default&file=' . $fileName;
			$this->setRedirect(JRoute::_($url, false));
			return false;
		}
		
		// attempt to save the data
		if (!$model->save($data)) {
			// save the data in the session
			$app->setUserState('com_visforms.source.data', $data);
			// redirect back to the edit screen
			$app->enqueueMessage(JText::sprintf('JERROR_SAVE_FAILED', $model->getError()), 'warning');
            $url = 'index.php?option=com_visforms&view=vistools&layout=default&file=' . $fileName;
			$this->setRedirect(JRoute::_($url, false));
		}
		$app->enqueueMessage(JText::_('COM_VISFORMS_FILE_SAVED'));
		
		// redirect the user and adjust session state based on the chosen task
		switch ($task) {
			case 'apply':
				// redirect back to the edit screen
				$url = 'index.php?option=com_visforms&view=vistools&layout=default&file=' . $fileName;
                $this->setRedirect(JRoute::_($url, false));
                break;
			default:
				// redirect to the list screen
				$this->setRedirect(JRoute::_('index.php?option=com_visforms', false));
				break;
		}
	}

	public function createFile() {
		$app      = JFactory::getApplication();
		$model    = $this->getModel();
		$file     = $app->input->get('file');
		$name     = $app->input->get('name');
		$type     = $app->input->get('type');

		if ($type == 'null') {
			$app->enqueueMessage(JText::_('COM_VISFORMS_INVALID_FILE_TYPE'), 'error');
			$url = 'index.php?option=com_visforms&view=vistools&layout=default&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		elseif (!preg_match('/^[a-zA-Z0-9-_]+$/', $name)) {
			$app->enqueueMessage(JText::_('COM_VISFORMS_INVALID_FILE_NAME'), 'error');
			$url = 'index.php?option=com_visforms&view=vistools&layout=default&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		elseif ($model->createFile($name, $type)) {
			$this->setMessage(JText::_('COM_VISFORMS_FILE_CREATE_SUCCESS'));
			$file = urlencode(base64_encode($name . '.' . $type));
			$url = 'index.php?option=com_visforms&view=vistools&layout=default&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		else {
			$app->enqueueMessage(JText::_('COM_VISFORMS_ERROR_FILE_CREATE'), 'error');
			$url = 'index.php?option=com_visforms&view=vistools&layout=default&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
	}

	public function uploadFile() {
		$app      = JFactory::getApplication();
		$model    = $this->getModel();
		$file     = $app->input->get('file');
		$upload   = $app->input->files->get('files');

		if ($return = $model->uploadFile($upload)) {
			$app->enqueueMessage(JText::_('COM_VISFORMS_FILE_UPLOAD_SUCCESS') . $upload['name']);
			$redirect = base64_encode($return);
			$url = 'index.php?option=com_visforms&view=vistools&layout=default&file=' . $redirect;
			$this->setRedirect(JRoute::_($url, false));
		}
		else {
			$app->enqueueMessage(JText::_('COM_VISFORMS_ERROR_FILE_UPLOAD'), 'error');
			$url = 'index.php?option=com_visforms&view=vistools&layout=default&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
	}

	public function delete(){
		$app   = JFactory::getApplication();
		$model = $this->getModel();
		$file  = $app->input->get('file');

		if (in_array(base64_decode(urldecode($file)), $this->protectedFiles)) {
			$app->enqueueMessage(JText::_('COM_VISFORMS_ERROR_OWW_FILE_DELETE'), 'warning');
			$url = 'index.php?option=com_visforms&view=vistools&layout=default&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		elseif ($model->deleteFile($file)) {
			$this->setMessage(JText::_('COM_VISFORMS_FILE_DELETE_SUCCESS'));
			$file = base64_encode('home');
			$url = 'index.php?option=com_visforms&view=vistools&layout=default&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		else {
			$app->enqueueMessage(JText::_('COM_VISFORMS_ERROR_FILE_DELETE'), 'error');
			$url = 'index.php?option=com_visforms&view=vistools&layout=default&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
	}

	public function renameFile() {
		$app     = JFactory::getApplication();
		$model   = $this->getModel();
		$file    = $app->input->get('file');
		$newName = $app->input->get('new_name');

		if (in_array(base64_decode(urldecode($file)), $this->protectedFiles)) {
			$app->enqueueMessage(JText::_('COM_VISFORMS_ERROR_RENAME_OWN_FILE'), 'warning');
			$url = 'index.php?option=com_visforms&view=vistools&layout=default&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		elseif (!preg_match('/^[a-zA-Z0-9-_]+$/', $newName)) {
			$app->enqueueMessage(JText::_('COM_VISFORMS_INVALID_FILE_NAME'), 'error');
			$url = 'index.php?option=com_visforms&view=vistools&layout=default&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		elseif ($rename = $model->renameFile($file, $newName)) {
			$this->setMessage(JText::_('COM_VISFORMS_FILE_RENAME_SUCCESS'));
			$url = 'index.php?option=com_visforms&view=vistools&layout=default&file=' . $rename;
			$this->setRedirect(JRoute::_($url, false));
		}
		else {
			$app->enqueueMessage(JText::_('COM_VISFORMS_ERROR_FILE_RENAME'), 'error');
			$url = 'index.php?option=com_visforms&view=vistools&layout=default&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
	}
}