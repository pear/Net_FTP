<?php

	ini_set('include_path', '/cvs/pear/Net_FTP:'.ini_get('include_path'));

    $hostname = "example.com";
    $user = "xxx";
    $pass = "xxx";

    function dump ( $var, $desc = null ) {

        if (isset($desc)) {
            echo "<br><b>$desc</b>";
        }
        echo "<br><pre>";
        var_dump($var);
        echo "</pre><br>";
    }

    require_once 'Net/FTP.php';

    $ftp = new Net_FTP();
    dump($ftp->setHostname($hostname), 'FTP::setHostname()');
    dump($ftp->setPort(21), 'FTP::setPort()');
    dump($ftp->connect(), 'FTP::connect()');
    dump($ftp->setUsername($user), 'FTP::setUsername()');
    dump($ftp->setPassword($pass), 'FTP::setPassword()');
    dump($ftp->login(), 'FTP::login()');
    dump($ftp->pwd(), 'FTP::pwd()');
    dump($ftp->ls(null, NET_FTP_DIRS_ONLY), 'FTP::ls()');
    dump($ftp->mkdir('test'), 'FTP::mkdir()');
    dump($ftp->cd('test'), 'FTP::cd()');
    dump($ftp->put('proftpd-1.2.8.tar.gz', 'proftpd-1.2.8.tar.gz'), 'FTP::upload()');
    dump($ftp->ls(null, NET_FTP_FILES_ONLY), 'FTP::ls()');
    dump($ftp->put('proftpd-1.2.8.tar.gz', '../proftpd-1.2.8.tar.gz'), 'FTP::upload()');
    dump($ftp->get('proftpd-1.2.8.tar.gz', 'proftpd-1.2.8.tar.gz', true, FTP_BINARY), 'FTP::get()');
    dump($ftp->ls(null, NET_FTP_FILES_ONLY), 'FTP::ls()');
    dump($ftp->cd('..'), 'FTP::cd()');
    dump($ftp->chmodRecursive('test', 777), 'FTP::chmodRecursive()');
    dump($ftp->ls(null, NET_FTP_DIRS_ONLY), 'FTP::ls()');
    dump($ftp->mdtm('proftpd-1.2.8.tar.gz', 'd.m.Y'), 'FTP::mdtm()');
    dump($ftp->size('proftpd-1.2.8.tar.gz'), 'FTP::size()');
    dump($ftp->rm('test/', true), 'FTP::rm()');
    dump($ftp->ls(null, NET_FTP_DIRS_ONLY), 'FTP::ls()');
    dump($ftp->disconnect(), 'FTP::disconnect()');
    
    
?>
