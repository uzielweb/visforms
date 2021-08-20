<?php
/**
 * Vistools editcss view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */
defined('_JEXEC') or die;
ksort($this->files, SORT_STRING);
?>

<ul class='nav nav-list directory-tree'>
	<?php foreach($this->files as $key => $value): ?>
		<?php if(is_array($value)): ?>
			<?php
			$keyArray  = explode('/', $key);
			$fileArray = explode('/', $this->fileName);
			$count     = 0;

			if (count($fileArray) >= count($keyArray))
			{
				for ($i = 0; $i < count($keyArray); $i++)
				{
					if ($keyArray[$i] === $fileArray[$i])
					{
						$count = $count + 1;
					}
				}

				if ($count == count($keyArray))
				{
					$class = "folder show";
				}
				else
				{
					$class = "folder";
				}
			}
			else
			{
				$class = "folder";
			}

			?>
			<li class="<?php echo $class; ?>">
				<a class='folder-url nowrap' href=''>
					<i class='icon-folder-close'>&nbsp;<?php $explodeArray = explode('/', $key); echo end($explodeArray); ?></i>
				</a>
				<?php echo $this->directoryTree($value); ?>
			</li>
		<?php endif; ?>
		<?php if(is_object($value)): ?>
			<li>
				<a class="file nowrap" href='<?php echo JRoute::_('index.php?option=com_visforms&view=vistools&&file=' . $value->id) ?>'>
					<i class='icon-file'>&nbsp;<?php echo $value->name; ?></i>
				</a>
			</li>
		<?php endif; ?>
	<?php endforeach; ?>
</ul>
