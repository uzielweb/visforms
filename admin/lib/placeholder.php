<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2019 vi-solutions
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class VisformsPlaceholder {

	protected $text;
	protected $matches;
	protected $mCount;
	protected $nextIndex;
	protected $currentMatch;
	protected $nonFieldPlaceholder = array('formtitle', 'id', 'created', 'created_by', 'modified', 'modified_by', 'ismdf', 'ipaddress', 'checked_out', 'checked_out_time', 'published', 'currentdate');
	protected $subscriptionNonFieldPlaceholder = array(
	);
	protected $ignoredPlaceholder = array ();

	public function __construct($text) {
		$this->text = $text;
		$this->matches = $this->getAllPlaceholderFullString();
		$this->mCount = count($this->matches);
		$this->nextIndex = 0;
		$hasSub = VisformsAEF::checkAEF(VisformsAEF::$subscription);
		if ($hasSub) {
			$this->nonFieldPlaceholder = array_merge($this->nonFieldPlaceholder, $this->subscriptionNonFieldPlaceholder);
		}
	}

	// returns an array of all placeholders as full string [context:mane|PARAM]
	// context is a prefix before a colon which makes the placeholder name unique
	// i.e. the id of an sql-statement or a framework object like user
	protected function getAllPlaceholderFullString() {
		$text = $this->text;
		$pattern = '/(?:\$\{|\[)(?:[a-zA-Z0-9\-_][a-zA-Z0-9\-_]*:)?[a-zA-Z0-9]{1}[a-zA-Z0-9\-_]*(?:\|[A-Z]*)?(?:\}|\]|\[\]})/';
		if (preg_match_all($pattern, $text, $matches)) {
			if (!empty($matches)) {
				return $matches[0];
			}
		}
		return array();
	}
	public function hasNext() {
		if ($this->nextIndex < $this->mCount) {
			return true;
		}
		return false;
	}

	public function getNext() {
		if ($this->hasNext()) {
			$this->currentMatch = $this->matches[$this->nextIndex];
			$this->nextIndex++;
			return $this->currentMatch;
		}
		return false;
	}

	protected function getPlaceholderParts() {
		$placeholder = $this->currentMatch;
		// todo should we instanciate array with empty parts (param, name, context)
		if (empty($placeholder)) {
			return array();
		}
		else {
			$parts = array();
			$parts['placeholder'] = $placeholder;
			$oldFormat = (strpos($placeholder, ']') !== false ) ? true : false;
			$firstSplit = explode('|', trim(trim($placeholder, '$'), '{}\[]'));
			if (count($firstSplit) === 2) {
				$parts['params'] = array_pop($firstSplit);
			}
			if (!empty($firstSplit[0])) {
				$secondSplit = explode(':', $firstSplit[0]);
				if (count($secondSplit) === 1)  {
					$parts['name'] = strtolower($secondSplit[0]);
				}
				else {
					$parts['context'] = $secondSplit[0];
					$parts['name'] = $secondSplit[1];
				}
			}

			return $parts;
		}
	}

	public function getPlaceholderPart($pName) {
		$parts = $this->getPlaceholderParts();
		if (is_array($parts) && isset($parts[$pName])) {
			return $parts[$pName];
		}
		else {
			return '';
		}
	}

	public function isNonFieldPlaceholder() {
		$name = $this->getPlaceholderPart('name');
		if (in_array($name, $this->nonFieldPlaceholder)) {
			return true;
		}
		return false;
	}

	public function replace($value) {
		// do not replace a placeholder if it is a placeholder with special meaning
		if (in_array($this->currentMatch, $this->ignoredPlaceholder)) {
			return;
		}
		if (is_array($value)) {
			$value = implode(', ', $value);
		}
		$newText = preg_replace('\'' . preg_quote($this->currentMatch) . '\'', $value, $this->text);
		$this->text = $newText;
	}

	public function replaceForSql($value) {
		if (is_array($value)) {
			$value = '("' . implode('", "', $value). '")';
		}
		$newText = preg_replace('\'' . preg_quote($this->currentMatch) . '\'', $value, $this->text);
		$this->text = $newText;
	}

	public function getText() {
		return $this->text;
	}
}