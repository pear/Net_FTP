<?php

    function dump ( $var, $desc = null ) {
    
        if (isset($desc)) {
            echo "<br><b>$desc</b>";
        }
        echo "<br><pre>";
        var_dump($var);
        echo "</pre><br>";
    }

    require_once 'Net/FTP.php';
    
    // $ftp = new Net_FTP('localhost', 21);
    $ftp = new Net_FTP();
    
    dump($ftp->setHostname('localhost'), 'FTP::setHostname()');
    dump($ftp->setPort(21), 'FTP::setPort()');
    dump($ftp->connect(), 'FTP::connect()');
    // dump($ftp->connect('localhost', 21), 'FTP::connect()');

    dump($ftp->setUsername('xxx'), 'FTP::setUsername()');
    dump($ftp->setPassword('xxx'), 'FTP::setPassword()');
    dump($ftp->login(), 'FTP::login()');
    // dump($ftp->login('xxx', 'xxx'), 'FTP::login()');

    dump($ftp->pwd(), 'FTP::pwd()');
    
    dump($ftp->mkdir('test'), 'FTP::mkdir()');
    dump($ftp->rm('test/', true), 'FTP::rm()');
    
    dump($ftp->cd('download'), 'FTP::cd()');
    
    // dump($ftp->ls(null, NET_FTP_FILES_ONLY), 'FTP::ls()');
    // dump($ftp->ls(null, NET_FTP_DIRS_ONLY), 'FTP::ls()');
    dump($ftp->ls(), 'FTP::ls()');
    
    // dump($ftp->execute('CHMOD 777 proftpd-1.2.8.tar.gz'), 'FTP::exec()');
    
    // dump($ftp->mdtm('proftpd-1.2.8.tar.gz'), 'FTP::mdtm()');
    dump($ftp->mdtm('proftpd-1.2.8.tar.gz', 'd.m.Y'), 'FTP::mdtm()');
    
    dump($ftp->size('proftpd-1.2.8.tar.gz'), 'FTP::size()');
    
    dump($ftp->get('proftpd-1.2.8.tar.gz', './proftpd-1.2.8.tar.gz', true, FTP_BINARY), 'FTP::get()');
    dump($ftp->cd('..'), 'FTP::cd()');
    
    // dump($ftp->getRecursive('download/', 'download_remote/'), 'FTP::getRecursive()');
    // dump($ftp->putRecursive('download_remote/', 'uploaded/', true), 'FTP::putRecursive()');
    
    dump($ftp->rm('uploaded/', true), 'FTP::rm()');
    
    
    dump($ftp->disconnect(), 'FTP::disconnect()');
    
    
?>