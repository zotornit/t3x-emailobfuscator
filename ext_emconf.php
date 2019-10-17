<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Email Obfuscator',
	'description' => 'Replaces the default email address spam protection with a better one. The email obfuscation is more randomized, safer and more user friendly for the website visitor.',
	'category' => 'fe',
	'shy' => 0,
	'version' => '5.0.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'clearcacheonload' => 1,
	'author' => 'Thomas Pronold',
	'author_email' => 'tp@zotorn.de',
	'author_company' => 'Zotorn IT',
	'constraints' => array(
		'depends' => array(
			'php' => '7.1.0-7.3.999',
			'typo3' => '8.7.0-9.5.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'autoload' => [
		'psr-4' => [
			'ZOTORN\\EmailObfuscator\\' => 'Classes'
		]
	],
);

?>
