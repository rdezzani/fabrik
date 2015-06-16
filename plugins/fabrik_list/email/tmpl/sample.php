<?php
/**
 * Email list plugin template example
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.email
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$this->filepath = "foo";

foreach ($data as $name => $value)
{
	if (preg_match('#_raw$#', $name))
	{
		continue;
	}

	$elementModel = $model->getElement($name);

	if (empty($elementModel))
	{
		continue;
	}

	$element = $elementModel->getElement();
	$label = $element->get('label');
	$fval = $elementModel->renderListData($val, $row);
	echo "$name : $label : $value : $fval<br />\n";
}
