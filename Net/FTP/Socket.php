<?php
    /**
    * Default FTP extension constants
    */
    define ( 'FTP_ASCII', 0 );
    define ( 'FTP_TEXT', 0 );
    define ( 'FTP_BINARY', 1 );
    define ( 'FTP_IMAGE', 1 );
    define ( 'FTP_TIMEOUT_SEC', 0 );

    define ( 'PASSIVE', 1 ); // Shall we use passive mode?
   
    /**
    * What needs to be done overall?
    *   #1 Add active mode
    *   #2 Make *all* functions check for correct respons code
    *   #3 Document better
    *   #4 Remove private functions
    *   #5 Alot of other things I don't remember
    */
    
    # # # # # # # !!!! NOTE !!!! # # # # # # #
    # Most of the comment's are "not working",
    # meaning they are not all up-to-date
    # # # # # # # !!!! NOTE !!!! # # # # # # #
    
    error_reporting ( E_ALL | E_STRICT );
    
    /**
    * mixed _ControlSend ( resource stream, string command [,bool return data ] );
    *
    * Sends infomation ( commands ) to stream.
    * Returns bytes written or FALSE on error unless $return_data is set
    *
    * @access   private
    * @param    resource    $stream ( Stream resource )
    * @param    string      $action ( Command to send )
    * @return   integer
    *
    * TODO:
    *       Remove function. This function shall not exists in the "final
    *       release" of this package. Its only used here for two resons; quicker
    *       development time and smaller code.
    *
    * NOTE:
    *       Currently this function returns server respons, by default, unless
    *       $read ( third parameter ) is set to false. This way saves one line
    *       of coding ( lol, yes, one line hahaha )
    */
    function _ControlSend ( &$stream, $action, $read = TRUE ) {
        $i = fputs ( $stream, $action. "\r\n" );
        if ( $read ) {
            return _ControlRead ( $stream );
        }
        return $i;
    }
    
    /**
    * array _ControlRead ( resource stream [, int bytes [, bool use array ] ] );
    *
    * Reads data from stream.
    * Returns data readed as array
    *
    * BUG:
    *       Not binary safe.
    *
    * TODO:
    *       Remove function. This function shall not exists in the "final
    *       release" of this package. Its only used here for two reasons;
    *       quicker development time and smaller code.
    *
    * TODO:
    *       Make stream_set_time() call to ftp_get_option to retrive connection
    *       timeout seconds.
    *
    * NOTE:
    *       Becouse the usage of trim(), in this function, this function is
    *       *not* binary safe.
    *
    * NOTE:
    *       This function can be used to read data from *both* control stream
    *       and data stream.
    *       
    * NOTE:
    *       If there is lagg problem, this function is ( problibly ) to blame
    *       ( hint; do { fgets(); } while ( $unread_bytes ); ) all thou it
    *       should be working fine now.
    *
    * @access   private
    * @param    resource    $stream  ( Stream resource )
    * @param    integer     $bytes   ( Optional, read in $bytes sized chunks )
    * @param    boolean     $use_array ( Optional, return "new line" as array )
    * @return   string
    */
    function _ControlRead ( &$stream, $bytes = 8192, $use_array = FALSE ) {
        # Set stream in "blocking mode" with timeout 1 second
        stream_set_blocking ( $stream, TRUE );
        stream_set_timeout ( $stream, 1 );

        # Read line from stream
        # Place the line in array ( if requested )
        # Check if there are unread bytes, if so loop
        # NOTE:
        #       Line gets overwritten! ( not the array )
        # Reson:
        #       We usually only want the last line

        # TODO:
        #       Install usleep(1) before the loop?
        #       ( maight be worth it so we get the time to destroy data sockets
        #       if needed etc. )
        # NOTE:
        #       Destroying data socket causes the text "Transfer complete" to be
        #       echod into Control stream.
        do {
            $contents = trim ( fgets ( $stream, $bytes ) );
            if ( $use_array ) {
                $content[] = $contents;
            }
            $array = socket_get_status ( $stream );
        } while ( $array[ 'unread_bytes' ] > 0 );
        
        if ( $use_array ) {
            return $content;
        }
        
        # First 3 letters are usually Respons codes
        $key = substr ( $contents, 0, 3 );
        if ( is_numeric ( $key ) ) {
            return array (
                'key' => $key,
                # ltrim() to get rid of the space which was located between
                # "Respons code" and "Respons text"
                'msg' => ltrim ( substr ( $contents, 3 ) )
            );
        }
        
        return array ( 'msg' => $contents );
    }
    
    /**
    * &resource ftp_connect ( string host [, int port [, int timeout ] ] );
    *
    * Opens an FTP connection and return resource or false on failure.
    *
    * TODO:
    *       The FTP extension has ftp_get_option() function which returns the
    * timeout variable. This function needs to be created and contain it as
    * static variable.
    *
    * TODO:
    *       The FTP extension has ftp_set_option() function which sets the
    *       timeout variable. This function needs to be created and called here.
    *
    * FTP Success respons code: 220
    *
    * @param    string  $host   ( Host to connect to )
    * @param    int     $port   ( Optional, port to connect to )
    * @param    int     $timeout( Optional, seconds until function timeouts )
    * @return   &resource
    */
    function &ftp_connect ( $host, $port = 21, $timeout = 90 ) {
        $fp = fsockopen ( $host, $port, $i, $s, $timeout );
        $array = _ControlRead ( $fp );
        
        if ( !is_resource ( $fp ) || !isset ( $array[ 'key' ] ) ||
                $array[ 'key' ] != 220 ) {
            return FALSE;
        }
        
        return $fp;
    }
    
    /**
    * boolean ftp_login ( resource stream, string username, string password );
    *
    * Logs in to an given FTP connection stream.
    * Returns TRUE on success or FALSE on failure.
    *
    * TODO:
    *       Throw warning on failure.
    *
    * NOTE:
    *       Username and password are *not* optional. Function will *not*
    *       assume "anonymous" if username and/or password is empty
    *
    * FTP Success respons code: 230
    *
    * @param    resource    $stream   ( FTP resource to login to )
    * @param    string      $username ( FTP Username to be used )
    * @param    string      $password ( FTP Password to be used )
    * @return   boolean
    */
    function ftp_login ( &$stream, $username, $password ) {
        
        _ControlSend ( $stream, 'USER ' .$username );
        $array = _ControlSend ( $stream, 'PASS ' .$password );
        
        if ( isset ( $array[ 'key' ] ) && $array [ 'key' ] == 230 ) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
    * boolean ftp_quit ( resource stream );
    *
    * Closes FTP connection.
    * Returns TRUE on success or FALSE on failure.
    *
    * NOTE: The PHP function ftp_quit is *alias* to ftp_close, here it is
    * the *other-way-around* ( ftp_close() is alias to ftp_quit() ).
    *
    * NOTE:
    *       resource is set to NULL since unset() can't unset the variable.
    *
    * @param    integer     $stream   ( FTP resource )
    * @return   boolean
    */
    function ftp_quit ( &$stream ) {
        _ControlSend ( $stream, 'QUIT' );
        fclose ( $stream );
        $stream = NULL;
        return TRUE;
    }
    
    /**
    * Alias to ftp_quit()
    */
    function ftp_close ( &$stream ) {
        return ftp_quit ( $stream );
    }
    
    /**
    * string ftp_pwd ( resource stream );
    *
    * Gets the current directory name.
    * Returns the current directory.
    *
    * Needs data connection: NO
    *
    * @param    integer     $stream     ( FTP resource )
    * @return   string
    */
    function ftp_pwd ( &$stream ) {
        $array = _ControlSend ( $stream, 'PWD' );
        return _GetString ( $array[ 'msg' ], '"', '"', 0 );
    }
    
    /**
    * boolean ftp_chdir ( resource stream, string directory );
    *
    * Changes the current directory to the specified directory.
    * Returns TRUE on success or FALSE on failure.
    *
    * TODO:
    *       FTP extension throws warning on failure. Throw that warning.
    *
    * FTP success respons code: 250
    * Needs data connection: NO
    *
    * @param    integer     $stream     ( FTP stream )
    * @param    string      $pwd        ( Directory name )
    * @return   boolean
    */
    function ftp_chdir ( &$stream, $pwd ) {
        $array = _ControlSend ( $stream, 'CWD ' .$pwd );
        
        if ( $array[ 'key' ] == 250 ) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
    * boolean ftp_pasv ( resource stream, boolean passive );
    *
    * Toggles passive mode ON/OFF.
    * Returns TRUE on success or FALSE on failure.
    *
    * NOTE:
    *       Should Toggle between passive and active mode, according to the FTP
    *       exteion atleast.
    *
    * TODO:
    *       Make it compatible with the FTP extension.
    *
    * TODO:
    *       Make fscokopen() use ftp_get_option() for timeout variable.
    *
    * IDEA:
    *       Try to enter passive mode, then destroy it. If success passive mode
    *       can be enabled. If it failes, passive mode can not be enabled.
    *
    * FTP success respons code: 227
    *
    * @param   integer  $stream  ( FTP stream )
    * @return  boolean
    */
    function ftp_pasv ( &$stream, $pasv ) {
    }
    
    /**
    * &resource _CreatePassive ( resource stream );
    *
    * Creates data connection for stream.
    * Returns resource to data connection.
    *
    * TODO:
    *       Remove function. This function shall not exists in the "final
    *       release" of this package. Its only used here for two reasons;
    *       quicker development time and smaller code.
    *
    * @access   private
    * @param    resource    $stream     ( FTP control resource )
    * @return   &resource
    */
    function &_CreatePassive ( &$stream ) {
        $msg = _ControlSend ( $stream, 'PASV' );
        
        $word = _GetString ( $msg[ 'msg' ], '(', ')', 0 );
        $array = split ( ',', $word );

        # IP which we are suppost to connect to
        # NOTE:
        #       IP does *not* need to be the same for data and control
        #       connection acording to the standart.
        $host = $array[0]. '.' .$array[1]. '.' .$array[2]. '.' .$array[3];
        # Calculate which port we are suppost to connect to.
        # $array[4]*2*2*2*2*2*2*2*2 + $array[5]
        $port = ( (int)$array[4] << 8 ) + (int)$array[5];
        
        $data = fsockopen ( $host, $port, $iError, $sError, 10 );
        return $data;
    }
    
    # This function problibly needs total rewrite
    function &_CreateActive ( &$stream  ) {
        static $accept; // Keep the socket alive so the ftp can connect to it
        # No point in creating socket with socket_create_listen cause we need to
        # read/write data to it also. Damn socket_accept.
        # I guess we need to read/write data to $socket - but the ftp server
        # needs to read/write from $accept - I guess?
        # that theary is *NOT TESTED* since that stupid socket_accept doesn't
        # work for some resons
        
        # Pick random "low bit"
        $low = rand ( 39, 250 );
        # Pick random "high bit"
        $high = rand ( 39, 250 );
        # Lowest  possible port would be; 10023
        # Highest possible port would be; 64246
        
        $port = ( $low<<8 ) + $high;
        $ip = str_replace ( '.', ',', $_SERVER[ 'SERVER_ADDR' ] );
        $s = $ip. ',' .$low. ',' .$high;
        var_dump ( $ip, $port, $low, $high, $s );
        
        $socket = socket_create ( AF_INET, SOCK_STREAM, SOL_TCP );
        if ( is_resource ( $socket ) ) {
            echo "Socket Create\n";
            if ( socket_bind ( $socket, '0.0.0.0', $port ) ) {
                echo "Socket bind\n";
                if ( socket_listen ( $socket ) ) {
                    echo "Socket listen\n";
                    /**
                    * Here we have a problem!
                    * socket_listen just hangs and I've *no* idea why
                    * set_time_limit() doesn't help us here
                    * Quote from manual ( about set_time_limit() ):
                    * Any time spent on activity that happens outside the execution
                    * of the script such as system calls using system(), stream
                    * operations, database queries, etc. is not included when
                    * determining the maximum time that the script has been running.
                    */
                    
                    # set_time_limit ( 10 ); // damn you set_time_limit() !
                    socket_set_timeout ( $socket, 1 ); # Doesn't seem to work
                    #socket_close ( $socket );
                    #return false;
#                    $accept = socket_accept ( $socket );
                    // Damn you socket_accept! You don't like me and I don't
                    // like you but please, work god damnit!
                    _ControlSend ( $stream, 'PORT ' .$s );
                    return $socket;
                }
            }
        }
        var_dump ( $i = socket_last_error(), socket_strerror ( $i ) );
        return FALSE;
    }
    /**
    * array ftp_rawlist ( resource stream, string directory [,bool recursive] );
    *
    * Returns a detailed list of files in the given directory.
    *
    * TODO:
    *       Enable the recursive feature.
    *
    * BUG:
    *       Does *not* support active connections.
    *
    * Needs data connection: YES
    *
    * @param    integer     $stream     ( FTP resource )
    * @param    string      $pwd        ( Path to retrive )
    * @param    boolean     $recursive  ( Optional, retrive recursive listing )
    * @return   array
    */
    function ftp_rawlist ( $stream, $pwd, $recursive = FALSE ) {
        # Suppost to check for passive mode...
        if ( PASSIVE ) {
            $data = _CreatePassive ( $stream );
            _ControlSend ( $stream, 'LIST ' .$pwd, FALSE );
            $contents = _ControlRead ( $data, 8192, TRUE );
            fclose ( $data );
            $data = NULL;
            # Sleep for one millionth of second
            # Waiting for datasocket to die so we can read "Transfer
            # complete"
            usleep(1);
            _ControlRead ( $stream );
        }
        else {
            $data = _CreateActive ( $stream );
            if ( !is_resource ( $data ) ) {
                return FALSE;
            }
            _ControlSend ( $stream, 'LIST ' .$pwd, FALSE );
            usleep ( 1 );
            $contents = socket_read ( $data, 64, PHP_NORMAL_READ );
            socket_close ( $data );
            $data = NULL;
            usleep ( 1 );
            _ControlRead ( $stream );
        }
        return $contents;
    }
    
    /**
    * string ftp_systype ( resource stream );
    *
    * Gets system type identifier of remote FTP server
    * Returns the remote system type
    *
    * @param    resource    $stream   ( FTP resource )
    * @return   string
    */
    function ftp_systype ( &$stream ) {
        $array = _ControlSend ( $stream, 'SYST' );
        return $array[ 'msg' ];
    }
    
    /**
    * boolean ftp_alloc ( resource stream, integer bytes [, string &message ] );
    *
    * Allocates space for a file to be uploaded
    * Return TRUE on success or FALSE on failure
    *
    * NOTE; Many FTP servers do not support this command and/or don't need it.
    *
    * FTP success respons key: Belive it's 200
    * Needs data connection: NO
    *
    * @param    resource    $stream     ( FTP stream )
    * @param    integer     $int        ( Space to allocate )
    * @param    string      $msg        ( Optional, textual representation of
    *                                       the servers response will be
    *                                       returned by refrence )
    * @return   boolean
    */
    function ftp_alloc ( &$stream, $int, &$msg = NULL ) {
        $array = _ControlSend ( $stream, 'ALLO ' .$int. ' R ' .$int );
        
        $msg = $array[ 'msg' ];
        if ( isset ( $array[ 'key' ] ) && (
                    $array[ 'key' ] == 200 ^ $array[ 'key' ] == 202 ) ) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
    * bool ftp_put ( resource stream, string remote_file, string local_file,
    *               int mode [, int startpos ] );
    *
    * Uploads a file to the FTP server
    * Returns TRUE on success or FALSE on failure.
    *
    * NOTE:
    *       The transfer mode specified must be either FTP_ASCII or FTP_BINARY.
    *
    * TODO:
    *       Make it "active" compatible
    *
    * @param    resource    $stream     ( FTP stream )
    * @param    string      $remote     ( Remote file to write )
    * @param    string      $local      ( Local file to upload )
    * @param    integer     $mode       ( Upload mode, FTP_ASCI || FTP_BINARY )
    * @param    integer     $pos        ( Optional, start upload at position )
    * @return   boolean
    */
    function ftp_put ( &$stream, $remote, $local, $mode, $pos = 0 ) {
        if ( !is_resource ( $stream ) || !is_readable ( $local ) ||
                !is_integer ( $mode ) || !is_integer ( $pos ) ) {
            return FALSE;
        }

        $types = array (
            0 => 'A',
            1 => 'I'
        );
        $windows = array (
            0 => 't',
            1 => 'b'
        );

        /**
        * TYPE values:
        *       A ( ASCII  )
        *       I ( BINARY )
        *       E ( EBCDIC )
        *       L ( BYTE   )
        */

        # Create data socket
        $data = _CreatePassive ( $stream );
        # Decide TYPE to use
        $msg = _ControlSend ( $stream, 'TYPE '. $types[ $mode ] );
        
        if ( $msg[ 'key' ] == 200 ) {
            $msg = _ControlSend ( $stream, 'STOR ' .$remote );
            # Creating resource to $local file
            $fp = fopen ( $local, 'r'. $windows[ $mode ] );
            if ( !is_resource ( $fp ) ) {
                fclose ( $data );
                $data = $fp = NULL;
                return FALSE;
            }
            # Loop throu that file and echo it to the data socket
            $i = 0;
            while ( !feof ( $fp ) ) {
                $i += fputs ( $data, fread ( $fp, 8192 ) );
            }
            fclose ( $data );
            $data = NULL;
            #usleep(1);
            $foo = _ControlRead ( $stream );
        }
        return TRUE;
    }

    /**
    * Changes to the parent directory
    * Returns TRUE on success or FALSE on failure
    *
    * @access  public
    * @param   integer $stream  ( Stream ID )
    * @return  boolean
    */
    function ftp_cdup ( &$stream ) {
        $array = _ControlSend ( $stream, 'CDUP' );
        if ( $array[ 'key' ] == 250 ) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
    * Set permissions on a file via FTP
    * Returns the new file permission on success or FALSE on error
    *
    * NOTE: This command is *not* supported by the standart
    * TODO: Figure out a way to chmod files via FTP
    * NOTE: This command not ready!
    *
    * @access  public
    * @param   integer $stream  ( Stream ID )
    * @param   integer $mode    ( Octal value )
    * @param   string  $file    ( File to change permissions on )
    * @return  integer
    */
    function ftp_chmod ( &$stream, $mode, $file ) {
        # chmod not in the standart, proftpd doesn't recognize it
        # use SITE CHMOD?
        $msg = _ControlSend ( $stream, 'SITE CHMOD ' .$mode. ' ' .$file );
        if ( $msg[ 'key' ] == 200 ) {
            return $mode;
        }
        
        trigger_error ( 'ftp_chmod() [<a
                href="function.ftp-chmod">function.ftp-chmod</a>]: ' .$msg[ 'msg' ], E_USER_WARNING );
        return FALSE;
        #$msg[ 'key' ]. ' ' .$msg[ 'msg' ];
    }
    
    /**
    * Deletes a file on the FTP server
    * Returns TRUE on success or FALSE on failure
    *
    * @access integer $stream  ( Stream ID )
    * @param  string  $path    ( File to delete )
    * @return boolean
    */
    function ftp_delete ( &$stream, $path ) {
        $msg = _ControlSend ( $stream, 'DELE ' .$path );
        if ( $msg[ 'key' ] == 250 ) {
            return TRUE;
        }

        return FALSE;
    }
    
    /**
    * Requests execution of a program on the FTP server
    * NOTE; SITE EXEC is *not* supported by the standart
    * Returns TRUE on success or FALSE on error
    *
    * TODO: Look a littlebit better into this
    *
    * @access  public
    * @param   integer $stream  ( Stream ID )
    * @param   string  $cmd     ( Command to send )
    * @return  boolean
    */
    function ftp_exec ( $stream, $cmd ) {
        # Command not defined in the standart
        # proftpd doesn't recognize SITE EXEC ( only help, chgrp, chmod and ratio )
        return $this->_ctrlSend ( $stream, 'SITE EXEC ' .$cmd, $msg, $key, 200 );
        # php.net/ftp_exec uses respons code 200 to verify if command was
        # sent successfully or not
    }
    
    /**
    * Method, used by some functions in this class, to get string between
    * two string A and string B
    * Returns string on success or FALSE on failure
    *
    * @access  private
    * @param   string  $string  ( String to search in )
    * @param   string  $from    ( Get from this text )
    * @param   string  $to      ( Get till this text )
    * @param   integer $offset  ( Start from this offset )
    * @return  string
    */
    function _GetString ( $string, $from, $to, $offset = NULL ) {
        static $i;
        
        if ( isset ( $offset ) ) {
            $i = $offset;
        }
        
        $i = $pos = strpos ( $string, $from, $i ) + strlen ( $from );
        $pos2 = strpos ( $string, $to, $pos ) - $pos;
        
        if ( $pos === FALSE || $pos2 === FALSE ) {
            return FALSE;
        }
        
        return substr ( $string, $pos, $pos2 );
    }
    
    # Open this file, standalone, to see how the read FTP extension behaves on
    # these test
    require_once 'test_suit.php';
?>
