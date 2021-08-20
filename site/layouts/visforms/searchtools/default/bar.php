<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\Registry\Registry;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();
$context = (!empty($data['options']['context'])) ? $data['options']['context'] : '';
if (is_array($data['options']))
{
	$data['options'] = new Registry($data['options']);
}

// Options
$filterButton = $data['options']->get('filterButton', true);
$searchButton = $data['options']->get('searchButton', true);

$filters = $data['view']->filterForm->getGroup('filter');
// ToDo add parameter "make small/use icons" to menu/plugin configuration; use parameter value in condition
$fitlerButtonText = (true) ? JText::_('JSEARCH_TOOLS') : '<span class="visicon-filter"></span>';
$clearButtonText = (true) ? JText::_('JSEARCH_FILTER_CLEAR') : '<span class="visicon-unpublish"></span>';
?>

<?php if (!empty($filters['filter_'.$context.'search'])) : ?>
	<?php if ($searchButton) : ?>
		<label for="filter_search" class="element-invisible">
			<?php echo JText::_('JSEARCH_FILTER'); ?>
		</label>
		<div class="btn-wrapper input-append">
			<?php echo $filters['filter_'.$context.'search']->input; ?>
			<?php if ($filters['filter_'.$context.'search']->description) : ?>
				<?php JHtmlBootstrap::tooltip('filter_'.$context.'search', array('title' => JText::_($filters['filter_'.$context.'search']->description))); ?>
			<?php endif; ?>
			<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
				<span class="visicon-search"></span>
			</button>
		</div>
		<?php if ($filterButton) : ?>
			<div class="btn-wrapper">
				<button type="button" class="btn hasTooltip js-stools-btn-filter" title="<?php echo JHtml::tooltipText('JSEARCH_TOOLS_DESC'); ?>">
                    <?php echo $fitlerButtonText;?> <span class="caret"></span>
				</button>
			</div>
		<?php endif; ?>
		<div class="btn-wrapper">
			<button type="button" class="btn hasTooltip js-stools-btn-clear  <?php echo $context; ?>" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>">
				<?php echo $clearButtonText;?>
			</button>
		</div>
	<?php endif; ?>
<?php endif;
