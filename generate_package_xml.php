<?php

	$make = false;
	require_once('PEAR/PackageFileManager.php');

	$pkg = new PEAR_PackageFileManager;

	// directory that PEAR CVS is located in
	$cvsdir  = '/cvs/pear/';
	$packagedir = $cvsdir . 'Net_FTP/';
	$category = 'Networking';	
	
	$e = $pkg->setOptions(
		array('baseinstalldir' => '',
		      'summary' => 'Net_FTP provides an OO interface to the PHP FTP functions plus some additions',
		      'description' => 'Net_FTP allows you to communicate with FTP servers in a more comfortable way
		                        than the native FTP functions of PHP do. The class implements everything nativly
		                        supported by PHP and additionally features like recursive up- and downloading,
		                        dircreation and chmodding. It although implements an observer pattern to allow
		                        for example the view of a progress bar.',
		      'version' => '1.3.0beta2',
	          'packagedirectory' => $packagedir,
	          'pathtopackagefile' => $packagedir,
              'state' => 'beta',
              'filelistgenerator' => 'cvs',
              'notes' => 'This release ads new features and fixes all open bugs.
Changelog:
-----------
              
Fixes:

* Added patch by Ilja Polivanovas <ipa@assis.lt> to enable correct directory permissions.
* Added rename() functionality. Thanks to the unnamed coder from "Webmestre ESGI" <wmaster_esgi@hotmail.com>.

Enhancements:

* Added rename() method.
              
Todo:
-----
              
* Extensive testing.
* Add example for observer.
',
			  'package' => 'Net_FTP',
			  'dir_roles' => array(
			  		'example' => 'doc'),
		      'ignore' => array('package.xml',
		                        'doc*', 
		                        'generate_package_xml.php',
		                        '*.tgz',
		                        'FTP_PHP5.php',
		                        ),
	));
	
	if (PEAR::isError($e)) {
    	echo $e->getMessage();
    	exit;
	}
	
	$e = $pkg->addMaintainer('toby', 'lead', 'Tobias Schlitt', 'toby@php.net');
	
	
	if (PEAR::isError($e)) {
    	echo $e->getMessage();
    	exit;
	}
		
	$e = $pkg->addDependency('ftp', null, 'has', 'ext');
	
	if (PEAR::isError($e)) {
    	echo $e->getMessage();
    	exit;
	}
	
	// hack until they get their shit in line with docroot role
	$pkg->addRole('tpl', 'php');
	$pkg->addRole('png', 'php');
	$pkg->addRole('gif', 'php');
	$pkg->addRole('jpg', 'php');
	$pkg->addRole('css', 'php');
	$pkg->addRole('js', 'php');
	$pkg->addRole('ini', 'php');
	$pkg->addRole('inc', 'php');
	$pkg->addRole('afm', 'php');
	$pkg->addRole('pkg', 'doc');
	$pkg->addRole('cls', 'doc');
	$pkg->addRole('proc', 'doc');
	$pkg->addRole('sh', 'script');
	
	if (isset($make)) {
    	$e = $pkg->writePackageFile();
	} else {
    	$e = $pkg->debugPackageFile();
	}
	
	if (PEAR::isError($e)) {
    	echo $e->getMessage();
	}
	
	if (!isset($make)) {
    	echo '<a href="' . $_SERVER['PHP_SELF'] . '?make=1">Make this file</a>';
	}
?>
