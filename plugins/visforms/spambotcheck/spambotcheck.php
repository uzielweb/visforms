<?php
/**
* @version		$Id: plgCkformsSpamCheck.php
* @package		visforms SpamCheck - check for possible spambots during register and login
* @author		vi-solutions, Aicha Vack the plugin is build on user-plugin spambotchek, originally written by Robert Kuster
* @copyright	Copyright (C) 2013 vi-solutions. All rights reserved.
* @license		GNU/GPL, see LICENSE.txt
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
jimport('joomla.plugin.plugin');
require_once(JPATH_SITE.'/plugins/visforms/spambotcheck/spambotcheck/spambotcheckimpl.php');
jimport('joomla.application.component.model');

class plgVisformsSpambotCheck extends JPlugin
{
    private $model;
    private $id;
    private $input;
    
    public function __construct(&$subject, $config) {
        parent::__construct($subject, $config);
        //load the translation
        $this->loadLanguage();
        $this->input=JFactory::getApplication()->input;
        $this->id = $this->input->getInt('id', 0);
    }
	
	
	/**
	 * Example store user method
	 *
	 * Method is called before user data is stored in the database
	 *
	 * @param 	array		holds the old user data (without new changes applied)
	 * @param 	boolean		true if a new user is stored
	 * 
	 * RKFIX - Check if this is a known spammer. If so:
	 * 			> prevent user registration
	 *			> notify the admins about the registration attempt via email
	 *			> show the the normal login notification to the user 
	 */
	public function onVisformsSpambotCheck($context = '') 
    {
        JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_visforms/models', 'VisformsModel');
        $this->model = JModelLegacy::getInstance('Visforms', 'VisformsModel', array('ignore_request' => true, 'id' => $this->id));
        $fields = $this->model->getItems();
        $form = $this->model->getForm();
        $user = Array ();
        $user['email'] = "";
        //find the first e-mail field in form and consider it the recipient mail
        foreach($fields as $value)
        {
            if(isset($value->typefield) && $value->typefield == "email")
            {
                $emailField = $value->name;
                if (isset($form->context))
                {
                    $emailField = $form->context . $emailField;
                }
                $user['email'] = $this->input->getString($emailField, '');
                break;
            }
        }
		
		//$this->params->set('current_action', 'Register');
		$spamString = "";
		if(self::isSpammer($user, $spamString))
		{
			if ($context == '')
            {
                //we come from an older version of the data edit extension
                //Throw a message for the spammer
                $app = JFactory::getApplication();
                $app->enqueueMessage(sprintf (JText::_('PLG_VISFORMS_SPAMBOTCHECK_USER_LOGIN_SPAM_TXT')), "error");
            }
            //do nothing else
			return true;			
		}
		return false;
	}
	
	/**
	 * Method check if the user specified is a spammer.
	 *
	 * @param 	array		holds the user data
	 * @param 	string		$spamstring hold the raw spam string 
	 * 
	 * @return boolean True if user is a spammer and False if he isn't. 
	 */
	function isSpammer($user, &$spamString)
	{
        $form = $this->model->getForm();
        if (!isset($form->spamprotection)) {
            return false;
        }
        $registry = new JRegistry;
        $params = $registry->loadString($form->spamprotection);
        $SpambotCheck = new plgVisformsSpambotCheckImpl($params, $user['email'], $_SERVER['REMOTE_ADDR'], "");
		$SpambotCheck->checkSpambots();
        if ($SpambotCheck->sIdentifierTag == false || strlen($SpambotCheck->sIdentifierTag) == 0 || strpos($SpambotCheck->sIdentifierTag, "SPAMBOT_TRUE") === false) {
            // not a spammer
            $spamString = "";
            return false;
        }
        
        // if we get here we have to deal with a spammer		
        $spamString = $SpambotCheck->sIdentifierTag;
        return true;
	}
}