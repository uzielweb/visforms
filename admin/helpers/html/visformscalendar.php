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
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\Utilities\ArrayHelper;

/**
 * Utility class for creating HTML Calendar
 *
 * @static
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @since        1.5.5
 */
class JHtmlVisformscalendar
{
	/**
	 * Displays a calendar control field
	 *
	 * @param string $value   The date value
	 * @param string $name    The name of the text field
	 * @param string $id      The id of the text field
	 * @param string $format  The date format
	 * @param mixed  $attribs Additional HTML attributes
	 *
	 * @return  string  HTML markup for a calendar field
	 *
	 * @since   1.5
	 */
	public static function calendarLT375($value, $name, $id, $format = '%Y-%m-%d', $attribs = null, $bt3layout = false, $label = null) {
		static $done;
		static $handlerloaded;
		$jversion = new JVersion();
		if (version_compare($jversion->getShortVersion(), '3.7.0', 'ge')) {
			// ToDo: Enable (and test) when Joomla! 3.7 Calendar works properly
			// return self::calendarGE375($value, $name, $id, $format, $attribs, $bt3layout, $label);
		}
		if ($done === null) {
			$done = array();
		}
		$readonly = isset($attribs['readonly']) && $attribs['readonly'] == 'readonly';
		$disabled = isset($attribs['disabled']) && $attribs['disabled'] == 'disabled';
		if (is_array($attribs)) {
			$attribs['class'] = isset($attribs['class']) ? $attribs['class'] : 'input-medium';
			$attribs['class'] = trim($attribs['class'] . ' hasTooltip');
			$attribs = JArrayHelper::toString($attribs);
		}
		JHtml::_('bootstrap.tooltip');
		// Format value when not '0000-00-00 00:00:00', otherwise blank it as it would result in 1970-01-01.
		if ((int) $value) {
			$tz = date_default_timezone_get();
			date_default_timezone_set('UTC');
			$inputvalue = strftime($format, strtotime($value));
			date_default_timezone_set($tz);
		}
		else {
			$inputvalue = '';
		}
		$document = JFactory::getDocument();
		// Load the calendar behavior
		JHtml::_('behavior.calendar');
		if (!$handlerloaded) {
			//Bugfix Joomla! 3.7 add css and javascript files which might be missing
			if (version_compare($jversion->getShortVersion(), '3.7.0', 'ge')) {
				$tag = JFactory::getLanguage()->getTag();
				$document->addStyleSheet(JURI::root(true) . '/media/system/css/calendar-jos.css');
				JHtml::_('script', $tag . '/calendar-setup.js', array('version' => 'auto', 'relative' => true));
			}
			$document->addScriptDeclaration('function validateDateOnUpdate () {var input = jQuery(this.inputField); input.valid(); jQuery(".isCal").trigger("update");}');
			$handlerloaded = true;
		}
		// Only display the triggers once for each control.
		if (!in_array($id, $done)) {
			$document
				->addScriptDeclaration(
					'jQuery(document).ready(function($) {Calendar.setup({
			// Id of the input field
			inputField: "' . $id . '",
			// Format of the input field
			ifFormat: "' . $format . '",
			// Trigger for the calendar (button ID)
			button: "' . $id . '_img",
			// Alignment (defaults to "Bl")
			align: "Tl",
			singleClick: true,
			firstDay: ' . JFactory::getLanguage()->getFirstDay() . ',
            onUpdate : validateDateOnUpdate
			});});'
				);
			$done[] = $id;
		}
		// Hide button using inline styles for readonly/disabled fields
		$btn_style = ($readonly || $disabled) ? ' style="display:none;"' : '';
		if (!empty($bt3layout)) {
			$div_class = (!$readonly) ? ' class="input-group"' : '';
		}
		else {
			$div_class = (!$readonly) ? ' class="input-append"' : '';
		}
		$html = '<div' . $div_class . '>';
		if (!empty($label)) {
			$html .= $label;
		}
		$html .= '<input type="text" title="' . (0 !== (int) $value ? JHtml::_('date', $value, null, null) : '')
			. '" name="' . $name . '" id="' . $id . '" value="' . htmlspecialchars($inputvalue, ENT_COMPAT, 'UTF-8') . '" ' . $attribs . ' />';
		if (!empty($bt3layout)) {
			$html .= '<span class="input-group-btn">'
				. '<button type="button" class="btn" id="' . $id . '_img"' . $btn_style . '><i class="visicon-calendar"></i></button>'
				. '</span>';
		}
		else {
			$html .= '<button type="button" class="btn" id="' . $id . '_img"' . $btn_style . '><i class="visicon-calendar"></i></button>';
		}
		$html .= '</div>';
		return $html;
	}

	public static function calendar($value, $name, $id, $format = '%Y-%m-%d', $attribs = array(), $layout = '', $label = null, $ukTooltip = false) {
		static $handlerloaded;
		$document = JFactory::getDocument();
		$tag = JFactory::getLanguage()->getTag();
		$calendar = JFactory::getLanguage()->getCalendar();
		$direction = strtolower($document->getDirection());
		// Get the appropriate file for the current language date helper
		$helperPath = 'system/fields/calendar-locales/date/gregorian/date-helper.min.js';
		if (!empty($calendar) && is_dir(JPATH_ROOT . '/media/system/js/fields/calendar-locales/date/' . strtolower($calendar))) {
			$helperPath = 'system/fields/calendar-locales/date/' . strtolower($calendar) . '/date-helper.min.js';
		}
		// Get the appropriate locale file for the current language
		$localesPath = 'system/fields/calendar-locales/en.js';
		if (is_file(JPATH_ROOT . '/media/system/js/fields/calendar-locales/' . strtolower($tag) . '.js')) {
			$localesPath = 'system/fields/calendar-locales/' . strtolower($tag) . '.js';
		}
        elseif (is_file(JPATH_ROOT . '/media/system/js/fields/calendar-locales/' . strtolower(substr($tag, 0, -3)) . '.js')) {
			$localesPath = 'system/fields/calendar-locales/' . strtolower(substr($tag, 0, -3)) . '.js';
		}
		$readonly = isset($attribs['readonly']) && $attribs['readonly'] === 'readonly';
		$disabled = isset($attribs['disabled']) && $attribs['disabled'] === 'disabled';
		$autocomplete = isset($attribs['autocomplete']) && $attribs['autocomplete'] === '';
		$autofocus = isset($attribs['autofocus']) && $attribs['autofocus'] === '';
		$required = isset($attribs['required']) && $attribs['required'] === '';
		$filter = isset($attribs['filter']) && $attribs['filter'] === '';
		$todayBtn = isset($attribs['todayBtn']) ? $attribs['todayBtn'] : true;
		$weekNumbers = isset($attribs['weekNumbers']) ? $attribs['weekNumbers'] : true;
		$showTime = isset($attribs['showTime']) ? $attribs['showTime'] : false;
		$fillTable = isset($attribs['fillTable']) ? $attribs['fillTable'] : true;
		$timeFormat = isset($attribs['timeFormat']) ? $attribs['timeFormat'] : 24;
		$singleHeader = isset($attribs['singleHeader']) ? $attribs['singleHeader'] : false;
		$hint = isset($attribs['placeholder']) ? $attribs['placeholder'] : '';
		$class = isset($attribs['class']) ? $attribs['class'] : 'input-medium';
		$onchange = isset($attribs['onChange']) ? $attribs['onChange'] : 'validateDateOnUpdate(this)';
		$showTime = ($showTime) ? "1" : "0";
		$todayBtn = ($todayBtn) ? "1" : "0";
		$weekNumbers = ($weekNumbers) ? "1" : "0";
		$fillTable = ($fillTable) ? "1" : "0";
		$singleHeader = ($singleHeader) ? "1" : "0";
		if (is_array($attribs)) {
			$attribs['class'] = isset($attribs['class']) ? $attribs['class'] : 'input-medium';
			$attribs = ArrayHelper::toString($attribs);
			if ($ukTooltip) {
			    $attribs .= ' data-uk-tooltip';
            }
		}
		// Format value when not nulldate ('0000-00-00 00:00:00'), otherwise blank it as it would result in 1970-01-01.
		if ($value && $value !== JFactory::getDbo()->getNullDate() && strtotime($value) !== false) {
			$tz = date_default_timezone_get();
			date_default_timezone_set('UTC');
			$value = strftime($format, strtotime($value));
			date_default_timezone_set($tz);
		}
		$cssFileExt = ($direction === 'rtl') ? '-rtl.css' : '.css';
		// Load polyfills for older IE
		JHtml::_('behavior.polyfill', array('event', 'classlist', 'map'), 'lte IE 11');
		JHtml::_('script', $localesPath, false, true, false, false, true);
		JHtml::_('script', $helperPath, false, true, false, false, true);
		JHtml::_('script', 'system/fields/calendar.min.js', false, true, false, false, true);
		JHtml::_('stylesheet', 'system/fields/calendar' . $cssFileExt, array(), true);
		if (!$handlerloaded) {
			$document->addScriptDeclaration('function validateDateOnUpdate (input) {jQuery(input).valid(); jQuery(".isCal").trigger("update"); return true;}');
			$handlerloaded = true;
		}
		switch ($layout) {
			case 'bt3layout' :
				$main_wrapper_class = "";
				$div_class = (!$readonly) ? ' class="input-group"' : '';
				$needControlWrapping = true;
				$needButtonWrapping = true;
				$buttonWrapperClass='input-group-btn';
				$btnClass = 'btn btn-secondary';
				break;
			case 'bt4mcindividual' :
			    $main_wrapper_class = "";
				$div_class = (!$readonly) ? ' class="input-group"' : '';
				$needControlWrapping = true;
				$needButtonWrapping = true;
				$buttonWrapperClass='input-group-append';
				$btnClass = 'btn btn-secondary';
				break;
			case 'uikit2' :
				$main_wrapper_class = "";
				$div_class = (!$readonly) ? ' class="uk-button-group"' : '';
				$needControlWrapping = false;
				$needButtonWrapping = false;
				$buttonWrapperClass='';
				$btnClass = ' uk-button uk-button-primary';
				break;
			case 'uikit3' :
				$main_wrapper_class = "uk-button-group ";
				$div_class = (!$readonly) ? ' class="uk-button-group"' : '';
				$needControlWrapping = false;
				$needButtonWrapping = true;
				$buttonWrapperClass='uk-inline';
				$btnClass = ' uk-button uk-button-primary';
				break;
			default :
				$main_wrapper_class = "";
				$div_class = (!$readonly) ? ' class="input-append"' : '';
				$needControlWrapping = true;
				$needButtonWrapping = false;
				$buttonWrapperClass='';
				$btnClass = 'btn btn-secondary';
				break;
		}
		$btn_style = ($readonly || $disabled) ? ' style="display:none;"' : '';
		?>

        <div class="field-calendar <?php echo $main_wrapper_class; ?>uk-width-1-1"><?php
		if ($needControlWrapping) {
			echo '<div' . $div_class . '>';
		}
		if (!empty($label)) {
			echo $label;
		} ?>
        <input type="text" id="<?php echo $id; ?>" name="<?php
		echo $name; ?>" value="<?php
		echo htmlspecialchars(($value !== '0000-00-00 00:00:00') ? $value : '', ENT_COMPAT, 'UTF-8'); ?>" <?php echo $attribs; ?>
			<?php echo !empty($hint) ? 'placeholder="' . htmlspecialchars($hint, ENT_COMPAT, 'UTF-8') . '"' : ''; ?>
               data-alt-value="<?php
			   echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>" autocomplete="off"
               onchange="<?php echo $onchange; ?>"/>
		<?php if (!empty($needButtonWrapping)) {
			echo '<span class="' .$buttonWrapperClass . '">';
		} ?>
        <button type="button" class="<?php echo $btnClass; ?>" <?php echo $btn_style; ?>
                id="<?php echo $id; ?>_btn"
                data-inputfield="<?php echo $id; ?>"
                data-dayformat="<?php echo $format; ?>"
                data-button="<?php echo $id; ?>_btn"
                data-firstday="<?php echo JFactory::getLanguage()->getFirstDay(); ?>"
                data-weekend="<?php echo JFactory::getLanguage()->getWeekEnd(); ?>"
                data-today-btn="<?php echo $todayBtn; ?>"
                data-week-numbers="<?php echo $weekNumbers; ?>"
                data-show-time="<?php echo $showTime; ?>"
                data-show-others="<?php echo $fillTable; ?>"
                data-time-24="<?php echo $timeFormat; ?>"
                data-only-months-nav="<?php echo $singleHeader; ?>"
			<?php echo !empty($minYear) ? 'data-min-year="' . $minYear . '"' : ''; ?>
			<?php echo !empty($maxYear) ? 'data-max-year="' . $maxYear . '"' : ''; ?>
        ><span class="visicon-calendar"></span></button>
		<?php if (!empty($needButtonWrapping)) {
			echo '</span>';
		} ?>

        </div><?php
		if ($needControlWrapping) {
			echo '</div>';
		}
	}
}