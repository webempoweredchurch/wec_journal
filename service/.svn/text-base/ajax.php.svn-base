<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 David Slayback <dave@webempoweredchurch.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * AJAX controller class for WEC Journal
 *
 * @author	David Slayback <dave@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage wec_journal
 */

class tx_wecjournal_ajax {
	
	 // The page ID for editing.
	protected $pid;
	 // The editing command.
	protected $cmd;
	protected $content 		 = array();
	protected $contentFormat = 'plain';
	protected $charset       = 'utf-8';
	protected $table 		 = 'tx_wecjournal_content';
		
	/**
	 * Constructor to initialize a frontend instance and a backend user.
	 *
	 * @return		void
	 */
	public function __construct() {
		$this->pid = t3lib_div::_GP('pid');

		tslib_eidtools::connectDB();
		$this->initializeTSFE($this->pid);
	}
	
	/**
	 * Main function for handling AJAX-defined actions.
	 *
	 * @return		void
	 */
	public function processAction() {
		$this->gpvars = t3lib_div::_GP('tx_wecjournal');
		$this->userID = $this->gpvars['userid'];
		$this->record = $this->gpvars['record'];
		$msg = $this->gpvars['message'];
		$topic = $this->gpvars['topic'];
		$subtopic = $this->gpvars['subtopic'];
		
			// Call post processing function, if applicable.
		$cmd = $this->gpvars['cmd'];
		if ($cmd == 'save') {
			$this->saveItem($msg,$topic,$subtopic);
		}
		else if ($cmd == 'saveAndClose') {
			$this->saveAndCloseItem($msg,$topic,$subtopic);
		}
		else if ($cmd == 'load') {
			$this->loadItem($topic,$subtopic);
		}
		else if ($cmd == 'close') {
			$this->closeItem();
		}
		else if ($cmd == 'addNewEntry') {
			$this->addNewEntryItem($this->gpvars['addEntryField']);
		}
//t3lib_div::debug($this->gpvars,"gpvars=");		
		$this->addContent('cmd', $cmd);
		$this->addContent('userid', $this->userID);

			// Return output
		$this->render('json');
//		$this->render('plain');
	}
	
	/**
	 * AJAX response to a save and close action on a particular record.
	 *
	 * @param	string		Name of the table.
	 * @param	integer		UID of the record.
	 * @return	void
	 */
	protected function saveAndCloseItem($message, $topic, $subtopic='') {
		$this->saveItem($message,$topic, $subtopic);
		
		$this->addContent('content', ' ');
	}

	/**
	 * AJAX response to a save action on a particular record.
	 *
	 * @param	string		Message content
	 * @return	void
	 * @todo	Dave: allow more than tt_content to be saved here
	 */
	protected function saveItem($message, $topic, $subtopic='') {
		// save current value and topic to tx_wecjournal_content

		// if existing record exists, grab current data from db
		if ($this->record) { 
			$curData = $this->getRow($this->table,$this->record);
		}

		// check if topic has changed...if so, then create new record
		if ($this->gpvars['curtopic'] && ($this->gpvars['curtopic'] != $topic) ) {
			if ($this->record) { // rename
				$this->addContent('oldtopic', $this->gpvars['curtopic']);
				$curData['topic'] = $topic;
			}
			$this->addContent('topic', $topic);
		}
				
		// if no record, then create a new one [ else saves over existing one ]
		if (empty($curData)) {
			$this->record = 0;
			$curData['topic'] = $topic;
			$curData['subtopic'] = $subtopic;
			$curData['user_id'] = $this->userID;
			$curData['crdate'] = mktime();
		}
		$curData['content'] = $message;
		$curData['tstamp'] = mktime();
		$curData['pid'] = $this->pid;
		
//t3lib_div::debug($curData,"wrote to ".$this->table." uid=".$this->record);		
		if ($this->record) {
			$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table, 'uid='.$this->record, $curData);
		}
		else {
			$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery($this->table, $curData);
			$this->record = $GLOBALS['TYPO3_DB']->sql_insert_id($res);	
		}
		if (mysql_error()) t3lib_div::debug(mysql_error(),"db insert error");

