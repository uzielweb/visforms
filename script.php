<?php

// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\Registry\Registry;
use Joomla\CMS\Version;

class com_visformsInstallerScript
{
	private $name;
    private $release;
	private $oldRelease;
	private $minimum_joomla_release;
	private $maximum_joomla_release = 3;
	private $min_visforms_version;
	private $max_downgrade_version;
	private $vfsubminversion;
	private $versionsWithPostflightFunction;
	private $last_modified_view_files_version;
	private $status;
	private $forms;
	private $loggerName;

	public function preflight($route, $adapter) {
		$this->loggerName = (string) $adapter->getManifest()->loggerName;
		$options['format'] = "{CODE}\t{MESSAGE}";
		$options['text_entry_format'] = "{PRIORITY}\t{MESSAGE}";
		$options['text_file'] = 'visforms_update.php';
		try {
			\JLog::addLogger($options, \JLog::ALL, array($this->loggerName, 'jerror'));
		}
		catch (RuntimeException $e) {
		}

		$this->name = $adapter->getManifest()->name;
		$this->release = $adapter->getManifest()->version;
		$this->oldRelease = "";
		$this->minimum_joomla_release = $adapter->getManifest()->attributes()->version;
		$this->min_visforms_version = $adapter->getManifest()->vfminversion;
		$max_downgrade_version = $this->getLastCompatibleVersion();
		$this->max_downgrade_version = (!empty($max_downgrade_version)) ? $max_downgrade_version : $this->release;
		$this->vfsubminversion = $adapter->getManifest()->vfsubminversion;
		// list all updates with special post flight functions here
		$this->versionsWithPostflightFunction = array('3.1.0', '3.2.0', '3.3.0', '3.4.0', '3.4.1', '3.5.1', '3.6.0', '3.6.3', '3.6.5', '3.7.0', '3.8.17', '3.10.0', '3.10.1', '3.10.2', '3.12.0', '3.13.0', '3.14.0');
		$this->last_modified_view_files_version = $adapter->getManifest()->last_modified_view_files_version;
		$this->status = new stdClass();
		$this->status->fixTableVisforms = array();
		$this->status->modules = array();
		$this->status->plugins = array();
		$this->status->tables = array();
		$this->status->folders = array();
		$this->status->component = array();
		$this->status->messages = array();
		$this->forms = $this->getForms();

		$jversion = new Version;
		$date = new JDate('now');
		$app = \JFactory::getApplication();
		$this->addVisformsLogEntry('*** Start ' . $route . ' of extension ' . $this->name . ' ' . $this->release . ': ' . $date . ' ***', \JLog::INFO);
		// abort if system requirements are not met
		if ($route != 'uninstall') {
            if( version_compare( $jversion->getShortVersion(), $this->minimum_joomla_release, 'lt' ) ) {
                $msg = \JText::_('COM_VISFORMS_WRONG_JOOMLA_VERSION') . $this->minimum_joomla_release;
                $app->enqueueMessage($msg, 'ERROR');
                $this->addVisformsLogEntry($msg, \JLog::ERROR);
                return false;
            }
			if (!defined( 'Joomla\CMS\Version::MAJOR_VERSION') ||(defined( 'Joomla\CMS\Version::MAJOR_VERSION') && $jversion::MAJOR_VERSION > $this->maximum_joomla_release)) {
                $msg = \JText::sprintf('COM_VISFORMS_WRONG_MAX_JOOMLA_VERSION', $this->maximum_joomla_release);
				$app->enqueueMessage($msg, 'ERROR');
				$this->addVisformsLogEntry($msg, \JLog::ERROR);
				return false;
			}

			// abort if the component being installed is lower than the last downgradable version
			if ($route == 'update') {
				$this->oldRelease = $this->getExtensionParam('version');
				$this->addVisformsLogEntry("Installed version is: " . $this->oldRelease . " Update version is : " . $this->release, \JLog::INFO);
				if (version_compare($this->release, $this->max_downgrade_version, 'lt')) {
				    $msg = \JText::sprintf('COM_VISFORMS_WRONG_VERSION_NEW', $this->oldRelease, $this->release);
					$app->enqueueMessage($msg, 'ERROR');
					$this->addVisformsLogEntry($msg, \JLog::ERROR);
					return false;
				}

                // process preflight for specific versions
                if (version_compare($this->oldRelease, $this->min_visforms_version, 'lt')) {
				    $msg = \JText::sprintf('COM_VISFORMS_INCOMPATIBLE_VERSION_NEW', $this->min_visforms_version, $this->oldRelease, $this->release);
	                $app->enqueueMessage($msg, 'ERROR');
                    $this->addVisformsLogEntry($msg, \JLog::ERROR);
                    return false;
                }

				// set permissions for css files (which might be edited through backend and set to readonly) so they can be updated
				$files = array('bootstrapform.css', 'bootstrapform.min.css', 'jquery.searchtools.css', 'jquery.searchtools.min.css', 'visdata.css', 'visdata.min.css', 'visforms.bootstrap4.css', 'visforms.bootstrap4.min.css', 'visforms.css', 'visforms.min.css', 'visforms.default.css', 'visforms.default.min.css', 'visforms.full.bootstrap4.css', 'visforms.full.bootstrap4.min.css', 'visforms.uikit2.css', 'visforms.uikit2.min.css', 'visforms.uikit3.css', 'visforms.uikit3.min.css');
				foreach ($files as $cssfile) {
					@chmod(JPath::clean(JPATH_ROOT . '/media/com_visforms/css/' . $cssfile), 0755);
				}
			}
			else {
				$this->addVisformsLogEntry("*** Start Install: " . $date . " ***", \JLog::INFO);
				$this->addVisformsLogEntry("Version is: " . $this->release, \JLog::INFO);
			}
			// create installation success message (only display if complete installation is executed successfully)
			if ($route == 'update') {
				$msg = \JText::_('COM_VISFORMS_UPDATE_VERSION') . $this->release . \JText::_('COM_VISFORMS_SUCESSFULL');
				if (version_compare($this->oldRelease, $this->last_modified_view_files_version, 'lt')) {
					$msg .= '<br /><strong style="color: red;">' . \JText::_('COM_VISORMS_DELETE_TEMPLATE_OVERRIDES') . '</strong>';
				}
			} else {
				if ($route == 'install') {
					$msg = JText::_('COM_VISFORMS_INSTALL_VERSION') . $this->release . \JText::_('COM_VISFORMS_SUCESSFULL');
				}
			}
            if (!empty($msg)) {
	            $this->status->component = array('name' => 'visForms', 'type' => $route, 'msg' => $msg);
            }
		}
	}

	public function postflight($route, $adapter) {
		if ($route == 'install' || $route == 'update') {
			//Install or update all extensions that come with component visForms
			$this->installExtensions($route, $adapter);
		}
		if ($route == 'update') {
			// run specific component adaptation for specific update versions
			if ((!empty($this->oldRelease)) && ((version_compare($this->oldRelease, '3.0.0', 'ge')) || (version_compare($this->oldRelease, '2.2.0', 'lt')))) {
				foreach ($this->versionsWithPostflightFunction as $versionWithDatabaseChanges) {
					if (version_compare($this->oldRelease, '2.1.0', 'ge') && version_compare($this->oldRelease, '2.2.0', 'lt') && $versionWithDatabaseChanges == "3.1.0") {
						continue;
					}
					if (version_compare($this->oldRelease, $versionWithDatabaseChanges, 'lt')) {
						$postFlightFunctionPostfix = str_replace('.', '_', $versionWithDatabaseChanges);
						$postFlightFunctionName = 'postFlightForVersion' . $postFlightFunctionPostfix;
						if (method_exists($this, $postFlightFunctionName)) {
							$this->$postFlightFunctionName();
						}
					}
				}
			}
			// we must check if tables are not yet converted to utf8mb4 every time, because the conversion can only be performed if the mysql engine supports utf8mb4
			$this->convertTablesToUtf8mb4();
			$this->deleteOldFiles();
			$this->warnUpdateToSubRequired($route);
			$this->warnSubUpdateRequired($route);
			$this->installPdfFonts();
		}
		if ($route == 'install') {
			$this->createFolder(array('images', 'visforms'));
			$this->installPdfFonts();
		}
		if ($route == 'install' || $route == 'update') {
			$this->installationResults($route);
        }
	}

	public function uninstall( $adapter) {
		$this->loggerName = (string) $adapter->getManifest()->loggerName;
		$options['format'] = "{CODE}\t{MESSAGE}";
		$options['text_entry_format'] = "{PRIORITY}\t{MESSAGE}";
		$options['text_file'] = 'visforms_update.php';
		$this->status = new stdClass();
		$this->status->modules = array();
		$this->status->plugins = array();
		$this->status->tables = array();
		$this->status->folders = array();
		$this->status->component = array();
		$this->status->messages = array();
		$this->forms = $this->getForms();
		try {
			\JLog::addLogger($options, \JLog::ALL, array($this->loggerName, 'jerror'));
		}
		catch (RuntimeException $e) {
		}
		$date = new JDate('now');
		$this->addVisformsLogEntry('*** Start uninstall of extension Visforms: ' . $date . ' ***', \JLog::INFO);
		$db = \JFactory::getDbo();
		//delete all visforms related tables in database
		$dataTables = $this->getPrefixFreeDataTableList();
		if (!empty($dataTables)) {
			$this->addVisformsLogEntry("*** Try to delete data tables ***", \JLog::INFO);
			foreach ($dataTables as $tn) {
			    $this->dropTable($tn);
			}
		}
		$visTables = array('#__visfields', '#__visforms', '#__visverificationcodes',
            '#__visforms_lowest_compat_version', '#__visforms_utf8_conversion', '#__visforms_spambot_attempts',
            '#__viscreator', '#__vispdf');
		foreach ($visTables as $visTable) {
		    $this->dropTable($visTable);
        }

		//uninstall plugins
		$this->addVisformsLogEntry("*** Try to uninstall extensions ***", \JLog::INFO);
		$manifest = $adapter->getParent()->manifest;
		$plugins = $manifest->xpath('plugins/plugin');
		foreach ($plugins as $plugin) {
			$name = (string)$plugin->attributes()->plugin;
			$group = (string)$plugin->attributes()->group;
			$plgWhere = $db->quoteName('type') . ' = ' . $db->quote('plugin') . ' AND ' . $db->quoteName('element') . ' = ' . $db->quote($name) . ' AND ' . $db->quoteName('folder') . ' = ' . $db->quote($group);
			$query = $db->getQuery(true)
				->select($db->quoteName('extension_id'))
				->from($db->quoteName('#__extensions'))
				->where($plgWhere);
			try {
				$db->setQuery($query);
				$extensions = $db->loadColumn();
			} 
			catch (RuntimeException $e) {
				$this->addVisformsLogEntry('Unable to get extension_id: ' . $name . ', ' . $e->getMessage(), \JLog::ERROR);
				continue;
			}
			if (count($extensions)) {
				foreach ($extensions as $id) {
					$installer = new JInstaller;
					try {
						$result = $installer->uninstall('plugin', $id);
						$this->status->plugins[] = array('name' => $name, 'group' => $group, 'result' => $result);
						if ($result) {
							$this->addVisformsLogEntry('Plugin sucessfully removed: ' . $name, \JLog::INFO);
						}
						else {
							$this->addVisformsLogEntry('Removal of plugin failed: ' . $name, \JLog::ERROR);
						}
					}
					catch (RuntimeException $e) {
						$this->addVisformsLogEntry('Removal of plugin failed: ' . $name . ', ' . $e->getMessage(), \JLog::ERROR);
					}
				}
			}
		}
		//uninstall modules
		$modules = $manifest->xpath('modules/module');
		foreach ($modules as $module) {
			$name = (string)$module->attributes()->module;
			$client = (string)$module->attributes()->client;
			if (is_null($client)) {
				$client = 'site';
			}
			if ($client == 'site') {
				$client_id = 0;
			} 
			else {
				$client_id = 1;
			}
			$db = \JFactory::getDbo();
			$modWhere = $db->quoteName('type') . ' = ' . $db->quote('module') . ' AND ' . $db->quoteName('element') . ' = ' . $db->quote($name) . ' AND ' . $db->quoteName('client_id') . ' = ' . $db->quote($client_id);
			$query = $db->getQuery(true);
			$query
				->select($db->quoteName('extension_id'))
				->from($db->quoteName('#__extensions'))
				->where($modWhere);
			try {
				$db->setQuery($query);
				$extensions = $db->loadColumn();
			}
			catch (RuntimeException $e) {
				$this->addVisformsLogEntry('Unable to get extension_id: ' . $name . ', ' . $e->getMessage(), \JLog::ERROR);
				continue;
			}
			if (count($extensions)) {
				foreach ($extensions as $id) {
					$installer = new JInstaller;
					try {
						$result = $installer->uninstall('module', $id);
						$this->status->modules[] = array('name' => $name, 'client' => $client, 'result' => $result);
						if ($result) {
							$this->addVisformsLogEntry('Module sucessfully removed: ' . $name, \JLog::INFO);
						}
						else {
							$this->addVisformsLogEntry('Removal of module failed: ' . $name, \JLog::ERROR);
						}
					}
					catch (RuntimeException $e) {
						$this->addVisformsLogEntry('Removal of module failed: ' . $name . ', ' . $e->getMessage(), \JLog::ERROR);
					}
				}
			}
		}

		//delete folders in image folder
		$this->addVisformsLogEntry("*** Try to delete custom files and folders ***", \JLog::INFO);
		jimport('joomla.filesystem.file');
		$folder = JPATH_ROOT .  '/images/visforms';
		if (JFolder::exists($folder)) {
			$result = array();
			try {
				$result[] = JFolder::delete($folder);
				$this->status->folders[] = array('folder' => $folder, 'result' => $result[0]);
				if ($result[0]) {
					$this->addVisformsLogEntry("Folder successfully removed: " . $folder, \JLog::INFO);
				}
				else {
					$this->addVisformsLogEntry('Problems removing folder: ' . $folder, \JLog::ERROR);
				}
			}
			catch (RuntimeException $e) {
				$this->addVisformsLogEntry('Problems removing folder: ' . $folder . ', ' . $e->getMessage(), \JLog::ERROR);
			}

		}

		// delete visuploads folder
		$folder = JPATH_ROOT . '/visuploads';
		if (JFolder::exists($folder)) {
			$result = array();
			try {
				$result[] = JFolder::delete($folder);
				$this->status->folders[] = array('folder' => $folder, 'result' => $result[0]);
				if ($result[0]) {
					$this->addVisformsLogEntry("Folder successfully removed: " . $folder, \JLog::INFO);
				}
				else {
					$this->addVisformsLogEntry('Problems removing folder: ' . $folder, \JLog::ERROR);
				}
			}
			catch (RuntimeException $e) {
				$this->addVisformsLogEntry('Problems removing folder: ' . $folder . ', ' . $e->getMessage(), \JLog::ERROR);
			}
		}

		$this->uninstallationResults();
	}

