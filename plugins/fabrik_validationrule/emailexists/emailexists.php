<?php
/**
*
* @package fabrikar
* @author Hugh Messenger
* @copyright (C) Hugh Messenger
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/


/**
 *
 *
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'plugin.php');
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'validation_rule.php');

class plgFabrik_ValidationruleEmailExists extends plgFabrik_Validationrule
{

	var $_pluginName = 'emailexists';

	/** @param string classname used for formatting error messages generated by plugin */
	var $_className = 'emailexists';

	/**
	 * validate the elements data against the rule
	 * @param string data to check
	 * @param object element
	 * @param int plugin sequence ref
	 * @return bol true if validation passes, false if fails
	 */

	function validate($data, &$element, $c)
	{
		if (empty($data)) {
			return false;
		}
		$params =& $this->getParams();
		$ornot = $params->get('emailexists_or_not');
		$ornot = $ornot[$c];
		$condition = $params->get('emailexists-validation_condition');
		$condition = $condition[$c];
		if ($condition !== '') {
			if (@eval($condition)) {
				return true;
			}
		}
		jimport('joomla.user.helper');
		$db = FabrikWorker::getDbo();
		$db->setQuery("SELECT id FROM #__users WHERE email = '$data'");
		$result = $db->loadResult();
		if (!$result) {
			if ($ornot == 'fail_if_exists') {
				return true;
			}
		}
		else {
			if ($ornot == 'fail_if_not_exists') {
				return true;
			}
		}
		return false;
	}

}
?>