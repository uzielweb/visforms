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

// Load the form filters
$filters = $data['view']->filterForm->getGroup('filter');
$data['options'] = !empty($data['options']) ? $data['options'] : array();
$context = (!empty($data['options']['context'])) ? $data['options']['context'] : '';
$hasSearchFilter = false;
?>
<?php if ($filters) : ?>
	<?php foreach ($filters as $fieldName => $field) : ?>
		<?php if ((empty($context) || (strpos($fieldName, $context) > 0)) && ($fieldName != 'filter_'.$context.'search') && ($fieldName != 'filter_'.$context.'vfsortordering')) :
            $hasSearchFilter = true; ?>
			<div class="js-stools-field-filter"><?php
                if (strtolower($field->type) == 'calendar') {
                    echo $field->label;
                }
                echo $field->input; ?>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
    <?php if ($hasSearchFilter) { ?>
        <div class="js-stools-field-filter">
            <button type="button" class="btn hasTooltip" title="" data-original-title="Suchen" onclick="this.form.submit()">
                <span class="icon-search"></span>
            </button>
        </div>
	<?php }?>
<?php endif; ?>
