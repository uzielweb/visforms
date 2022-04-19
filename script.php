<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_visforms
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Version;

class pkg_vfbaseInstallerScript
{
	private $release;
	private $oldRelease;
	private $minimum_joomla_release;
	private $maximum_joomla_release = 3;
	private $min_visforms_version;
	private $name;
	private $loggerName;

	public function preflight($route,  $adapter) {
		$this->loggerName = $adapter->getManifest()->loggerName;
		$options['format']    = "{CODE}\t{MESSAGE}";
		$options['text_entry_format'] = "{PRIORITY}\t{MESSAGE}";
		$options['text_file'] = 'visforms_update.php';
		try {
			\JLog::addLogger($options, \JLog::ALL, array($this->loggerName, 'jerror'));
		}
		catch (RuntimeException $e) {
		}
		$this->release = $adapter->getManifest()->version;
		$this->minimum_joomla_release = $adapter->getManifest()->attributes()->version;
		$this->oldRelease = "";
		$this->min_visforms_version = $adapter->getManifest()->vfminversion;
		$this->name = $adapter->getManifest()->name;
		$jversion = new Version;
		$date = new JDate('now');
		$app = \JFactory::getApplication();
		$this->addVisformsLogEntry('*** Start ' . $route . ' of extension ' . $this->name . ' ' . $this->release . ': ' . $date . ' ***', \JLog::INFO);
		if ($route != 'uninstall') {
			// abort if the current Joomla release is too old or too new
			if (version_compare($jversion->getShortVersion(), $this->minimum_joomla_release, 'lt')) {
			    $msg = \JText::sprintf('PKG_VFBASE_WRONG_JOOMLA_VERSION', $this->name, $this->minimum_joomla_release);
				$app->enqueueMessage($msg, 'ERROR');
				$this->addVisformsLogEntry($msg, \JLog::ERROR);
				return false;
			}
			if (!defined( 'Joomla\CMS\Version::MAJOR_VERSION') ||(defined( 'Joomla\CMS\Version::MAJOR_VERSION') && $jversion::MAJOR_VERSION > $this->maximum_joomla_release)) {
			    $msg = \JText::sprintf('PKG_VFBASE_WRONG_MAX_JOOMLA_VERSION', $this->name, $this->maximum_joomla_release);
				$app->enqueueMessage($msg, 'ERROR');
				$this->addVisformsLogEntry($msg, \JLog::ERROR);
				return false;
			}
		}
		// skip if the component being installed is older than the currently installed version
		if ($route == 'update') {
			$this->oldRelease = $this->getParam('version', $this->name);
			if (version_compare($this->release, $this->oldRelease, 'lt')) {
				return true;
			}
			$this->addVisformsLogEntry('Try to update from version ' . $this->oldRelease . ' to ' . $this->release, \JLog::INFO);
		}
	}

	public function postflight($route,  $adapter) {
        $manifest = $adapter->getParent()->manifest;
        $packages = $manifest->xpath('files/file');
		if ($route == 'install') {
			if (!empty($packages)) {
			    $this->deleteUpdateSites($packages);
            }
		}
		if ($route == 'update') {
            if (!empty($packages)) {
                $this->deleteUpdateSites($packages);
            }
        }
		if ($route !== 'uninstall') {
		    $this->enableExtension('plg_editors-xtd_visformfields', 'plugin', 'visformfields', 'editors-xtd');
            $this->enableExtension('plg_visforms_visforms', 'plugin', 'visforms', 'visforms');
            $this->enableExtension('plg_visforms_spambotcheck', 'plugin', 'spambotcheck', 'visforms');
		}
		echo '<h2>' . (($route == 'update') ? \JText::_('PKG_VFBASE_PACKAGE_UPDATE_STATE') : \JText::_('PKG_VFBASE_PACKAGE_INSTALLATION_STATUS')) . '</h2>';
		$this->addVisformsLogEntry($route . ' of ' . $this->name . ' successfull', \JLog::INFO);
	}

	public function uninstall($adapter) {
		$manifestFile = JPATH_MANIFESTS . '/packages/pkg_vfbase.xml';
		if (!file_exists($manifestFile)) {
		    return;
        }
		$xml = simplexml_load_file($manifestFile);
		if (!$xml) {
		    return;
        }
		$release = $xml->version;
		if (empty($release)) {
		    return;
        }
		$language = JFactory::getLanguage();
		$language->load('pkg_vfbase', JPATH_ROOT  , 'en-GB', true);
		$language->load('pkg_vfbase', JPATH_ROOT  , null, true);
        echo '<h2>' .  \JText::_('PKG_VFBASE_PACKAGE_REMOVAL_SUCESSFUL') . '</h2>'; ?><?php
	}

