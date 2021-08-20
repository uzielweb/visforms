<?php

/**
 * @version		$Id: script.php 22354 2011-11-07 05:01:16Z github_bot $
 * @package		com_visforms
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );
jimport('joomla.log.log');

class JLogLoggerFormattedtextvisforms extends JLogLoggerFormattedtext
{
	/**
	 * @var array Translation array for JLogEntry priorities to SysLog priority names.
	 * @since 11.1
	 */
	protected $priorities = array(
		JLog::EMERGENCY => 'EMG',
		JLog::ALERT => 'ALT',
		JLog::CRITICAL => 'CRI',
		JLog::ERROR => 'ERR',
		JLog::WARNING => 'WRN',
		JLog::NOTICE => 'NTC',
		JLog::INFO => 'INF',
		JLog::DEBUG => 'DBG');
}
?>