<?php
/**
 * MenuRules class	for visforms
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6
 */
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\Rules\RulesInterface;
use Joomla\CMS\Component\Router\RouterView;


class VisformsRouterRulesMenu implements RulesInterface
{

	protected $router;
	protected $lookup = array();


	public function __construct(RouterView $router) {
		$this->router = $router;
		$this->buildLookup();
		$test = true;
	}

	public function preprocess(&$query) {
		$active = $this->router->menu->getActive();
		/**
		 * If the active item id is not the same as the supplied item id or we have a supplied item id and no active
		 * menu item then we just use the supplied menu item and continue
		 */
		if (isset($query['Itemid']) && ($active === null || $query['Itemid'] != $active->id)) {
			return;
		}
		// Get query language
		$language = isset($query['lang']) ? $query['lang'] : '*';
		if (!isset($this->lookup[$language])) {
			$this->buildLookup($language);
		}
		// Check if the active menu item matches the requested query
		if ($active !== null && isset($query['Itemid'])) {
			// Check if active->query and supplied query are the same
			$match = true;
			foreach ($active->query as $k => $v) {
				if (isset($query[$k]) && $v !== $query[$k]) {
					// Compare again without alias
					if (is_string($v) && $v == current(explode(':', $query[$k], 2))) {
						continue;
					}
					$match = false;
					break;
				}
			}
			if ($match) {
				// Just use the supplied menu item
				return;
			}
		}
		$needles = $this->router->getPath($query);
		$layout = isset($query['layout']) && $query['layout'] !== 'default' ? ':' . $query['layout'] : '';
		if ($needles) {
			foreach ($needles as $view => $ids) {
				$viewLayout = $view . $layout;
				if ($layout && isset($this->lookup[$language][$viewLayout])) {
					if (is_bool($ids)) {
						$query['Itemid'] = $this->lookup[$language][$viewLayout];
						return;
					}
					foreach ($ids as $id => $segment) {
						if (isset($this->lookup[$language][$viewLayout][(int) $id])) {
							$query['Itemid'] = $this->lookup[$language][$viewLayout][(int) $id];
							return;
						}
					}
				}
				if (isset($this->lookup[$language][$view])) {
					if (is_bool($ids)) {
						$query['Itemid'] = $this->lookup[$language][$view];
						return;
					}
					foreach ($ids as $id => $segment) {
						if (isset($this->lookup[$language][$view][(int) $id])) {
							$query['Itemid'] = $this->lookup[$language][$view][(int) $id];
							return;
						}
					}
				}
			}
		}
		// Check if the active menuitem matches the requested language
		if ($active && $active->component === 'com_' . $this->router->getName()
			&& ($language === '*' || in_array($active->language, array('*', $language)) || !\JLanguageMultilang::isEnabled())) {
			$query['Itemid'] = $active->id;
			return;
		}
		// If not found, return language specific home link
		$default = $this->router->menu->getDefault($language);
		if (!empty($default->id)) {
			$query['Itemid'] = $default->id;
		}
	}

	protected function buildLookup($language = '*') {
		// Prepare the reverse lookup array.
		if (!isset($this->lookup[$language])) {
			$this->lookup[$language] = array();
			$component = ComponentHelper::getComponent('com_' . $this->router->getName());
			$views = $this->router->getViews();
			$attributes = array('component_id');
			$values = array((int) $component->id);
			$attributes[] = 'language';
			$values[] = array($language, '*');
			$items = $this->router->menu->getItems($attributes, $values);
			foreach ($items as $item) {
				if (isset($item->query['view'], $views[$item->query['view']])) {
					$view = $item->query['view'];
					$layout = '';
					if (isset($item->query['layout'])) {
						$layout = ':' . $item->query['layout'];
					}
					if ($views[$view]->key) {
						if (!isset($this->lookup[$language][$view . $layout])) {
							$this->lookup[$language][$view . $layout] = array();
						}
						if (!isset($this->lookup[$language][$view])) {
							$this->lookup[$language][$view] = array();
						}
						// If menuitem has no key set, we assume 0.
						if (!isset($item->query[$views[$view]->key])) {
							$item->query[$views[$view]->key] = 0;
						}
						/**
						 * Here it will become a bit tricky
						 * language != * can override existing entries
						 * language == * cannot override existing entries
						 */
						if (!isset($this->lookup[$language][$view . $layout][$item->query[$views[$view]->key]]) || $item->language !== '*') {
							$this->lookup[$language][$view . $layout][$item->query[$views[$view]->key]] = $item->id;
							// We can have menus with different layouts (edit, detailitem, data) for the same form, which technicly are different
							// in the visformsdata array of the lookup they will override each other
							//$this->lookup[$language][$view][$item->query[$views[$view]->key]] = $item->id;
						}
					}
					else {
						/**
						 * Here it will become a bit tricky
						 * language != * can override existing entries
						 * language == * cannot override existing entries
						 */
						if (!isset($this->lookup[$language][$view . $layout]) || $item->language !== '*') {
							$this->lookup[$language][$view . $layout] = $item->id;
							// We can have menus with different layouts (edit, detailitem, data) for the same form, which technicly are different
							// in the visformsdata array of the lookup they will override each other
							//$this->lookup[$language][$view] = $item->id;
						}
					}
				}
			}
		}
	}

	public function parse(&$segments, &$vars) {}

	public function build(&$query, &$segments) {}
}
