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

class visFormsImportExportHelper
{
	public static $formKey = 'form';
	public static $pdfKey = 'pdfs';
	public static $fieldsKey = 'fields';
	public static $dataKey = 'datas';
	public static $pluginsWithDbTable = array('visformsadd', 'visformsddr', 'visformsmailattachments', 'visformsms', 'visformscustomxmlmail'	);
}