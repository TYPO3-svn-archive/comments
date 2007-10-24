<?php
// $Id: $
if (!defined ('TYPO3_MODE')) die('Access denied.');

$TCA['tx_comments_comments'] = array(
	'ctrl' => $TCA['tx_comments_comments']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'content,firstname,lastname,email,location,homepage,remote_addr',
		'maxDBListItems' => 60,
	),
	'columns' => array(
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'external_ref' => array(
			'label' => 'LLL:EXT:comments/locallang_db.xml:tx_comments_comments.external_ref',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'prepand_tname' => true,
				'allowed' => '*',
				'minsize' => 1,
				'maxsize' => 1,
				'size' => 1,
			),
		),
		'external_prefix' => array(
			'label' => 'LLL:EXT:comments/locallang_db.xml:tx_comments_comments.external_prefix',
			'config' => array(
				'type' => 'input',
				'size' => 15,
				'eval' => 'trim,alphanum_x',
			),
		),
		'approved' => array(
			'label' => 'LLL:EXT:comments/locallang_db.xml:tx_comments_comments.approved',
			'config' => array(
				'type' => 'check',
				'items' => array(
					array('LLL:EXT:comments/locallang_db.xml:tx_comments_comments.approved.I.0', '')
				),
				'default' => 0,
			),
		),
		'firstname' => array(
			'label' => 'LLL:EXT:comments/locallang_db.xml:tx_comments_comments.firstname',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim,required',
			),
		),
		'lastname' => array(
			'label' => 'LLL:EXT:comments/locallang_db.xml:tx_comments_comments.lastname',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim',
			),
		),
		'email' => array(
			'label' => 'LLL:EXT:comments/locallang_db.xml:tx_comments_comments.email',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim',
			),
		),
		'homepage' => array(
			'label' => 'LLL:EXT:comments/locallang_db.xml:tx_comments_comments.homepage',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim',
			),
		),
		'location' => array(
			'label' => 'LLL:EXT:comments/locallang_db.xml:tx_comments_comments.location',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim',
			),
		),
		'content' => array(
			'label' => 'LLL:EXT:comments/locallang_db.xml:tx_comments_comments.content',
			'config' => array(
				'type' => 'text',
				'wrap' => 'virtual',
				'cols' > 48,	// full form width
				'rows' => 15,
			),
		),
		'remote_addr' => array(
			'label' => 'LLL:EXT:comments/locallang_db.xml:tx_comments_comments.remote_addr',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim,required,is_in',
				'is_in' => '0123456789.',
			),
		),
		'double_post_check' => array(
			'label' => '',
			'config' => array(
				'type' => 'passthrough'
			)
		)
	),
	'types' => array(
		0 => array('showitem' => 'hidden;;;;1,approved;;;;2-2-2,firstname;;;;3-3-3,lastname,email,homepage,location,content,remote_addr,external_ref;;;;5-5-5,external_prefix'),
	),
);
?>