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

// Load the form list fields
$list = $data['view']->filterForm->getGroup('filter');
$data['options'] = !empty($data['options']) ? $data['options'] : array();
$context = (!empty($data['options']['context'])) ? $data['options']['context'] : '';
?>
<?php if ($list) : ?>
    <div class="ordering-select hidden-phone">
		<?php foreach ($list as $fieldName => $field) :
			if ($fieldName == 'filter_'.$context.'vfsortordering') : ?>
                <div class="js-stools-field-list">
					<?php echo $field->input; ?>
                </div><?php
            endif;
        endforeach; ?>
    </div>
<?php endif; ?>