	private function dropTable($table) {
		$db = \JFactory::getDbo();
		try {
			$db->setQuery("drop table if exists $table");
			$db->execute();
			$this->status->tables[] = array('message' => \JText::sprintf('COM_VISFORMS_TABLE_DROPPED', $table));
			$this->addVisformsLogEntry('Table dropped: ' . $table, \JLog::INFO);
		}
		catch (RuntimeException $e) {
			$this->status->tables[] = array('message' => \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage()));
			$this->addVisformsLogEntry('Unable to drop table: '.$table.', ' . $e->getMessage(), \JLog::ERROR);
		}
    }

	private function postFlightForVersion3_1_0() {
		// skipped, on updates from versions between 2.1.0 and 2.2.0 (actual latest version is 2.1.2)
		$this->addVisformsLogEntry('*** Perform postflight for Version 3.1.0 ***', \JLog::INFO);
		$this->addVisformsLogEntry('*** Try to run fixTableVisforms3_1_0 ***', \JLog::INFO);
		$this->addColumns(array(
			'emailreceiptsettings' => array('name' => 'emailreceiptsettings', 'type' => 'TEXT'),
			'frontendsettings' => array('name' => 'frontendsettings', 'type' => 'TEXT')
        ));
		try {
			$this->fixTableVisforms3_1_0();
		}
		catch (RuntimeException $e) {
			$message = \JText::sprintf('COM_VISFORMS_PROBLEM_UPDATE_DATABASE', 'fixTableVisforms3_1_0') . " " . \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			$this->addVisformsLogEntry('Problems with update of tables: #__visforms', \JLog::ERROR);
		}
		// add new menu params
		$this->addVisformsLogEntry('*** Try to add new menu params ***', \JLog::INFO);
		$menu_params = array('sortorder' => 'id', 'display_num' => '20');
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(array('id', 'link', 'params')))
			->from($db->quoteName('#__menu'))
			->where($db->quoteName('type') . ' = ' . $db->quote('component'));
		$db->setQuery($query);
		$menus = new stdClass();
		try {
			$menus = $db->loadObjectList();
		}
		catch (RuntimeException $e) {
			$message = \JText::_('COM_VISFORMS_UNABLE_TO_UPDATE_MENU_PARAMS') . " " . \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			$this->addVisformsLogEntry('Unable to load menu params: ' . $e->getMessage(), \JLog::WARNING);
		}
		if ($menus) {
			foreach ($menus as $menu) {
				if ((isset($menu->link)) && ($menu->link != "") && (strpos($menu->link, "view=visformsdata") !== false)) {
					$params = json_decode($menu->params, true);
					// add the new variable(s) to the existing one(s)
					foreach ($menu_params as $name => $value) {
						$params[(string)$name] = (string)$value;
						// store the combined new and existing values back as a JSON string
						$paramsString = json_encode($params);
						$db->setQuery('UPDATE #__menu SET params = ' .
							$db->quote($paramsString) . ' WHERE ' . $db->quoteName('id') . ' = ' . $db->quote($menu->id));
						try {
							$db->execute();
							$this->addVisformsLogEntry('Param added: ' . $name . 'to menu with id: ' . $menu->id, \JLog::INFO);
						}
						catch (RuntimeException $e) {
							$this->addVisformsLogEntry('Unable to add param :' . $name . 'to menu with id: ' . $menu->id . " " . $e->getMessage(), \JLog::ERROR);
						}
					}
				}
			}
		}
	}

	private function postFlightForVersion3_2_0() {
		$this->addVisformsLogEntry('*** Perform postflight for Version 3.2.0 ***', JLog::INFO);
		$db = JFactory::getDbo();
		try {
			$this->convertParamsToJsonField('layoutsettings',
				array('formCSSclass' => "", 'required' => 'top'),
				array('formlayout' => 'visforms', 'usebootstrapcss' => '0', 'requiredasterix' => '1')
			);
		}
		catch (RuntimeException $e) {
			$this->status->messages[] = array('message' => \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage()));
			$this->addVisformsLogEntry("Problems converting params in table: #__visforms, " . $e->getMessage(), \JLog::ERROR);
		}
		try {
			$this->dropColumns(array('formCSSclass', 'required'));
		}
		catch (RuntimeException $e) {
			$this->status->messages[] = array('message' => \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage()));
			$this->addVisformsLogEntry("Problems with dropping fields from table: #__visforms, " . $e->getMessage(), \JLog::ERROR);
		}

		try {
			$this->setParams(array('f_submit_attribute_class' => 'btn ', 'f_reset_attribute_class' => 'btn '), 'visfields', 'defaultvalue', $db->quoteName('typefield') . " in ( " . $db->quote('submit') . ", " . $db->quote('reset') . ")");
		}
		catch (RuntimeException $e) {
			$this->status->messages[] = array('message' => \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage()));
			$this->addVisformsLogEntry("Unable to set params in table #__visfields, " . $e->getMessage(), \JLog::WARNING);
		}
		try {
			$this->setParams(array('emailreceiptincip' => '1'), 'visforms', 'emailreceiptsettings');
		}
		catch (RuntimeException $e) {
			$this->status->messages[] = array('message' => \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage()));
			$this->addVisformsLogEntry("Unable to set params in table #__visforms, " . $e->getMessage(), \JLog::WARNING);
		}

		// enforce creation of _save datatable
		try {
			$this->createDataTableSave3_2_0();
		}
		catch (RuntimeException $e) {
			$this->addVisformsLogEntry("Problems creating _save tables, " . $e->getMessage(), \JLog::ERROR);
		}
		// Add column ismfd to data tables
		try {
			$this->updateDataTable3_2_0();
		}
		catch (RuntimeException $e) {
			$this->addVisformsLogEntry("Problems updateing data tables, " . $e->getMessage(), \JLog::ERROR);
		}
		// convert option list of radio buttons and selects from former custom format string to json in table visfields
		try {
			$this->convertSelectRadioOptionList();
		}
		catch (RuntimeException $e) {
			$this->addVisformsLogEntry("Problems converting option list string, " . $e->getMessage(), \JLog::ERROR);
		}
	}

	private function postFlightForVersion3_3_0() {
		$this->addVisformsLogEntry('*** Perform postflight for Version 3.3.0 ***', \JLog::INFO);
		// copy params from plg_visforms_spambotcheck into forms or set default values in form
		$this->addVisformsLogEntry("*** Try to copy params from Plugin Visforms Spambotcheck to forms ***", \JLog::INFO);
		$plgParamsForm = array("spbot_check_ip" => "1",
			"spbot_check_email" => "1",
			"allow_generic_email_check" => "0",
			"spbot_whitelist_email" => "",
			"spbot_whitelist_ip" => "",
			"spbot_log_to_db" => "0",
			"spbot_stopforumspam" => "1",
			"spbot_stopforumspam_max_allowed_frequency" => "0",
			"spbot_projecthoneypot" => "0",
			"spbot_projecthoneypot_api_key" => "",
			"spbot_projecthoneypot_max_allowed_threat_rating" => "0",
			"spbot_sorbs" => "1",
			"spbot_spamcop" => "1",
			"spbot_blacklist_email" => "");

		$newPlgParamsForm = $this->getPlgvscParmas($plgParamsForm);

		if (is_array($newPlgParamsForm)) {
			$plgParamsForm = $newPlgParamsForm;
		}
		$db = JFactory::getDbo();
		$registry = new Registry;
		$registry->loadArray($plgParamsForm);
		$plgParamsForm = (string)$registry;
		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__visforms'))
			->set($db->quoteName('spamprotection') . " = " . $db->quote($plgParamsForm));
		$db->setQuery($query);
		try {
			$db->execute();
			$this->addVisformsLogEntry("Plugin Visforms Spambotcheck params added to forms", \JLog::INFO);
		}
		catch (RuntimeException $e) {
			$this->addVisformsLogEntry("Unable to add plugin Visforms Spambotcheck params to forms: " . $e->getMessage(), \JLog::ERROR);
		}

	}

	private function postFlightForVersion3_4_0() {
		$this->addVisformsLogEntry('*** Perform postflight for Version 3.4.0 ***', \JLog::INFO);
		$forms = $this->forms;
		if ($forms) {
			$this->addVisformsLogEntry("*** Try to set additional frontendsettings in table: #__visforms ***", \JLog::INFO);
			$db = \JFactory::getDbo();
			$this->addVisformsLogEntry(count($forms) . " form recordsets to process", \JLog::INFO);
			foreach ($forms as $form) {
				try {
					$this->setParams(array('frontendaccess' => ((isset($form->access)) ? $form->access : '1'), 'allowfedv' => '1', 'displaycreated' => '0', 'displaycreatedtime' => '0'), 'visforms', 'frontendsettings', $db->quoteName('id') . " = " . $db->quote($form->id));
					$this->addVisformsLogEntry("Value successfully set for form with id: " . $form->id, \JLog::INFO);
				}
				catch (RuntimeException $e) {
					$this->status->fixTableVisforms[] = array('form' => $form->id, 'result' => false, 'resulttext' => \JText::sprintf('COM_VISFORMS_EMAIL_ADDRESS_FIELD_UPDATE_FAILED', 'frontendaccess'));
					$this->addVisformsLogEntry("Problems setting value for form with id: " . $form->id . ', ' . $e->getMessage(), \JLog::ERROR);
				}
			}
		}
		else {
			$this->addVisformsLogEntry("No form recordsets to process", \JLog::INFO);
		}
	}

	private function postFlightForVersion3_4_1() {
		$this->addVisformsLogEntry('*** Perform postflight for Version 3.4.1 ***', \JLog::INFO);
		try {
			$this->convertParamsToJsonField('captchaoptions',
				array('captchacustominfo' => '', 'captchacustomerror' => ''),
				array('captchalabel' => 'Captcha', 'showcaptchalabel' => '0')
			);
		}
		catch (RuntimeException $e) {
			$this->status->messages[] = array('message' => \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage()));
			$this->addVisformsLogEntry("Problems converting params in table: #__visforms, " . $e->getMessage(), \JLog::ERROR);
		}
		try {
			$this->dropColumns(array('captchacustominfo', 'captchacustomerror'));
		}
		catch (RuntimeException $e) {
			$this->status->messages[] = array('message' => \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage()));
			$this->addVisformsLogEntry("Problems with dropping fields from table: #__visforms, " . $e->getMessage(), \JLog::ERROR);
		}
	}

	private function postFlightForVersion3_5_1() {
		$this->addVisformsLogEntry('*** Perform postflight for Version 3.5.1 ***', \JLog::INFO);
		try {
			$this->convertParamsToJsonField('viscaptchaoptions',
				array(),
				array('image_width' => '215', 'image_height' => '80', 'image_bg_color' => '#ffffff', 'text_color' => '#616161', 'line_color' => '#616161',
					'noise_color' => '#616161', 'text_transparency_percentage' => '50', 'use_transparent_text' => '0', 'code_length' => '6', 'case_sensitive' => '0',
					'perturbation' => '0.75', 'num_lines' => '8', 'captcha_type' => 'self::SI_CAPTCHA_STRING'
				)
			);
		}
		catch (RuntimeException $e) {
			$this->status->messages[] = array('message' => \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage()));
			$this->addVisformsLogEntry("Problems converting params in table: #__visforms, " . $e->getMessage(), \JLog::ERROR);
		}

		try {
			$this->convertParamsToJsonField('emailresultsettings',
				array('emailresultincfile' => "0"),
				array('emailresultincfield' => '1', 'emailresultinccreated' => '1', 'emailresultincformtitle' => '1', 'emailresultincip' => '1')
			);
		}
		catch (RuntimeException $e) {
			$this->status->messages[] = array('message' => \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage()));
			$this->addVisformsLogEntry("Problems converting params in table: #__visforms, " . $e->getMessage(), \JLog::ERROR);
		}
		try {
			$this->dropColumns(array('emailresultincfile'));
		}
		catch (RuntimeException $e) {
			$this->status->messages[] = array('message' => \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage()));
			$this->addVisformsLogEntry("Problems with dropping fields from table: #__visforms, " . $e->getMessage(), \JLog::ERROR);
		}
	}

	private function postFlightForVersion3_6_0() {
		$this->addVisformsLogEntry('*** Perform postflight for Version 3.6.0 ***', \JLog::INFO);
		$this->updateDataTable3_6_0();
	}

	private function postFlightForVersion3_6_3() {
		$this->addVisformsLogEntry('*** Perform postflight for Version 3.6.3 ***', \JLog::INFO);
		// Content plugin visforms was replaced with the visforms plugin visforms
		$this->addVisformsLogEntry('Try to uninstall content plugin visforms', \JLog::INFO);
		$name = (string)'visforms';
		$group = (string)'content';
		$db = \JFactory::getDbo();
		$plgWhere = $db->quoteName('type') . ' = ' . $db->quote('plugin') . ' AND ' . $db->quoteName('element') . ' = ' . $db->quote($name) . ' AND ' . $db->quoteName('folder') . ' = ' . $db->quote($group);
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($plgWhere);
		$db->setQuery($query);
		try {
			$extensions = $db->loadColumn();
		}
		catch (RuntimeException $e) {
			$this->addVisformsLogEntry('Unable to get extension_id: ' . $name . ', ' . $e->getMessage(), \JLog::ERROR);
		}
		if (count($extensions)) {
			foreach ($extensions as $id) {
				$installer = new JInstaller;
				try {
					$result = $installer->uninstall('plugin', $id);
					$this->status->plugins[] = array('name' => $name, 'group' => $group, 'result' => $result);
					if ($result) {
						$this->addVisformsLogEntry('Plugin sucessfully removed: ' . $name, \JLog::INFO);
					} else {
						$this->addVisformsLogEntry('Removal of plugin failed: ' . $name, \JLog::ERROR);
					}
				}
				catch (RuntimeException $e) {
					$this->addVisformsLogEntry('Removal of plugin failed: ' . $name . ', ' . $e->getMessage(), \JLog::ERROR);
				}
			}
		}
	}

	private function postFlightForVersion3_6_5() {
		$this->addVisformsLogEntry('*** Perform postflight for Version 3.6.5 ***', \JLog::INFO);
		try {
			$this->setParams(array('includeheadline' => '1'), 'visforms', 'exportsettings');
		}
		catch (RuntimeException $e) {
			$this->status->messages[] = array('message' => \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage()));
			$this->addVisformsLogEntry("Unable to set params in table #__visforms, " . $e->getMessage(), \JLog::WARNING);
		}
	}

	private function postFlightForVersion3_7_0() {
		$this->addVisformsLogEntry('*** Perform postflight for Version 3.7.0 ***', \JLog::INFO);
		$this->fixSepInStoredUserInputsFromMultiSelect();
		$this->convertTableEngine();
	}

	private function postFlightForVersion3_8_17() {
		$this->addVisformsLogEntry('*** Perform postflight for Version 3.8.17 ***', \JLog::INFO);
		$this->convertEditMailOptions();
		$this->setLastCompatibleVersion('3.8.17');
	}

	private function postFlightForVersion3_10_0() {
		$this->addVisformsLogEntry('*** Perform postflight for Version 3.10.0 ***', \JLog::INFO);
		$this->addVisformsLogEntry("*** Try to alter data tables ***", \JLog::INFO);
		$forms = $this->forms;
		if (!empty($forms)) {
			$this->addVisformsLogEntry(count($forms) . " form recordsets to process", \JLog::INFO);
			foreach ($forms as $form) {
				try {
					$this->addColumns(array('modified' => array('name' => 'modified', 'type' => 'datetime', 'notNull' => true, 'default' => '0000-00-00 00:00:00'),
						array('name' => 'modified_by', 'type' => 'int', 'length' => '11', 'notNull' => true, 'default' => 0)
					),
						'visforms_' . $form->id);
				} catch (RuntimeException $e) {
					$this->status->messages[] = array('message' => \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage()));
					$this->addVisformsLogEntry("Problems adding fields to table: #__visforms, " . $form->id . " " . $e->getMessage(), \JLog::ERROR);
				}
			}
		}
	}

	private function postFlightForVersion3_10_1() {
		$this->addVisformsLogEntry('*** Perform postflight for Version 3.10.1 ***', \JLog::INFO);
		$this->addVisformsLogEntry('*** Try to fix time zone settings differences in created date in data tables ***', \JLog::INFO);
		$db = JFactory::getDbo();
		$tableList = $this->getPrefixFreeDataTableList();
		if (empty($tableList)) {
			return;
		}
		foreach ($tableList as $tn) {
			// fix time zone settings differences in created date in data tables
			try {
				// get column list
				$columns = $db->getTableColumns($tn, false);
				$keys = array('id');
				if (isset($columns['created'])) {$keys[] = 'created';}
				if (count($keys) > 1) {
					// fix timezoneoffset in record sets
					$query = $db->getQuery(true);
					$query->select($db->quoteName($keys))
						->from($db->quoteName($tn));
					$db->setQuery($query);
					$datas = $db->loadObjectList();
					if (!empty($datas)) {
						foreach ($datas as $data) {
							$changed = false;
							if (isset($data->created) && $data->created !== "0000-00-00 00:00:00" ) {
								$date = JFactory::getDate($data->created, JFactory::getConfig()->get('offset'));
								$date->setTimezone(new DateTimeZone('UTC'));
								$data->created = $date->toSql();
								$changed = true;
							}
							if ($changed) {
								$db->updateObject($tn, $data, 'id');
							}
						}
					}
				}
			}
			catch (RuntimeException $e) {
				$this->addVisformsLogEntry("Unable to fix dates for table: " . $tn . ', ' . $e->getMessage(), \JLog::ERROR);
			}
		}
	}

	private function postFlightForVersion3_10_2() {
		$this->addVisformsLogEntry('*** Perform postflight for Version 3.10.2 ***', \JLog::INFO);
		$this->deleteUpdateSiteLinks();
	}

	private function postFlightForVersion3_12_0() {
		$this->addVisformsLogEntry('*** Perform postflight for Version 3.12.0 ***', \JLog::INFO);
	    $tableList = $this->getPrefixFreeDataTableList();
	    if (!empty($tableList)) {
	        foreach ($tableList as $table) {
	            $table = str_replace('#__', '', $table);
	            $this->dropColumns(array('articleid'), $table);
            }
        }
		$this->setLastCompatibleVersion('3.10.1');
	}

	private function postFlightForVersion3_13_0() {
		$this->addVisformsLogEntry('*** Perform postflight for Version 3.13.0 ***', \JLog::INFO);
		$this->installPdfFonts();
    }

	private function postFlightForVersion3_14_0() {
		$this->addVisformsLogEntry('*** Perform postflight for Version 3.14.0 ***', \JLog::INFO);
		$this->convertFormLayoutOptions();
		$this->setLastCompatibleVersion('3.14.0');
	}

	private function convertFormLayoutOptions() {
	    $forms = $this->getForms();
	    if (empty($forms)) {
	        return;
        }
		$this->addVisformsLogEntry('Try to update form layout settings', \JLog::INFO);
		foreach ($forms as $form) {
			if (empty($form->layoutsettings)) {
			    continue;
            }
			try {
				$registry = new JRegistry;
				$registry->loadString($form->layoutsettings);
				$layoutSettings = $registry->toArray();
			}
			catch (RuntimeException $e) {
				$this->addVisformsLogEntry("Unable to update form layout settings for form " . $form->id . ', ' . $e->getMessage(), \JLog::ERROR);
				continue;
			}
			if (empty($layoutSettings['formlayout'])) {
				continue;
            }
			switch ($layoutSettings['formlayout']) {
				case 'bthorizontal' :
					$layoutSettings['formlayout'] = 'btdefault';
					$layoutSettings['displaysublayout'] = 'horizontal';
					break;
				case 'mcindividual' :
					$layoutSettings['formlayout'] = 'btdefault';
					$layoutSettings['displaysublayout'] = 'individual';
					break;
				case 'bt3horizontal' :
					$layoutSettings['formlayout'] = 'bt3default';
					$layoutSettings['displaysublayout'] = 'horizontal';
					break;
				case 'bt3mcindividual' :
					$layoutSettings['formlayout'] = 'bt3default';
					$layoutSettings['displaysublayout'] = 'individual';
					break;
				default :
					continue 2;
			}
			try {
				$registry = new JRegistry;
				$registry->loadArray($layoutSettings);
				$form->layoutsettings = (string) $registry;
			}
			catch (RuntimeException $e) {
				$this->addVisformsLogEntry("Unable to update form layout settings for form " . $form->id . ', ' . $e->getMessage(), \JLog::ERROR);
				continue;
			}
			try {
				$result = JFactory::getDbo()->updateObject('#__visforms', $form, 'id');
			}
			catch (RuntimeException $e) {
				$this->addVisformsLogEntry("Unable to update form layout settings for form " . $form->id . ', ' . $e->getMessage(), \JLog::ERROR);
			}
	    }
    }

	private function getLowerCaseTableList() {
		$db = \JFactory::getDbo();
		$tablesAllowed = $db->getTableList();
		if (!empty($tablesAllowed)) {
			return array_map('strtolower', $tablesAllowed);
		}
		else {
		    return false;
        }
    }

    private function getForms() {
	    $db = \JFactory::getDbo();
	    $query = $db->getQuery(true);
	    $query->select('*')
		    ->from($db->qn('#__visforms'));
	    try {
		    $db->setQuery($query);
		    return $db->loadObjectList();
	    }
	    catch (RuntimeException $e) {
		    $this->addVisformsLogEntry('Unable to load form list from database: ' . $e->getMessage(), \JLog::ERROR);
		    return false;
	    }
    }

    private function getPrefixFreeDataTableList () {
	    $prefixFreeTableList = array();
	    $forms = $this->forms;
	    if (empty($forms)) {
	        return $prefixFreeTableList;
        }
	    $db = \JFactory::getDbo();
	    $tableList = $this->getLowerCaseTableList();
	    if (empty($tableList)) {
		    return $prefixFreeTableList;
	    }
        foreach ($forms as $form) {
            $tnfulls = array(strtolower($db->getPrefix() . "visforms_" . $form->id), strtolower($db->getPrefix() . "visforms_" . $form->id . "_save"));
            foreach ($tnfulls as $tnfull) {
                if (in_array($tnfull, $tableList)) {
                    $prefixFreeTableList[] = str_replace(strtolower($db->getPrefix()), "#__", $tnfull);
                }
            }
        }

	    return $prefixFreeTableList;
    }

	private function installationResults($route) {
		$language = JFactory::getLanguage();
		$language->load('com_visforms');
		$rows = 0;
		$image = ($route == 'update') ? 'logo-banner-u.png' : 'logo-banner.png';
		$src = "https://www.vi-solutions.de/images/f/$this->release/$image";
		$extension_message = array();
		$extension_message[] = ($route == 'update') ? '' : '<h2 style="text-align: center;">' . \JText::_('COM_VISFORMS_INSTALL_MESSAGE') . '</h2>';
		$extension_message[] = '<img src="'.$src.'" alt="visForms" align="right" />';
		$extension_message[] = '<h2>' . (($route == 'update') ? \JText::_('COM_VISFORMS_UPDATE_STATE') : \JText::_('COM_VISFORMS_INSTALLATION_STATUS')) . '</h2>';
		$extension_message[] = '<table class="adminlist table table-striped">';
		$extension_message[] = '<thead>';
		$extension_message[] = '<tr>';
		$extension_message[] = '<th class="title" colspan="2" style="text-align: left;">' . \JText::_('COM_VISFORMS_EXTENSION') . '</th>';
		$extension_message[] = '<th width="30%" style="text-align: left;">' . \JText::_('COM_VISFORMS_STATUS') . '</th>';
		$extension_message[] = '</tr>';
		$extension_message[] = '</thead>';
		$extension_message[] = '<tfoot>';
		$extension_message[] = '<tr>';
		$extension_message[] = '<td colspan="3"></td>';
		$extension_message[] = '</tr>';
		$extension_message[] = '</tfoot>';
		$extension_message[] = '<tbody>';
		$extension_message[] = '<tr class="row0">';
		$extension_message[] = '<td class="key" colspan="2">' . \JText::_('COM_VISFORMS_COMPONENT') . '</td>';
		$extension_message[] = '<td><strong>' . $this->status->component['msg'] . '</strong></td>';
		$extension_message[] = '</tr>';
		if (count($this->status->modules)):
			$extension_message[] = '<tr>';
			$extension_message[] = '<th>' . \JText::_('COM_VISFORMS_MODULE') . '</th>';
			$extension_message[] = '<th>' . \JText::_('COM_VISFORMS_CLIENT') . '</th>';
			$extension_message[] = '<th></th>';
			$extension_message[] = '</tr>';
			foreach ($this->status->modules as $module):
				$module_message = "";
				if (!isset($module['type'])) {
					$plugin_message = ($module['result']) ? '<strong>' . \JText::_('COM_VISFORMS_REMOVED') : '<strong style="color: red">' . \JText::_('COM_VISFORMS_NOT_REMOVED');
				}
				else {
					$module_message = ($module['result']) ? (($module['type'] == 'install') ? '<strong>' . \JText::_('COM_VISFORMS_INSTALLED') : '<strong>' . \JText::_('COM_VISFORMS_UPDATED')) : (($module['type'] == 'install') ? '<strong style="color: red">' . \JText::_('COM_VISFORMS_NOT_INSTALLED') : '<strong style="color: red">' . \JText::_('COM_VISFORMS_NOT_UPDATED'));
				}
				$extension_message[] = '<tr class="row' . (++$rows % 2) . '">';
				$extension_message[] = '<td class="key">' . $module['name'] . '</td>';
				$extension_message[] = '<td class="key">' . ucfirst($module['client']) . '</td>';
				$extension_message[] = '<td>' . $module_message . '</strong></td>';
				$extension_message[] = '</tr>';
			endforeach;
		endif;
		if (count($this->status->plugins)):
			$extension_message[] = '<tr>';
			$extension_message[] = '<th>' . \JText::_('COM_VISFORMS_PLUGIN') . '</th>';
			$extension_message[] = '<th>' . \JText::_('COM_VISFORMS_GROUP') . '</th>';
			$extension_message[] = '<th></th>';
			$extension_message[] = '</tr>';
			foreach ($this->status->plugins as $plugin):
				$plugin_message = '';
				if (!isset($plugin['type'])) {
					$plugin_message = ($plugin['result']) ? '<strong>' . \JText::_('COM_VISFORMS_REMOVED') : '<strong style="color: red">' . \JText::_('COM_VISFORMS_NOT_REMOVED');
				}
				else {
					$plugin_message = ($plugin['result']) ? (($plugin['type'] == 'install') ? '<strong>' . \JText::_('COM_VISFORMS_INSTALLED') : '<strong>' . \JText::_('COM_VISFORMS_UPDATED')) : (($plugin['type'] == 'install') ? '<strong style="color: red">' . \JText::_('COM_VISFORMS_NOT_INSTALLED') : '<strong style="color: red">' . \JText::_('COM_VISFORMS_NOT_UPDATED'));
				}
				$extension_message[] = '<tr class="row' . (++$rows % 2) . '">';
				$extension_message[] = '<td class="key">' . ucfirst($plugin['name']) . '</td>';
				$extension_message[] = '<td class="key">' . ucfirst($plugin['group']) . '</td>';
				$extension_message[] = '<td>' . $plugin_message . '</strong></td>';
				$extension_message[] = '</tr>';
			endforeach;
		endif;
		if (count($this->status->folders)):
			$extension_message[] = '<tr>';
			$extension_message[] = '<th colspan="2">' . \JText::_('COM_VISFORMS_FILESYSTEM') . '</th>';
			$extension_message[] = '<th></th>';
			$extension_message[] = '</tr>';
			foreach ($this->status->folders as $folder):
				$folder_message = '';
				$folder_message = ($folder['result']) ? '<strong>' . \JText::_('COM_VISFORMS_CREATED') : '<strong style="color: red">' . \JText::_('COM_VISFORMS_NOT_CREATED');
				$extension_message[] = '<tr class="row' . (++$rows % 2) . '">';
				$extension_message[] = '<td class="key" colspan="2">' . ucfirst($folder['folder']) . '</td>';
				$extension_message[] = '<td>' . $folder_message . '</strong></td>';
				$extension_message[] = '</tr>';
			endforeach;
		endif;
		if (count($this->status->fixTableVisforms)):
			$extension_message[] = '<tr>';
			$extension_message[] = '<th colspan="2">' . \JText::_('COM_VISFORMS_UPDATE_FIX_FOR_FORM_DATA') . '</th>';
			$extension_message[] = '<th></th>';
			$extension_message[] = '</tr>';
			foreach ($this->status->fixTableVisforms as $recordset):
				$table_message = '';
				$table_message = ($recordset['result']) ? '<strong>' . $recordset['resulttext'] : '<strong style="color: red">' . $recordset['resulttext'];
				$extension_message[] = '<tr class="row' . (++$rows % 2) . '">';
				$extension_message[] = '<td class="key" colspan="2">' . \JText::_('COM_VISFORMS_FORM_WITH_ID') . $recordset['form'] . '</td>';
				$extension_message[] = '<td>' . $table_message . '</strong></td>';
				$extension_message[] = '</tr>';
			endforeach;
		endif;
		if (count($this->status->messages)) :
			$extension_message[] = '<tr>';
			$extension_message[] = '<th colspan="2">' . \JText::_('COM_VISFORMS_MESSAGES') . '</th>';
			$extension_message[] = '<th></th>';
			$extension_message[] = '</tr>';
			foreach ($this->status->messages as $message) {
				$extension_message[] = '<tr class="row' . (++$rows % 2) . '">';
				$extension_message[] = '<td class="key" colspan="2"></td>';
				$extension_message[] = '<td><strong style="color: red">' . $message['message'] . '</strong></td>';
				$extension_message[] = '</tr>';
			}
		endif;
		$extension_message[] = '</tbody>';
		$extension_message[] = '</table>';
		echo implode(' ', $extension_message);
	}

	private function uninstallationResults() {
		$language = JFactory::getLanguage();
		$language->load('com_visforms');
		$rows = 0;
		$src = "https://www.vi-solutions.de/images/f/$this->release/logo-banner-d.png";
		
		echo '<img src="'.$src.'" alt="visForms" align="right" />';?>
        <h2><?php echo \JText::_('COM_VISFORMS_REMOVAL_STATUS'); ?></h2>
        <table class="adminlist table table-striped">
            <thead>
            <tr>
                <th class="title" colspan="2"
                    style="text-align: left;"><?php echo \JText::_('COM_VISFORMS_EXTENSION'); ?></th>
                <th width="30%" style="text-align: left;"><?php echo \JText::_('COM_VISFORMS_STATUS'); ?></th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="3"></td>
            </tr>
            </tfoot>
            <tbody>
            <tr class="row0">
                <td class="key" colspan="2"><?php echo \JText::_('COM_VISFORMS_COMPONENT'); ?></td>
                <td><strong><?php echo \JText::_('COM_VISFORMS_REMOVED'); ?></strong></td>
            </tr>
			<?php if (count($this->status->modules)): ?>
                <tr>
                    <th><?php echo \JText::_('COM_VISFORMS_MODULE'); ?></th>
                    <th><?php echo \JText::_('COM_VISFORMS_CLIENT'); ?></th>
                    <th></th>
                </tr>
				<?php foreach ($this->status->modules as $module): ?>
                    <tr class="row<?php echo(++$rows % 2); ?>">
                        <td class="key"><?php echo $module['name']; ?></td>
                        <td class="key"><?php echo ucfirst($module['client']); ?></td>
                        <td><?php echo ($module['result']) ? '<strong>' . \JText::_('COM_VISFORMS_REMOVED') : '<strong style="color: red">' . \JText::_('COM_VISFORMS_NOT_REMOVED'); ?></strong></td>
                    </tr>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php if (count($this->status->plugins)): ?>
                <tr>
                    <th><?php echo \JText::_('COM_VISFORMS_PLUGIN'); ?></th>
                    <th><?php echo \JText::_('COM_VISFORMS_GROUP'); ?></th>
                    <th></th>
                </tr>
				<?php foreach ($this->status->plugins as $plugin): ?>
                    <tr class="row<?php echo(++$rows % 2); ?>">
                        <td class="key"><?php echo ucfirst($plugin['name']); ?></td>
                        <td class="key"><?php echo ucfirst($plugin['group']); ?></td>
                        <td><?php echo ($plugin['result']) ? '<strong>' . \JText::_('COM_VISFORMS_REMOVED') : '<strong style="color: red">' . \JText::_('COM_VISFORMS_NOT_REMOVED'); ?></strong></td>
                    </tr>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php if (count($this->status->tables)) { ?>
                <tr>
                    <th><?php echo \JText::_('COM_VISFORMS_TABLES'); ?></th>
                    <th></th>
                    <th></th>
                </tr>
				<?php foreach ($this->status->tables as $table) { ?>
                    <tr class="row<?php echo(++$rows % 2); ?>">
                        <td class="key" colspan="3"><?php echo ucfirst($table['message']); ?></td>
                    </tr>
				<?php } ?>
			<?php } ?>
			<?php if (count($this->status->folders)): ?>
                <tr>
                    <th colspan="2"><?php echo \JText::_('COM_VISFORMS_FILESYSTEM'); ?></th>
                    <th></th>
                </tr>
				<?php foreach ($this->status->folders as $folder): ?>
                    <tr class="row<?php echo(++$rows % 2); ?>">
                        <td class="key" colspan="2"><?php echo ucfirst($folder['folder']); ?></td>
                        <td><?php echo ($folder['result']) ? '<strong>' . \JText::_('COM_VISFORMS_DELETED') : '<strong style="color: red">' . \JText::_('COM_VISFORMS_NOT_DELETED'); ?></strong></td>
                    </tr>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php if (!empty($this->status->messages)) : ?>
                <tr>
                    <th colspan="2"><?php echo \JText::_('COM_VISFORMS_MESSAGES'); ?></th>
                    <th></th>
                </tr>
				<?php foreach ($this->status->messages as $message) {
					?>
                    <tr class="row<?php echo(++$rows % 2); ?>">
                        <td class="key" colspan="2"></td>
                        <td><?php echo '<strong style="color: red">' . $message['message'] . '</strong>'; ?></td>
                    </tr>
				<?php } ?>
			<?php endif; ?>
            </tbody>
        </table>
		<?php
	}

	private function createFolder($folders = array()) {
		$this->addVisformsLogEntry("*** Try to create folders ***", \JLog::INFO);
		// create visforms folder in image directory and copy an index.html into it
		jimport('joomla.filesystem.file');
		$folder = JPATH_ROOT;
		foreach ($folders as $name) {
			$folder .= '/' . $name;
		}

		if (($folder != JPATH_ROOT) && !(JFolder::exists($folder))) {
			$result = array();
			try {
				$result[] = JFolder::create($folder);
				$this->status->folders[] = array('folder' => $folder, 'result' => $result[0]);
				if ($result[0]) {
					$this->addVisformsLogEntry("Folder successfully created: " . $folder, \JLog::INFO);
				} 
				else {
					$this->addVisformsLogEntry("Problems creating folder: " . $folder, \JLog::ERROR);
				}
			} 
			catch (RuntimeException $e) {
				$this->addVisformsLogEntry("Problems creating folders, " . $e->getMessage(), \JLog::ERROR);
			}

			$src = JPATH_ROOT . '/media/com_visforms/index.html';
			$dest = JPath::clean($folder .  '/index.html');

			try {
				$result[] = JFile::copy($src, $dest);
				$this->status->folders[] = array('folder' => $folder . '/index.html', 'result' => $result[1]);
				if ($result[1]) {
					$this->addVisformsLogEntry("File successfully copied: " . $dest, \JLog::INFO);
				} 
				else {
					$this->addVisformsLogEntry("Problems copying file: " . $dest, \JLog::ERROR);
				}
			} 
			catch (RuntimeException $e) {
				$this->addVisformsLogEntry("Problems copying files, " . $e->getMessage(), \JLog::ERROR);
			}
		}
	}

	private function installExtensions($route, $adapter) {
		$this->addVisformsLogEntry("*** Try to install extensions ***", \JLog::INFO);
		$db = \JFactory::getDbo();
		$src = $adapter->getParent()->getPath('source');
		$manifest = $adapter->getParent()->manifest;
		$types = array(array('libraries', 'library'), array('plugins', 'plugin'), array('modules', 'module'));
		foreach ($types as $type) {
			$etype = $type[0];
			$ename = $type[1];
			$xmldefs = $manifest->xpath($etype . '/' . $ename);
			foreach ($xmldefs as $xmldef) {
				$name = (string)$xmldef->attributes()->$ename;
				$newVersion = (string)$xmldef->attributes()->version;
				$version = "";
				$extWhere = $db->quoteName('type') . ' = ' . $db->quote($ename) . ' AND ' . $db->quoteName('element') . ' = ' . $db->quote($name);
				if ($ename == 'plugin') {
					$group = (string)$xmldef->attributes()->group;
					$path = $src . '/' . $etype . '/' . $group;
					if (JFolder::exists($src . '/' . $etype . '/' . $group . '/' . $name)) {
						$path = $src . '/' . $etype . '/' . $group . '/' . $name;
					}
					$extWhere .= ' AND ' . $db->quoteName('folder') . ' = ' . $db->quote($group);
				}
				if ($ename == 'module') {
					$client = (string)$xmldef->attributes()->client;
					if (is_null($client)) {
						$client = 'site';
					}
					if ($client == 'site') {
						$client_id = 0;
					} 
					else {
						$client_id = 1;
					}
					($client == 'administrator') ? $path = $src . '/administrator/' . $etype . '/' . $name : $path = $src . '/' . $etype . '/' . $name;
					$extWhere .= ' AND ' . $db->quoteName('client_id') . ' = ' . $db->quote($client_id);
				}
				if ($ename == 'library') {
					$path = $src . '/' . $etype . '/' . $name;
				}
				$query = $db->getQuery(true);
				$query
					->select($db->quoteName('extension_id'))
					->from($db->quoteName('#__extensions'))
					->where($extWhere);
				$extension = array();
				try {
					$db->setQuery($query);
					$extension = $db->loadColumn();
				} 
				catch (RuntimeException $e) {
					$message = \JText::sprintf('COM_VISFORMS_UNABLE_TO_GET_EXTENSION_ID', $name) . " " . \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
					$this->status->messages[] = array('message' => $message);
					$this->addVisformsLogEntry('Unable to get extension_id: ' . $name . ', ' . $e->getMessage(), \JLog::ERROR);
					continue;
				}
				$installer = new JInstaller;
				if (count($extension)) {
					// make sure we have got only on id, if not use the first
					if (is_array($extension)) {
						$extension = $extension[0];
					}
					// check if we need to update
					try {
						$version = $this->getExtensionParam('version', (int)$extension);
					} 
					catch (RuntimeException $e) {
						$message = \JText::sprintf('COM_VISFORMS_UNABLE_TO_GET_EXTENSION_PARAMS', $name) . " " . \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
						$this->status->messages[] = array('message' => $message);
						$this->addVisformsLogEntry('Unable to get ' . $ename . ' params: ' . $name . ', ' . $e->getMessage(), \JLog::ERROR);
						continue;
					}
					if (version_compare($newVersion, $version, 'gt')) {
						$installationType = "update";
					}
				} 
				else {
					$installationType = "install";
				}
				if (isset($installationType)) {
					try {
						$result = $installer->$installationType($path);
						$resultArray = array('name' => $name, 'result' => $result, 'type' => $installationType);
						if ($ename == "plugin") {
							$resultArray['group'] = $group;
							$this->status->plugins[] = $resultArray;
							// we have to enable some plugins
							if ($name == 'visforms') {
								$this->addVisformsLogEntry("Try to enable " . $ename . " " . $name, \JLog::INFO);
								$this->enableExtension($extWhere);
							}
							if ($name == 'spambotcheck') {
								$this->addVisformsLogEntry("Try to enable " . $ename . " " . $name, \JLog::INFO);
								$this->enableExtension($extWhere);
							}
							if ($name == 'visformfields') {
								$this->addVisformsLogEntry("Try to enable " . $ename . " " . $name, \JLog::INFO);
								$this->enableExtension($extWhere);
							}

						}
						if ($ename == "module") {
							$resultArray['client'] = $client;
							$this->status->modules[] = $resultArray;
						}
						if ($result) {
							$this->addVisformsLogEntry($installationType . " of " . $ename . ' sucessful: ' . $name, \JLog::INFO);
						} 
						else {
							$this->addVisformsLogEntry($installationType . " of " . $ename . ' failed: ' . $name, \JLog::ERROR);
						}
					} 
					catch (RuntimeException $e) {
						$this->addVisformsLogEntry($installationType . " of " . $ename . ' failed: ' . $name . ', ' . $e->getMessage(), \JLog::ERROR);
					}
					unset($installationType);
				}
			}
		}
	}

	private function getExtensionParam($name, $eid = 0) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('manifest_cache'));
		$query->from($db->quoteName('#__extensions'));
		// check if a extenstion id is given. If yes we want a parameter from this extension
		if ($eid != 0) {
			$query->where($db->quoteName('extension_id') . ' = ' . $db->quote($eid));
		} 
		else {
			// we want a parameter from component visForms
			$query->where($this->getComponentWhereStatement());
		}
		try {
			$db->setQuery($query);
			$manifest = json_decode($db->loadResult(), true);
		} 
		catch (RuntimeException $e) {
			$message = \JText::sprintf('COM_VISFORMS_UNABLE_TO_GET_VALUE_OF_PARAM', $name) . " " . \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			$this->addVisformsLogEntry('Unable to get value of param ' . $name . ', ' . $e->getMessage(), \JLog::ERROR);
		}

		return $manifest[$name];
	}

	private function setExtensionParams($param_array) {
		if (count($param_array) > 0) {
			$db = \JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select($db->quoteName('params'))
				->from($db->quoteName('#__extensions'))
				->where($this->getComponentWhereStatement());
			$db->setQuery($query);
			$params = json_decode($db->loadResult(), true);
			foreach ($param_array as $name => $value) {
				$params[(string)$name] = (string)$value;
			}
			$paramsString = json_encode($params);
			$db->setQuery('UPDATE #__extensions SET params = ' .
				$db->quote($paramsString) . ' WHERE ' . $this->getComponentWhereStatement());
			$db->execute();
		}
	}

	private function setParams($param_array, $table, $fieldName, $where = "") {
		if (count($param_array) > 0) {
			$this->addVisformsLogEntry("*** Try to add params to table: #__" . $table . " ***", \JLog::INFO);
			$db = \JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select($db->quoteName(array('id', $fieldName)))
				->from($db->quoteName('#__' . $table));
			if ($where != "") {
				$query->where($where);
			}
			$results = new stdClass();
			try {
				$db->setQuery($query);
				$results = $db->loadObjectList();
				$this->addVisformsLogEntry(count($results) . ' recordsets to process', \JLog::INFO);
			}
			catch (RuntimeException $e) {
				$this->addVisformsLogEntry('Unable to load param fields, ' . $e->getMessage(), \JLog::ERROR);
			}
			if ($results) {
				foreach ($results as $result) {
					$params = json_decode($result->$fieldName, true);
					// add the new variable(s) to the existing one(s)
					foreach ($param_array as $name => $value) {
						$params[(string)$name] = (string)$value;
					}
					// store the combined new and existing values back as a JSON string
					$paramsString = json_encode($params);
					try {
						$db->setQuery('UPDATE #__' . $table . ' SET ' . $fieldName . ' = ' .
							$db->quote($paramsString) . ' WHERE id=' . $result->id);
						$db->execute();
						$this->addVisformsLogEntry("Params successfully added", \JLog::INFO);
					}
					catch (RuntimeException $e) {
						$this->addVisformsLogEntry('Problems with adding params ' . $e->getMessage(), \JLog::ERROR);
					}
				}
			}
		}
	}

	// create where statement to select visforms component record in #__extensions table
	private function getComponentWhereStatement() {
		$db = \JFactory::getDbo();
		$where = $db->quoteName('type') . ' = ' . $db->quote('component') . ' AND ' . $db->quoteName('element') . ' = ' . $db->quote('com_visforms') . ' AND ' . $db->quoteName('name') . ' = ' . $db->quote('visforms');

		return $where;
	}

	private function deleteOldFiles() {
		$filesToDelete = array(
			'/administrator/components/com_visforms/controllers/vishelp.php',
			'/administrator/components/com_visforms/css/visforms_min.css',
			'/administrator/components/com_visforms/images/icon-16-visforms.png',
			'/administrator/components/com_visforms/js/jquery-ui.js',
			'/administrator/components/com_visforms/js/jquery-ui.min.js',
			'/administrator/components/com_visforms/models/vishelp.php',
			'/administrator/components/com_visforms/models/fields/aeffrontenddataedit.php',
			'/administrator/components/com_visforms/models/fields/btsize.php',
			'/administrator/components/com_visforms/models/fields/donate.php',
			'/administrator/components/com_visforms/models/fields/spaceraefhidden.php',
			'/administrator/components/com_visforms/views/vistools/tmpl/css.php',
			'/administrator/components/com_visforms/views/visfields/tmpl/default_batch.php',
			'/components/com_visforms/captcha/images/audio_icon.gif',
			'/components/com_visforms/controllers/message.php',
			'/components/com_visforms/models/message.php',
			'/components/com_visforms/lib/layout/helper/formlayoutstatebt3horizontal.php',
			'/components/com_visforms/lib/layout/helper/formlayoutstatebt3mcindividual.php',
			'/libraries/visolutions/tcpdf/encodings_maps.php',
			'/libraries/visolutions/tcpdf/htmlcolors.php',
			'/libraries/visolutions/tcpdf/pdf417.php',
			'/libraries/visolutions/tcpdf/spotcolors.php',
			'/libraries/visolutions/tcpdf/tcpdf_filters.php',
			'/libraries/visolutions/tcpdf/unicode_data.php',
			'/media/com_visforms/js/visforms.min.js',
			'/plugins/system/visformsdatadelete/language/de-DE/de-DE.plg_system_visformsdeletedata.ini',
			'/plugins/system/visformsdatadelete/language/de-DE/de-DE.plg_system_visformsdeletedata.sys.ini',
			'/plugins/system/visformsdatadelete/language/en-GB/en-GB.plg_system_visformsdeletedata.ini',
			'/plugins/system/visformsdatadelete/language/en-GB/en-GB.plg_system_visformsdeletedata.sys.ini'
		);
		$foldersToDelete = array(
			'/administrator/components/com_visforms/views/vishelp',
			'/administrator/components/com_visforms/lib/placeholder',
            '/components/com_visforms/views/message'
		);
		$this->addVisformsLogEntry('*** Try to delete old files ***', \JLog::INFO);
		jimport('joomla.filesystem.file');
		foreach ($filesToDelete as $fileToDelete) {
			$oldfile = JPath::clean(JPATH_ROOT . $fileToDelete);
			if (JFile::exists($oldfile)) {
				try {
					JFile::delete($oldfile);
					$this->addVisformsLogEntry($oldfile . " deleted", \JLog::INFO);
				} 
				catch (RuntimeException $e) {
					$this->addVisformsLogEntry('Unable to delete ' . $oldfile . ': ' . $e->getMessage(), \JLog::INFO);
				}
			} else {
				$this->addVisformsLogEntry($oldfile . " does not exist.", \JLog::INFO);
			}

		}
		foreach ($foldersToDelete as $folderToDelete) {
			$folder = JPath::clean(JPATH_ROOT . $folderToDelete);
			if (JFolder::exists($folder)) {
				try {
					JFolder::delete($folder);
					$this->addVisformsLogEntry($folder . "deleted", \JLog::INFO);
				} catch (RuntimeException $e) {
					$this->addVisformsLogEntry('Unable to delete ' . $folder . ': ' . $e->getMessage(), \JLog::INFO);
				}
			} else {
				$this->addVisformsLogEntry($folder . " does not exist.", \JLog::INFO);
			}

		}

	}

	private function fixTableVisforms3_1_0() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$this->addVisformsLogEntry('*** Try to move emailreceipt params into new param field emailreceiptsettings ***', \JLog::INFO);
		$query
			->select($db->quoteName(array('id', 'emailreceiptincfield', 'emailreceiptincfile', 'emailrecipientincfilepath')))
			->from($db->quoteName('#__visforms'));
		$db->setQuery($query);
		try {
			$forms = $db->loadObjectList();
			$this->addVisformsLogEntry(count($forms) . " form recordsets to process", \JLog::INFO);
		}
		catch (RuntimeException $e) {
			$this->addVisformsLogEntry('Unable to get forms: ' . $e->getMessage(), \JLog::ERROR);
		}
		if (count($forms) > 0) {
			foreach ($forms as $form) {
				$emailreceiptsettings = array();
				$emailreceiptsettings['emailreceiptincfield'] = isset($form->emailreceiptincfield) ? $form->emailreceiptincfield : 0;
				$emailreceiptsettings['emailreceiptincfile'] = isset($form->emailreceiptincfile) ? $form->emailreceiptincfile : 0;
				$emailreceiptsettings['emailrecipientincfilepath'] = (isset($form->emailrecipientincfilepath)) ? $form->emailrecipientincfilepath : 0;
				$emailreceiptsettings['emailreceiptinccreated'] = 1;
				$emailreceiptsettings['emailreceiptincformtitle'] = 1;
				if (is_array($emailreceiptsettings)) {
					$registry = new Registry;
					$registry->loadArray($emailreceiptsettings);
					$emailreceiptsettings = (string)$registry;
					$query = $db->getQuery(true);
					$query->update($db->quoteName('#__visforms'))
						->set($db->quoteName('emailreceiptsettings') . " = " . $db->quote($emailreceiptsettings))
						->where($db->quoteName('id') . " = " . $db->quote($form->id));
					$db->setQuery($query);
					try {
						$db->execute();
						$this->addVisformsLogEntry('Update successfull for form with id: ' . $form->id, \JLog::INFO);
					}
					catch (RuntimeException $e) {
						$this->addVisformsLogEntry('Problems with update for form with id: ' . $form->id . ', ' . $e->getMessage(), \JLog::ERROR);
					}
				}
				else {
					$this->addVisformsLogEntry('Frontendsettings have invalid type. Cannot update form with id: ' . $form->id, \JLog::ERROR);
				}
			}
			$this->addVisformsLogEntry("*** Try to drop fields from table #__visforms ***", \JLog::INFO);
			$columnsToDelete = array('emailreceiptincfield', 'emailreceiptincfile', 'emailrecipientincfilepath');
			$this->addVisformsLogEntry(count($columnsToDelete) . " fields to drop", \JLog::INFO);
			foreach ($columnsToDelete as $columnToDelete) {
				$queryStr = ("ALTER TABLE " . $db->quoteName('#__visforms') . "DROP COLUMN " . $db->quoteName($columnToDelete));
				$db->setQuery($queryStr);
				try {
					$db->execute();
					$this->addVisformsLogEntry("Field successfully dropped: " . $columnToDelete, \JLog::INFO);
				}
				catch (RuntimeException $e) {
					$this->addVisformsLogEntry("Problems dropping field: " . $columnToDelete . ', ' . $e->getMessage(), \JLog::ERROR);
				}
			}
		}
		$this->addVisformsLogEntry('*** Try to move params for frontend display into new param field frontendsettings ***', \JLog::INFO);
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(array('id', 'displayip', 'displaydetail', 'autopublish')))
			->from($db->quoteName('#__visforms'));
		$db->setQuery($query);
		try {
			$forms = $db->loadObjectList();
			$this->addVisformsLogEntry(count($forms) . " form recordsets to process", \JLog::INFO);
		}
		catch (RuntimeException $e) {
			$this->addVisformsLogEntry('Unable to get forms: ' . $e->getMessage(), \JLog::ERROR);
		}
		if (count($forms) > 0) {
			foreach ($forms as $form) {
				$frontendsettings = array();
                $frontendsettings['displayip'] = (isset($form->displayip)) ? $form->displayip : 0;
                $frontendsettings['displaydetail'] = (isset($form->displaydetail)) ? $form->displaydetail : 0;
                $frontendsettings['autopublish'] = (isset($form->autopublish)) ? $form->autopublish : 1;
				$frontendsettings['displayid'] = 0;
				if (is_array($frontendsettings)) {
					$registry = new Registry;
					$registry->loadArray($frontendsettings);
					$frontendsettings = (string)$registry;
					$query = $db->getQuery(true);
					$query->update($db->quoteName('#__visforms'))
						->set($db->quoteName('frontendsettings') . " = " . $db->quote($frontendsettings))
						->where($db->quoteName('id') . " = " . $db->quote($form->id));
					$db->setQuery($query);
					try {
						$db->execute();
						$this->addVisformsLogEntry('Update successfull for form with id: ' . $form->id, \JLog::INFO);
					}
					catch (RuntimeException $e) {
						$this->addVisformsLogEntry('Problems with update for form with id: ' . $form->id, \JLog::ERROR);
					}
				}
				else {
					$this->addVisformsLogEntry('Frontendsettings have invalid type. Cannot update form with id: ' . $form->id, \JLog::ERROR);
				}
			}
			$this->addVisformsLogEntry("*** Try to drop fields from table #__visforms ***", \JLog::INFO);
			$columnsToDelete = array('displayip', 'displaydetail', 'autopublish');
			$this->addVisformsLogEntry(count($columnsToDelete) . " fields to drop", \JLog::INFO);
			foreach ($columnsToDelete as $columnToDelete) {
				$queryStr = ("ALTER TABLE " . $db->quoteName('#__visforms') . "DROP COLUMN " . $db->quoteName($columnToDelete));
				$db->setQuery($queryStr);
				try {
					$db->execute();
					$this->addVisformsLogEntry("Field successfully dropped: " . $columnToDelete, \JLog::INFO);
				}
				catch (RuntimeException $e) {
					$this->addVisformsLogEntry("Problems dropping field: " . $columnToDelete . ', ' . $e->getMessage(), \JLog::ERROR);
				}
			}
		}
	}

	private function createDataTableSave3_2_0() {
		$this->addVisformsLogEntry("*** Try to create _save tables ***", \JLog::INFO);
		// get all form records from database
		$forms = $this->forms;
		if (!empty($forms)) {
			$db = JFactory::getDbo();
			$dbDriver = $db->getServerType();
			$fileName = JPATH_ROOT . '/administrator/components/com_visforms/sql/others/'.$dbDriver.'/savedatatable.sql';

			foreach ($forms as $form) {
				// create __save datatable if it doesn't exists
				try {
					$dataTable = "#__visforms_" . $form->id;
					$tn = "#__visforms_" . $form->id . "_save";
					$tableList = $this->getPrefixFreeDataTableList();
					// Create the _save data table if data table exists
					if (in_array($dataTable, $tableList) && !in_array($tn, $tableList) && is_file($fileName)) {
						// Create _save table
						$query = "create table if not exists ".$db->qn($tn);
						$query .= @file_get_contents($fileName);
						$db->setQuery($query);
						$db->execute();

						// Add existing Fields
						$query = $db->getQuery(true);
						$query->select('*')
							->from($db->qn('#__visfields'))
							->where($db->qn('fid') . ' = ' . $form->id);
						$db->setQuery($query);
						$fields = $db->loadObjectList();
						if (!empty($fields)) {
							foreach ($fields as $field) {
								$fieldname = "F" . $field->id;
								$query = "ALTER TABLE " . $tn . " ADD " . $fieldname . " TEXT";
								$db->SetQuery($query);
								$db->execute();
							}
						}
						$this->status->fixTableVisforms[] = array('form' => $form->id, 'result' => true, 'resulttext' => \JText::_('COM_VISFORMS_CREATION_OF_DATATABLE_SAVE_SUCCESSFUL'));
						$this->addVisformsLogEntry("_save table successfully create for form with id: " . $form->id, \JLog::INFO);
					}
				}
				catch (RuntimeException $e) {
					$this->status->fixTableVisforms[] = array('form' => $form->id, 'result' => false, 'resulttext' => \JText::_('COM_VISFORMS_CREATION_OF_DATATABLE_SAVE_FAILED') . ': '. $e->getMessage());
					$this->addVisformsLogEntry("Unable to create _save table for form with id: " . $form->id . ', ' . $e->getMessage(), \JLog::ERROR);
				}
			}
		}
	}

	private function updateDataTable3_2_0() {
		$this->addVisformsLogEntry("*** Try to update data tables ***", \JLog::INFO);
		$forms = $this->forms;
		if (!empty($forms)) {

			$tableList = $this->getPrefixFreeDataTableList();
			foreach ($forms as $form) {
				$dataTable = "#__visforms_" . $form->id;
				if (in_array($dataTable, $tableList)) {
					try {
						$this->addColumns(array('ismfd' => array('name' => 'ismfd', 'type' => 'TINYINT', 'length' => '4', 'notNull' => true, 'default' => '0'),
							array('name' => 'checked_out', 'type' => 'int', 'length' => '10', 'notNull' => true, 'default' => '0'),
							array('name' => 'checked_out_time', 'type' => 'datetime', 'notNull' => true, 'default' => '0000-00-00 00:00:00')
						),
							'visforms_' . $form->id);
					}
					catch (RuntimeException $e) {
						$this->status->messages[] = array('message' => \JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage()));
						$this->addVisformsLogEntry("Problems adding fields to table: " . $dataTable . ", " . $e->getMessage(), \JLog::ERROR);
					}
				}
			}
		}
	}

	private function updateDataTable3_6_0() {
		JLog::add("*** Try to update data tables ***", JLog::INFO);
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(array('id', 'saveresult')))
			->from($db->quoteName('#__visforms'));
		$db->setQuery($query);
		try {
			$forms = $db->loadAssocList();
		} catch (RuntimeException $e) {
			JLog::add('Unable to get forms: ' . $e->getMessage(), JLog::ERROR);
		}
		if (count($forms) > 0) {
			JLog::add(count($forms) . " form recordsets to process", JLog::INFO);
			foreach ($forms as $form) {
				try {
					$this->addColumns(array('created_by' => array('name' => 'created_by', 'type' => 'INT', 'length' => '11', 'notNull' => true, 'default' => '0')
					),
						'visforms_' . $form['id']);
				} catch (RuntimeException $e) {
					$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
					$this->status->messages[] = array('message' => $message);
					JLog::add("Problems adding fields to table: #__visforms, " . $form['id'] . " " . $e->getMessage(), JLog::ERROR);
				}
				try {
					$this->addColumns(array('created_by' => array('name' => 'created_by', 'type' => 'INT', 'length' => '11', 'notNull' => true, 'default' => '0')
					),
						'visforms_' . $form['id'] . '_save');
				} catch (RuntimeException $e) {
					$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
					$this->status->messages[] = array('message' => $message);
					JLog::add("Problems adding fields to table: #__visforms, " . $form['id'] . "_save " . $e->getMessage(), JLog::ERROR);
				}
			}
		}
	}

	private function getPlgvscParmas($plgParamsForm = array()) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('params'))
			->from('#__extensions')
			->where($db->quoteName('name') . " = " . $db->quote("plg_visforms_spambotcheck") . " AND " . $db->quoteName('folder') . " = " . $db->quote("visforms"));
		$db->setQuery($query);
		try {
			$params = json_decode($db->loadResult(), true);
		}
		catch (RuntimeException $e) {
			$this->addVisformsLogEntry("Cannot retrieve Plugin params, " . $e->getMessage(), \JLog::ERROR);
			return false;
		}
		if (!isset($params) || !is_array($params) || !(count($params) > 0)) {
			$this->addVisformsLogEntry("Cannot retrieve Plugin params", \JLog::ERROR);
			return false;
		}
		if ($params['spbot_projecthoneypot_api_key'] != "") {
			$plgParamsForm['spbot_projecthoneypot'] = "1";
		}

		return $newPlgParamsForm = array_merge($plgParamsForm, $params);
	}

	private function addColumns($columnsToAdd = array(), $table = "visforms") {
		if (count($columnsToAdd) > 0) {
			$this->addVisformsLogEntry("*** Try to add new fields to table: #__" . $table . " ***", \JLog::INFO);
			$this->addVisformsLogEntry(count($columnsToAdd) . " fields to add", \JLog::INFO);
			$db = \JFactory::getDbo();
			foreach ($columnsToAdd as $columnToAdd) {
				// we need at least a column name
				if (!(isset($columnToAdd['name'])) || ($columnToAdd['name'] == "")) {
					continue;
				}
				$queryStr = $db->getQuery(true);
				$queryStr = ("ALTER TABLE " . $db->quoteName('#__' . $table) . "ADD COLUMN " . $db->quoteName($columnToAdd['name']) .
					((isset($columnToAdd['type']) && ($columnToAdd['type'] != "")) ? " " . $columnToAdd['type'] : " text") .
					((isset($columnToAdd['length']) && ($columnToAdd['length'] != "")) ? "(" . $columnToAdd['length'] . ")" : "") .
					((isset($columnToAdd['attribute']) && ($columnToAdd['attribute'] != "")) ? " " . $columnToAdd['attribute'] : "") .
					((isset($columnToAdd['notNull']) && ($columnToAdd['notNull'] == true)) ? " not NULL" : "") .
					((isset($columnToAdd['default']) && ($columnToAdd['default'] !== "")) ? " DEFAULT " . $db->quote($columnToAdd['default']) : " DEFAULT ''"));
				try {
					$db->setQuery($queryStr);
					$db->execute();
					$this->addVisformsLogEntry("Field added: " . $columnToAdd['name'], \JLog::INFO);
				}
				catch (RuntimeException $e) {
					$this->addVisformsLogEntry("Unable to add field: " . $columnToAdd['name'] . ', ' . $e->getMessage(), \JLog::ERROR);
				}
			}
		}
	}

	private function dropColumns($columnsToDrop = array(), $table = "visforms") {
		$this->addVisformsLogEntry("*** Try to drop fields from table #__" . $table . " ***", \JLog::INFO);
		if (count($columnsToDrop) > 0) {
			$this->addVisformsLogEntry(count($columnsToDrop) . " fields to drop", \JLog::INFO);
			$db = \JFactory::getDbo();
			foreach ($columnsToDrop as $columnToDrop) {
				$queryStr = ("ALTER TABLE " . $db->quoteName('#__' . $table) . "DROP COLUMN " . $db->quoteName($columnToDrop));
				try {
					$db->setQuery($queryStr);
					$db->execute();
					$this->addVisformsLogEntry("Field successfully dropped: " . $columnToDrop, \JLog::INFO);
				}
				catch (RuntimeException $e) {
					$this->addVisformsLogEntry("Problems dropping field: " . $columnToDrop . ', ' . $e->getMessage(), \JLog::ERROR);
				}
			}
		}
		else {
			$this->addVisformsLogEntry('No fields to drop', \JLog::INFO);
		}
	}

	private function convertParamsToJsonField($paramFieldName, $oldFields = array(), $additionalValues= array(), $table = 'visforms') {
	    // get values from specified old db fields, merge with new additional values, convert to JSON string and store in paramFieldName in db
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$this->addVisformsLogEntry("*** Try to convert params in table: #__" . $table . "***", \JLog::INFO);
		if (count($oldFields) > 0) {
			$fields = array_merge(array('id'), array_keys($oldFields));
		}
		else {
			$fields = array('id');
		}
		$query
			->select($db->quoteName($fields))
			->from($db->quoteName('#__' . $table));
		$db->setQuery($query);
		try {
			$forms = $db->loadObjectList();
		}
		catch (RuntimeException $e) {
			$this->addVisformsLogEntry('Unable to get values from old fields: ' . $e->getMessage(), \JLog::ERROR);
		}
		if (count($forms) > 0) {
			$this->addVisformsLogEntry(count($forms) . " recordsets to process", \JLog::INFO);
			foreach ($forms as $form) {
				$paramArray = array();
				if (count($oldFields) > 0) {
					foreach ($oldFields as $oldFieldName => $oldFieldDefault) {
						if (isset($form->$oldFieldName)) {
							$paramArray[$oldFieldName] = $form->$oldFieldName;
						}
						else {
							$paramArray[$oldFieldName] = $oldFieldDefault;
						}
					}
				}
				if (count($additionalValues) > 0) {
					foreach ($additionalValues as $newFieldName => $newFieldDefault) {
						$paramArray[$newFieldName] = $newFieldDefault;
					}
				}
				if (is_array($paramArray)) {
					$registry = new Registry;
					$registry->loadArray($paramArray);
					$paramArray = (string)$registry;
					$query = $db->getQuery(true);
					$query->update($db->quoteName('#__' . $table))
						->set($db->quoteName($paramFieldName) . " = " . $db->quote($paramArray))
						->where($db->quoteName('id') . " = " . $db->quote($form->id));
					$db->setQuery($query);
					try {
						$db->execute();
						$this->addVisformsLogEntry("Modified params saved in record set with id: " . $form->id, \JLog::INFO);
					}
					catch (RuntimeException $e) {
						$this->status->fixTableVisforms[] = array('form' => $form->id, 'result' => false, 'resulttext' => JText::_('COM_VISFORMS_PARAMS_LOST'));
						$this->addVisformsLogEntry("Unable to save modified params in record set with id: " . $form->id . ', ' . $e->getMessage(), \JLog::ERROR);
					}
				}
				else {
					$this->addVisformsLogEntry('Params have invalid type. Cannot update record set with id: ' . $form->id, \JLog::ERROR);
				}
			}
		}
		else {
			$this->addVisformsLogEntry('No recordsets to process', \JLog::INFO);
		}
	}

	// Convert option list of radio buttons and selects from former custom format string to json in table visfields
	private function convertSelectRadioOptionList() {
		$this->addVisformsLogEntry("*** Try to convert option list string of radio buttons and selects to json in table: #__visfields ***", \JLog::INFO);
		// get all field records from database
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(array('id', 'typefield', 'defaultvalue')))
			->from($db->quoteName('#__visfields'))
			->where($db->quoteName('typefield') . " IN (" . $db->quote('select') . ", " . $db->quote('radio') . ")");
		$db->setQuery($query);
		try {
			$fields = $db->loadObjectList();
		}
		catch (RuntimeException $e) {
			$this->addVisformsLogEntry('Unable to get fields: ' . $e->getMessage(), \JLog::ERROR);
		}
		if (count($fields) > 0) {
			$this->addVisformsLogEntry(count($fields) . " field recordsets to process", \JLog::INFO);
			foreach ($fields as $field) {
			    if (empty($field->defaultvalue)) {
			        continue;
                }
				// convert defaultvalue to array
				$registry = new Registry;
				$registry->loadString($field->defaultvalue);
				$field->defaultvalue = $registry->toArray();
				$optionFieldName = "f_" . $field->typefield . "_list_hidden";
				// get old option string
				$oldOptions = $field->defaultvalue[$optionFieldName];
				$this->addVisformsLogEntry("Old option list value in field with id: " . $field->id . " is " . $oldOptions, \JLog::INFO);
				$newOptsString = '';
				// extract old options
				if ($oldOptions != "") {
					// index of newOptions has to start with 1 not with 0
					$i = 1;
					$newOptsString .= '{';
					$options = explode("[-]", $oldOptions);
					foreach ($options as $option) {
						$val = explode("==", $option);
						$key = explode("||", $val[1]);
						$ipos = strpos($key[1], ' [default]');
						// remove the [default]
						if ($ipos != false) {
							$key[1] = substr($key[1], 0, $ipos);
							$ipos = "1";
						}

						$newOptsString .= '"' . $i . '":{"listitemid":' . $i . ',"listitemvalue":"' . $key[0] . '","listitemlabel":"' . $key[1] . '"';

						// add listitemischecked if the option is set as default
						if ($ipos == "1") {
							$newOptsString .= ',"listitemischecked":"' . $ipos . '"';
						}
						$newOptsString .= "},";
						$i++;
					}
					$newOptsString = rtrim($newOptsString, ",") . '}';
				}
				if ($newOptsString != "") {
					$this->addVisformsLogEntry("New option list value in field with id: " . $field->id . " is " . $newOptsString, \JLog::INFO);
					$field->defaultvalue[$optionFieldName] = $newOptsString;
					$registry = new Registry();
					$registry->loadArray($field->defaultvalue);
					$newDefaultvalue = (string)$registry;
					$query = $db->getQuery(true);
					$query->update($db->quoteName('#__visfields'))
						->set($db->quoteName('defaultvalue') . " = " . $db->quote($newDefaultvalue))
						->where($db->quoteName('id') . " = " . $db->quote($field->id));
					$db->setQuery($query);
					try {
						$db->execute();
						$this->addVisformsLogEntry("Modified option list saved in field with id: " . $field->id, \JLog::INFO);
					}
					catch (RuntimeException $e) {
						$this->addVisformsLogEntry("Unable to save modified option list in field with id: " . $field->id . ', ' . $e->getMessage(), \JLog::ERROR);
					}
				}
			}
		}
	}

	private function enableExtension($extWhere) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__extensions'))
			->set($db->quoteName('enabled') . " = 1")
			->where($extWhere);
		try {
			$db->setQuery($query);
			$db->execute();
			$this->addVisformsLogEntry("Extension successfully enabled", \JLog::INFO);
		}
		catch (RuntimeException $e) {
			$this->addVisformsLogEntry("Unable to enable extension " . $e->getMessage(), \JLog::ERROR);
		}
	}

	private function addVisformsLogEntry($message, $code = \JLog::ERROR) {
		try {
			\JLog::add($message, $code, $this->loggerName);
		} 
		catch (RuntimeException $exception) {
			// prevent installation routine from failing due to problems with logger
		}
	}

	private function runQuery($sql) {
		$this->addVisformsLogEntry('Try to run sql query: ' . $sql, \JLog::INFO);
		$db = JFactory::getDbo();
		$query = $sql;
		try {
			$db->setQuery($query);
			$db->execute();
			return true;
		}
		catch (Exception $e) {
			$this->addVisformsLogEntry("Unable to run sql query: " . $e->getMessage(), \JLog::ERROR);
			return false;
		}
	}

	private function fixSepInStoredUserInputsFromMultiSelect() {
		$this->addVisformsLogEntry('Try to change separator in stored user inputs in fields of type select and multicheckbox with mulitselect', \JLog::INFO);
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn(array('fid', 'id', 'typefield', 'defaultvalue')))
			->from($db->qn('#__visfields'))
			->where($db->qn('typefield') . ' = ' . $db->q('select'), 'OR')
			->where($db->qn('typefield') . ' = ' . $db->q('multicheckbox'));
		$db->setQuery($query);
		try {
			$fields = $db->loadObjectList();
		}
		catch (RuntimeException $e) {
			$this->addVisformsLogEntry("Unable to get fields definition: " . $e->getMessage(), \JLog::ERROR);
			return false;
		}
		if (empty($fields)) {
			$this->addVisformsLogEntry("No fields to process", \JLog::INFO);
			return true;
		}
		$count = count($fields);
		$this->addVisformsLogEntry($count . " fields to process", \JLog::INFO);
		$tablelist = $db->getTableList();
		if (!empty($tablelist)) {
			$tablelist = array_map('strtolower', $tablelist);
		}
		foreach ($fields as $field) {
			$test = strtolower($db->getPrefix() . 'visforms_' . $field->fid);
			// only try to process fields if data table exists.
			if ((empty($tablelist)) || (!in_array($test, $tablelist))) {
				$this->addVisformsLogEntry("User inputs of form with id " . $field->fid . " are not stored in database. Nothing to do.", \JLog::INFO);
				continue;
			}
			if (!empty($field->defaultvalue)) {
				$registry = new Registry;
				$registry->loadString($field->defaultvalue);
				$field->defaultvalue = $registry->toArray();
				$key = 'f_' . $field->typefield;
				// only process fields which actually have a multi select option enabled
				// if an admins has changed this options results of conversion may be incorrect
				switch ($field->typefield) {
					case 'select':
						if (empty($field->defaultvalue[$key . '_attribute_multiple'])) {
							// skip this field
							$this->addVisformsLogEntry("Option multi select is not enabled in field with id " . $field->id . " Nothing to do.", \JLog::INFO);
							continue 2;
						}
						break;
					case 'multicheckbox':
						break;
					default:
						// should never happen but skip this field
						continue 2;
				}
				// extract array of allowed options from field definition
				$options = json_decode($field->defaultvalue[$key . '_list_hidden']);
				$returnopts = array();
				$hasOptionWithComma = false;
				if ((!empty($options)) && (is_object($options))) {
					foreach ($options as $option) {
						if ((!empty($option)) && (!empty($option->listitemvalue)) && (substr_count($option->listitemvalue, ','))) {
							$hasOptionWithComma = true;
						}
						$returnopts[] = $option->listitemvalue;
					}
				}
				// get stored user inputs from data table
				$datatablefieldkey = 'f' . $field->id;
				$query = $db->getQuery(true);
				$query->select($db->qn(array('id', $datatablefieldkey)))
					->from($db->qn('#__visforms_' . $field->fid));
				$db->setQuery($query);
				try {
					$storedValues = $db->loadObjectList();
				}
				catch (RuntimeException $e) {
					$this->addVisformsLogEntry("Unable to get stored user input for field with id " . $field->id . ":" . $e->getMessage(), \JLog::ERROR);
				}
				// no user inputs stored
				if (!empty($storedValues)) {
					foreach ($storedValues as $storedValue) {
						if (empty($storedValue->$datatablefieldkey)) {
							$this->addVisformsLogEntry("User inputs for recordset " . $storedValue->id . " for field with id " . $field->id . " are empty. Nothing to do.", \JLog::INFO);
							continue;
						}
						$fixedStoreValue = $this->replaceSeparator($storedValue->$datatablefieldkey, $returnopts, $storedValue->id, $field->id, $hasOptionWithComma);
						$fixedData = new stdClass();
						$fixedData->id = $storedValue->id;
						$fixedData->$datatablefieldkey = $fixedStoreValue;
						try {
							$db->updateObject('#__visforms_' . $field->fid, $fixedData, 'id');
							$this->addVisformsLogEntry("Fixed user inputs for recordset " . $storedValue->id . " for field with id " . $field->id . " stored. Original value was: " . $storedValue->$datatablefieldkey . " fixed value is: " . $fixedStoreValue, \JLog::INFO);
						}
						catch (RuntimeException $e) {
							$this->addVisformsLogEntry("Unable to store fixed value for recordset " . $storedValue->id . " for field with id " . $field->id . " in database. Original value was: " . $storedValue->$datatablefieldkey . " fixed value is: " . $fixedStoreValue . ": " . $e->getMessage(), \JLog::ERROR);
						}
					}
				}
				unset($storedValue);
				unset($storedValues);
				unset($fixedData);
				unset($fixedStoreValue);
				// fix data stored in "save" table
				if ((empty($tablelist)) || (!in_array(strtolower($db->getPrefix() . 'visforms_' . $field->fid . '_save'), $tablelist))) {
					continue;
				}
				$query = $db->getQuery(true);
				$query->select($db->qn(array('id', $datatablefieldkey)))
					->from($db->qn('#__visforms_' . $field->fid . '_save'));
				$db->setQuery($query);
				try {
					$storedValues = $db->loadObjectList();
				}
				catch (RuntimeException $e) {
					$this->addVisformsLogEntry("Unable to get stored user input for field with id " . $field->id . " from save table:" . $e->getMessage(), \JLog::ERROR);
				}
				if (!empty($storedValues)) {
					foreach ($storedValues as $storedValue) {
						if (empty($storedValue->$datatablefieldkey)) {
							$this->addVisformsLogEntry("User inputs for recordset " . $storedValue->id . " for field with id " . $field->id . " in save table are empty. Nothing to do.", \JLog::INFO);
							continue;
						}
						$fixedStoreValue = $this->replaceSeparator($storedValue->$datatablefieldkey, $returnopts, $storedValue->id, $field->id, $hasOptionWithComma);
						$fixedData = new stdClass();
						$fixedData->id = $storedValue->id;
						$fixedData->$datatablefieldkey = $fixedStoreValue;
						try {
							$db->updateObject('#__visforms_' . $field->fid . '_save', $fixedData, 'id');
							$this->addVisformsLogEntry("Fixed user inputs for recordset " . $storedValue->id . " for field with id " . $field->id . " stored in save table. Original value was: " . $storedValue->$datatablefieldkey . " fixed value is: " . $fixedStoreValue, \JLog::INFO);
						}
						catch (RuntimeException $e) {
							$this->addVisformsLogEntry("Unable to store fixed value in save database. Original value was: " . $storedValue->$datatablefieldkey . " fixed value is: " . $fixedStoreValue . ": " . $e->getMessage(), \JLog::ERROR);
						}
					}
				}
				unset($storedValue);
				unset($storedValues);
				unset($datatablefieldkey);
				unset($returnopts);
				unset($fixedData);
				unset($fixedStoreValue);
				unset($options);
				unset($key);
				unset($hasOptionWithComma);
			}
			unset($field);
		}
	}

	private function replaceSeparator($storedValue, $validOptions, $fieldid, $recordid, $hasOptionWithComma = false) {
		if (empty($hasOptionWithComma)) {
			$tmp = explode(",", $storedValue);
			foreach ($tmp as $index => $word) {
				$tmp[$index] = (string) trim($word);
			}
		}
		else {
			// start with the longest option value
			usort($validOptions, function ($a, $b) {
				if (strlen($a) == strlen($b)) {
					return 0;
				}

				return (strlen($a) > strlen($b)) ? -1 : 1;
			});
			// array with used valid options
			$tmp = array();
			foreach ($validOptions as $validOption) {
				// check if this value is part of the stored string, add it to the new array and remove it from stored string
				if ((!empty($storedValue)) && (strpos($storedValue . ',', $validOption . ',') !== false)) {
					$tmp[] = $validOption;
					$storedValue = str_replace($validOption . ',', '', $storedValue . ',');
				}
			}
			// stored user input contains parts which are no valid option, add these to fixed stored Value
			if (!empty($storedValue)) {
				$trimmed = rtrim($storedValue, ',');
				if (!empty($trimmed)) {
				    $trimmed = (string) trim($trimmed);
				}
				if (!empty($trimmed)) {
					$tmp[] = $trimmed;
				}
			}
			$addLogEntry = true;
		}
		$fixedStoreValue = implode("\0, ", $tmp);
		if (!empty($addLogEntry)) {
			$this->addVisformsLogEntry("Potential problem: Selected vaules in recordset " . $recordid . " for field with id " . $fieldid . " contains options with comma. Converting options with commas can cause invalid data. Old value:  " . $storedValue . ". Stored new values:" . $fixedStoreValue, \JLog::INFO);
		}

		return $fixedStoreValue;
	}

	private function convertTablesToUtf8mb4() {
	    // Joomla! will use character set utf8 as default, if utf8mb4 is not supported
        // if we have successfully converted to utf8md4, we set a flag in the database
		$db = \JFactory::getDbo();
		$serverType = $db->getServerType();
		if ($serverType != 'mysql') {
			return;
		}

		try {
			$db->setQuery('SELECT ' . $db->quoteName('converted')
				. ' FROM ' . $db->quoteName('#__visforms_utf8_conversion')
			);
			$convertedDB = $db->loadResult();
		}
		catch (Exception $e) {
			// Render the error message from the Exception object
			$this->addVisformsLogEntry("Unable to run sql query: " . $e->getMessage(), \JLog::ERROR);
			return;
		}

		if ($db->hasUTF8mb4Support()) {
			$converted = 2;
		}
		else {
			$converted = 1;
		}

		if ($convertedDB == $converted) {
			return;
		}
		$tablelist = $db->getTableList();
		foreach ($tablelist as $table) {
			if ((strpos($table, '_visforms') !== false) || (strpos($table, '_visfields') !== false) || (strpos($table, '_viscreator') !== false) || (strpos($table, '_vispdf') !== false)) {
				if (!$this->runQuery('ALTER TABLE ' . $table . ' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci')) {
				    $converted = 0;
                }
				if (!$this->runQuery('ALTER TABLE ' . $table . ' DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci')){
				    $converted = 0;
                }

			}
			if (strpos($table, '_visverificationcode') !== false) {
			    // table has a key on a varchar field. This may result in data loss on conversion.
                // Therefore we must drop the key, enlarge column and set the key later again.
                // Character set of key column is set to utf8mb4_bin not utf8mb4_unicode_ci
				if (!$this->runQuery('ALTER TABLE ' . $table . ' DROP KEY `idx_email`')) {
					$converted = 0;
				}
				if (!$this->runQuery('ALTER TABLE ' . $table . '  MODIFY `email` varchar(400) NOT NULL DEFAULT ""')) {
					$converted = 0;
				}
				if (!$this->runQuery('ALTER TABLE ' . $table . ' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci')) {
					$converted = 0;
				}
				if (!$this->runQuery('ALTER TABLE ' . $table . '  MODIFY `email` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT ""')) {
					$converted = 0;
				}
				if (!$this->runQuery('ALTER TABLE ' . $table . ' DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci')){
					$converted = 0;
				}
				if (!$this->runQuery('ALTER TABLE ' . $table . ' ADD KEY `idx_email` (`email`(100))')) {
					$converted = 0;
				}
			}
		}
        try {
	        $db->setQuery('UPDATE ' . $db->quoteName('#__visforms_utf8_conversion')
		        . ' SET ' . $db->quoteName('converted') . ' = ' . $converted . ';')->execute();
        }
        catch (Exception $e) {
	        $this->addVisformsLogEntry("Unable to run sql query: " . $e->getMessage(), \JLog::ERROR);
        }
    }

	private function convertTableEngine() {
	    $db = \JFactory::getDbo();
		$this->addVisformsLogEntry('Try to change storage engine', \JLog::INFO);
		$tablelist = $db->getTableList();
		foreach ($tablelist as $table) {
			if ((strpos($table, '_visforms') !== false) || (strpos($table, '_visfields') !== false)) {
				$this->runQuery('ALTER TABLE ' . $table . ' ENGINE=InnoDB');
			}
		}
	}

	function cmp($a, $b) {
		if (strlen($a) == strlen($b)) {
			return 0;
		}

		return (strlen($a) > strlen($b)) ? 1 : -1;
	}
	
	private function convertEditMailOptions() {
		$this->addVisformsLogEntry('*** Try to convert edit mail options ***', \JLog::INFO);
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn(array(
			'id', 'emailreceipt', 'emailreceiptsubject', 'emailreceiptfrom', 'emailreceiptfromname', 'emailreceipttext', 'emailreceiptsettings',
			'emailresult', 'emailfrom', 'emailfromname', 'emailto', 'emailcc', 'emailbcc', 'subject', 'emailresulttext', 'emailresultsettings'
		)))
			->from($db->qn('#__visforms'));
		$db->setQuery($query);
		try {
			$forms = $db->loadObjectList();
		}
		catch (RuntimeException $e) {
			$this->addVisformsLogEntry("Unable to get stored options: " . $e->getMessage(), \JLog::ERROR);
		}
		if (empty($forms)) {
			return;
		}
		foreach ($forms as $form) {
			$newform = new stdClass();
			$newform->id = $form->id;
			// Result Mail
			$editemailresultsettings = array(
				'editemailresult' => '0',
				'editemailfrom' => '',
				'editemailfromname' => '',
				'editemailto' => '',
				'editemailcc' => '',
				'editemailbcc' => '',
				'editsubject' => '',
				'editemailresulttext' => '',
				'editemailresultincfield' => '1',
				'editemailresulthideemptyfields' => '0',
				'editemailresultincdatarecordid' => '1',
				'editemailresultinccreated' => '1',
				'editemailresultincformtitle' => '1',
				'editemailresultincip' => '1',
				'editreceiptmailaslink' => '0',
				'editemailresultincfile' => '0',
				'editemailresultmodifiedonly' => '0'
			);
			if (!empty($emailresultsettings)) {
                $registry = new Registry;
                $registry->loadString($form->emailresultsettings);
                $emailresultsettings = $registry->toArray();
				if (isset($emailresultsettings['editemailresult'])) {
					$editemailresultsettings['editemailresult'] = $emailresultsettings['editemailresult'];
					// if edit mail is enabled copy values from emailreceiptsettings into new edit mail parameters else keep default settings
					if (!empty($editemailresultsettings['editemailresult'])) {
						$editemailresultsettings['editemailfrom'] = $form->emailfrom;
						$editemailresultsettings['editemailfromname'] = $form->emailfromname;
						$editemailresultsettings['editemailto'] = $form->emailto;
						$editemailresultsettings['editemailcc'] = $form->emailcc;
						$editemailresultsettings['editemailbcc'] = $form->emailbcc;
						$editemailresultsettings['editsubject'] = $form->subject;
						$editemailresultsettings['editemailresulttext'] = $form->emailresulttext;
						foreach ($emailresultsettings as $pname => $pvalue) {
							$key = 'edit' . $pname;
							if (array_key_exists($key, $editemailresultsettings)) {
								$editemailresultsettings[$key] = $pvalue;
							}
						}
					}
					unset($emailresultsettings['editemailresult']);
				}
				if (isset($emailresultsettings['editemailresultmodifiedonly'])) {
					$editemailresultsettings['editemailresultmodifiedonly'] = $emailresultsettings['editemailresultmodifiedonly'];
					unset($emailresultsettings['editemailresultmodifiedonly']);
				}
				$registry = new Registry;
				$registry->loadArray($emailresultsettings);
				$newform->emailresultsettings = (string)$registry;
			}
			$registry = new Registry;
			$registry->loadArray($editemailresultsettings);
			$newform->editemailresultsettings = (string)$registry;

			// Receipt Mail
			$editemailreceiptsettings = array(
				'editemailreceipt' => '0',
				'editemailreceiptsubject' => '',
				'editemailreceiptfrom' => '',
				'editemailreceiptfromname' => '',
				'editemailreceipttext' => '',
				'editemailreceiptincfield' => '0',
				'editemailreceipthideemptyfields' => '0',
				'editemailreceiptincdatarecordid' => '1',
				'editemailrecipientincfilepath' => '0',
				'editemailreceiptinccreated' => '1',
				'editemailreceiptincformtitle' => '1',
				'editemailreceiptincip' => '1',
				'editemailreceiptincfile' => '0',
				'editemailreceiptmodifiedonly' => '0'
			);
			if (!empty($emailreceiptsettings)) {
                $registry = new Registry;
                $registry->loadString($form->emailreceiptsettings);
                $emailreceiptsettings = $registry->toArray();
				if (isset($emailreceiptsettings['editemailreceipt'])) {
					$editemailreceiptsettings['editemailreceipt'] = $emailreceiptsettings['editemailreceipt'];
					// if edit mail is enabled copy values from emailreceiptsettings into new edit mail parameters else keep default settings
					if (!empty($emailreceiptsettings['editemailreceipt'])) {
						$editemailreceiptsettings['editemailreceiptsubject'] = $form->emailreceiptsubject;
						$editemailreceiptsettings['editemailreceiptfrom'] = $form->emailreceiptfrom;
						$editemailreceiptsettings['editemailreceiptfromname'] = $form->emailreceiptfromname;
						$editemailreceiptsettings['editemailreceipttext'] = $form->emailreceipttext;
						foreach ($emailreceiptsettings as $pname => $pvalue) {
							$key = 'edit' . $pname;
							if (array_key_exists($key, $editemailreceiptsettings)) {
								$editemailreceiptsettings[$key] = $pvalue;
							}
						}
					}
					unset($emailreceiptsettings['editemailreceipt']);
				}
				if (isset($emailreceiptsettings['editemailreceiptmodifiedonly'])) {
					$editemailreceiptsettings['editemailreceiptmodifiedonly'] = $emailreceiptsettings['editemailreceiptmodifiedonly'];
					unset($emailreceiptsettings['editemailreceiptmodifiedonly']);
				}
				$registry = new Registry;
				$registry->loadArray($emailreceiptsettings);
				$newform->emailreceiptsettings = (string)$registry;
			}
			$registry = new Registry;
			$registry->loadArray($editemailreceiptsettings);
			$newform->editemailreceiptsettings = (string)$registry;
            try {
	            $db->updateObject('#__visforms', $newform, 'id');
	            $this->addVisformsLogEntry('Edit mail options converted', \JLog::INFO);
            }
            catch (Exception $e) {
	            $this->addVisformsLogEntry('Problems convert edit mail options: ' . $e->getMessage(), \JLog::ERROR);
            }
		}
	}

	private function deleteUpdateSiteLinks() {
		$this->addVisformsLogEntry('*** Try to reduce number of update site links ***', \JLog::INFO);
		$success = true;
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($this->getComponentWhereStatement());
		try {
			$db->setQuery($query);
			$extension = $db->loadResult();
		} catch (RuntimeException $e) {
			$this->addVisformsLogEntry('Unable to get Visforms extension_id: ' . $e->getMessage(), \JLog::ERROR);
			return;
		}
		if (empty($extension)) {
		    return;
        }
        $updateSiteIds = $this->getUpdateSites($extension);
		if (empty($updateSiteIds)) {
		    return;
        }
		$in = implode(", ", $updateSiteIds);
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__update_sites_extensions'));
		$query->where($db->quoteName('update_site_id') . ' IN (' . $in . ')');
		try {
			$db->setQuery($query);
			$db->execute();
		} 
		catch (RuntimeException $e) {
			$this->addVisformsLogEntry('Problems deleting record sets in #__update_sites_extensions : ' . $e->getMessage(), \JLog::ERROR);
			return;
		}
        foreach ($updateSiteIds as $updateSiteId) {
	        $query = $db->getQuery(true);
	        $query->delete($db->quoteName('#__update_sites'));
	        $query->where($db->quoteName('update_site_id') . ' = ' . $updateSiteId);
	        try {
		        $db->setQuery($query);
		        $db->execute();
	        } 
			catch (RuntimeException $e) {
		        $this->addVisformsLogEntry('Problems deleting record sets in #__update_sites : ' . $e->getMessage(), \JLog::ERROR);
	        }
        }
    }

	private function getUpdateSites($extension) {
		$db = \JFactory::getDbo();
		$extendWheres = array(
			$db->quoteName('#__update_sites.location') . ' LIKE ' . $db->q('%visforms_3_2%'),
            $db->quoteName('#__update_sites.location') . ' LIKE ' . $db->q('%visforms_3_5%'),
			$db->quoteName('#__update_sites.location') . ' LIKE ' . $db->q('%visforms_3_6%'),
			$db->quoteName('#__update_sites.location') . ' LIKE ' . $db->q('%visforms_3_7%')
		);
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName('#__update_sites.update_site_id'))
			->from($db->quoteName('#__update_sites_extensions'))
            ->leftJoin('#__update_sites ON #__update_sites.update_site_id = #__update_sites_extensions.update_site_id')
			->where($db->quoteName('extension_id') . ' = ' . $db->quote($extension))
            ->extendWhere('AND', $extendWheres, 'OR');
		try {
			$db->setQuery($query);
			$update_site_ids = $db->loadColumn();
		} 
		catch (RuntimeException $e) {
			return false;
		}
		return $update_site_ids;
	}
	
	private function warnSubUpdateRequired ($route){
		$this->addVisformsLogEntry('Check if Subscription update is necessary', \JLog::INFO);
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('manifest_cache'))
			->from($db->qn('#__extensions'))
			->where($db->qn('element') . ' = ' . $db->q('pkg_vfsubscription'))
			->where($db->qn('type') . ' = ' . $db->q('package'));
		try {
			$db->setQuery($query);
			$manifest = json_decode($db->loadResult(), true);
			$version = $manifest['version'];
			if (!empty($version) && version_compare($version, $this->vfsubminversion, 'lt')) {
				$msg = '<br /><strong style="color: red;"><br />' . \JText::sprintf('COM_VISORMS_SUBSCRIPTION_UPDATE_REQUIRED', $this->vfsubminversion) . '</strong>';
				if (!empty($this->status->component)) {
				    $this->status->component['msg'] .= $msg;
                }
				else {
					$this->status->component = array('name' => 'visForms', 'type' => $route, 'msg' => $msg);
				}
			}
		}
		catch (Exception $e) {
			return false;
		}
		return false;
	}

	private function setLastCompatibleVersion($version) {
		$this->addVisformsLogEntry('Try to set last compatible version sequenz.', \JLog::INFO);
	    $db = \JFactory::getDbo();
	    try {
		    $db->setQuery('UPDATE ' . $db->quoteName('#__visforms_lowest_compat_version')
			    . ' SET ' . $db->quoteName('vfversion') . ' = ' . $db->q($version))->execute();
	    }
	    catch (Exception $e) {
		    $this->addVisformsLogEntry("Unable to set last compatible version seqeuenz from db: " . $e->getMessage(), \JLog::ERROR);
        }
    }

	private function getLastCompatibleVersion() {
		$this->addVisformsLogEntry('Try to get last compatible version sequenz', \JLog::INFO);
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('vfversion'))
                ->from($db->qn('#__visforms_lowest_compat_version'));
		try {
			$db->setQuery($query);
		    return $db->loadResult();
        }
		catch (Exception $e) {
			$this->addVisformsLogEntry("Unable to get last compatible version seqeuenz from db: " . $e->getMessage(), \JLog::ERROR);
            return false;
		}
	}

	private function warnUpdateToSubRequired($route) {
	    if (file_exists(JPATH_ROOT . '/administrator/manifests/packages/pkg_vfsubscription.xml')) {
	        return true;
        }
	    $extensions = array(
		    JPATH_ROOT . '/components/com_visforms/views/edit/view.html.php',
		    JPATH_ROOT . '/plugins/visforms/vfdelaydoubleregistration/vfdelaydoubleregistration.xml',
		    JPATH_ROOT . '/plugins/visforms/vfmaxsubmissions/vfmaxsubmissions.xml',
		    JPATH_ROOT . '/plugins/visforms/vfmailattachments/vfmailattachments.xml',
		    JPATH_ROOT . '/plugins/visforms/vfcustommailadr/vfcustommailadr.xml',
		    JPATH_ROOT . '/plugins/content/vfdataview/vfdataview.xml',
		    JPATH_ROOT . '/plugins/content/vfformview/vfformview.xml',
		    JPATH_ROOT . '/plugins/search/visformsdata/visformsdata.xml',
		    JPATH_ROOT . '/administrator/manifests/files/vfsearchbar.xml',
		    JPATH_ROOT . '/components/com_visforms/layouts/visforms/progress/default.php',
		    JPATH_ROOT . '/components/com_visforms/lib/field/calculation.php',
		    JPATH_ROOT . '/administrator/manifests/files/vfbt3layouts.xml'
        );
	    foreach ($extensions as $extension) {
	        if (file_exists($extension)) {
	            $lang = (\JFactory::getLanguage()->getTag() === 'de-DE') ? 'de' : 'en';
	            $infoLinksRoot = 'https://www.vi-solutions.de';
		        $eUpInfoLink  = '<a href="'.$infoLinksRoot . '/index.php?option=com_vislinkrouter&linktype=extupdatemoreinfo&lang='. $lang. '" target="_blank">'.\JText::_('COM_VISFORMS_UPDATE_EXTENSION_TO_SUB_LINK_TEXT') .'</a>';
		        $sUpInfoLink = '<a href="' . $infoLinksRoot . '/index.php?option=com_vislinkrouter&linktype=subupdatemoreinfo&lang='. $lang. '" target="_blank">'.\JText::_('COM_VISFORMS_UPDATE_OLD_SUB_TO_SUB_LINK_TEXT') .'</a>';
		        $msg = '<br /><strong style="color: red;">' . \JText::sprintf('COM_VISFORMS_UPDATE_TO_SUBSCRIPTION', $this->vfsubminversion, $eUpInfoLink, $sUpInfoLink) . '</strong>';
		        if (!empty($this->status->component)) {
			        $this->status->component['msg'] .= $msg;
		        }
		        else {
			        $this->status->component = array('name' => 'visForms', 'type' => $route, 'msg' => $msg);
		        }
		        return;
            }
        }
    }

    private function installPdfFonts() {
	    $this->addVisformsLogEntry('*** Try to install pdf fonts ***', \JLog::INFO);
	    $tmp_dest = JFactory::getConfig()->get('tmp_path');
	    if (@file_exists(JPath::clean(JPATH_ROOT . '/media/com_visforms/tcpdf/fonts/helvetica.php'))) {
	        return;
        }
	    $handle = @fopen("https://vi-solutions.de/index.php?option=com_vislinkrouter&linktype=pdffonts", "rb");
	    $contents = '';
	    while (!feof($handle)) {
		    $contents .= fread($handle, 8192);
	    }
	    fclose($handle);
	    file_put_contents($tmp_dest . '/fonts.zip', $contents);


	    $zip = new ZipArchive;
	    $res = $zip->open($tmp_dest . '/fonts.zip');
	    if ($res === true) {
		    $zip->extractTo(JPath::clean(JPATH_ROOT . '/media/com_visforms/tcpdf/'));
		    $zip->close();
		    $this->addVisformsLogEntry('*** Pdf fonts successfully installed ***', \JLog::INFO);
		    jimport('joomla.filesystem.file');
			try {
				JFile::delete($tmp_dest . '/fonts.zip');
				$this->addVisformsLogEntry("fonts.zip deleted", \JLog::INFO);
			}
			catch (RuntimeException $e) {
				$this->addVisformsLogEntry('Unable to delete fonts.zip: ' . $e->getMessage(), \JLog::INFO);
			}
	    } else {
		    $this->addVisformsLogEntry('*** Pdf fonts not successfully installed ***', \JLog::ERROR);
	    }
    }
}

?>