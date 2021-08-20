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


class VisformsLayoutVisforms extends VisformsLayout
{

	protected function getCustomRequiredCss($parent) {
		$fullParent = 'form#' . $parent;
		$css        = array();
		//css for required fields with placeholder instead of label
		$css[] = $fullParent . ' div.required > label.visCSSlabel.asterix-ancor:after ';
		$css[] = '{content:"*"; color:red; display: inline-block; padding-left: 0; } ';
		//css for all other required fields
		$css[] = $fullParent . ' div.required > label.visCSSlabel:after, ';
		$css[] = $fullParent . ' div.required > label.vflocationlabel:after ';
		$css[] = '{content:"*"; color:red; display: inline-block; padding-left: 10px; } ';

		return implode('', $css);
	}

	protected function addCustomCss($parent) {
		$fullParent = 'form#' . $parent;
		$css        = array();
		$css[]      = $fullParent . ' .vflocationsubform {display: block;}';
		$css[]      = $fullParent . ' .vflocationsubform .locationinput, ';
		$css[]      = $fullParent . ' .vflocationsubform .getmylocationbutton ';
		$css[]      = '{display: inline-block; margin-bottom: 0; vertical-align: middle; cursor: pointer;}';
		$css[] = $fullParent . ' #dynamic_recaptcha_1.g-recaptcha {display: inline-block; }';

		return implode('', $css);
	}

	protected function addDefaultResponsiveCss($parent) {
		$fullParent = 'form#' . $parent;
		$css        = array();
		$css[]      = '@media (max-width: 979px) {';
		$css[]      = $fullParent . ' .visCSSlabel:not(.visCheckbox) {float: none;margin-right: 0;width: 100%;}';
		$css[]      = $fullParent . ' .visCSSmargLeft, ';
		$css[]      = $fullParent . ' , .editor ';
		$css[]      = '{margin-left: 0;}';
		$css[]      = $fullParent . ' p:not(.visCSSrbinl) .visCSSinput, ';
		$css[]      = $fullParent . ' , .editor ';
		$css[]      = '{width: 99%;}';
		$css[] = '}';

		return implode('', $css);
	}
}