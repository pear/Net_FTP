<?php
    # Open this file, standalone, to see how the real FTP extension behaves on
    # these test.
    
    
    if ( basename ( $_SERVER[ 'PHP_SELF' ] ) == basename ( __FILE__ ) ) {
        dl ( 'ftp.so' );
    }

    list ( $usec, $sec ) = explode ( ' ', microtime () );
    $time = (float)$usec + (float)$sec;
   
   /**
    * Function used by the test suit.
    * Takes in boolean parameter and return as string
    */
    function BoolToString ( $bool ) {
        return $bool == TRUE ? 'TRUE' : 'FALSE';
    }
    /**
    * Function used by the test suit.
    * Spits out test results
    */
    function dump ( $action, $result, $msg = FALSE ) {
        if ( is_bool ( $result ) ) {
            $result = BoolToString ( $result );
        }

        if ( is_array ( $result ) ) {
            echo '<span style="font-weight: bold;">' .$action. ':</span>' ."\n";
            foreach ( $result as $key => $value ) {
                echo ' ' .$key. ': ' .$value. "\n";
            }
        }
        else {
            echo '<span style="font-weight: bold;">' .$action. '</span>:' ."\n";
            echo $result;
            if ( $msg ) {
                echo ' ( ' .$msg. ' )';
            }
            echo "\n";
        }
        
        echo '<hr style="border: 1px solid #000;"/>';# . "\n";
        flush();
    }
    
    /**
    * Little test suit
    */
    $stream = ftp_connect ( '10.0.0.1' );
    if ( is_resource ( $stream ) ) {
        dump ( 'Logging in', $bool = ftp_login   ( $stream, 'pub', 'public'   ) );
        if ( $bool ) {
            dump ( 'PWD',            ftp_pwd     ( $stream              ) );
            dump ( 'Systype',        ftp_systype ( $stream              ) );
            dump ( 'CHDIR "movies"',          ftp_chdir   ( $stream, 'movies'    ) );
            dump ( 'PWD',            ftp_pwd     ( $stream              ) );
            dump ( 'CDUP',           ftp_cdup    ( $stream              ) );
            dump ( 'RAWLIST "."',    ftp_rawlist ( $stream, '.'         ) );
            dump ( 'CHMOD',          ftp_chmod   ( $stream, 0777, 'sfv3.php'  ) );
            dump ( 'ALLOCATE',       ftp_alloc   ( $stream, filesize (
                            'notes'    ),  $msg   ), $msg );
            dump ( 'UPLOAD ASCII',   ftp_put     ( $stream, 'glossary', 'notes',
                            FTP_ASCII  ), 'gloassary' );
            dump ( 'UPLOAD BINARY',  ftp_put     ( $stream, 'bar.tar','foo.tar',
                            FTP_BINARY ), 'bar.tar'   );
            dump ( 'RAWLIST "."',    ftp_rawlist ( $stream, '.'         ) );
            dump ( 'DELETE GLOSSARY',ftp_delete  ( $stream, 'glossary'  ) );
            dump ( 'DELETE BAR',     ftp_delete  ( $stream, 'bar.tar'   ) );
            dump ( 'RAWLIST "."',    ftp_rawlist ( $stream, '.'         ) );
        }
        dump ( 'QUIT',               ftp_quit    ( $stream ) );
    }
    list ( $usec, $sec ) = explode ( ' ', microtime () );
    $end = (float)$usec + (float)$sec;
    echo $end-$time;
    
?>
</pre>

