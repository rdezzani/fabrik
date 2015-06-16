<?php
/**
 * Fabrik nvd3_chart Chart Plug-in Model - Discrete Bar Chart
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.nvd3_chart
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

use Fabrik\Helpers\Worker;
use \Fabrik\Admin\Models\Lizt;

/**
 * Fabrik nvd3_chart Discrete bar chart
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.nvd3_chart
 * @since       3.2rc2
 */
class DiscreteBarChart
{
	/**
	 * Params
	 *
	 * @var JParameters
	 */
	protected $params;

	/**
	 * Constructor
	 *
	 * @param   JParameters  params
	 */
	public function __construct($params)
	{
		$this->params = $params;
	}

	/**
	 * Return format:
	 * [
	 *  {
	 *    key: "Cumulative Return",
	 *    values: [
	 *      {
	 *        "label" : "A" ,
	 *        "value" : -29.765957771107
	 *      } ,
	 *     	{
     * 		  "label" : "B" ,
	 *        "value" : 0
	 * 		}
	 * 		]
  	 * 	}
	 * ]
	 *
	 * @return  array
	 */
	public function render()
	{
		$params = $this->params;
		$labelColumn = $params->get('label_field');
		$valueColumn = $params->get('value_field');

		$listId = $this->getListId();
		list($rows, $labelColumn, $valueColumn) = $listId? $this->listQuery($listId) : $this->dbQuery();
		$return = array();
		$values = array();

		$entry = new stdClass;

		foreach ($rows as $row)
		{
			$o = new stdClass;

			// Key needs to be a numeric value.
			$o->value = (float) $row->$valueColumn;
			$o->label = $row->$labelColumn;
			$values[] = $o;
		}

		$entry->values = $values;
		$entry->key = 'todo';
		$return[] = $entry;

		return $return;
	}

	/**
	 * Get list id from the selected table name
	 *
	 * @return  int
	 */
	protected function getListId()
	{
		if (isset($this->listid))
		{
			return $this->listid;
		}

		$params = $this->params;
		$db = Worker::getDbo(false, $params->get('conn_id'));
		$table = $params->get('tbl');
		$query = $db->getQuery(true);
		$query->select('id')->from('#__fabrik_lists')->where('db_table_name = ' . $db->q($table));
		$db->setQuery($query);
		$this->listid = $db->loadResult();

		return $this->listid;
	}

	/**
	 * Get rows from db table
	 *
	 * @return array($rows, $labelColumn, $valueColumn)
	 */
	protected function dbQuery()
	{
		$params = $this->params;
		$db = Worker::getDbo(false, $params->get('conn_id'));
		$query = $db->getQuery(true);
		$table = $params->get('tbl');
		$labelColumn = $params->get('label_field');
		$valueColumn = $params->get('value_field');
		$calc = $params->get('value_calc', '');
		$query->from($table);

		if ($params->get('data_mode') == 0)
		{
			if ($calc !== '')
			{
				$valueColumn = $calc . '(' . $valueColumn . ')';
			}

			$query->select($labelColumn)->select($valueColumn);
		}
		else
		{
			$labelColumns = explode(',', $params->get('label_columns'));
			$query->select($labelColumns);
		}

		$query->group($labelColumn);
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		return array($rows, $labelColumn, $valueColumn);
	}

	/**
	 * Get rows from list
	 *
	 * @param   int  $listId  List id
	 *
	 * @return array($rows, $labelColumn, $valueColumn)
	 */
	protected function listQuery($listId)
	{
		$params = $this->params;
		$db = Worker::getDbo(false, $params->get('conn_id'));
		$input = JFactory::getApplication()->input;
		$fabrik_show_in_list = $input->get('fabrik_show_in_list');
		$labelColumn = $params->get('label_field');
		$valueColumn = $params->get('value_field');
		$calc = $params->get('value_calc', '');
		$listModel = new lizt;
		$listModel->setId($listId);
		$formModel = $listModel->getFormModel();

		$listModel->pluginQueryGroupBy = array($params->get('label_field'));
		$fields = array();

		if ($labelElement = $listModel->getElement($labelColumn, false, true))
		{
			$labelColumn = $labelElement->getFullName(true, false);
			$fields[] = $labelElement->getId();
		}

		if ($valueElement = $listModel->getElement($valueColumn, false, true))
		{
			$valueElement->calcSelectModifier = $calc;
			$valueColumn = $valueElement->getFullName(true, false);
			$fields[] = $valueElement->getId();
		}

		$input->set('fabrik_show_in_list', $fields);
		$query = $listModel->buildQuery();
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Reset list/forms in case they are embedded in a content plugin
		$input->set('fabrik_show_in_list', $fabrik_show_in_list);
		$listModel->reset();
		$formModel->unsetData(true);

		return array($rows, $labelColumn, $valueColumn);
	}
}
