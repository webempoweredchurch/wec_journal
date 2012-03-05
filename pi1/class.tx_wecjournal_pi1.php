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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'WEC Journal' for the 'wec_journal' extension.
 *
 * @author	David Slayback <dave@webempoweredchurch.org>
 * @package	TYPO3
 * @subpackage	tx_wecjournal
 */
class tx_wecjournal_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_wecjournal_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_wecjournal_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'wec_journal';	// The extension key.
	var $pi_checkCHash = true;
	var $journalTable 	= 'tx_wecjournal_content';

	var $curEntry;		// current entry in journal
	var $topicList;		// list of topics
	var $subtopicList;	// list of subtopics

	// Frontend RTE variables
	var $RTEObj;
	var $docLarge = 0;
	var $RTEcounter = 0;
	var $formName;
	var $additionalJS_initial = '';// Initial JavaScript to be printed before the form (should be in head, but cannot due to IE6 timing bug)
	var $additionalJS_pre = array();// Additional JavaScript to be printed before the form (works in Mozilla/Firefox when included in head, but not in IE6)
	var $additionalJS_post = array();// Additional JavaScript to be printed after the form
	var $additionalJS_submit = array();// Additional JavaScript to be executed on submit
	var $PA = array(
		'itemFormElName' =>  '',
		'itemFormElValue' => '',
	);
	var $specConf = array(
		'rte_transform' => array(
		'parameters' => array('mode' => 'ts_css')
		)
	);
	var $thisConfig = array();
	var $RTEtypeVal = 'text';


	function init($conf) {
		if (!$this->cObj) $this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->conf = $conf; // Storing configuration as a member var
		$this->pi_loadLL();
//		$GLOBALS['TSFE']->set_no_cache();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		$this->pi_setPiVarDefaults(); // Set default piVars from TS
		$this->pi_initPIflexForm();		// Initialize the FlexForms array
		$this->url = $this->pi_getPageLink($GLOBALS['TSFE']->id,$GLOBALS['TSFE']->sPre); //Current URL
		$this->config = $this->setFFconfig(); // Set the Flexform and TypoScript values

		$this->userID = $GLOBALS['TSFE']->fe_user->user['uid'];
		$this->userName = $GLOBALS['TSFE']->fe_user->user['username'];

		// set up template
		$this->templateCode = $this->config['templateCode'];

		// set incoming POST vars
		$this->postvars = t3lib_div::_GP('tx_wecjournal');

			// load prototype/scriptaculous for FE editing and AJAX
		$cssFile = $this->conf['cssFile'] ? $this->conf['cssFile'] : t3lib_extMgm::siteRelPath('wec_journal') . 'res/journal.css';
		$GLOBALS['TSFE']->additionalHeaderData['wecjournal'] .= '<link href="' . $cssFile . '" rel="stylesheet" type="text/css" />';;
		$GLOBALS['TSFE']->additionalHeaderData['wecjournal'] .= '<script type="text/javascript" src="typo3/contrib/prototype/prototype.js"></script>';
		$GLOBALS['TSFE']->additionalHeaderData['wecjournal'] .= '<script type="text/javascript" src="typo3/contrib/scriptaculous/scriptaculous.js"></script>';
		$this->loadJournal();

		$this->loadHooks();

		$this->printJournal();

		$GLOBALS['TSFE']->additionalHeaderData['wecjournal'] .= '<script type="text/javascript" src="' . t3lib_extMgm::siteRelPath('wec_journal') . 'res/journal.js"></script>';
	}

	/**
	 * The main method of the Plugin -- initialize and draw the journal
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->init($conf);
	    if ($conf['isLoaded'] != 'yes')
	      return $this->pi_getLL('errorIncludeStatic');
		if (!$this->userID) {
			return $this->pi_getLL('no_user');
		}

		// load in template and fill in markers
		$template = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_JOURNAL###');
		$this->fillMarkerArray();

		// then do the substitution with the template
		$content = $this->cObj->substituteMarkerArrayCached($template, $this->marker, $this->subpartMarker, array());
		// clear out any empty template fields
		$content = preg_replace('/###.*?###/', '', $content);
		
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Define all possible fields from TypoScript and FlexForm.
	 *
     * @return	array       Configuration array made from TypoScript and FlexForm
	 */
    function setFFconfig() {
		// config: name, flexform sheet, flexform field, type(1=file, 2=), vDEF)
        $arrFFConfig = array(
            'templateCode'        => array('template_file', 'sDEF', 'templateFile', 1),
            'pid'                 => array('pages', 		'sDEF', 'pid',	3),
        );
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$this->prefixId]['setFFconfig'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$this->prefixId]['setFFconfig'] as $_funcRef) {
				$arrSelectConf = t3lib_div::callUserFunction($_funcRef,$arrFFConfig, $this);
			}
		}
        return $arrOutput = $this->getFFconfig($arrFFConfig);
    }

    /**
	 * Check configuration in TypoScript and FlexForm.
	 * FlexForm has precendence over TypoScript
	 *
	 * @param	array		Definition array for TypoScipt and FlexForm
	 * @return	array       Configuration array made from TypoScript and FlexForm
	 */
    function getFFconfig($arrFFConfig) {
    	$strTemp = '';
        foreach ($arrFFConfig as $strKey => $arrItem) {
        	$strValue = !empty($arrItem[4]) ? $arrItem[4] : 'vDEF';
            $strFFValue = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $arrItem[0], $arrItem[1], 'lDEF', $strValue);
            $arrItem[2] = "['" . str_replace('.',".']['",$arrItem[2]) . "']";
            eval("\$strTemp = \$this->conf".$arrItem[2].";");
            if ($arrItem[3]==1) { // file
                $arrOutput[$strKey] = $this->cObj->fileResource($strFFValue ? 'uploads/tx_wecjournal/' . $strFFValue : $strTemp);
            } elseif ($arrItem[3]==3) { // pid value
            	$arrOutput[$strKey] = ($strFFValue!='') ? $strFFValue : $strTemp;
            	$arrOutput[$strKey] = $arrOutput[$strKey] ? $arrOutput[$strKey] : $GLOBALS['TSFE']->id;
            } else { // integer or string value
				// use the TS constant first, then the flexform
                $arrOutput[$strKey] = ($strTemp != '') ? $strTemp : $strFFValue;
				//$arrOutput[$strKey] = ($strFFValue != '') ? $strFFValue : $strTemp;
            }
        }
        return $arrOutput;
    }

	function setupJournal() {
		// setup RTE if enabled
		if(!$this->conf['RTEenabled'])
			$this->RTEObj = 0;
		else if (!$this->RTEObj && t3lib_extMgm::isLoaded('rtehtmlarea')) {
			require_once(t3lib_extMgm::extPath('rtehtmlarea').'pi2/class.tx_rtehtmlarea_pi2.php');
			$this->RTEObj = t3lib_div::makeInstance('tx_rtehtmlarea_pi2');
		} elseif (!$this->RTEObj && t3lib_extMgm::isLoaded('tinymce_rte')) {
			require_once(t3lib_extMgm::extPath('tinymce_rte').'pi1/class.tx_tinymce_rte_pi1.php');
			$this->RTEObj = t3lib_div::makeInstance('tx_tinymce_rte_pi1');
//		} elseif (!$this->RTEObj && $this->conf['RTEenabled'] && t3lib_extMgm::isLoaded('tinyrte')) {
//			require_once(t3lib_extMgm::extPath('tinyrte').'class.tx_tinyrte_base.php');
//			$this->RTEObj = t3lib_div::makeInstance('tx_tinyrte_base');
		} else {
			$this->RTEObj = 0;
		}
	}

	function fillMarkerArray() {
		$this->setupJournal();

		$firstName = $GLOBALS['TSFE']->fe_user->user['first_name'] ? $GLOBALS['TSFE']->fe_user->user['first_name'] : $GLOBALS['TSFE']->fe_user->user['username'];
		if (strlen($firstName)) $firstName .= '\'s';
		$this->marker['###JOURNAL_NAME###'] = ($firstName ? $firstName : 'My ') . ' Journal';
		$this->marker['###JOURNAL_BUTTON_IMAGE###'] = $this->conf['journalBtnImage'] ? $this->conf['journalBtnImage'] :  t3lib_extMgm::siteRelPath('wec_journal') . 'res/img/scrollbtn.png';

		// add topic list
		$curTopic = ($this->curEntry) ? $this->curEntry['topic'] : '';
		$this->marker['###CHOOSE_TOPIC_LABEL###'] = $this->pi_getLL('choose_topic_label','Choose Entry: ');
		$onchangeHref = $this->getAbsoluteURL($GLOBALS['TSFE']->id);
//		$topicSelect = '<select class="chooseTopic" id="chooseTopic" size="1" onchange="if (this.selectedIndex >= 0) window.location.href=\''.$onchangeHref.'\'+this.options[this.selectedIndex].value;">';
		$topicSelect = '<select class="chooseTopic" id="chooseTopic" size="1">';
		if ($this->topicList) {
			foreach ($this->topicList as $tpc) {
//				$paramArray['tx_wecjournal[topic]'] = htmlspecialchars($tpc,ENT_QUOTES);
//				$topicURL = $this->getAbsoluteURL($GLOBALS['TSFE']->id, $paramArray);
				$topicSelect .= '<option value="' . htmlspecialchars($tpc,ENT_QUOTES) . '" '. (($curTopic == $tpc) ? ' selected' : '') .'>'.$tpc.'</option>';
			}
		}
		$topicSelect .= '<option value="-1"'.(($curTopic == -1) ? ' selected' : '') . '>' . $this->pi_getLL('topic_none','None') . '</option>';
		$topicSelect .= '</select>';
		$this->marker['###TOPIC_SELECT_DROPDOWN###'] = $topicSelect;

		// Add Topic field
		$this->marker['###CURRENT_TOPIC_LABEL###'] = $this->pi_getLL('current_topic_label','Title: ');
		$this->marker['###ADD_ENTRY_FORM###'] = '<input type="text" name="tx_wecjournal[topic]" id="journalTopicField" />';

		// add subtopic list
		$curSubtopic = ($this->curEntry) ? $this->curEntry['subtopic'] : '';
		$this->marker['###SUBTOPIC_LABEL###'] = $this->pi_getLL('subtopic_label','Subtopic: ');
		if (count($this->subtopicList)) {
//			$subtopicSelect = '<select class="chooseSubtopic" size="1" onchange="if (this.selectedIndex >= 0) window.location.href=this.options[this.selectedIndex].value;">';
			$subtopicSelect = '<select class="chooseSubtopic" size="1"';
			foreach ($this->subtopicList as $tpc) {
				$paramArray['tx_wecjournal[subtopic]'] = $tpc;
				$subtopicSelect .= '<option value="'.$this->getAbsoluteURL($GLOBALS['TSFE']->id, $paramArray).'" '. (($curSubtopic == $tpc) ? ' selected' : '') .'>'.$tpc.'</option>';
			}
			$subtopicSelect .= '</select>';
			$this->marker['###SUBTOPIC_SELECT_DROPDOWN###'] = $subtopicSelect;
		}

		// Add hidden fields
		$curRec = $this->curEntry ? $this->curEntry['uid'] : '';
		$hiddenFields['cmd'] = '';
		$hiddenFields['curtopic'] = $curTopic;
		$hiddenFields['subtopic'] = $curSubtopic;
		$hiddenFields['record'] = $curRec;
		$hiddenFields['userid'] = $this->userID;
		$hiddenFields['pid'] = $GLOBALS['TSFE']->id;

		// if setHiddenFields hook, then send var to add to or clear
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_wecjournal']['setHiddenFields'])) {
			$hooks =& $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_wecjournal']['setHiddenFields'];
			$hookParameters = &$hiddenFields;
			foreach ($hooks as $hookFunction)	{
				t3lib_div::callUserFunction($hookFunction, $hookParameters, $this);
			}
		}
		foreach ($hiddenFields as $hfieldName=>$hfieldValue) {
			$this->marker['###JOURNAL_HIDDEN_FIELDS###'] .= '<input type="hidden" name="tx_wecjournal[' . $hfieldName . ']" value="' . $hfieldValue . '" />';
		}
		$this->marker['###JOURNAL_HIDDEN_FIELDS###'] .= '<input type="hidden" name="wecjournal_size" id="wecjournal_size" value="' . strlen(strip_tags($this->curEntry['content'])). '" />';

		// Add Front-End RTE editing
		if(true && is_object($this->RTEObj) && $this->RTEObj->isAvailable()) {
			$this->RTEcounter++;
			$this->table = 'tx_wecjournal';
			$this->field = 'message';
			$this->formName = 'journalForm';
			$this->PA['itemFormElName'] = 'tx_wecjournal[message]';
			$msg = "";

			if (t3lib_div::_GP('tx_wecjournal[message]'))
				$msg = t3lib_div::_GP('tx_wecjournal[message]');
			else if ($this->curEntry)
				$msg = $this->curEntry['content'];
			$this->PA['itemFormElValue'] = $msg;
			$this->thePidValue = $GLOBALS['TSFE']->id;

//			$this->RTEObj->RTEdivStyle  = $this->conf['RTEfontSize'] ? 'font-size:'.$this->conf['RTEfontSize'].';' : '';
			$this->conf['RTEheight'] = '280px';
			$this->conf['RTEwidth'] = '300px';
			$this->RTEObj->RTEdivStyle .= $this->conf['RTEheight'] ? 'height:' . $this->conf['RTEheight'] . ';' : '';
			$this->RTEObj->RTEdivStyle .= $this->conf['RTEwidth']  ? 'width:'  . $this->conf['RTEwidth']  . ';' : '';
			$this->RTEObj->RTEdivStyle .= 'background-color:#e0d0b0;';
			$RTEItem = $this->RTEObj->drawRTE($this, $this->table, $this->field, $row=array(), $this->PA, $this->specConf, $this->thisConfig, $this->RTEtypeVal, '', $this->thePidValue);
			$this->marker['###RTE_PRE_FORM###'] = $this->additionalJS_initial . '
				<script type="text/javascript">' . implode(chr(10), $this->additionalJS_pre) . '
					</script>';
			$this->marker['###RTE_POST_FORM###'] = '
				<script type="text/javascript">' . implode(chr(10), $this->additionalJS_post) . '
					</script>';
			$this->marker['###RTE_SUBMIT###'] = implode(';', $this->additionalJS_submit);
			$this->marker['###RTE_FORM_ENTRY###'] = $RTEItem;

			$this->subpartMarker['###SHOW_MESSAGE_TEXTAREA###'] = '';
		}
		else {
			if ($this->curEntry)
				$this->marker['###VALUE_MESSAGE###'] = $this->curEntry['content'];

			$this->subpartMarker['###SHOW_MESSAGE_RTE###'] = '';
		}

		// Set WINDOW POSITION and SIZE, if in cookie
		$windowStyle = '';
		global $HTTP_COOKIE_VARS;
		$journalCookie = isset($HTTP_COOKIE_VARS['tx_wecjournal']) ? stripslashes($HTTP_COOKIE_VARS['tx_wecjournal']) : '';
		if ($journalCookie) {
			$journalCookie = t3lib_div::trimExplode('|',$journalCookie);
			$xVal = $yVal = $widthVal = $heightVal = $visibilityVal = -1;
			foreach ($journalCookie as $jVal) {
				if (($p = strpos($jVal,"x=")) !== FALSE) {
					$xVal = (int) substr($jVal,$p+2);
				}
				else if (($p = strpos($jVal,'y=')) !== FALSE) {
					$yVal = (int) substr($jVal,$p+2);
				}
				else if (($p = strpos($jVal,'wd=')) !== FALSE) {
					$widthVal = (int) substr($jVal,$p+3);
					if ($widthVal == 'null')
						$widthVal = -1;
				}
				else if (($p = strpos($jVal,'ht=')) !== FALSE) {
					$heightVal = (int) substr($jVal,$p+3);
					if ($heightVal == 'null') {
						$heightVal = -1;
					}
				}
				else if (($p = strpos($jVal,'vis=')) !== FALSE) {
					$visibilityVal = substr($jVal,$p+4);
				}
			}
			if ($xVal != -1) 	$windowStyle .= 'left:' . $xVal . 'px;right:auto;';
			if ($yVal != -1) 	$windowStyle .= 'top:'  . $yVal . 'px;';
//			if ($widthVal >= 0) $windowStyle .= 'width:'. $widthVal . 'px;';
//			if ($heightVal >= 0) $windowStyle .= 'height:'.$heightVal . 'px;';
			if ($visibilityVal >= 0) $windowStyle .= 'display:'.(($visibilityVal && ($visibilityVal != 'none')) ? 'block' : 'none').';';
//			if (strlen($windowStyle)) $windowStyle .= 'position:absolute;';
		}
		else {
			$windowStyle = 'display:none;';
		}

		$this->marker['###JOURNAL_WINDOW_STYLE###'] = $windowStyle;
	}


	// Loads the latest entry for the current user. Also loads in all topics and subtopics
	//
	function loadJournal() {
		if (!$this->userID)
			return;

		$where = 'user_id='.$this->userID;
		$where .= $this->cObj->enableFields($this->journalTable);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->journalTable, $where, '', 'tstamp DESC');
		if (mysql_error()) t3lib_div::debug(array(mysql_error(), $res),$lwhere);
		$this->curEntry = 0;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if (!$this->curEntry)
				$this->curEntry = $row;
			// add to topics
			if ($t = ($row['topic'])) {
				$this->topicList[] = $t;
			}
			if ($t = $row['subtopic']) {
				$this->subtopicList[] = $t;
			}
		}
	}

	// Prints various entries from the journal
	//
	function printJournal() {

		// allow to specify the printjournal.php file or use one in extension
		if ($this->conf['printjournalfile'])
			$printJournalFile = $this->conf['printjournalfile'];
		else {
			$dn = dirname($_SERVER['PHP_SELF']);
			if (($dn != '/') && ($dn != '\\'))
				$printJournalFile = 'http://'.$_SERVER['HTTP_HOST'].$dn.'/';
			else
				$printJournalFile = 'http://'.$_SERVER['HTTP_HOST'].'/';
			$printJournalFile .= t3lib_extMgm::siteRelPath('wec_journal').'pi1/printjournal.php';
		}
		// allow to configure popup for printjournal
		if ($this->conf['printjournalfile_popup'])
			$printJournalFile_popup = $this->conf['printjournalfile_popup'];
		else
			$printJournalFile_popup = 'width=350,height=300,left="+((screen.width - 350) /2)+", top="+((screen.height - 300) / 2)+",,scrollbars=0, resizable=0,toolbar=0,location=0,status=0,menubar= 0';

		$GLOBALS['TSFE']->additionalHeaderData['wecjournal'] .= '
			<script type="text/javascript">
			  <!--
				function printJournalMenu() {
					printwin = window.open("'.$printJournalFile.'", "printwin","'.$printJournalFile_popup.'");
					printwin.focus();
					if (!printwin.opener) printwin.opener = self;
				}
				
				function doprint(start,end) {
					windowLoc = window.location.href;
					if  ((st = windowLoc.indexOf("printstart")) > 0)  // strip off params if already there
						windowLoc = windowLoc.substr(0,st-1);
					if (windowLoc.charAt(windowLoc.length-1) != \'/\') // add ending / if not there for realURL
						windowLoc += \'/\';
					if  ((st = windowLoc.indexOf(\'?\')) > 0) // if already has a front param
						location.href=windowLoc+\'&printstart=\'+start+\'&printend=\'+end;
					else
						location.href=windowLoc+\'?printstart=\'+start+\'&printend=\'+end;
				}

				function checkIfShouldSave(saveStr) {

					return false;
				}

				function printJournal() {
					if (!checkIfShouldSave("'.$this->pi_getLL('print_journal_saveBefore','Do you want to save before you print?').'")) {
						printJournalMenu();
					}
				}
			// -->
		   </script>
		';

/*
		if ($this->postvars['printj'] == 1) {
			// do popup with:
			//		- all entries
			//		- last 20 entries
			//		- last 10 entries
			//		- this entry
			//

			$printStr = 'window'


		}
		// when popup returns, then print out in a new window the given # of entries
		else if ($w = $this->postvars['printwhich']) {
			$howMany = $w;
			if ($howMany == 'all') {
				// limit to 500...if print them all out.
				$howMany = 500;
			}

			$where = 'user_id='.$this->userID;
			$where .= $this->cObj->enableFields($this->journalTable);
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->journalTable, $where, '', 'tstamp DESC', '', $howMany);
			if (mysql_error()) t3lib_div::debug(array(mysql_error(), $res),$where);
			$printStr = '';
			$curTopic = '';
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				if ($curTopic != $row['topic']) {
					$printStr .= '<h1>' . $row['topic'] . '</h1>';
					$curTopic = $row['topic'];
				}
				$printStr .= $row['content'];
			}

			if (strlen($printStr)) {
				echo $printStr;
			}
		}
*/

	}

	// Hook in various topics or subtopics
	function loadHooks() {
		// if topic hook, then process. A topic hook will fill in the topics. This means that will be restricted to given topics.
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_wecjournal']['addTopics'])) {
			$hooks =& $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_wecjournal']['addTopics'];
			$hookParameters = array('topicList' => &$this->topicList);
			foreach ($hooks as $hookFunction)	{
				t3lib_div::callUserFunction($hookFunction, $hookParameters, $this);
			}
		}

		// if subtopic hook, then process. A subtopic hook will fill in the subtopics. This wil restrict to given subtopics.
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_wecjournal']['addSubtopics'])) {
			$hooks =& $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_wecjournal']['addSubtopics'];
			$hookParameters = array('subtopicList' => &$this->subtopicList);
			foreach ($hooks as $hookFunction)	{
				t3lib_div::callUserFunction($hookFunction, $hookParameters, $this);
			}
		}

	}

	/**
	* Getting the full URL (ie. http://www.host.com/... to the given ID with all needed params
	* This function handles cross-site (on same server) links
	*
	* @param integer  $id: Page ID
	* @param string   $urlParameters: array of parameters to include in the url (i.e., "$urlParameters['action'] = 4" would append "&action=4")
	* @return string  $url: URL
	*/
	function getAbsoluteURL($id, $extraParameters = '') {
		$pageURL = $this->pi_getPageLink($id, '', $extraParameters);

		// if did not cross page boundaries, then generate url from info
		if (strpos($pageURL,"http") === FALSE) {
			$serverProtocol = t3lib_div::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://';
			$absURL = $serverProtocol . $_SERVER['HTTP_HOST'] . '/' . $pageURL;
		}
		else // crosses boundaries (likely different url on same server)
			$absURL = $pageURL;

		$absURL = str_replace('&','&amp;', $absURL);
		return $absURL;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_journal/pi1/class.tx_wecjournal_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_journal/pi1/class.tx_wecjournal_pi1.php']);
}

?>