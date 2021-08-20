<?php

/**
 * @version		$Id: script.php 22354 2011-11-07 05:01:16Z github_bot $
 * @package		com_visforms
 * @subpackage	plg_visforms_spamcheck
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

class plgvisformsspambotcheckInstallerScript
{
	private $release;
	private $oldRelease;
	private $name;
	private $loggerName;

    public function preflight($route, $adapter) {
	    $this->loggerName = $adapter->getManifest()->loggerName;
        $options['format'] = "{CODE}\t{MESSAGE}";
        $options['text_entry_format'] = "{PRIORITY}\t{MESSAGE}";
        $options['text_file'] = 'visforms_update.php';
        try {
	        \JLog::addLogger($options, \JLog::ALL, array($this->loggerName, 'jerror'));
        }
        catch (RuntimeException $e) {
        }

        $this->release = $adapter->getManifest()->version;
	    $this->oldRelease = "";
		$this->name = $adapter->getManifest()->name;
        $date = new JDate('now');

		$this->addVisformsLogEntry('*** Start ' . $route . ' of extension ' . $this->name . ' ' . $this->release . ': ' . $date . ' ***', \JLog::INFO);
        // must get old Release in preflight!
        if ( $route == 'update' ) {
	        $this->oldRelease = $this->getParam('version', $this->name);
			$this->addVisformsLogEntry('Try to update from version ' . $this->oldRelease . ' to ' . $this->release, \JLog::INFO);
        }
    }

    public function install($adapter) {
		// Give a warning if cURL is not enabled on system; plugin will not be able to identify spammer
		$extension = 'curl';
		if (!extension_loaded($extension)) {
			$this->addVisformsLogEntry(\JText::_('PLG_VISFORMS_SPAMBOTCHECK_CURL_MISSING'), \JLog::WARNING);
		}
	}

    public function update($adapter) {
		if (version_compare($this->oldRelease, '3.1.0', 'lt')) {
			$this->addVisformsLogEntry('*** Try to remove params of Plugin Visforms Spambotcheck ***', \JLog::INFO);
            // remove params
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$fields = array(
				$db->qn('params')  . ' = ' . $db->q('{}')
			);
			$conditions = array(
				$db->qn('name') . ' = ' . $db->q('plg_visforms_spambotcheck'),
				$db->qn('folder') . ' = ' . $db->q('visforms')
			);
            $query->update($db->qn('#__extensions'))
				->set($fields)
				->where($conditions);
            try {
	            $db->setQuery($query);
                $db->execute();
	            $this->addVisformsLogEntry('Params removed', \JLog::INFO);
            }
            catch (Exception $e) {
	            $this->addVisformsLogEntry('Unable to remove params: ' . $e->getMessage(), \JLog::ERROR);
            }
		}
	}
	
	public function postflight($route, $adapter) {
		$this->addVisformsLogEntry($route . ' successfull', \JLog::INFO);
	}

	private function addVisformsLogEntry($message, $code = \JLog::ERROR) {
		try {
			\JLog::add($message, $code,$this->loggerName);
		}
		catch (RuntimeException $exception) {
			// prevent installation routine from failing due to problems with logger
		}
	}

	// get a variable from the manifest cache in database
	private function getParam( $pname, $name ) {
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
}

?>
