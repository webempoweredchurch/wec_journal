<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_wecjournal_content'] = array (
	'ctrl' => $TCA['tx_wecjournal_content']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,content,topic,subtopic,user_id'
	),
	'feInterface' => $TCA['tx_wecjournal_content']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'content' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_journal/locallang_db.xml:tx_wecjournal_content.content',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
				'wizards' => array(
					'_PADDING' => 2,
					'RTE' => array(
						'notNewRecords' => 1,
						'RTEonly'       => 1,
						'type'          => 'script',
						'title'         => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
						'icon'          => 'wizard_rte2.gif',
						'script'        => 'wizard_rte.php',
					),
				),
			)
		),
		'topic' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_journal/locallang_db.xml:tx_wecjournal_content.topic',		
			'config' => array (
				'type' => 'input',	
				'size' => '24',	
				'max' => '127',
			)
		),
		'subtopic' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_journal/locallang_db.xml:tx_wecjournal_content.subtopic',		
			'config' => array (
				'type' => 'input',	
				'size' => '24',	
				'max' => '127',
			)
		),
		'user_id' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_journal/locallang_db.xml:tx_wecjournal_content.user_id',		
			'config' => array (
				'type'     => 'input',
				'size'     => '4',
				'max'      => '4',
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array (
					'upper' => '1000',
					'lower' => '10'
				),
				'default' => 0
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, content;;;richtext[]:rte_transform[mode=ts], topic, subtopic, user_id')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>