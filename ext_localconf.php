<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

	// Add AJAX support
$TYPO3_CONF_VARS['FE']['eID_include']['wec_journal'] = 'EXT:wec_journal/service/ajax.php';


t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_wecjournal_pi1.php', '_pi1', 'list_type', 1);
?>