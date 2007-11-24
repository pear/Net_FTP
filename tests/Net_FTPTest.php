<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Net_FTP main test file
 *
 * To run the tests either execute this from the top directory
 * $ phpunit Net_FTPTest tests\Net_FTPTest.php 
 * or
 * $ php tests\Net_FTPTest.php
 *
 * In both cases you need PHPUnit installed
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
 * @copyright 1997-2007 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Net_FTP
 * @since     File available since Release 0.0.1
 * @link      http://www.phpunit.de PHPUnit
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Net_FTPTest::main');
}

chdir(dirname(__FILE__));

require_once 'PHPUnit/Framework.php';
require_once '../Net/FTP.php';

/**
 * Unit test case for Net_FTP
 *
 * @category  Networking
 * @package   FTP
 * @author    Jorrit Schippers <jorrit@ncode.nl>
 * @copyright 1997-2007 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Net_FTP
 * @since     Class available since Release 1.4
 */
class Net_FTPTest extends PHPUnit_Framework_TestCase
{
    protected $ftp;
    protected $ftpdir;
    
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     * @return void
     */
    public static function main()
    {
        include_once 'PHPUnit/TextUI/TestRunner.php';

        $suite  = new PHPUnit_Framework_TestSuite('Net_FTPTest');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     * @return void
     */
    protected function setUp()
    {
        if (file_exists('config.php')) {
            include_once 'config.php';
            
            $this->ftp = new Net_FTP(FTPHOST, FTPPORT, 30);
            $res       = $this->ftp->connect();
            
            if (PEAR::isError($res)) {
                $this->ftp = null;
                return;
            }
            
            $res = $this->ftp->login(FTPUSER, FTPPASSWORD);
            
            if (PEAR::isError($res)) {
                $this->ftp = null;
                return;
            }
            
            if ('' !== FTPDIR) {
                $res = $this->ftp->cd(FTPDIR);
                
                if (PEAR::isError($res)) {
                    $this->ftp = null;
                    return;
                }
            }
            
            $res = $this->ftp->pwd('test');
            
            if (PEAR::isError($res)) {
                $this->ftp = null;
                return;
            }
            
            $this->ftpdir = $res;
            
            $res = $this->ftp->mkdir('test');
            
            if (PEAR::isError($res)) {
                $this->ftp = null;
                return;
            }
            
            $res = $this->ftp->cd('test');
            
            if (PEAR::isError($res)) {
                $this->ftp = null;
                return;
            }
        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     * @return void
     */
    protected function tearDown()
    {
        if ($this->ftp != null) {
            $this->ftp->cd($this->ftpdir);
            $this->ftp->rm('test', true);
            $this->ftp->disconnect();
            
            $this->ftpdir = null;
            $this->ftp    = null;
        }
    }
    
    /**
     * Tests functionality of Net_FTP::mkdir()
     * 
     * @return void
     * @see Net_FTP::mkdir()
     */
    public function testMkdir()
    {
        if ($this->ftp == null) {
            $this->markTestSkipped('This test requires a working FTP connection.'.
            ' Setup config.php with proper configuration parameters.');
            return;
        }
        $this->ftp->mkdir('dir1', false);
        $this->ftp->mkdir('dir1/dir2/dir3/dir4', true);
        $this->assertTrue($this->ftp->cd('dir1/dir2/dir3/dir4'));
    }
    
    /**
     * Tests functionality of Net_FTP::mkdir()
     * 
     * @return void
     * @see Net_FTP::mkdir()
     */
    public function testRename()
    {
        if ($this->ftp == null) {
            $this->markTestSkipped('This test requires a working FTP connection.'.
            ' Setup config.php with proper configuration parameters.');
            return;
        }
        $this->ftp->put('testfile.dat', 'testfile.dat', FTP_ASCII);
        $this->assertTrue($this->ftp->rename('testfile.dat', 'testfile2.dat'));
    }

    /**
     * Tests functionality of Net_FTP::_makeDirPermissions()
     * 
     * @return void
     * @see Net_FTP::_makeDirPermissions()
     */
    public function testMakeDirPermissions()
    {
        if ($this->ftp == null) {
            $this->markTestSkipped('This test requires a working FTP connection.'.
            ' Setup config.php with proper configuration parameters.');
            return;
        }
        $tests = array(
            '111' => '111',
            '110' => '110',
            '444' => '555',
            '412' => '512',
            '641' => '751',
            '666' => '777',
            '400' => '500',
            '040' => '050',
            '004' => '005',
        );
        
        foreach ($tests AS $in => $out) {
            $this->assertEquals($this->ftp->_makeDirPermissions($in), $out);
        }
    }

    /**
     * Tests functionality of Net_FTP::size()
     * 
     * @return void
     * @see Net_FTP::size()
     */
    public function testSize()
    {
        if ($this->ftp == null) {
            $this->markTestSkipped('This test requires a working FTP connection.'.
            ' Setup config.php with proper configuration parameters.');
            return;
        }
        // upload in binary to avoid addition/removal of characters
        $this->ftp->put('testfile.dat', 'testfile.dat', FTP_BINARY);
        $this->assertEquals($this->ftp->size('testfile.dat'),
            filesize('testfile.dat'));
    }
    
    /**
     * Tests functionality of Net_FTP::setMode(), Net_FTP::checkFileExtension(),
     * Net_FTP::addExtension() and Net_FTP::removeExtension()
     * 
     * @return void
     * @see Net_FTP::checkFileExtension(), Net_FTP::addExtension(),
     *      Net_FTP::removeExtension(), Net_FTP::setMode()
     */
    public function testExtensions()
    {
        if ($this->ftp == null) {
            $this->markTestSkipped('This test requires a working FTP connection.'.
            ' Setup config.php with proper configuration parameters.');
            return;
        }
        $this->ftp->setMode(FTP_ASCII);
        $this->ftp->addExtension(FTP_BINARY, 'tst');
        $this->assertEquals($this->ftp->checkFileExtension('test.tst'), FTP_BINARY);
        $this->ftp->removeExtension('tst');
        $this->assertEquals($this->ftp->checkFileExtension('test.tst'), FTP_ASCII);
        $this->ftp->setMode(FTP_BINARY);
        $this->assertEquals($this->ftp->checkFileExtension('test.tst'), FTP_BINARY);
    }

    /**
     * Tests functionality of Net_FTP::getExtensionsFile()
     * 
     * @return void
     * @see Net_FTP::getExtensionsFile()
     */
    public function testGetExtensionsFile()
    {
        if ($this->ftp == null) {
            $this->markTestSkipped('This test requires a working FTP connection.'.
            ' Setup config.php with proper configuration parameters.');
            return;
        }
        $res = $this->ftp->getExtensionsFile('extensions.ini');
        $this->assertFalse(PEAR::isError($res), 'Test extensions file could be'.
            'loaded');
        
        $this->ftp->setMode(FTP_BINARY);
        $this->assertEquals($this->ftp->checkFileExtension('test.asc'), FTP_ASCII);
        $this->ftp->setMode(FTP_ASCII);
        $this->assertEquals($this->ftp->checkFileExtension('test.gif'), FTP_BINARY);
    }

    /**
     * Tests changes made to fix bug #9611
     * 
     * @link http://pear.php.net/bugs/bug.php?id=9611
     * @return void
     */
    public function testBug9611()
    {
        if ($this->ftp == null) {
            $this->markTestSkipped('This test requires a working FTP connection.'.
            ' Setup config.php with proper configuration parameters.');
            return;
        }
        $dirlist = array(
            'drwxr-xr-x  75 upload   (?)          3008 Oct 30 21:09 ftp1'
        );
        
        $res = $this->ftp->_determineOSMatch($dirlist);
        $this->assertFalse(PEAR::isError($res),
            'The directory listing should be recognized');
        
        $this->assertEquals($res['pattern'],
            $this->ftp->_ls_match['unix']['pattern'],
            'The input should be parsed by the unix pattern');
    }
}

// Call Net_FTPTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'Net_FTPTest::main') {
    Net_FTPTest::main();
}
?>
