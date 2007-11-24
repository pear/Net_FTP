<?php

require_once 'PEAR/PackageFileManager2.php';

function dumpError($err) {
    echo $err->getMessage();
    exit;
    var_dump($err);
    die();
}

$cvsdir  = '/cvs/pear/';
$packagedir = $cvsdir . 'Net_FTP/';

$current_version = '1.3.3';

$summary = 'Net_FTP provides an OO interface to the PHP FTP functions plus some additions';

$description =
'Net_FTP allows you to communicate with FTP servers in a more comfortable way
than the native FTP functions of PHP do. The class implements everything natively
supported by PHP and additionally features like recursive up- and downloading,
dircreation and chmodding. It although implements an observer pattern to allow
for example the view of a progress bar.';
	
$current_notes =
'* Fixed Bug #7146: Recursive mkdir() broken on Windows
* Fixed Bug #7270: Recursive rmdir() broken
* Fixed Bug #7527: ls fails if there are no files and a total line
* Fixed Bug #8102: Loading file extension and checking extension gives binary for ascii files
* Fixed Bug #9611: (, ? and ) break detection of the unix platform
* Fixed Bug #10237: put() doesn\'t run ftp_alloc to allocate space
* PEAR Coding Style Valid
* Removed package.xml version 1.0
* Added some unit tests';

PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'dumpError');

$p2 = new PEAR_PackageFileManager2();
$p2->setOptions(
    array(
        'baseinstalldir'    => '/',
        'filelistgenerator' => 'cvs',
        'packagedirectory'  => dirname(__FILE__),
        'include'           => array(),
        'ignore'            => array(
            'package.xml',
            'package2.xml',
            '*.tgz',
            'generate*',
            'doc*',
            'FTP_PHP5.php',
        ),
        'dir_roles'         => array(
            'tests'     => 'test',
            'example'   => 'doc'
        ),
        'simpleoutput'      => true,
    )
);

$p2->setPackage('Net_FTP');
$p2->setSummary($summary);
$p2->setDescription($description);
$p2->setChannel('pear.php.net');

$p2->setPackageType('php');

$p2->generateContents();

$p2->setReleaseVersion($current_version);
$p2->setAPIVersion('1.0.0');
$p2->setReleaseStability('stable');
$p2->setAPIStability('stable');

$p2->setNotes($current_notes);

$p2->addGlobalReplacement('package-info', '@package_version@', 'version');

$p2->addRelease();

$p2->addMaintainer('lead', 'jschippers', 'Jorrit Schippers', 'jschippers@php.net', 'no');
$p2->addMaintainer('lead', 'toby', 'Tobias Schlitt', 'toby@php.net', 'no');

$p2->setPhpDep('4.3.0');
$p2->setPearinstallerDep('1.3.0');

$p2->setLicense('PHP License', 'http://www.php.net/license');

$p2->addExtensionDep('required', 'ftp');

if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')) {
    echo "Writing package file\n";
    $p2->writePackageFile();
} else {
    echo "Debugging package file\n";
    $p2->debugPackageFile();
}
?>