	private function deleteUpdateSites($packages) {
        $db = JFactory::getDbo();
        // remove upload site information for all extensions from database
        foreach ($packages as $package) {
            $type = (string) $package->attributes()->type;
            $name = (string) $package->attributes()->id;
            $group = (!empty($package->attributes()->group)) ? (string) $package->attributes()->group : '';
            $id = $this->getExtensionId($type, $name, $group, 0);
            if (!empty($id)) {
                $update_site_ids = $this->getUpdateSites($id);
                if (!empty($update_site_ids)) {
                    $update_sites_ids_a = implode(',', $update_site_ids);
                    $query = $db->getQuery(true);
                    $query->delete($db->quoteName('#__update_sites'));
                    $query->where($db->quoteName('update_site_id') . ' IN (' . $update_sites_ids_a . ')');
                    try {
                        $db->setQuery($query);
                        $db->execute();
                    }
                    catch (RuntimeException $e) {
                        $this->addVisformsLogEntry("Problems deleting record sets in #__update_sites : " . $e->getMessage(), \JLog::INFO);
                    }
                    $query = $db->getQuery(true);
                    $query->delete($db->quoteName('#__update_sites_extensions'));
                    $query->where($db->quoteName('extension_id') . ' = ' . $id);
                    try {
                        $db->setQuery($query);
                        $db->execute();
                    }
                    catch (RuntimeException $e) {
                        $this->addVisformsLogEntry("Problems deleting record sets in #__update_sites_extensions : " . $e->getMessage(), \JLog::INFO);
                    }
                }
            }
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

	// get a variable from the manifest cache in database
	private function getParam($pname, $name) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('manifest_cache'))
			->from($db->qn('#__extensions'))
			->where($db->qn('name') . ' = ' . $db->q($name));
		try {
			$db->setQuery($query);
			$manifest = json_decode($db->loadResult(), true);
			return $manifest[$pname];
		}
		catch (Exception $e) {
			$this->addVisformsLogEntry('Unable to get ' . $name . ' ' . $pname . ' from manifest cache in databese, ' . $e->getMessage(), \JLog::ERROR);
			return false;
		}
	}

	private function getExtensionId($type, $name, $group = '', $client_id = 0) {
		$db = \JFactory::getDbo();
		$where = $db->quoteName('type') . ' = ' . $db->quote($type) . ' AND ' . $db->quoteName('element') . ' = ' . $db->quote($name);
		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($where);
		try {
			$db->setQuery($query);
			$id = $db->loadResult();
		}
		catch (RuntimeException $e) {
			$this->addVisformsLogEntry('Unable to get extension_id: ' . $name . ', ' . $e->getMessage(), \JLog::INFO);
			return false;
		}
		return $id;
	}

	private function getUpdateSites($extension) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('update_site_id'))
			->from($db->quoteName('#__update_sites_extensions'))
			->where($db->quoteName('extension_id') . ' = ' . $extension);
		try {
			$db->setQuery($query);
			$update_site_ids = $db->loadColumn();
		}
		catch (RuntimeException $e) {
			$this->addVisformsLogEntry('Unable to get update sites id: ' . $extension . ', ' . $e->getMessage(), \JLog::INFO);
			return false;
		}
		return $update_site_ids;
	}
	
	private function registryArrayFromString($settings = '') {
		if (empty($settings)) {
			return array();
		}
		try {
			$registry = new JRegistry;
			$registry->loadString($settings);
			return $registry->toArray();
		}
		catch (RuntimeException $e) {
			return array();
		}
    }
	
	private function registryStringFromArray($values = array()) {
		if (is_string($values)) {
			return $values;
		}
	    try {
		    $registry = new JRegistry;
		    $registry->loadArray($values);
		    return (string) $registry;
	    }
		catch (RuntimeException $e) {
		    return '';
	    }
    }

    protected function deleteOldFiles($filesToDelete, $foldersToDelete) {
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

	private function enableExtension($name, $type, $element, $folder = '') {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__extensions'))
			->set($db->quoteName('enabled') . " = 1")
			->where($db->quoteName('name') . ' = ' . $db->quote($name))
			->where($db->quoteName('type') . ' = ' . $db->quote($type))
			->where($db->quoteName('element') . ' = ' . $db->quote($element));
		if (!empty($folder)) {
			$query->where($db->quoteName('folder') . ' = ' . $db->quote($folder));
		}
		try {
			$db->setQuery($query);
			$db->execute();
			$this->addVisformsLogEntry("Extension successfully enabled", \JLog::INFO);
		}
		catch (RuntimeException $e) {
			$this->addVisformsLogEntry("Unable to enable extension " . $e->getMessage(), \JLog::ERROR);
		}
	}
}
?>