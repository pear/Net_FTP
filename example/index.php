<?php

	require_once 'Net/FTP.php';
	require_once 'Var_Dump.php';
	
	// Initializing test variables (required!)
	
    $host           = '';
    $port           = 21;
    $user           = '';
    $pass           = '';
    
    $baseDir        = './test/';
    $testUpDir      = 'test_up/';
    $testDownDir    = 'test_down/';
    $singleTestFile = 'test.zip';
	
	// Initializing Var_Var_Dump::display
	
	if (isset($_SERVER)) {
	    Var_Dump::displayInit(array(
            'display_mode'=>'XHTML_Text'
        ), array(
            'mode'=>'normal',
            'offset'=>4
        ));
        function head ( $text ) {
            echo '<br /><b>'.$text.'</b><br />';
        }
	} else {
	    Var_Dump::displayInit(array('display_mode'=>'Text'));
	    function head ( $text ) {
	        echo "\n--- ".$text." ---\n";
	    }
	}

	head("\$ftp = new Net_FTP();");
    $ftp = new Net_FTP();
    
    head("\$ftp->setHostname($host)");
    Var_Dump::display($ftp->setHostname($host));
    
    head("\$ftp->setPort($port)");
    Var_Dump::display($ftp->setPort($port));
    
    head("\$ftp->connect($host, $port)");
    Var_Dump::display($ftp->connect());
    
    head("\$ftp->setUsername($user)");
    Var_Dump::display($ftp->setUsername($user));
    
    head("\$ftp->setPassword(xxx)");
    Var_Dump::display($ftp->setPassword($pass));
    
    head("\$ftp->login($user, xxx)");
    Var_Dump::display($ftp->login($user, $pass));
    
    head("\$ftp->pwd()");
    Var_Dump::display($ftp->pwd());
    
    head("\$ftp->ls(null, NET_FTP_DIRS_FILES)");
    Var_Dump::display($ftp->ls(null, NET_FTP_DIRS_FILES));
    
    head("\$ftp->mkdir($baseDir)");
    Var_Dump::display($ftp->mkdir($baseDir));
    
    head("\$ftp->cd($baseDir)");
    Var_Dump::display($ftp->cd($baseDir));
    
    head("\$ftp->ls(null, NET_FTP_RAWLIST)");
    Var_Dump::display($ftp->ls(null, NET_FTP_RAWLIST));
    
    head("\$ftp->put($baseDir$singleTestFile, $singleTestFile)");
    Var_Dump::display($ftp->put($baseDir.$singleTestFile, $singleTestFile));
    
    head("\$ftp->ls(null, NET_FTP_FILES_ONLY)");
    Var_Dump::display($ftp->ls(null, NET_FTP_FILES_ONLY));
    
    head("\$ftp->put($baseDir$singleTestFile, $singleTestFile, true)");
    Var_Dump::display($ftp->put($baseDir.$singleTestFile, $singleTestFile, true));
    
    head("\$ftp->ls(null, NET_FTP_FILES_ONLY)");
    Var_Dump::display($ftp->ls(null, NET_FTP_FILES_ONLY));
    
    head("\$ftp->mdtm($singleTestFile, 'd.m.Y H:i:s')");
    Var_Dump::display($ftp->mdtm($singleTestFile, 'd.m.Y'));
    
    head("\$ftp->size($singleTestFile)");
    Var_Dump::display($ftp->size($singleTestFile));
    
    head("\$ftp->get($singleTestFile, $baseDir$singleTestFile, true)");
    Var_Dump::display($ftp->get($singleTestFile, $baseDir.$singleTestFile, true));
    
    head("\$ftp->chmod($singleTestFile, 700)");
    Var_Dump::display($ftp->chmod($singleTestFile, 700));
    
    head("\$ftp->ls(null, NET_FTP_FILES_ONLY)");
    Var_Dump::display($ftp->ls(null, NET_FTP_FILES_ONLY));
    
    head("\$ftp->cd('../')");
    Var_Dump::display($ftp->cd('../'));
    
    head("\$ftp->chmodRecursive($baseDir, 777)");
    Var_Dump::display($ftp->chmodRecursive($baseDir, 777));
    
    head("\$ftp->ls(null, NET_FTP_DIRS_ONLY)");
    Var_Dump::display($ftp->ls(null, NET_FTP_DIRS_ONLY));
    
    head("\$ftp->putRecursive($baseDir$testUpDir, $baseDir$testUpDir)");
    Var_Dump::display($ftp->putRecursive($baseDir.$testUpDir, $baseDir.$testUpDir));
    
    head("\$ftp->putRecursive($baseDir$testUpDir, $baseDir$testUpDir)");
    Var_Dump::display($ftp->putRecursive($baseDir.$testUpDir, $baseDir.$testUpDir, true));
    
    head("\$ftp->cd($baseDir:$testUpDir)");
    Var_Dump::display($ftp->cd($baseDir.$testUpDir));
    
    head("\$ftp->ls(null, NET_FTP_DIRS_FILES)");
    Var_Dump::display($ftp->ls(null, NET_FTP_DIRS_FILES));
    
    head("\$ftp->cd(../../)");
    Var_Dump::display($ftp->cd('../../'));
    
    head("\$ftp->getRecursive($baseDir$testUpDir, $baseDir$testDownDir)");
    Var_Dump::display($ftp->getRecursive($baseDir.$testUpDir, $baseDir.$testDownDir, true));
    
    head("\$ftp->rm($baseDir, true)");
    Var_Dump::display($ftp->rm($baseDir, true));
    
    head("\$ftp->ls(null, NET_FTP_DIRS_ONLY)");
    Var_Dump::display($ftp->ls(null, NET_FTP_DIRS_ONLY));
    
    head("\$ftp->disconnect()");
    Var_Dump::display($ftp->disconnect());
    
?>