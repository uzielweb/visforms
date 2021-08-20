<?php

/**
 * @copyright	Copyright (C) 2010 vi-solutions. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * author		MysteryFCM, modified by Robert Kuster and Aicha Vack
 */

defined('_JEXEC') or die('Restricted access');

class plgVisformsSpambotCheckHelpers {

    public static function cleanEMailWhitelist($email_whitelist) {
        if ($email_whitelist != '') {
            $email_whitelist = str_replace(' ', '', $email_whitelist);
            while ($email_whitelist[strlen($email_whitelist) - 1] == ',') {
                $email_whitelist = substr($email_whitelist, 0, strlen($email_whitelist) - 1);
            }
        }

        return $email_whitelist;
    }

    public static function cleanEMailBlacklist($email_blacklist) {
        if ($email_blacklist != '') {
            $email_blacklist = str_replace(' ', '', $email_blacklist);
            while ($email_blacklist[strlen($email_blacklist) - 1] == ',') {
                $email_blacklist = substr($email_blacklist, 0, strlen($email_blacklist) - 1);
            }
        }

        return $email_blacklist;
    }

    public static function cleanUsername($sUsername) {
        if ($sUsername != '') {
            $sUsername = addslashes(htmlentities($sUsername));
            $sUsername = urlencode($sUsername);
            $sUsername = str_replace(" ", "%20", $sUsername); // no spaces		
        }

        return $sUsername;
    }

    public static function isCUrlAvailable() {
        $extension = 'curl';
        if (extension_loaded($extension)) {
            return true;
        } else {
            return false;
        }
    }

    public static function isURLOnline($sSiteToCheck) {
        // check, if curl is available
        if (self::isCUrlAvailable()) {
            // check if url is online
            $curl = @curl_init($sSiteToCheck);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_FAILONERROR, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            @curl_exec($curl);
            if (curl_errno($curl) != 0) {
                return false;
            } else {
                return true;
            }
            curl_close($curl);
        } else {
            //curl is not loaded, this won't work
            return false;
        }
    }

    public static function getURL($sURL) {
        if (self::isURLOnline($sURL) == false) {
            $sURLTemp = 'Unable to connect to server';
            return $sURLTemp;
        } else {
            if (function_exists('file_get_contents') && ini_get('allow_url_fopen') == true) {
                // Use file_get_contents
                $sURLTemp = @file_get_contents($sURL);
            } else {
                // Use cURL (if available)
                if (self::isCUrlAvailable()) {
                    $curl = @curl_init();
                    curl_setopt($curl, CURLOPT_URL, $sURL);
                    curl_setopt($curl, CURLOPT_VERBOSE, 1);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_HEADER, 0);
                    $sURLTemp = @curl_exec($curl);
                    curl_close($curl);
                } else {
                    $sURLTemp = 'Unable to connect to server';
                    return $sURLTemp;
                }
            }
            return $sURLTemp;
        }
    }

    public static function isvalidIP($ip) {
        if ($ip != '') {
            $regex = "'\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b'";
            return preg_match($regex, $ip) ? $ip : '';
        }

        return '';
    }

    public static function isvalidEmail($email) {
        if ($email != '') {
            $regex = '/^([a-zA-Z0-9_\.\-\+%])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/';
            return preg_match($regex, $email) ? $email : '';
        }

        return '';
    }

	public static function logSpammerToDB($sEmail, $sIP, $sUsername, $sEngine, $sRequest, $sRawReturn, $sParsedReturn, &$plgParams) {
        if (!$plgParams->get('spbot_log_to_db', 0)) {
            return false;
        }

        // Change empty vars to "NULL"
        if ($sEmail == '') {
            $sEmail = 'NULL';
        }
        if ($sIP == '') {
            $sIP = 'NULL';
        }
        if ($sUsername == '') {
            $sUsername = 'NULL';
        }

        $sEmail = str_replace(array("0x", ",", "%", "'", "\r\n", "\r", "\n"), "", $sEmail);
        $sIP = str_replace(array("0x", ",", "%", "'", "\r\n", "\r", "\n"), "", $sIP);
        // add DB record
        $sDate = gmdate("Y-m-d H:i:s", time());
        $spamAttempt = new StdClass();
        $spamAttempt->email = $sEmail;
        $spamAttempt->ip = $sIP;
        $spamAttempt->engine = $sEngine;
		$spamAttempt->request = $sRequest;
        $spamAttempt->raw_return = $sRawReturn;
        $spamAttempt->parsed_return = $sParsedReturn;
        $spamAttempt->attempt_date = $sDate;
        try {
            \JFactory::getDbo()->insertObject('#__visforms_spambot_attempts', $spamAttempt);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}

?>