		$this->addContent('record', $this->record);
		$this->addContent('content',' ');
	}

	/**
	 * AJAX response to a close action on a particular record.
	 *
	 * @return	void
	 */
	protected function closeItem() {
		// does nothing but return
		
		$this->addContent('content',' ');
	}
	
	/**
	 * AJAX response to a load latest entry for a given topic/subtopic
	 *
	 * @param	string		topic to load
	 * @param	string		subtopic to load
	 * @return	void
	 */
	protected function loadItem($topic,$subtopic='') {
		
		if (strlen($topic)) 
			$where .= 'topic="'.$topic.'"';
		if (strlen($subtopic)) {
			if ($where) $where .= ' AND ';
			$where .= 'subtopic="'.$subtopic.'"';
		}
		if (!$where)
			$where = '1=1';
//t3lib_div::debug($where,"where=");
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_wecjournal_content', $where,'','tstamp DESC');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);		
		
//t3lib_div::debug($row,"row=");		
		if ($row) {
			$this->addContent('record',$row['uid']);
			$this->addContent('content',$row['content']);
			$this->addContent('topic',$row['topic']);
			$this->addContent('subtopic',$row['subtopic']);
		}
		else {
			$this->addContent('record',0);
		}
	}
	
	/**
	 * AJAX response to add a new entry for journal
	 *
	 * @return	void
	 */
	protected function addNewEntryItem($title) {
		// check if existing record with same name
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->table, 'topic LIKE \'' . $title . '%\'', '', 'topic');
		$newData = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$newData = $row;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		// if existing record, then add number to title
		if (count($newData)) { // if existing record, then grab data
			$newData['topic'] = $title . '-1';
		}
		else {
			$newData['topic'] = $title;			
		}
		$newData['crdate'] = mktime();
		$newData['tstamp'] = mktime();
		unset($newData['uid']);
		
		$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery($this->table, $newData);
		$this->record = $GLOBALS['TYPO3_DB']->sql_insert_id($res);	
		if (mysql_error()) t3lib_div::debug(mysql_error(),"db insert error");

		$this->addContent('record', $this->record);
		$this->addContent('content',' ');
	}	
	
	/**
	 * Initialize the TYPO3 Frontend for a given page id.
	 * 
	 * @param		integer		The page id.
	 * @return		void
	 * @todo		feUserObj doesn't seem to be used.
	 */
	protected function initializeTSFE($pid, $feUserObj = '') {
		global $TSFE, $TYPO3_CONF_VARS;

			// include necessary classes:
			// Note: BEfunc is needed from t3lib_tstemplate
		require_once(PATH_t3lib . 'class.t3lib_page.php');
		require_once(PATH_t3lib . 'class.t3lib_tstemplate.php');
		require_once(PATH_t3lib . 'class.t3lib_befunc.php');
		require_once(PATH_tslib . 'class.tslib_fe.php');
		require_once(PATH_t3lib . 'class.t3lib_userauth.php');
		require_once(PATH_tslib . 'class.tslib_feuserauth.php');
		require_once(PATH_tslib . 'class.tslib_content.php');
		require_once(PATH_tslib . 'class.tslib_fe.php');

			// @todo 	jeff: don't include templavoila directly
		if (t3lib_extMgm::isLoaded('templavoila')) {	
			require_once(t3lib_extMgm::extPath('templavoila').'class.tx_templavoila_api.php');
		}

			// create object instances:
		$temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');
		$TSFE = new $temp_TSFEclassName($TYPO3_CONF_VARS, $pid, 0, true);

		$TSFE->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$TSFE->tmpl = t3lib_div::makeInstance('t3lib_tstemplate');
		$TSFE->tmpl->init();

			// fetch rootline and extract ts setup:
		$TSFE->rootLine = $TSFE->sys_page->getRootLine(intval($pid));
		$TSFE->getConfigArray();

			// then initialize fe user
		$TSFE->initFEuser();
		$TSFE->fe_user->fetchGroupData();
		
		
		$TT = new t3lib_timeTrack;
		$TT->start();

			// Include the TCA
		$TSFE->includeTCA();

			// Get the page
		$TSFE->fetch_the_id();
		$TSFE->getPageAndRootline();
		$TSFE->initTemplate();
		$TSFE->tmpl->getFileName_backPath = PATH_site;
		$TSFE->forceTemplateParsing = true;
		$TSFE->getConfigArray();

			// Get the Typoscript as its inherited from parent pages
		$template = t3lib_div::makeInstance('t3lib_tsparser_ext'); // Defined global here!
		$template->tt_track = 0;
		$template->init();
		$sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$rootLine = $sys_page->getRootLine($pid);
		$template->runThroughTemplates($rootLine); // This generates the constants/config + hierarchy info for the template.
		$template->generateConfig();

			// Save the setup
		$this->setup = $template->setup;

			// Including pagegen will make sure that extension PHP files are included
		require_once(PATH_tslib.'class.tslib_pagegen.php');
		include(PATH_tslib.'pagegen.php');
	}

	/**
	 * Gets the database row for a specific content element.
	 *
	 * @param	integer		UID of the content element.
	 * @return	array
	 */
	protected function getRow($table, $uid) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, 'uid=' . $uid);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return $row;
	}

	function render($type='plain') {
		if ($type == 'plain') {
			header('Content-type: text/html; charset='.$this->charset);
			header('X-JSON: true');
			echo implode('', $this->content);	
		}
		else if ($type == 'json') {
			$content = t3lib_div::array2json($this->content);
			header('Content-type: application/json; charset=' . $this->charset);
			header('X-JSON: true');			
			echo $content;
		}
	}
	
	public function addContent($key, $content) {
		$oldcontent = false;
		if (array_key_exists($key, $this->content)) {
			$oldcontent = $this->content[$key];
		}
		if (!isset($content) || !strlen($content)) {
			unset($this->content[$key]);
		} elseif (!isset($key) || !strlen($key)) {
			$this->content[] = $content;
		} else {
			$this->content[$key] = $content;
		}
		return $oldcontent;
	}	
}

	// exit, if script is called directly (must be included via eID in index_ts.php)
if (!defined ('PATH_typo3conf')) die ('Could not access this script directly!');

$wecJournalAjax = t3lib_div::makeInstance('tx_wecjournal_ajax');
$wecJournalAjax->processAction();
?>
