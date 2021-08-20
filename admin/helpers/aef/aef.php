<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6
 *
 */

defined('_JEXEC') or die;
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class VisformsAEF
{
	// Subscription expects $allowfrontenddataedit
    public static $allowfrontenddataedit = 0;
	public static $allowFrontEndDataEdit = 0;
    public static $delayDoubleRegistrationExists = 1;
    public static $maxSubmissions = 2;
    public static $mailAttachments = 3;
    public static $vfCustomMailAddress = 4;
    public static $vfDataView = 5;
    public static $vfFormView = 6;
    public static $searchVisFormsData = 7;
    public static $searchBar = 8;
    public static $multiPageForms = 9;
    public static $customFieldTypeCalculation = 10;
    public static $bootStrap3Layouts = 11;
    public static $subscription = 12;
    public static $subFiles = 13;
    public static $pdf = 14;
    public static $customFieldTypeLocation = 15;
	public static $customFieldTypeSignature = 16;
	public static $bootStrap4Layouts = 17;
	public static $uikit3Layouts = 18;
	public static $uikit2Layouts = 19;
	public static $doubleOptIn = 20;

    public static function checkAEF($feature) {
        switch ($feature) {
            case self::$allowFrontEndDataEdit :
                return ((self::featureExists(JPATH_ROOT . '/components/com_visforms/views/edit/view.html.php'))
                    || (self::featureExists(JPATH_ROOT . '/administrator/manifests/packages/pkg_vfsubscription.xml')));
            case self::$delayDoubleRegistrationExists :
                return self::featureExists(JPATH_ROOT . '/plugins/visforms/vfdelaydoubleregistration/vfdelaydoubleregistration.xml');
            case self::$maxSubmissions :
                return self::featureExists(JPATH_ROOT . '/plugins/visforms/vfmaxsubmissions/vfmaxsubmissions.xml');
            case self::$mailAttachments :
                return self::featureExists(JPATH_ROOT . '/plugins/visforms/vfmailattachments/vfmailattachments.xml');
            case self::$vfCustomMailAddress :
                return self::featureExists(JPATH_ROOT . '/plugins/visforms/vfcustommailadr/vfcustommailadr.xml');
            case self::$vfDataView :
                return self::featureExists(JPATH_ROOT . '/plugins/content/vfdataview/vfdataview.xml');
            case self::$vfFormView :
                return self::featureExists(JPATH_ROOT . '/plugins/content/vfformview/vfformview.xml');
            case self::$searchVisFormsData :
                return self::featureExists(JPATH_ROOT . '/plugins/search/visformsdata/visformsdata.xml');
            case self::$searchBar :
                return ((self::featureExists(JPATH_ROOT . '/administrator/manifests/files/vfsearchbar.xml'))
                    || (self::featureExists(JPATH_ROOT . '/administrator/manifests/packages/pkg_vfsubscription.xml')));
            case self::$multiPageForms :
                return ((self::featureExists(JPATH_ROOT . '/components/com_visforms/layouts/visforms/progress/default.php'))
                    || (self::featureExists(JPATH_ROOT . '/administrator/manifests/packages/pkg_vfsubscription.xml')));
            case self::$customFieldTypeCalculation :
                return ((self::featureExists(JPATH_ROOT . '/components/com_visforms/lib/field/calculation.php'))
                    && ((self::featureExists(JPATH_ROOT . '/administrator/manifests/files/vfcustomfieldtypes.xml')
                        || (self::featureExists(JPATH_ROOT . '/administrator/manifests/packages/pkg_vfsubscription.xml')))));
            case self::$bootStrap3Layouts :
                return ((self::featureExists(JPATH_ROOT . '/administrator/manifests/files/vfbt3layouts.xml'))
                    || (self::featureExists(JPATH_ROOT . '/administrator/manifests/packages/pkg_vfsubscription.xml')));
            case self::$subscription :
                return (self::featureExists(JPATH_ROOT . '/administrator/manifests/packages/pkg_vfsubscription.xml'));
            case self::$subFiles :
                return (self::featureExists(JPATH_ROOT . '/administrator/manifests/files/vfsubsfiles.xml'));
            case self::$pdf :
                return (self::featureExists(JPATH_ROOT . '/administrator/components/com_visforms/views/vispdf/view.html.php'));
	        case self::$customFieldTypeLocation :
	        	return self::featureExists(JPATH_ROOT . '/components/com_visforms/lib/field/location.php');
	        case self::$customFieldTypeSignature :
		        return self::featureExists(JPATH_ROOT . '/components/com_visforms/lib/field/signature.php');
	        case self::$bootStrap4Layouts :
		        return (self::featureExists(JPATH_ROOT . '/components/com_visforms/lib/html/layout/bt4mcindividual.php'));
	        case self::$uikit3Layouts :
		        return (self::featureExists(JPATH_ROOT . '/components/com_visforms/lib/html/layout/uikit3.php'));
	        case self::$uikit2Layouts :
		        return (self::featureExists(JPATH_ROOT . '/components/com_visforms/lib/html/layout/uikit2.php'));
	        case self::$doubleOptIn :
		        return self::featureExists(JPATH_ROOT . '/plugins/visforms/vfdoubleoptin/vfdoubleoptin.xml');
            default:
                break;
        }
    }

    public static function checkForOneAef($excludeSubscription = true) {
        $exist = false;
        $vars = get_class_vars('VisformsAEF');
        foreach ($vars as $name => $var) {
            // only check static properties
            if (isset(self::$$name)) {
                if ($excludeSubscription && self::$$name == 12) {
                    continue;
                }
                if (self::checkAEF(self::$$name)) {
                    $exist = true;
                    break;
                }
            }
        }
        return $exist;
    }

    // Check for all aef feature
    public static function checkForAllOldSubFeature() {
        $exist = true;
        $vars = get_class_vars('VisformsAEF');
        foreach ($vars as $name => $var) {
            if ($var > 11) {
            	continue;
            }
            // only check static properties
            if (!(isset(self::$$name)) || (!(self::checkAEF(self::$$name)))) {
                $exist = false;
                break;
            }
        }
        return $exist;
    }

    protected static function featureExists($file) {
        if (!(JFile::exists(JPath::clean($file)))) {
            return false;
        }
        else {
            return true;
        }
    }

    public static function getAefList() {
        $list = array();
        $class = new ReflectionClass('VisformsAEF');
        $aefs = $class->getStaticProperties();
        if ((empty($aefs)) || (!is_array($aefs))) {
            return $list;
        }
        foreach ($aefs as $aef) {
            $list[$aef] = self::checkAEF($aef);
        }
        return $list;
    }

    public static function getVersion($feature) {
        switch ($feature) {
            // todo: test and enable, commented features are not used yet
            case self::$allowFrontEndDataEdit :
                if (self::checkAEF(static::$subscription)) {
                    return '1.5.4';
                }
                else {
                    return self::extractVersionFromXMLFile(JPATH_ROOT . '/administrator/manifests/files/vffrontedit.xml');
                }
            case self::$delayDoubleRegistrationExists :
                return self::extractVersionFromXMLFile(JPATH_ROOT . '/plugins/visforms/vfdelaydoubleregistration/vfdelaydoubleregistration.xml');
            case self::$maxSubmissions :
                return self::extractVersionFromXMLFile(JPATH_ROOT . '/plugins/visforms/vfmaxsubmissions/vfmaxsubmissions.xml');
            case self::$mailAttachments :
                return self::extractVersionFromXMLFile(JPATH_ROOT . '/plugins/visforms/vfmailattachments/vfmailattachments.xml');
            case self::$vfCustomMailAddress :
                return self::extractVersionFromXMLFile(JPATH_ROOT . '/plugins/visforms/vfcustommailadr/vfcustommailadr.xml');
            case self::$vfDataView :
                return self::extractVersionFromXMLFile(JPATH_ROOT . '/plugins/content/vfdataview/vfdataview.xml');
            case self::$vfFormView :
                return self::extractVersionFromXMLFile(JPATH_ROOT . '/plugins/content/vfformview/vfformview.xml');
            case self::$searchVisFormsData :
                return self::extractVersionFromXMLFile(JPATH_ROOT . '/plugins/search/visformsdata/visformsdata.xml');
            case self::$searchBar :
                if (self::checkAEF(static::$subscription)) {
                    return '1.0.0';
                }
                else {
                    return self::extractVersionFromXMLFile(JPATH_ROOT . '/administrator/manifests/files/vfsearchbar.xml');
                }
            case self::$multiPageForms :
                if (self::checkAEF(static::$subscription)) {
                    return '1.0.2';
                }
                else {
                    return self::extractVersionFromXMLFile(JPATH_ROOT . '/administrator/manifests/files/vfmultipageforms.xml');
                }
            case self::$customFieldTypeCalculation :
                return self::extractVersionFromXMLFile(JPATH_ROOT . '/administrator/manifests/files/vfcustomfieldtypes.xml');
            case self::$bootStrap3Layouts :
                if (self::checkAEF(static::$subscription)) {
                    return '1.0.3';
                }
                else {
                    return self::extractVersionFromXMLFile(JPATH_ROOT . '/administrator/manifests/files/vfbt3layouts.xml');
                }
            case self::$subscription :
                return self::extractVersionFromXMLFile(JPATH_ROOT . '/administrator/manifests/packages/pkg_vfsubscription.xml');
            case self::$subFiles :
                return self::extractVersionFromXMLFile(JPATH_ROOT . '/administrator/manifests/files/vfsubsfiles.xml');
            default:
                return false;
        }
    }

    protected static function extractVersionFromXMLFile($file)
    {
        if (!(JFile::exists(JPath::clean($file)))) {
            return false;
        }
        else {
            // suppress warnings
            libxml_use_internal_errors(true);
            $xml = simplexml_load_file($file);
            if ($xml === false) {
                return false;
            }
            return $xml->version;
        }
    }
}
