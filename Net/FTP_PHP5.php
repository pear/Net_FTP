<?php

	/*
   +----------------------------------------------------------------------+
   | Net_FTP Version 1.3	                                              |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2004 Tobias Schlitt                               |
   +----------------------------------------------------------------------+
   | This source file is subject to version 3.0 of the PHP license,       |
   | that is available at through the world-wide-web at                   |
   | http://www.php.net/license/3_0.txt.                                  |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:       Tobias Schlitt <toby@php.net>                         |
   +----------------------------------------------------------------------+
	*/

    function __autoload($classname) {
        $classname = preg_replace("/_/", "/", $classname);
        include_once("$classname.php");
    }
    
    define("NET_FTP_FILES_ONLY", 0, true);
    define("NET_FTP_DIRS_ONLY", 1, true);
    define("NET_FTP_DIRS_FILES", 2, true);
    define("NET_FTP_RAWLIST", 3, true);

 	/**
 	* Class for comfortable FTP-communication
 	*
 	* This class provides comfortable communication with FTP-servers. You may do everything
    * enabled by the PHP-FTP-extension and further functionalities, like recursive-deletion,
    * -up- and -download. Another feature is to create directories recursively.
 	*
 	* Change-Log:
    *
    * 2002-02-21:
    *
 	* Now that the class runs into beta stadium, the API may not change any more. (Maybe there will be some
    * optional parameters added later on, but the existing once will not change any further.) Notice, that
    * the API has not changed until alpha stadiu. So, you have not to change anything on your
    * existing code using this class.
 	*
    * - Added support for ftp_nb_{put||get}-functions. If these functions do not exist the methods
    * will fall back on ftp_{put||get}. (Thanks to Alexander Skwar!)
    * - Added support for recursive directory creation. (Thanks to Alexander Skwar!)
    * - Fixed some bugs
    * - Fixed documentation
    *
 	*
 	* @since PHP 4.2.3
 	* @author Tobias Schlitt <tobias@schlitt.info>
 	* @see http://www.schlitt.info
 	*/


    class Net_FTP {

        /**
        * The host to connect to
        *
        * @access private
        * @private string
        */

        private $_hostname;

        /**
        * The port for ftp-connection (standard is 21)
        *
        * @access private
        * @private string
        */

        private $_port = 21;

        /**
        * The username for login
        *
        * @access private
        * @private string
        */

        private $_username;

        /**
        * The password for login
        *
        * @access private
        * @private string
        */

        private $_password;

        /**
        * Determine wether to use passive-mode (true) or active-mode (false)
        *
        * @access private
        * @private bool
        */

        private $_passv;

        /**
        * The standard mode for ftp-transfer
        *
        * @access private
        * @private string
        */

        private $_mode;

        /**
        * This holds the handle for the ftp-connection
        *
        * @access private
        * @private int
        */

        private $_handle;

        /**
        * Saves file-extensions for ascii- and binary-mode
        *
        * The array contains 2 sub-arrays ("ascii" and "binary"), which both contain
        * file-extensions without the "." (".php" = "php").
        *
        * @access private
        * @private array
        */

        private $_file_extensions;

        /**
        * ls match
        *
        * ls match
        *
        * @access private
        * @private string
        */

        private $ls_match = '/(?:(d)|.)([rwx-]+)\s+(\w+)\s+(\w+)\s+(\w+)\s+(\w+)\s+(\S+\s+\S+\s+\S+)\s+(.+)/';
        private $ls_regex_map = array('name'=>8,'size'=>6,'rights'=>2,'user'=>4,'group'=>5,
								'files_inside'=>3,'date'=>7,'is_dir'=>1);

        /**
        * This generates a new FTP-Object
        * This generates a new FTP-Object. The FTP-connection will not be established, yet.
        * You can leave $host and $port blank, if you want. The $host will not be set
        * and the $port will be left at 21. You have to set the $host manualy before
        * trying to connect.
        *
        * @access public
        * @param string $host The hostname (optional)
        * @param int $port The port (optional)
        * @return void
        */

        public function __construct ( $host = null, $port = null ) {

            if (isset($host)) {
                $this->setHostname($host);
            }
            if (isset($port)) {
                $this->setPort($port);
            }

            $this->_file_extensions[FTP_ASCII] = array();
            $this->_file_extensions[FTP_BINARY] = array();

        }

        /**
        * Build the FTP-connection
        * This function generates the FTP-connection. You can optionally define a
        * hostname and/or a port. If you do so, this data is stored inside the object.
        *
        * @access public
        * @param string $host The Hostname (optional)
        * @param int $port The Port (optional)
        * @return void
        * @throw Exception
        */

        public function connect ( $host = null, $port = null ) {
            if (isset($host)) {
                $this->setHostname($host);
            }
            if (isset($port)) {
                $this->setPort($port);
            }
            $handle = @ftp_connect($this->getHostname(), $this->getPort());
            if (!$handle) {
                throw new Exception("Connection to host failed");
            } else {
                $this->_handle =& $handle;
            }
            return;
        }

        /**
        * Close the FTP-connection
        * This method closes the FTP-connection
        *
        * @access public
        * @return void
        * @throw Exception
        */

        public function disconnect ( ) {
            if (!@ftp_close($this->_handle)) {
                throw new Exception('Could not disconnect from '.$this->getHostname().':'.$this->getPort().'.');
            }
        }

        /**
        * This logges you into the ftp-server
        * This logges you into the ftp-server. You are free to specify username and password
        * in this method. If you specify it, the values will be taken into the coresponding
        * attributes, if do not specify, the attributes are taken.
        *
        * @access public
        * @param string $username The username to use (optional)
        * @param string $password The password to use (optional)
        * @return void
        * @throw Exception
        */

        public function login ( $username = null, $password = null ) {

            if (!isset($username)) {
                $username = $this->getUsername();
            } else {
                $this->setUsername($username);
            }

            if (!isset($password)) {
                $password = $this->getPassword();
            } else {
                $this->setPassword($password);
            }

            $res = @ftp_login($this->_handle, $username, $password);

            if (!$res) {
                throw new Exception('Could not logon using login '.$username.'.');
            }
        }


        /**
        * Change the current directory to the following
        * This changes the currently used directory. You can use either an absolute
        * directory-path (e.g. "/home/blah") or a relative one (e.g. "../test").
        *
        * @access public
        * @param string $dir The directory to go to.
        * @return void
        * @throw Exception
        */

        public function cd ( $dir ) {
            if (!@ftp_chdir($this->_handle, $dir)) {
                throw new Exception('Could not change to directory "'.$dir.'".');
            }
        }

        /**
        * Show's you the actual path on the server
        * This function questions the ftp-handle for the actual selected path and returns it.
        *
        * @access public
        * @return string $path The actual path
        * @throw Exception
        */

        public function pwd () {
            $res = @ftp_pwd($this->_handle);
            if (!$res) {
                throw new Exception('Could not determine actual directory');
            } else {
                return (string)$res;
            }
        }

        /**
        * Make a new dir on the ftp-server
        * This works similar to the mkdir-command on your local maschine. You can either give
        * it an absolute or relative path. The relative path will be completed with the actual
        * selected server-path. (see: pwd())
        *
        * @access public
        * @param string $dir Absolute or relative dir-path
	    * @param bool $recursive Create all needed directories
        * @return void
        * @throw Exception
        */

        public function mkdir($dir, $recursive = false) {
            $dir = $this->_construct_path($dir);
	        if ($recursive === false){
		        if (!@ftp_mkdir($this->_handle, $dir)) {
			       throw new Exception('Failed to create directory "'.$dir.'".');
		        }
	        } else {
	    	    $pos = 0;
		        $elements = array();
		        while (false !== ($pos = strpos($dir, '/', $pos + 1))){
			        $elements[] = substr($dir, 0, $pos);
		        }
		        foreach ($elements as $element){
		            try {
			             $this->mkdir($element, false);
		            } catch (Exception $e) {
		                throw $e;
		            }
		        }
	        }
        }

        /**
        * Execute a command on the server (per SITE EXEC)
        * This method tries executing a command on the ftp, using SITE EXEC.
        *
        * @access public
        * @param string $command The command to execute
        * @return mixed $res The result of the command (if successfull)
        * @throw Exception
        */

        public function execute ( $command ) {
            $res = @ftp_exec($this->_handle, $command);
            if (!$res) {
                throw new Exception('Failed to execute command "'.$command.'".');
            } else {
                return $res;
            }
        }

        /**
        * Execute a SITE command on the server
        * This method tries to execute a SITE command on the ftp server.
        *
        * @access public
        * @param string $command The command with parameters to execute
        * @return void
        * @throw Exception
        */

        public function site ( $command ) {
            $res = @ftp_site($this->_handle, $command);
            if (!$res) {
                throw new Exception('Failed to execute site command "'.$command.'".');
            }
        }

        /**
        * Chmod a file on the server
        * This method will try to chmod the file specified on the server
        * Currently, you must give a number as the the permission argument (777 or
        * similar). The file can be either a relative or absolute path.
        * NOTE: Some servers do not support this feature. In that case, you will
        * get a PEAR error object returned. If successful, the method returns true
        *
        * @access public
        * @param mixed      $target The file or array of files to set permissions for
        * @param integer    $permissions The mode to set the file permissions to
        * @return void
        * @throw Exception
        */

        public function chmod($target, $permissions)
        {

            // If $target is an array: Loop through it.
            if (is_array($target)) {

                for ($i = 0; $i < count($target); $i++) {
                    try {
                        $res = $this->chmod($target[$i], $permissions);
                    } catch (Exception $e) {
                        throw $e;
                    }                        
                } // end for i < count($target)

            } else {
                $res = $this->site("CHMOD " . $permissions . " " . $target);
                if (!$res) {
                    throw new Exception("CHMOD permisssions '".$permissions."' to target '".$target."' failed");
                }

            } // end if is_array

        } // end method chmod

        /**
        * Chmod a folder recursively on the server
        * This method will try to chmod a folder and all of its contents
        * on the server. The target argument must be a folder or an array of folders
        * and the permissions argument have to be an integer (i.e. 777).
        * The file can be either a relative or absolute path.
        * NOTE: Some servers do not support this feature. In that case, you
        * will get a PEAR error object returned. If successful, the method
        * returns true
        *
        * @access public
        * @param mixed      $target         The folder or array of folders to
        *                                   set permissions for
        * @param integer    $permissions    The mode to set the folder
        *                                   and file permissions to
        * @return void
        * @throw Exception
        */
        public function chmodRecursive($target, $permissions)
        {

            // If $target is an array: Loop through it
            if (is_array($target)) {

                for ($i = 0; $i < count($target); $i++) {
                    try {
                        $res = $this->chmodRecursive($target[$i], $permissions);
                    }
                } // end for i < count($target)

            } else {

                $remote_path = $this->_construct_path($target);

                // Chmod the directory itself
                try {
                    $result = $this->chmod($remote_path, $permissions);
                } catch (Exception $e) {
                    throw $e;
                }
                
                // If $remote_path last character is not a slash, add one
                if (substr($remote_path, strlen($remote_path)-1) != "/") {
                    $remote_path .= "/";
                }

                $dir_list = array();
                $mode = NET_FTP_DIRS_ONLY;
                try {
                    $dir_list = $this->ls($remote_path, $mode);
                } catch (Exception $e) { 
                    throw $e;
                }
                foreach ($dir_list as $dir_entry) {

                    $remote_path_new = $remote_path.$dir_entry["name"]."/";

                    try {
                        $result = $this->chmod($remote_path_new, $permissions);
                        $result = $this->chmodRecursive($remote_path_new, $permissions);
                    } catch (Exception $e) {
                        throw $e;
                    }

                } // end foreach dir_list as dir_entry

                $file_list = array();
                $mode = NET_FTP_FILES_ONLY;
                $file_list = $this->ls($remote_path, $mode);

                foreach ($file_list as $file_entry) {
                    $remote_file = $remote_path.$file_entry["name"];
                    try {
                        $this->chmod($remote_file, $permissions);
                    } catch (Exception $e) {
                        throw $e;
                    }
                } // end foreach $file_list

            } // end if is_array
        } // end method chmodRecursive


        /**
        * Get the last modification-time of a file.
        * This will return the last modification-time of a file. You can either give this
        * function a relative or an absolute path to the file to check.
        * NOTE: Some servers will not support this feature and the function works
        * only on files, not directories! When successful,
        * it will return the last modification-time as a unix-timestamp or, when $format is
        * specified, a preformated timestring.
        *
        * @access public
        * @param string $file The file to check
        * @param string $format The format to give the date back (optional, if not set, it will return a Unix timestamp)
        * @return mixed Unix timestamp
        * @throw Exception
        */

        public function mdtm ( $file, $format = null ) {
            $file = $this->_construct_path($file);
            if ($this->_check_dir($file)) {
                throw new Exception('Directories have no last modification time.');
            }
            $res = @ftp_mdtm($this->_handle, $file);
            if ($res == -1) {
                throw new Exception('Could not access file "'.$file.'".');
            }
            if (isset($format)) {
                $res = date($format, $res);
                if (!$res) {
                    throw new Exception("Date-format failed on timestamp '$res'.");
                }
            }
        }

        /**
        * Get the size of a file
        * This will return the size of a given file in bytes. You can either give this function
        * a relative or an absolute file-path. NOTE: Some servers do not support this feature!
        *
        * @access public
        * @param string $file The file to check
        * @return int $res Size in bytes
        * @throw Exception
        */

        public function size ( $file ) {

            $file = $this->_construct_path($file);
            $res = @ftp_size($this->_handle, $file);
            if ($res == -1) {
                throw new Exception("Could not determine filesize of '$file'.", 0);
            } else {
                return $res;
            }
        }

        /**
        * List the current (or another) directory
        * This method returns a directory-list of the current directory or given one.
        * To display the current selected directory, simply set the first parameter to null
        * or leave it blank, if you do not want to use any other parameters.
        * <BR><BR>
        * There are 4 different modes of listing directories. Either to list only
        * the files (using NET_FTP_FILES_ONLY), to list only directories (using
        * NET_FTP_DIRS_ONLY) or to show both (using NET_FTP_DIRS_FILES, which is default).
        * <BR><BR>
        * The 4th one is the NET_FTP_RAWLIST, which returns just the array created by the
        * ftp_rawlist()-function build into PHP.
        * <BR><BR>
        * The other function-modes will return an array containing the requested data.
        * The files and dirs are listed in human-sorted order, but if you select
        * NET_FTP_DIRS_FILES the directories will be added above the files,
        * but although both sorted.
        * <BR><BR>
        * All elements in the arrays are assotiative arrays themselves. The have the following
        * structure:
        * <BR><BR>
        * Dirs:<BR>
        *           ["name"]        =>  string The name of the directory<BR>
        *           ["rights"]      =>  string The rights of the directory (in style "rwxr-xr-x")<BR>
        *           ["user"]        =>  string The owner of the directory<BR>
        *           ["group"]       =>  string The group-owner of the directory<BR>
        *           ["files_inside"]=>  string The number of files/dirs inside the directory
        *                                      excluding "." and ".."<BR>
        *           ["date"]        =>  int The creation-date as Unix timestamp<BR>
        *           ["is_dir"]      =>  bool true, cause this is a dir<BR>
        * <BR><BR>
        * Files:<BR>
        *           ["name"]        =>  string The name of the file<BR>
        *           ["size"]        =>  int Size in bytes<BR>
        *           ["rights"]      =>  string The rights of the file (in style "rwxr-xr-x")<BR>
        *           ["user"]        =>  string The owner of the file<BR>
        *           ["group"]       =>  string The group-owner of the file<BR>
        *           ["date"]        =>  int The creation-date as Unix timestamp<BR>
        *           ["is_dir"]      =>  bool false, cause this is a file<BR>
        *
        * @access public
        * @param string $dir The directory to list or null, when listing the current directory.
        * @param int $mode The mode which types to list (files, directories or both).
        * @return array $listing The directory list as described above
        * @throw Exception
        */

        public function ls ( $dir = null, $mode = NET_FTP_DIRS_FILES ) {
            if (!isset($dir)) {
                $dir = @ftp_pwd($this->_handle);
                if (!$dir) {
                    throw new Exception("Could not retrieve current directory");
                }
            }
            if (($mode != NET_FTP_FILES_ONLY) && ($mode != NET_FTP_DIRS_ONLY) && ($mode != NET_FTP_RAWLIST)) {
                $mode = NET_FTP_DIRS_FILES;
            }
            try {
                switch ($mode) {
                    case NET_FTP_DIRS_FILES:    $res = $this->_ls_both ( $dir );
                                                break;
                    case NET_FTP_DIRS_ONLY:     $res = $this->_ls_dirs ( $dir );
                                                break;
                    case NET_FTP_FILES_ONLY:    $res = $this->_ls_files ( $dir );
                                                break;
                    case NET_FTP_RAWLIST:       $res = @ftp_rawlist($this->_handle, $dir);
                                                break;
                }
            } catch (Exception $e) {
                throw $e;
            }
            return $res;
        }

        /**
        * This will delete a file or directory (maybe recursive).
        * This method will delete the given file or directory ($path) from the server.
        *
        * Wether the given string is a file or directory is only determined by the last
        * sign inside the string ("/" or not).
        *
        * If you specify a directory, you can optionally specify $recursive as true,
        * to let the directory be deleted recursive (with all sub-directories and files
        * inherited).
        *
        * You can either give a absolute or relative path for the file / dir. If you choose to
        * use the relative path, it will be automatically completed with the actual
        * selected directory.
        *
        * @access public
        * @param string $path The absolute or relative path to the file / directory.
        * @return void
        * @throw Exception
        */

        public function rm ( $path, $recursive = false ) {

            $path = $this->_construct_path($path);

            if ($this->_check_dir($path)) {
                if ($recursive) {
                    return $this->_rm_dir_recursive($path);
                } else {
                    return $this->_rm_dir($path);
                }
            } else {
                return $this->_rm_file($path);
            }
        }

        /**
        * Download a file from the ftp-server
        * This function will download a file from the ftp-server. You can either spcify a absolute
        * path to the file (beginning with "/") or a relative one, which will be completed
        * with the actual directory you selected on the server. You can specify
        * the path to which the file will be downloaded on the local
        * maschine, if the file should be overwritten if it exists (optionally, default is
        * no overwriting) and in which mode (FTP_ASCII or FTP_BINARY) the file should be
        * downloaded (if you do not specify this, the method tries to determine it automatically
        * from the mode-directory or uses the default-mode, set by you). If you give a relative
        * path to the local-file, the script-path is used as basepath.
        *
        * @access public
        * @param string $remote_file The absolute or relative path to the file to download
        * @param string $local_file The local file to put the downloaded in
        * @param bool $overwrite Wether to overwrite existing file
        * @param int $mode Either FTP_ASCII or FTP_BINARY
        * @return bool $res True on success, otherwise PEAR::Error
        */

        public function get ( $remote_file, $local_file, $overwrite = false, $mode = null ) {

            if (!isset($mode)) {
                $mode = $this->checkFileExtension($remote_file);
            }

            $remote_file = $this->_construct_path($remote_file);

            if (@file_exists($local_file) && !$overwrite) {
                return PEAR::raiseError("Local file '$local_file' exists and may not be overwriten.", 0);
            }
            if (@file_exists($local_file) && !@is_writeable($local_file) && $overwrite) {
                return PEAR::raiseError("Local file '$local_file' is not writeable. Can not overwrite.", 0);
            }

            if (@function_exists('ftp_nb_get')){
	            $res = @ftp_nb_get($this->_handle, $local_file, $remote_file, $mode);
	            while ($res == FTP_MOREDATA) {
	                $res = @ftp_nb_continue ($this->_handle);
	            }
	        } else {
	            $res = @ftp_get($this->_handle, $local_file, $remote_file, $mode);
	        }
            if (!$res) {
                return PEAR::raiseError("File '$remote_file' could not be downloaded to '$local_file'.", 0);
            } else {
                return true;
            }
        }

        /**
        * Upload a file from the ftp-server
        * This function will upload a file to the ftp-server. You can either spcify a absolute
        * path to the remote-file (beginning with "/") or a relative one, which will be completed
        * with the actual directory you selected on the server. You can specify
        * the path from which the file will be uploaded on the local
        * maschine, if the file should be overwritten if it exists (optionally, default is
        * no overwriting) and in which mode (FTP_ASCII or FTP_BINARY) the file should be
        * downloaded (if you do not specify this, the method tries to determine it automatically
        * from the mode-directory or uses the default-mode, set by you). If you give a relative
        * path to the local-file, the script-path is used as basepath.
        *
        * @access public
        * @param string $local_file The local file to upload
        * @param string $remote_file The absolute or relative path to the file to upload to
        * @param bool $overwrite Wether to overwrite existing file
        * @param int $mode Either FTP_ASCII or FTP_BINARY
        * @return bool $res True on success, otherwise PEAR::Error
        */

        public function put ( $local_file, $remote_file, $overwrite = false, $mode = null ) {

            if (!isset($mode)) {
                $mode = $this->checkFileExtension($local_file);
            }
            $remote_file = $this->_construct_path($remote_file);

            if (!@file_exists($local_file)) {
                return PEAR::raiseError("Local file '$local_file' does not exist.", 0);
            }
            if ((@ftp_size($this->_handle, $remote_file) != -1) && !$overwrite) {
                return PEAR::raiseError("Remote file '$remote_file' exists and may not be overwriten.", 0);
            }

            if (function_exists('ftp_nb_put')){
	            $res = @ftp_nb_put($this->_handle, $remote_file, $local_file, $mode);
	            while ($res == FTP_MOREDATA) {
	                $res = @ftp_nb_continue($this->_handle);
	            }

            } else {
	            $res = @ftp_put($this->_handle, $remote_file, $local_file, $mode);
	        }
            if (!$res) {
                return PEAR::raiseError("File '$local_file' could not be uploaded to '$remote_file'.", 0);
            } else {
                return true;
            }
        }


        /**
        * Transfer a whole directory-structure to the local host
        * This functiona allows you to transfer a whole directory-structure from the
        * remote-ftp to your local host. You have to give a remote-directory (ending with
        * '/') and the local directory (ending with '/') where to put the files you download.
        * The remote path is automatically completed with the current-remote-dir, if you give
        * a relative path to this function. You can give a relative path for the $local_path,
        * too. Then the script-basedir will be used for comletion of the path.
        * The parameter $overwrite will determine, wether to overwrite existing files or not.
        * Standard for this is false. Fourth you can explicitly set a mode for all transfer-
        * actions done. If you do not set this, the method tries to determine the transfer-
        * mode by checking your mode-directory for the file-extension. If the extension is not
        * inside the mode-directory, it will get your default-mode.
        *
        * @access public
        * @param string $remote_path The path to download
        * @param string $local_path The path to download to
        * @param bool $overwrite Wether to overwrite existing files (true) or not (false, standard). (optional)
        * @param int $mode The transfermode (either FTP_ASCII or FTP_BINARY). (optional)
        * @return bool $res True on succes, otherwise PEAR::Error
        */

        public function getRecursive ( $remote_path, $local_path, $overwrite = false, $mode = null ) {

            $remote_path = $this->_construct_path($remote_path);
            if (!$this->_check_dir($remote_path)) {
                return PEAR::raiseError("Given remote-path '$remote_path' seems not to be a directory.", 0);
            }
            if (!$this->_check_dir($local_path)) {
                return PEAR::raiseError("Given local-path '$local_path' seems not to be a directory.", 0);
            }

            if (!@is_dir($local_path)) {
                $res = @mkdir($local_path);
                if (!$res) {
                    return PEAR::raiseError("Could not create dir '$local_path'", 0);
                }
            }
            $dir_list = array();
            $dir_list = $this->ls($remote_path, $mode = NET_FTP_DIRS_ONLY);
            foreach ($dir_list as $dir_entry) {
                $remote_path_new = $remote_path.$dir_entry["name"]."/";
                $local_path_new = $local_path.$dir_entry["name"]."/";
                $result = $this->getRecursive($remote_path_new, $local_path_new, $overwrite, $mode);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
            $file_list = array();
            $file_list = $this->ls($remote_path, $mode = NET_FTP_FILES_ONLY);
            foreach ($file_list as $file_entry) {
                $remote_file = $remote_path.$file_entry["name"];
                $local_file = $local_path.$file_entry["name"];
                $result = $this->get($remote_file, $local_file, $overwrite, $mode);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
            return true;
        }

        /**
        * Transfer a whole directory-structure to the local host
        * This functiona allows you to transfer a whole directory-structure from the
        * remote-ftp to your local host. You have to give a remote-directory (ending with
        * '/') and the local directory (ending with '/') where to put the files you download.
        * The remote path is automatically completed with the current-remote-dir, if you give
        * a relative path to this function. You can give a relative path for the $local_path,
        * too. Then the script-basedir will be used for comletion of the path.
        * The parameter $overwrite will determine, wether to overwrite existing files or not.
        * Standard for this is false. Fourth you can explicitly set a mode for all transfer-
        * actions done. If you do not set this, the method tries to determine the transfer-
        * mode by checking your mode-directory for the file-extension. If the extension is not
        * inside the mode-directory, it will get your default-mode.
        *
        * @access public
        * @param string $remote_path The path to download
        * @param string $local_path The path to download to
        * @param bool $overwrite Wether to overwrite existing files (true) or not (false, standard). (optional)
        * @param int $mode The transfermode (either FTP_ASCII or FTP_BINARY). (optional)
        * @return bool $res True on succes, otherwise PEAR::Error
        */

        public function putRecursive ( $local_path, $remote_path, $overwrite = false, $mode = null ) {
            $remote_path = $this->_construct_path($remote_path);
            if (!$this->_check_dir($local_path) || !is_dir($local_path)) {
                return PEAR::raiseError("Given local-path '$local_path' seems not to be a directory.", 0);
            }
            if (!$this->_check_dir($remote_path)) {
                return PEAR::raiseError("Given remote-path '$remote_path' seems not to be a directory.", 0);
            }
            $old_path = $this->pwd();
            if (PEAR::isError($this->cd($remote_path))) {
                $res = $this->mkdir($remote_path);
                if (PEAR::isError($res)) {
                    return $res;
                }
            }
            $this->cd($old_path);
            $dir_list = $this->_ls_local($local_path);
            foreach ($dir_list["dirs"] as $dir_entry) {
                $remote_path_new = $remote_path.$dir_entry."/";
                $local_path_new = $local_path.$dir_entry."/";
                $result = $this->putRecursive($local_path_new, $remote_path_new, $overwrite, $mode);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }

            foreach ($dir_list["files"] as $file_entry) {
                $remote_file = $remote_path.$file_entry;
                $local_file = $local_path.$file_entry;
                $result = $this->put($local_file, $remote_file, $overwrite, $mode);
                /*if (PEAR::isError($result)) {
                    return $result;
                }*/
            }
            return true;
        }

        /**
        * Check a filname to be transfered in ascii- or binary-mode
        * This checks, wether a file should be transfered in ascii- or binary-mode
        * by it's file-extension. If the file-extension is not set or
        * the extension is not inside one of the extension-dirs, the actual set
        * transfer-mode is returned.
        *
        * @access public
        * @param string $filename The filename to be checked
        * @return int Either FTP_ASCII or FTP_BINARY
        */

        public function checkFileExtension ( $filename ) {

            $pattern = "/\.(.*)$/";
            $has_extension = preg_match($pattern, $filename, $eregs);
            if (!$has_extension) {
                return $this->_mode;
            } else {
                $ext = $eregs[1];
            }

            if (!empty($this->_file_extensions[$ext])) {
                return $this->_file_extensions[$ext];
            }

            return $this->_mode;
        }

        /**
        * Reconstruates the path, if given relative
        * Reconstruates the path, if given relative
        *
        * @access private
        * @param string $path The path to check and construct
        * @return string $path The build path
        */

        private function _construct_path ( $path ) {

            if (substr($path, 0, 1) != "/") {
                $actual_dir = @ftp_pwd($this->_handle);
                if (substr($actual_dir, (strlen($actual_dir) - 2), 1) != "/") {
                    $actual_dir .= "/";
                }
                $path = $actual_dir.$path;
            }
            return $path;
        }

        /**
        * Checks, wether a given string is a directory-path (ends with "/") or not.
        * Checks, wether a given string is a directory-path (ends with "/") or not.
        *
        * @access private
        * @param string $path Path to check
        * @return bool $res True if $path is a directory, otherwise false
        */

        private function _check_dir ( $path ) {

            if (substr($path, (strlen($path) - 1), 1) == "/") {
                return true;
            } else {
                return false;
            }
        }

        /**
        * This will remove a file
        * This will remove a file
        *
        * @access private
        * @param string $file The file to delete
        * @return bool $res true on success, otherwise PEAR::Error
        */

        private function _rm_file ( $file ) {

            if (substr($file, 0, 1) == "/") {
                $res = @ftp_delete($this->_handle, $file);
            } else {
                $actual_dir = @ftp_pwd($this->_handle);
                if (substr($actual_dir, (strlen($actual_dir) - 2), 1) != "/") {
                    $actual_dir .= "/";
                }
                $file = $actual_dir.$file;
                $res = @ftp_delete($this->_handle, $file);
            }

            if (!$res) {
                return PEAR::raiseError("Could not delete file '$file'.", 0);
            } else {
                return true;
            }
        }

        /**
        * This will remove a dir
        * This will remove a dir
        *
        * @access private
        * @param string $dir The dir to delete
        * @return bool $res true on success, otherwise PEAR::Error
        */

        private function _rm_dir ( $dir ) {
            if (substr($dir, (strlen($dir) - 1), 1) != "/") {
                return PEAR::raiseError("Directory name '$dir' is invalid, has to end with '/'", 0);
            }
            $res = @ftp_rmdir($this->_handle, $dir);
            if (!$res) {
                return PEAR::raiseError("Could not delete directory '$dir'.", 0);
            } else {
                return true;
            }
        }

        /**
        * This will remove a dir and all subdirs and -files
        * This will remove a dir and all subdirs and -files
        *
        * @access private
        * @param string $file The dir to delete recursively
        * @return bool $res true on success, otherwise PEAR::Error
        */

        private function _rm_dir_recursive ( $dir ) {
            if (substr($dir, (strlen($dir) - 1), 1) != "/") {
                return PEAR::raiseError("Directory name '$dir' is invalid, has to end with '/'", 0);
            }
            $file_list = $this->_ls_files($dir);
            foreach ($file_list as $file) {
                $file = $dir.$file["name"];
                $res = $this->rm($file);
                if (PEAR::isError($res)) {
                    return $res;
                }

            }
            $dir_list = $this->_ls_dirs($dir);
            foreach ($dir_list as $new_dir) {
                $new_dir = $dir.$new_dir["name"]."/";
                $res = $this->_rm_dir_recursive($new_dir);
                if (PEAR::isError($res)) {
                    return $res;
                }
            }
            $res = $this->_rm_dir($dir);
            if (!$res) {
                return $res;
            } else {
                return true;
            }
        }

        /**
        * Lists up files and directories
        * Lists up files and directories
        *
        * @access private
        * @param string $dir The directory to list up
        * @return array $res An array of dirs and files
        */

        private function _ls_both ( $dir ) {
            $list_splitted = $this->_list_and_parse($dir);
            if (!is_array($list_splitted["files"])) {
                $list_splitted["files"] = array();
            }
            if (!is_array($list_splitted["dirs"])) {
                $list_splitted["dirs"] = array();
            }
            $res = array();
            @array_splice($res, 0, 0, $list_splitted["files"]);
            @array_splice($res, 0, 0, $list_splitted["dirs"]);
            return $res;
        }

        /**
        * Lists up directories
        * Lists up directories
        *
        * @access private
        * @param string $dir The directory to list up
        * @return array $res An array of dirs
        */

        private function _ls_dirs ( $dir ) {
            $list["dirs"] = array();
            $list = $this->_list_and_parse($dir);
            return $list["dirs"];
        }

        /**
        * Lists up files
        * Lists up files
        *
        * @access private
        * @param string $dir The directory to list up
        * @return array $res An array of files
        */

        private function _ls_files ( $dir ) {
            $list = $this->_list_and_parse($dir);
            if (!is_array($list["files"])) {
                $list["files"] = array();
            }
            return $list["files"];
        }

        /**
        * This lists up the directory-content and parses the items into well-formated arrays
        * This lists up the directory-content and parses the items into well-formated arrays
        * The results of this array are sorted (dirs on top, sorted by name;
        * files below, sorted by name).
        *
        * @access private
        * @param string $dir The directory to parse
        * @return array $res Lists of dirs and files
        */

        private function _list_and_parse ( $dir ) {
            $dirs_list = array();
            $files_list = array();
            $dir_list = @ftp_rawlist($this->_handle, $dir);
            foreach ($dir_list as $entry) {
                if (!preg_match($this->ls_match, $entry, $m)) {
                    continue;
                }
                $entry = array();
    	        foreach ($this->ls_regex_map as $key=>$val) {
	        	    $entry[$key] = $m[$val];
        	    }
            	$entry['stamp'] = $this->_parse_Date($entry['date']);

                if ($entry['is_dir']) {
                    $dirs_list[] = $entry;
                } else {
                    $files_list[] = $entry;
                }
            }
            @usort($dirs_list, array("Net_FTP", "_nat_sort"));
            @usort($files_list, array("Net_FTP", "_nat_sort"));
            $res["dirs"] = $dirs_list;
            $res["files"] = $files_list;
            return $res;
        }

        /**
        * Lists a local directory
        * Lists a local directory
        *
        * @access private
        * @param string $dir_path The dir to list
        * @return array $res The list of dirs and files
        */

        private function _ls_local ($dir_path) {

            $dir = dir($dir_path);
            $dir_list = array();
            $file_list = array();
            while (false !== ($entry = $dir->read())) {
                if (($entry != '.') && ($entry != '..')) {
                    if (is_dir($dir_path.$entry)) {
                        $dir_list[] = $entry;
                    } else {
                        $file_list[] = $entry;
                    }
                }
            }
            $dir->close();
            $res['dirs'] = $dir_list;
            $res['files'] = $file_list;
            return $res;
        }


        /**
        * Function for use with usort().
        * Function for use with usort().
        * Compares the list-array-elements by name.
        *
        * @access private
        */

        private function _nat_sort ( $item_1, $item_2 ) {
            return strnatcmp($item_1['name'], $item_2['name']);
        }

        /**
        * Parse dates to timestamps
        * Parse dates to timestamps
        *
        * @access private
        * @param string $date Date
        * @return int $res Timestamp
        */

        private function _parse_Date ( $date ) {
            // Sep 10 22:06 => Sep 10, <year> 22:06
            if (preg_match('/([A-Za-z]+)[ ]+([0-9]+)[ ]+([0-9]+):([0-9]+)/', $date, $res)) {
                $year = date('Y');
                $month = $res[1];
                $day = $res[2];
                $hour = $res[3];
                $minute = $res[4];
                $date = "$month $day, $year $hour:$minute";
            }
            // 09-10-04 => 09/10/04
            elseif (preg_match('/^\d\d-\d\d-\d\d/',$date)) {
            	$date = str_replace('-','/',$date);
            }
            $res = strtotime($date);
            if (!$res) {
                return PEAR::raiseError('Dateconversion failed.', 0);
            }
            return $res;
        }

        /**
        * Set the Hostname
        * Set the Hostname
        *
        * @access public
        * @param string $host The Hostname to set
        */

        public function setHostname ( $host ) {
            $this->_hostname = $host;
        }

        /**
        * Set the Port
        * Set the Port
        *
        * @access public
        * @param int $port The Port to set
        */

        public function setPort ( $port ) {
            $this->_port = $port;
        }

        /**
        * Set the Username
        * Set the Username
        *
        * @access public
        * @param string $user The Username to set
        */

        public function setUsername ( $user ) {
            $this->_username = $user;
        }

        /**
        * Set the Password
        * Set the Password
        *
        * @access private
        * @param string $password The Password to set
        */

        public function setPassword ( $password ) {
            $this->_password = $password;
        }

        /**
        * Set the transfer-mode
        * Set the transfer-mode. You can use the predefined constants
        * FTP_ASCII or FTP_BINARY. The mode will be stored for any further transfers.
        *
        * @access public
        * @param int $mode The mode to set
        * @return bool $res True on success, otherwise PEAR::Error
        */

        public function setMode ( $mode ) {
            if (($mode == FTP_ASCII) || ($mode == FTP_BINARY)) {
                $this->_mode = $mode;
                return true;
            } else {
                return PEAR::raiseError('FTP-Mode has either to be FTP_ASCII or FTP_BINARY', 1);
            }
        }

        /**
        * Set the transfer-method to passive mode
        * Set the transfer-method to passive mode
        *
        * @access public
        * @return void
        */

        public function setPassive () {
            $this->_passv = true;
            @ftp_pasv($this->_handle, true);
        }

        /**
        * Set the transfer-method to active mode
        * Set the transfer-method to active mode
        *
        * @access public
        * @return void
        */

        public function setActive () {
            $this->_passv = false;
            @ftp_pasv($this->_handle, false);
        }

        /**
        * Adds an extension to a mode-directory
        * The mode-directory saves file-extensions coresponding to filetypes
        * (ascii e.g.: 'php', 'txt', 'htm',...; binary e.g.: 'jpg', 'gif', 'exe',...).
        * The extensions have to be saved without the '.'. And
        * can be predefined in an external file (see: getExtensionsFile()).
        *
        * The array is build like this: 'php' => FTP_ASCII, 'png' => FTP_BINARY
        *
        * To change the mode of an extension, just add it again with the new mode!
        *
        * @access public
        * @param int $mode Either FTP_ASCII or FTP_BINARY
        * @param string extension
        * @return void
        */

        public function addExtension ( $mode, $ext ) {
            $this->_file_extensions[$ext] = $mode;
        }

        /**
        * Remove an extension from a mode-directory
        * The removes an extension from the mode-directories (described above).
        *
        * @access public
        * @param string $ext The extension to remove
        * @return void
        */

        public function removeExtension ( $ext ) {
            unset($this->_file_extensions[$ext]);
        }

        /**
        * Get the mode-directories from a file
        * This get's both (ascii- and binary-mode-directories) from the given file. Beware,
        * if you read a file into the mode-directory, all former set values will be
        * unset!
        *
        * @access public
        * @param string $filename The file to get from
        * @return bool True on success, otherwise PEAR::Error
        */

        public function getExtensionsFile ( $filename ) {

            if (!file_exists($filename)) {
                return PEAR::raiseError("Extensions-file '$filename' does not exist", 0);
            }

            if (!is_readable($filename)) {
                return PEAR::raiseError("Extensions-file '$filename' is not readable", 0);
            }

            $this->_file_extension = @parse_ini_file($filename);
            return true;
        }

        /**
        * Returns the Hostname
        *
        * @access public
        * @return string $host The Hostname
        */

        public function getHostname ( ) {
            return $this->_hostname;
        }

        /**
        * Returns the Port
        *
        * @access public
        * @return int $port The Port
        */

        public function getPort ( ) {
            return $this->_port;
        }

        /**
        * Returns the Username
        *
        * @access public
        * @return string $username The Username
        */

        public function getUsername ( ) {
            return $this->_username;
        }

        /**
        * Returns the Password
        *
        * @access public
        * @return string $password The Password
        */

        public function getPassword ( ) {
            return $this->_password;
        }

        /**
        * Returns the Transfermode
        * Returns the Transfermode
        *
        * @access public
        * @return int $mode The transfermode, either FTP_ASCII or FTP_BINARY.
        */

        public function getMode ( ) {
            return $this->_mode;
        }

        /**
        * Returns, wether the connection is set to passive mode or not
        * Returns, wether the connection is set to passive mode or not
        *
        * @access public
        * @return bool True if passive-, false if active-mode
        */

        public function isPassive ( ) {
            return $this->_passive;
        }

        /**
        * Returns the mode set for a file-extension
        * Returns the mode set for a file-extension
        *
        * @access public
        * @param string $ext The extension you wanna ask for
        * @return int $mode Either FTP_ASCII, FTP_BINARY or NULL (if not set a mode for it)
        */

        public function getExtensionMode ( $ext ) {
            return @$this->_file_extensions[$ext];
        }

    }
?>
