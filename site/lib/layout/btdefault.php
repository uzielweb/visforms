<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class VisformsLayoutBtdefault extends VisformsLayout
{
	protected function getCustomRequiredCss($parent) {
		$fullParent = 'form#' . $parent;
		$css        = array();
		//css for required fields except checkboxes and inputs with placeholder instead of label
		$css[] = $fullParent . ' div.required > label:after, ';
		//css for required checkboxes
		$css[] = $fullParent . ' div.required > label.checkbox.asterix-ancor:after, ';
		//css for required inputs with placeholder instead of label
		$css[] = $fullParent . ' div.required > span.asterix-ancor:after, ';
		//css for required date inputs with placeholder instead of label
		$css[] = $fullParent . ' div.required > div.asterix-ancor > div:after ';
		$css[] = '{content:"*"; color:red; display: inline-block; padding-left: 10px; } ';
		//no required asterix on the control labels of individual radio control or checkbox control in checkbeox groups
		$css[] = $fullParent . ' div.required > label.radio:after, ';
		$css[] = $fullParent . ' div.required > label.checkbox:after ';
		$css[] = '{content:""; color:red; } ';

		return implode('', $css);
	}

	protected function addCustomCss($parent) {
		$fullParent = 'form#' . $parent;
		$css        = array();
		$css[]      = $fullParent . ' .vflocationsubform {display: block;}';
		$css[]      = $fullParent . ' .vflocationsubform .locationinput, ';
		$css[]      = $fullParent . ' .vflocationsubform .getmylocationbutton ';
		$css[]      = '{display: inline-block; margin-bottom: 0; vertical-align: middle;}';

		return implode('', $css);
	}
}