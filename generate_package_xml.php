<?php

	$make = false;
	require_once('PEAR/PackageFileManager.php');

	$pkg = new PEAR_PackageFileManager;

	// directory that PEAR CVS is located in
	$cvsdir  = '/cvs/pear/';
	$packagedir = $cvsdir . 'Net_FTP/';
	$category = 'Networking';	

	$version = '1.3.0';
	
	$summary = 'Net_FTP provides an OO interface to the PHP FTP functions plus some additions';
	
	$description = <<<EOT
Net_FTP allows you to communicate with FTP servers in a more comfortable way
than the native FTP functions of PHP do. The class implements everything nativly
supported by PHP and additionally features like recursive up- and downloading,
dircreation and chmodding. It although implements an observer pattern to allow
for example the view of a progress bar.
EOT;
	
	$notes = <<<EOT
Another month of testing did not bring any more critical bugs to daylight. Therefore 
and because PEAR 1.4.0 will depend optionally on Net_FTP I decided to run the next 
stable release. The following changes were made to the package files:

 - Updated year.
 - Updated docblocks regarding the new standard.
 - Fixed whitespace issues.
 - Fixed bug 3362: bug in Net_FTP::_rm_file.
 - Added PEAR 1.4 compatible package2.xml (package.xml version 2.0).
EOT;
	
	$e = $pkg->setOptions(
		array('baseinstalldir' => '',
		      'summary' => $summary,
		      'description' => $description,
		      'version' => $version,
	          'packagedirectory' => $packagedir,
	          'pathtopackagefile' => $packagedir,
              'state' => 'stable',
              'filelistgenerator' => 'cvs',
              'notes' => $notes,
			  'package' => 'Net_FTP',
			  'dir_roles' => array(
			  		'example' => 'doc',
			  		'test' => 'test'),
		      'ignore' => array('package.xml',
		                        'doc*',
		                        'test*', 
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
