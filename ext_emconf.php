<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "emailobfuscator".
 *
 * Auto generated 03-02-2014 22:45
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
	'version' => '4.0.0',
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
			'php' => '5.5.0-7.1.999',
			'typo3' => '7.6.0-8.8.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:17:{s:9:"ChangeLog";s:4:"15bd";s:21:"ext_conf_template.txt";s:4:"d988";s:12:"ext_icon.gif";s:4:"05cb";s:17:"ext_localconf.php";s:4:"f21c";s:21:"Classes/EmailLink.php";s:4:"8b69";s:27:"Classes/EmailObfuscator.php";s:4:"7f41";s:30:"Classes/EncryptedEmailLink.php";s:4:"b85c";s:22:"Classes/Obfuscator.php";s:4:"faa5";s:42:"Classes/Exception/InvalidLinkException.php";s:4:"6ed3";s:30:"Classes/Service/CSSService.php";s:4:"cf2d";s:53:"Resources/Public/Assets/Javascript/emailobfuscator.js";s:4:"8a91";s:28:"Tests/Unit/EmailLinkTest.php";s:4:"dfe0";s:37:"Tests/Unit/EncryptedEmailLinkTest.php";s:4:"7974";s:29:"Tests/Unit/ObfuscatorTest.php";s:4:"6027";s:49:"Tests/Unit/Exception/InvalidLinkExceptionTest.php";s:4:"2fab";s:14:"doc/manual.pdf";s:4:"53de";s:14:"doc/manual.sxw";s:4:"8afb";}',
	'suggests' => array(
	),
);

?>