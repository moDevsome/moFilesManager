<?php

/*
 * moFilesManager
 * 
 * A small library for handling files and folders more easier
 * 
 * Required PHP Version : PHP 7.0 or higher
 * 
 * Release id : 1.0
 * Release state : BETA
 * Release date : 2019-12-XX
 * 
 * @author Mickaël Outhier <contact@mickael-outhier.fr>
 *
 * @copyright (c) 2019 Mickaël Outhier (contact@mickael-outhier.fr)
 *  
 * @license The MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace moFilesManager;

$php_version = (float) substr(phpversion(), 0, 3);
if($php_version < 7.0) {

    trigger_error('moFilesManager require PHP 7.0 or higher', E_USER_ERROR);

}

require_once 'moFileManager.php';
require_once 'moFileManagerMimeTypes.php';
require_once 'moFolderManager.php';

class moFilesManager {

    private static $debug_state = FALSE;
    private static $display_error = FALSE;

    private static $last_called_method = '';
    private static $logs = array();

    private static $script_real_path = '';

    /**
     * Get the last recorded trace
     *
     * @return  string the last trace is debuging is enabled
     *
     * @since   1.0.0
     */
    public static function getLastLog() : string {

        $logs = __::getLogs();
        return $logs[count($logs) - 1];

    }

    /**
     * Get all recorded traces
     *
     * @return  array An array containing each trace is debuging is enabled
     *
     * @since   1.0.0
     */
    public static function getLogs() : array {

        return self::$logs;

    }

    /**
     * Get current debuging mode
     *
     * @return  array An array containing $state and $display_error value
     *
     * @since   1.0.0
     */
    public static function getDebugState() : array {

        return array('debug_state' => self::$debug_state, 'display_error' => self::$display_error);

    }

    /**
     * Enable or Disable debuging mode
     * 
     * @param   boolean  $state  TRUE to enable Log, FALSE to disable
     * @param   boolean  $display_error  True to display errors. Default : FALSE
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public static function setDebugState(bool $state, bool $display_error = FALSE) : void {

        self::$debug_state = $state;
        self::$display_error = $display_error;

    }

    /**
	 * formatPath()
     * rebuild and secure a given path by removing illegitimate directory separator and formating for to be well used by the class
     * 
     * @param   string  $path  The path to format
     * 
     * @return  string $path
	 *
	 * @since   1.0.0
	 */
    public static function formatPath(string $path) : string {
        
        $segments = explode(DIRECTORY_SEPARATOR, $path);
        $output = array();

        foreach($segments as $segment) {

            if(strlen($segment) > 0) {

                $output[] = $segment;

            }

        }

        return DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $output);
    }



    
    /**
     * PLEASE Don't use the following methods
     * *************************************
     */
    private static function checkCall() {

        $debug_back_trace = debug_backtrace();

        if(isset($debug_back_trace[1]['file'])) {

            $file_path_segments = explode(DIRECTORY_SEPARATOR, $debug_back_trace[1]['file']);
            $count_file_path_segments = count($file_path_segments);
            $file_name = $file_path_segments[$count_file_path_segments - 1];
            $file_folder = $file_path_segments[$count_file_path_segments - 3];

            if(in_array($file_name, array('moFolderManager.php','moFileManager.php','moFilesManager.php')) AND $file_folder === 'mofilesmanager') {

                return TRUE;

            }

        }
        
        return FALSE;
    }

    public static function addLog(string $log, string $type = 'INFO') : void {

        if(self::checkCall() === FALSE) { trigger_error('Wrong way ! moFilesManager::addLog() cannot be called from this context', E_USER_ERROR); }

        if(self::$debug_state === FALSE) { return; }

        $call_line = debug_backtrace()[0]['line'];
        $back_trace = debug_backtrace()[1];
        $index = count(self::$logs) + 1;
        $log = date('Y-m-d H:i:s').' ['.$index.'] '.$type.' ['.$back_trace['class'].$back_trace['type'].$back_trace['function'].'()][line '.$call_line.'] '.$log;
        
        if( $back_trace['function'] !== self::$last_called_method ) {

            self::$last_called_method = $back_trace['function'];
            $log.= PHP_EOL.'Called from '.$back_trace['file'].':'.$back_trace['line'];

        }
        
        if(self::$display_error === TRUE) {

            if($type !== 'INFO') {

                trigger_error($log, E_USER_WARNING);

            }

        }

        self::$logs[] = $log;
    }

    public static function getRandomString() : string {
        
        if(self::checkCall() === FALSE) { trigger_error('Wrong way ! moFilesManager::addLog() cannot be called from this context', E_USER_ERROR); }

        $characters = str_split( str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') );
        $start = rand(12, count($characters) - 12);
        $counter = 1;
        $random_string = substr( md5(date('YmdHis')) , 2, rand(6, 9));

        do {

            $random_string.= $characters[$start];
            $start++;
            $counter++;

        }
        while($counter < 9);

        return $random_string;

    }

}
?>