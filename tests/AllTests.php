<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Net_FTP test suite file
 *
 * To run the tests execute this from the top directory for a CVS checkout
 * $ pear run-tests -ur
 * or this if you've installed the package
 * $ pear run-tests -pu Net_FTP
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Networking
 * @package   FTP
 * @author    Tobias Schlitt <toby@php.net>
 * @copyright 1997-2008 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Net_FTP
 * @link      http://www.phpunit.de PHPUnit
 * @since     File available since Release 0.0.1
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Net_FTP_AllTests::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

chdir(dirname(__FILE__));
require_once 'Net_FTPTest.php';

/**
 * Unit test case for Net_FTP
 *
 * @category  Networking
 * @package   FTP
 * @author    Jorrit Schippers <jschippers@php.net>
 * @copyright 1997-2008 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Net_FTP
 * @since     Class available since Release 1.3.3
 */
class Net_FTP_AllTests
{
    /**
     * Main test suite run method
     *
     * @return void
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    /**
     * Main test suite method
     *
     * @return void
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Net_FTP Tests');
        $suite->addTestSuite('Net_FTPTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Net_FTP_AllTests::main') {
    Net_FTP_AllTests::main();
}
?>