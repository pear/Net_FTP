<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Net_FTP observer example.
 *
 * Net FTP Observer example to use with HTML_Progress package
 * (PHP 4 >= PHP 4.3.0)
 *
 * PHP versions 4 and 5
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
 * @author    Laurent Laville <pear@laurent-laville.org>
 * @copyright 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Net_FTP
 * @link      http://pear.php.net/package/HTML_Progress
 * @since     File available since Release 1.3.0
 */

require_once 'Net/FTP.php';
require_once 'Net/FTP/Observer.php';
require_once 'HTML/Progress.php';

/**
 * Initializing test variables (required!)
 */
$ftp = array(
    'host' => '',
    'port' => 21,
    'user' => '',
    'pass' => ''
);    

$dest      = 'tmp';              // this directory must exists in your ftp server !
$overwrite = true;               // overwrite all existing files on the ftp server
$files     = array(
    'HTML_Progress-1.2.0.tgz',
    'php4ever.png'               // initializing contents (required!) file(s) must
);                               // exists file(s) to upload


/**
 * 1. Defines the FTP/Progress Observer
 *
 * @category  Networking
 * @package   FTP
 * @author    Tobias Schlitt <toby@php.net>
 * @copyright 1997-2008 The PHP Group
 * @license   http://www.php.net/license/3_0.txt PHP License 3.0
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Net_FTP
 * @since     0.0.1
 * @access    public
 */
class Observer_ProgressUpload extends Net_FTP_Observer
{
    var $progress;
    
    /**
     * Constructor for the upload observer
     *
     * @param HTML_Progress &$progress Progress bar
     *
     * @return void
     */
    function Observer_ProgressUpload(&$progress)
    {
        /* Call the base class constructor. */
        parent::Net_FTP_Observer();

        /**
           Configure the observer:
           
           Be sure to have an indeterminate progress meter when
           @link http://www.php.net/manual/en/function.ftp-nb-put.php
           stores a file on the FTP server (non-blocking)
         */
        $this->progress =& $progress;
        $this->progress->setIndeterminate(true);
    }

    /**
     * Notification method
     *
     * @param mixed $event Variable describing occured event
     *
     * @return void
     */
    function notify($event)
    {
        $this->progress->display();
        $this->progress->sleep();
                 
        if ($this->progress->getPercentComplete() == 1) {
            $this->progress->setValue(0);
        } else {
            $this->progress->incValue();
        }
    }
}

//
// 2. defines the progress meter 
//
$meter = new HTML_Progress();
$ui    = &$meter->getUI();
$ui->setProgressAttributes(array(
    'background-color' => '#e0e0e0'
));        
$ui->setStringAttributes(array(
    'color'  => '#996',
    'background-color' => '#CCCC99'
));        
$ui->setCellAttributes(array(
    'active-color' => '#996'
));

$meter->setAnimSpeed(200);
$meter->setIncrement(10);
$meter->setStringPainted(true);     // get space for the string
$meter->setString("");              // but don't paint it
$meter->setIndeterminate(true);     // progress meter start in indeterminate mode
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>FTP/Progress Observer example</title>
<style type="text/css">
<!--
body {
    background-color: #CCCC99;
    color: #996;
    font-family: Verdana, Arial;
}

<?php echo $meter->getStyle(); ?>
// -->
</style>
<script type="text/javascript">
<!--
<?php echo $meter->getScript(); ?>
//-->
</script>
</head>
<body>

<?php 
echo $meter->toHtml();
@set_time_limit(0);  // unlimited time operation (removed 30s default restriction)

$f = new Net_FTP();

//
// 3. connect to the FTP server 
//
$ret = $f->connect($ftp['host'], $ftp['port']);
if (PEAR::isError($ret)) {
    die($ret->getMessage());
}
printf('connected at <b>%s</b><br />', $ftp['host']);

//
// 4. login to the FTP server as a well-known user
//
$ret = $f->login($ftp['user'], $ftp['pass']);
if (PEAR::isError($ret)) {
    $f->disconnect();
    die($ret->getMessage());
}
printf('login as <b>%s</b><br />', $ftp['user']);

//
// 5. changes directory to final destination for upload operation
//
$ret = $f->cd($dest);
if (PEAR::isError($ret)) {
    $f->disconnect();
    die($ret->getMessage());
}

//
// 6. attachs an instance of the FTP/Progress subclass observer
//
$observer = new Observer_ProgressUpload($meter);
$ok       = $f->attach($observer);
if (!$ok) {
    die('cannot attach a FTP Observer');
}

//
// 7. moves files on the FTP server
//
foreach ($files as $file) {
    $ret = $f->put($file, basename($file), $overwrite);
    if (PEAR::isError($ret)) {
        if ($ret->getCode() == NET_FTP_ERR_OVERWRITEREMOTEFILE_FORBIDDEN
            && !$overwrite) {
            printf('%s <br />', $ret->getMessage());
            continue;  // it is just a warning when \$overwrite variable is false
        }
        die($ret->getMessage());
    }
    printf('<b>%s</b> transfer completed <br />', basename($file));
}
$f->detach($observer);

//
// 8. checks if files are really on the FTP server
//
$ret = $f->ls(null, NET_FTP_RAWLIST);
if (PEAR::isError($ret)) {
    $f->disconnect();
    die($ret->getMessage());
}
print '<pre>';
var_dump($ret);
print '</pre>';

//
// 9. says goodbye to the FTP server !
//
$f->disconnect();
echo 'Done!';
?>
