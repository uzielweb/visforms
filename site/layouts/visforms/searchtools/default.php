<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();
$context = (!empty($data['options']['context'])) ? $data['options']['context'] : '';
$filters = $data['view']->filterForm->getGroup('filter');
if ((VisformsAEF::checkAEF(7)) || (VisformsAEF::checkAEF(8)) || (VisformsAEF::checkAEF(0)) || (VisformsAEF::checkAEF(5))) {
	// Set some basic options (
	$customOptions = array(
		'filtersHidden'       => isset($data['options']['filtersHidden']) ? $data['options']['filtersHidden'] : empty($data['view']->activeFilters),
		'defaultLimit'        => isset($data['options']['defaultLimit']) ? $data['options']['defaultLimit'] : JFactory::getApplication()->get('list_limit', 20),
		'searchFieldSelector' => '#filter_'.$context.'search',
		'orderFieldSelector'  => '#list_fullordering',
        'clearBtnSelector' => (!empty($context)) ? '.'.$context : '.js-stools-btn-clear'
	);

	if (!empty($filters) && is_array($filters)) {
		$filtercount = count($filters);
		if ((array_key_exists('filter_'.$context.'search', $filters)) && (!empty($filtercount))) {
			$filtercount--;
		}
		if ((array_key_exists('filter_'.$context.'vfsortordering', $filters)) && (!empty($filtercount))) {
			$filtercount--;
		}
	}
	$customOptions['filterButton'] = (!empty($filtercount)) ? true : false;
	
	$data['options'] = array_merge($customOptions, $data['options']);

	$formSelector = !empty($data['options']['formSelector']) ? $data['options']['formSelector'] : '#'.$context.'adminForm';
	if (!empty($data['options']['hasLocationRadiusSearch'])) {
	    JHtmlVisformslocation::includeLocationSearchJs();
    }

	// Load search tools
	JHtml::_('searchtools.form', $formSelector, $data['options']);

?>
<div class="js-stools clearfix">
	<div class="clearfix">
		<div class="js-stools-container-bar">
			<?php echo JLayoutHelper::render('visforms.searchtools.default.bar', $data, JPATH_ROOT. '/components/com_visforms/layouts'); ?>
		</div>
		<div class="js-stools-container-list hidden-phone hidden-tablet">
			<?php echo JLayoutHelper::render('visforms.searchtools.default.list', $data, JPATH_ROOT. '/components/com_visforms/layouts'); ?>
		</div>
	</div>
	<!-- Filters div -->
	<div class="js-stools-container-filters clearfix">
		<?php echo JLayoutHelper::render('visforms.searchtools.default.filters', $data, JPATH_ROOT. '/components/com_visforms/layouts'); ?>
	</div>
</div>
<?php } ?>
