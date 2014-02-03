<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Thomas Pronold (tp@tpronold.de)
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

$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all']['tx_emailobfuscator']
    = 'EXT:emailobfuscator/Classes/EmailObfuscator.php:&tx_emailobfuscator->init';

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']['tx_emailobfuscator']
    = 'EXT:emailobfuscator/Classes/Service/AddCSS.php:&Tx_Emailobfuscator_Service_AddCSS->init';

t3lib_extMgm::addTypoScriptSetup('page.includeJS.emailobfuscator = EXT:emailobfuscator/Resources/Public/Assets/Javascript/emailobfuscator.js');




//TODO userwörter ingaben prüfen, bzw ignoreren falls javascript reserved word.
//TODO logging
//TODO default spam dedection

//TODO den ganzen conf sachen adden, userdefinded css usw
//TODO XHTML