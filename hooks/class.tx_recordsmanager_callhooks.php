<?php

class tx_recordsmanager_callhooks
{
	function getMainFields_preProcess($table, $row, $parent) {
		$recordsHide = explode(',', t3lib_div::_GP('recordsHide'));
		if (count($recordsHide) > 0) {
			$parent->hiddenFieldListArr = array_merge($parent->hiddenFieldListArr, $recordsHide);
		}
	}
}

?>