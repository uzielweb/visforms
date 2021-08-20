<?php
/**
 * JHTMLHelper for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */
defined('_JEXEC') or die( 'Direct Access to this location is not allowed.' );
use Joomla\String\StringHelper;
require_once JPATH_ROOT . '/administrator/components/com_visforms/lib/visformsSql.php';

/**
 * Utility class for creating HTML Grids
 *
 * @static
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @since   1.5.5
 */
class JHtmlVisformsselect
{
	protected static $loaded = array();
    public static $nullbyte = "\0";
    public static $msdbseparator = "\0, ";
    
    /**
     * Explode database value of stored user input of fields with type select or multicheckbox
     * @param string $dbvalue: multiple values of multiselect or multichechbox are separated by "\0, "
     * @return array
     * @since  Visform 3.7.0
     */
    public static function explodeMsDbValue ($dbvalue)
    {
        $values = explode(self::$msdbseparator , $dbvalue);
        foreach ( $values as $index => $word) {
             $values[$index] = (string) trim($word);
        }
        return $values;
    }
    
    //remove Nullbit from string
    public static function removeNullbyte($value)
    {
        if ((!empty($value)) && is_string($value)) {
            $value = str_replace(self::$nullbyte, "", $value);
        }
        return $value;
    }

    public static function extractHiddenList ($optionString = '')
    {
        $options = array();
        $returnopts = array();
        if ($optionString != "") {
            $options = json_decode($optionString);
            foreach ($options as $option) {
                if (!empty($option->listitemvalue)) {
                    $option->listitemvalue = (string) trim($option->listitemvalue);
                }
                if (isset($option->listitemischecked) && ($option->listitemischecked == "1")) {
                    $selected = true;
                }
                else {
                    $selected = false;
                }
                $option->listitemredirecturl = (isset($option->listitemredirecturl)) ? StringHelper::trim($option->listitemredirecturl) : '';
	            $option->listitemmail = (isset($option->listitemmail)) ? StringHelper::trim($option->listitemmail) : '';
	            $option->listitemmailcc = (isset($option->listitemmailcc)) ? StringHelper::trim($option->listitemmailcc) : '';
	            $option->listitemmailbcc = (isset($option->listitemmailbcc)) ? StringHelper::trim($option->listitemmailbcc) : '';
	            $option->listiteminputclass = (isset($option->listiteminputclass)) ? $option->listiteminputclass : '';
	            $option->listitemlabelclass = (isset($option->listitemlabelclass)) ? $option->listitemlabelclass : '';

                $returnopts[] = array( 'id' => $option->listitemid, 'value' => $option->listitemvalue, 'label' => $option->listitemlabel, 'selected' => $selected, 'redirecturl' => $option->listitemredirecturl, 'mail' => $option->listitemmail, 'mailcc' => $option->listitemmailcc, 'mailbcc' => $option->listitemmailbcc, 'inputclass' =>  $option->listiteminputclass, 'labelclass' =>  $option->listitemlabelclass);
            }
        }       
        return $returnopts;
    }
    
    public static function mapDbValueToOptionLabel ($dbValue, $fieldHiddenList)
    {
        $newextracteditemfieldvalues = array();
        $fieldoptions = JHtmlVisformsselect::extractHiddenList($fieldHiddenList);                   
        if (empty($fieldoptions)) {
            return false;
        }
        $extracteditemvalues = JHtmlVisformsselect::explodeMsDbValue($dbValue);
        $newextracteditemfieldvalues = array();
        foreach ($fieldoptions as $fieldoption) {
            foreach ($extracteditemvalues as $extracteditemvalue) {
                if ($extracteditemvalue == $fieldoption['value']) {
                    $newextracteditemfieldvalues[] = $fieldoption['label'];
                }                      
            }
        }
        return $newextracteditemfieldvalues;
    }

    public static function getOptionsFromSQL($sql, $inputContext = '') {
	    $returnopts = array();
	    $i = 1;
	    try {
	    	$sqlHelper = new VisformsSql($sql, $inputContext);
		    $items = $sqlHelper->getItemsFromSQL();
	    }
	    catch (Exception $e) {
		    return $returnopts;
	    }
	    if (!empty($items)) {
		    foreach ($items as $item) {
			    if (isset($item->label) && isset($item->value)) {
				    $returnopts[] = array('id' => $i, 'value' => $item->value, 'label' => $item->label, 'selected' => false, 'redirecturl' => (isset($item->redirecturl) ? $item->redirecturl : ''), 'mail' => (isset($item->mail) ? $item->mail : ''), 'mailcc' => (isset($item->mailcc) ? $item->mailcc : ''), 'mailbcc' => (isset($item->mailbcc) ? $item->mailbcc : ''), 'labelclass' => (isset($item->labelclass) ? $item->labelclass : ''));
			    }
		    }
	    }
	    return $returnopts;
    }

    public static function mapDbValueToSqlOptionLabel ($dbValue, $sql) {
	    $fieldoptions = JHtmlVisformsselect::getOptionsFromSQL($sql);
	    $extracteditemvalues = JHtmlVisformsselect::explodeMsDbValue($dbValue);
	    $newextracteditemfieldvalues = array();
	    foreach ($extracteditemvalues as $extracteditemvalue) {
	        foreach ($fieldoptions as $fieldoption) {
	        	if (isset($optionLabel)) {
			        unset($optionLabel);
		        }
			    if ($extracteditemvalue == $fieldoption['value']) {
				    $optionLabel = $fieldoption['label'];
				    break;
			    }
		    }
		    if (isset($optionLabel)) {
			    $newextracteditemfieldvalues[] = $optionLabel;
		    }
		    else {
			    $newextracteditemfieldvalues[] = $extracteditemvalue;
		    }
	    }
	    return $newextracteditemfieldvalues;
    }

	public static function getStoredUserInputs($fieldId, $formId, $recordId = 0, $pulishedOnly = false) {
		$db = JFactory::getDbO();
		$query = $db->getQuery(true);
		$query->select($db->qn('F' . $fieldId))
			->from($db->qn('#__visforms_' . $formId));
		// parameter $field->uniquepublishedvaluesonly (published only) does not yet exist, therfore always false
		if (!empty($pulishedOnly)) {
			$query->where($db->qn('published') . ' = ' . 1);
		}
		// exclude current record on edit submit
		if (!empty($recordId)) {
			$query->where($db->qn('id') . ' != ' . $recordId);
		}
		$query->where($db->qn('F' . $fieldId) . ' IS NOT NULL');
		$query->where($db->qn('F' . $fieldId) . " != ''");
		$query->group($db->qn('F' . $fieldId));
		$db->setQuery($query);
		try {
			return $db->loadColumn();
		}
		catch (Exception $exc) {
			return array();
		}
	}

	public static function loadSearchableApi () {
		if (!empty(static::$loaded[__METHOD__])) {
			return true;
		}
		$doc = JFactory::getDocument();
		JHtml::_('jquery.framework');
		JHtml::_('script', 'media/com_visforms/js/select2.js', array('version' => 'auto', 'relative' => false, 'detectBrowser' => false, 'detectDebug' => false));
		$doc->addStyleSheet(JURI::root(true) . '/media/com_visforms/css/select2.min.css', array('version' => 'auto', 'relative' => false, 'detectBrowser' => false, 'detectDebug' => false));
		static::$loaded[__METHOD__] = true;
		return false;
	}
}
?>