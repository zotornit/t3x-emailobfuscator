<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "emailobfuscator".
 *
 * Auto generated 30-01-2014 20:21
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Email Obfuscator',
	'description' => 'Replaces the default email address spam protection with a better one. The email obfuscation is more randomized, safer and more userfriendly for the website visitor.',
	'category' => 'fe',
	'shy' => 0,
	'version' => '1.1.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => '',
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Thomas Pronold',
	'author_email' => 'tp@tpronold.de',
	'author_company' => 'tpronold.de',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.3.0-5.999.999',
			'typo3' => '4.5.0-6.1.999',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:9:{s:9:"ChangeLog";s:4:"53b5";s:28:"class.tx_emailobfuscator.php";s:4:"74a7";s:21:"ext_conf_template.txt";s:4:"f9c5";s:12:"ext_icon.gif";s:4:"05cb";s:17:"ext_localconf.php";s:4:"393f";s:14:"ext_tables.php";s:4:"636b";s:53:"Resources/Public/Assets/Javascript/emailobfuscator.js";s:4:"dedd";s:14:"doc/manual.pdf";s:4:"53de";s:14:"doc/manual.sxw";s:4:"8afb";}',
	'suggests' => array(
	),
);

?>