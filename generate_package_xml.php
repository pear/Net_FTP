<?php

require_once 'PEAR/PackageFileManager2.php';

function dumpError($err) {
    var_dump($err);
    die();
}

$cvsdir  = '/cvs/pear/';
$packagedir = $cvsdir . 'Net_FTP/';

$version = '1.3.1';

$summary = 'Net_FTP provides an OO interface to the PHP FTP functions plus some additions';

$description = <<<EOT
Net_FTP allows you to communicate with FTP servers in a more comfortable way
than the native FTP functions of PHP do. The class implements everything nativly
supported by PHP and additionally features like recursive up- and downloading,
dircreation and chmodding. It although implements an observer pattern to allow
for example the view of a progress bar.
EOT;
	
	$notes = <<<EOT
* Fixed Bug #4102: Problem detecting os method ls().
* Fixed Bug #5337: _list_and_parse behavior with an empty remote directory.
* Fixed Bug #4836: Off-by-one error in regex for Windows directory listings.
* Fixed Bug #4749: ls() fails when connection is closed.
* Fixed Bug #4969: Recursive rm ends in endless loop.
* Fixed Bug #5895: Recursive chmod ends in endless loop.
* Fixed Bug #4009: _determine_os_match doesn't take into account numbered users and groups.
* Fixed Bug #4008: _list_and_parse tries to determine OS on an empty list.
* Fixed Bug #3778: Notice of Uninitialized string offset in function _rm_dir_recursive.
EOT;

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

$p2->setReleaseVersion($version);
$p2->setAPIVersion('1.0.0');
$p2->setReleaseStability('stable');
$p2->setAPIStability('stable');

$p2->setNotes($notes);

$p2->addGlobalReplacement('package-info', '@package_version@', 'version');

$p2->addRelease();

$p2->addMaintainer('lead', 'toby', 'Tobias Schlitt', 'toby@php.net');

$p2->setPhpDep('4.3.0');
$p2->setPearinstallerDep('1.3.0');

$p2->setLicense('PHP License', 'http://www.php.net/license');

$p2->addExtensionDep('required', 'ftp');

$p1 =& $p2->exportCompatiblePackageFile1();

if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')) {
    echo "Writing package file\n";
    $p2->writePackageFile();
    $p1->writePackageFile();
} else {
    echo "Debugging package file\n";
    $p2->debugPackageFile();
    $p1->debugPackageFile();
}
?>
