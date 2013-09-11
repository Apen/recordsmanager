<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "recordsmanager".
 *
 * Auto generated 11-06-2013 15:47
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Records management in a BE module',
	'description' => 'Add modules to easily manage your records (insert, edit & export in be/eId) in one place.',
	'category' => 'module',
	'shy' => 0,
	'version' => '1.1.4',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'CERDAN Yohann [Site-nGo]',
	'author_email' => 'cerdanyohann@yahoo.fr',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.0.0-0.0.0',
			'typo3' => '4.5.0-6.0.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:59:{s:9:"ChangeLog";s:4:"1fa8";s:21:"ext_conf_template.txt";s:4:"999f";s:12:"ext_icon.gif";s:4:"914a";s:17:"ext_localconf.php";s:4:"ab6f";s:14:"ext_tables.php";s:4:"2973";s:14:"ext_tables.sql";s:4:"e557";s:37:"Classes/Controller/EditController.php";s:4:"310e";s:39:"Classes/Controller/ExportController.php";s:4:"7f7e";s:39:"Classes/Controller/InsertController.php";s:4:"7182";s:21:"Classes/Eid/Index.php";s:4:"36d7";s:51:"Classes/Hooks/class.tx_recordsmanager_callhooks.php";s:4:"66fe";s:26:"Classes/Utility/Config.php";s:4:"4e2b";s:29:"Classes/Utility/Powermail.php";s:4:"ff70";s:25:"Classes/Utility/Query.php";s:4:"c748";s:37:"Classes/ViewHelpers/RawViewHelper.php";s:4:"fd93";s:49:"Classes/ViewHelpers/Widget/PaginateViewHelper.php";s:4:"3e70";s:60:"Classes/ViewHelpers/Widget/Controller/PaginateController.php";s:4:"1b0f";s:28:"Configuration/Tca/Config.php";s:4:"e5ee";s:34:"Configuration/TypoScript/setup.txt";s:4:"4aba";s:46:"Resources/Private/Backend/Layouts/Default.html";s:4:"5891";s:51:"Resources/Private/Backend/Templates/Edit/Index.html";s:4:"233e";s:53:"Resources/Private/Backend/Templates/Export/Index.html";s:4:"e6a0";s:53:"Resources/Private/Backend/Templates/Insert/Index.html";s:4:"34f8";s:40:"Resources/Private/Language/locallang.xml";s:4:"8305";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"cc9c";s:58:"Resources/Private/Php/class.tx_recordsmanager_flexfill.php";s:4:"4542";s:52:"Resources/Private/Php/php_writeexcel-0.3.0/CHANGELOG";s:4:"3434";s:78:"Resources/Private/Php/php_writeexcel-0.3.0/class.writeexcel_biffwriter.inc.php";s:4:"b171";s:74:"Resources/Private/Php/php_writeexcel-0.3.0/class.writeexcel_format.inc.php";s:4:"69fd";s:75:"Resources/Private/Php/php_writeexcel-0.3.0/class.writeexcel_formula.inc.php";s:4:"74a1";s:77:"Resources/Private/Php/php_writeexcel-0.3.0/class.writeexcel_olewriter.inc.php";s:4:"188f";s:76:"Resources/Private/Php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php";s:4:"fc6b";s:79:"Resources/Private/Php/php_writeexcel-0.3.0/class.writeexcel_workbookbig.inc.php";s:4:"a487";s:77:"Resources/Private/Php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php";s:4:"afe0";s:50:"Resources/Private/Php/php_writeexcel-0.3.0/CONTACT";s:4:"c267";s:62:"Resources/Private/Php/php_writeexcel-0.3.0/example-bigfile.php";s:4:"ac45";s:61:"Resources/Private/Php/php_writeexcel-0.3.0/example-colors.php";s:4:"b834";s:59:"Resources/Private/Php/php_writeexcel-0.3.0/example-demo.php";s:4:"944f";s:61:"Resources/Private/Php/php_writeexcel-0.3.0/example-merge2.php";s:4:"2d8d";s:60:"Resources/Private/Php/php_writeexcel-0.3.0/example-panes.php";s:4:"a858";s:61:"Resources/Private/Php/php_writeexcel-0.3.0/example-repeat.php";s:4:"f748";s:61:"Resources/Private/Php/php_writeexcel-0.3.0/example-simple.php";s:4:"a531";s:61:"Resources/Private/Php/php_writeexcel-0.3.0/example-stocks.php";s:4:"26d1";s:63:"Resources/Private/Php/php_writeexcel-0.3.0/example-textwrap.php";s:4:"33fe";s:46:"Resources/Private/Php/php_writeexcel-0.3.0/FAQ";s:4:"ec96";s:79:"Resources/Private/Php/php_writeexcel-0.3.0/functions.writeexcel_utility.inc.php";s:4:"ca81";s:49:"Resources/Private/Php/php_writeexcel-0.3.0/HEADER";s:4:"e6b3";s:51:"Resources/Private/Php/php_writeexcel-0.3.0/HOMEPAGE";s:4:"5a02";s:50:"Resources/Private/Php/php_writeexcel-0.3.0/LICENSE";s:4:"7fbc";s:50:"Resources/Private/Php/php_writeexcel-0.3.0/php.bmp";s:4:"de9a";s:49:"Resources/Private/Php/php_writeexcel-0.3.0/README";s:4:"4e69";s:49:"Resources/Private/Php/php_writeexcel-0.3.0/THANKS";s:4:"5969";s:47:"Resources/Private/Php/php_writeexcel-0.3.0/TODO";s:4:"3bb0";s:66:"Resources/Private/Templates/ViewHelpers/Widget/Paginate/Index.html";s:4:"62e0";s:31:"Resources/Public/Icons/edit.gif";s:4:"983a";s:33:"Resources/Public/Icons/export.gif";s:4:"7111";s:56:"Resources/Public/Icons/icon_tx_recordsmanager_config.gif";s:4:"914a";s:33:"Resources/Public/Icons/insert.gif";s:4:"448f";s:14:"doc/manual.sxw";s:4:"cb29";}',
	'suggests' => array(
	),
);

?>