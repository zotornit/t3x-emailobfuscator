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

$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output']['tx_emailobfuscator']
    = 'EXT:emailobfuscator/Classes/EmailObfuscator.php:&tx_emailobfuscator->init';

$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['headerNoCache']['tx_emailobfuscator']
    = 'EXT:emailobfuscator/Classes/Service/CSSService.php:&Tx_Emailobfuscator_Service_CSSService->addAllowedSelectorsToCSSDefaultStyle';

t3lib_extMgm::addTypoScriptSetup('page.includeJS.emailobfuscator = EXT:emailobfuscator/Resources/Public/Assets/Javascript/emailobfuscator.js');