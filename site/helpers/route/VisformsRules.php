<?php
/**
 * @package		Joomla.Site
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

use Joomla\CMS\Component\Router\Rules\RulesInterface;


class VisformsRouterRulesVisforms implements RulesInterface
{

	public function __construct($router) {
		$this->router = $router;
	}

	public function preprocess(&$query) {
	}

	public function build(&$query, &$segments) {
		// StandardRules have already remove layout which is part of the menu link (i.e. visformsdata&layout=data). We only have to replace other layouts (i.e. visformsdata&layout=detail)
		if (isset($query['layout'])) {
			$segments[] = $query['layout'];
			unset($query['layout']);
		}
		// if we deal with a data detail view, there is a additional parameter cid which we put into $segments (on the last position)
		if (isset($query['cid'])) {
			$segments[] = $query['cid'];
			unset($query['cid']);
		}
		// menunorules will remove view and id from query, too
		return;
	}

	public function parse(&$segments, &$vars) {
		$count = count($segments);
		// if there is only one segment, then it is the layout
		if ($count >= 1) {
			$vars['layout'] = $segments[0];
			unset($segments[0]);
		}
		if ($count >= 2) {
			$vars['cid'] = $segments[1];
			unset($segments[1]);
			// a visformsdata view directly called. This seems to be used as url in the search plugin
			// only more than 2 segements, if nomenurules are enabled
			if ($count >= 3) {
				$vars['view'] = $segments[2]; //"visformsdata"; ToDo does it make a difference if we use segments[2] or visformsdata?
				unset($segments[2]);
			}
			if ($count >= 4) {
				$part = preg_replace('/-/', ':', $segments[3], 1);
				if (is_string($part)) {
					$parts = explode(':', $part, 2);
					if (count($parts) === 2) {
						$vars['id'] = $parts[0];
					}
				}
				unset($segments[3]);
			}
		}
		return;
	}
}
