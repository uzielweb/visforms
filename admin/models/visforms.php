<?php
/**
 * visforms model for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

/**
 * visforms Model
 *
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @since        Joomla 1.6 
 */
class VisformsModelVisforms extends JModelList
{
	/**
	 * forms data array
	 *
	 * @var array
	 */
	var $_data;
	
	/**
	* Items total
	* @var integer
	*/
	var $_total = null;
	
	/**
	* Pagination object
	* @var object
	*/
	var $_pagination = null;
	
	/*
	 * Constructor
	 *
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) 
        {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'published', 'a.published',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'access', 'a.access', 'access_level',
				'username', 'nbfields',
				'language', 'a.language',
				'a.hits',
			);
		}
		
		parent::__construct($config);
	}
	
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
     * 
     * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return	void
     * 
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		
		$published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);
		
		$language = $this->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$access = $this->getUserStateFromRequest($this->context.'.filter.access', 'filter_access');
		$this->setState('filter.access', $access);

		// List state information.
		parent::populateState('a.id', 'asc');
        
        // Force a language
		$forcedLanguage = $app->input->get('forcedLanguage');

		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}
	}
	
	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
     * 
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
     * 
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser();
        $app    = JFactory::getApplication();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*,(select count(*) from #__visfields c where c.fid=a.id) nbfields, (select b.username from #__users as b where a.created_by = b.id) as username'
			)
		);
		$tn = "#__visforms";
		$query->from($tn . ' AS a');
		
		// Join over the language
		$query->select('l.title AS language_title')
            ->join('LEFT', $db->quoteName('#__languages').' AS l ON l.lang_code = a.language');
		
		// Join over the asset groups.
		$query->select('ag.title AS access_level')
            ->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		//Show only forms that save data in modal select box for formdata menu
        $layout = $app->input->get('layout');
		if ($layout == 'modal_data')
		{
			$query->where('saveresult = 1');
		}

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) 
        {
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '') 
        {
			$query->where('(a.published = 0 OR a.published = 1)');
		}
		
		// Filter by search in title.
		$search = $this->getState('filter.search');
		if (!empty($search)) 
        {
			$search = $db->Quote('%'.$db->escape($search, true).'%');
			$query->where('(a.title LIKE '.$search.' OR a.name LIKE '.$search.')');
		}
		
		// Filter on the language.
		if ($language = $this->getState('filter.language')) 
        {
			$query->where('a.language = '.$db->quote($language));
		}
		
		// Filter by access level.
		if ($access = $this->getState('filter.access')) 
        {
			$query->where('a.access = ' . (int) $access);
		}
		
		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.id');
		$orderDirn	= $this->state->get('list.direction', 'asc');
		
		if($orderCol == 'language')
			$orderCol = 'l.title';
		if($orderCol == 'access_level')
			$orderCol = 'ag.title';
		$query->order($db->escape($orderCol.' '.$orderDirn));
		return $query;
	}
}
	
