<?php

/*
 * moFilesManager\File
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
use moFilesManager;

/**
 * File handler class.
 *
 * @since       1.0.0
 */
class File {

    private $mime_types_object = FALSE; 

    private $path = FALSE;
    private $name = FALSE;
    private $folder = FALSE;
    private $file_exist = FALSE;

    private $perms = FALSE;
    private $is_readable = FALSE;
    private $is_writable = FALSE;

    private $size = 0;
    private $content = FALSE;

    private $extension = '';
    private $mime_type = '';
    private $type = '';

    private $last_access_time = 0;
    private $last_update_time = 0;

    private $upload_error = array();

    /**
	 * Just an alias of moFilesManager::formatPath()

     * @param   string  $path  The path to format
     * 
     * @return  string $path
	 *
	 * @since   1.0.0
	 */
    private function formatPath(string $path) : string {
        
        return moFilesManager\moFilesManager::formatPath( $path ); 

    }

    /**
	 * Set Filepath on the fly
     * 
     * @param   string  $path  The path of this object
     * 
     * @return  object  The current file object
	 *
	 * @since   1.0.0
	 */
    public function setPath(string $path) : object {

        // Reset the current properties
        $this->path = FALSE;
        $this->name = FALSE;
        $this->folder = FALSE;
        $this->file_exist = FALSE;

        $this->perms = FALSE;
        $this->is_readable = FALSE;
        $this->is_writable = FALSE;

        $this->size = 0;
        $this->content = FALSE;

        $this->extension = '';
        $this->mime_type = '';
        $this->type = '';

        $this->last_access_time = 0;
        $this->last_update_time = 0;

        if( strlen($path) > 0 ) {

            $this->path = $this->formatPath($path);
            $this->name = basename($this->path);
            $this->folder = dirname($this->path);
            $this->file_exist = is_file($this->path);

            if($this->file_exist === TRUE) {

                $this->size = filesize($this->path);

                $this->perms = substr(sprintf('%o', fileperms($this->path)), -4);
                $this->is_writable = is_writable($this->path);
                $this->is_readable = is_readable($this->path);

                $path_info = pathinfo($this->path);
                if(isset($path_info['extension'])) {

                    $this->extension = $path_info['extension'];

                }
                else {

                    $this->extension = '';

                }

                if($this->is_readable === TRUE) {

                    $this->content = file_get_contents($this->path);
                    $this->mime_type = mime_content_type($this->path);
                    $this->type = $this->mime_types_object->getType( $this->mime_type );

                }
                else {

                    $this->content = FALSE;
                    $this->mime_type = FALSE;
                    $this->type = FALSE;

                }

                $this->last_access_time = fileatime($this->path);
                $this->last_update_time = filectime($this->path);

            }

        }
        else {

            moFilesManager\moFilesManager::addLog('The provided path is an empty string.', 'WARNING');
  
        }
        
        return $this;

    }

    /**
	 * Return the current file content
	 *
	 * @return  string  The current file content
	 *
	 * @since   1.0.0
	 */
    public function getContent() : string {

        if($this->is_readable === FALSE) {

            moFilesManager\moFilesManager::addLog('The current file "'.$this->path.'" is not readable. Return : empty string.', 'ERROR');
            return '';

        }

        if(is_string($this->content)) {

            return $this->content;

        }

        return '';
    }

    /**
	 * Set file content
     * 
     * @param   string  $content  The content that will be added to the file
	 *
	 * @return  object  The current file object
	 *
	 * @since   1.0.0
	 */
    public function setContent(string $content) : object {

        if($this->file_exist === TRUE AND $this->is_writable === FALSE) {

            moFilesManager\moFilesManager::addLog('The current file "'.$this->path.'" is not writable.', 'ERROR');
            return $this;

        }

        if(!empty($content)) {

            $this->content = $content;

        }
        else {

            moFilesManager\moFilesManager::addLog('No content has been added because the provided string is empty.', 'ERROR');
   
        }

        return $this;
    }

    /**
	 * Append string to the current file content 
     * 
     * @param   string  $content  The content that will be added to the file
     * @param   boolean  $prepend  True to add the content at the begining of the file. Default : FALSE
	 *
	 * @return  object  The current file object
	 *
	 * @since   1.0.0
	 */
    public function addContent(string $content, bool $prepend = FALSE) : object {

        if($this->file_exist === TRUE AND $this->is_writable === FALSE) {

            moFilesManager\moFilesManager::addLog('The current file "'.$this->path.'" is not writable.', 'ERROR');
            return $this;

        }

        if(!empty($content)) {

            if(is_string($this->content)) {

                if($prepend === TRUE) {

                    $content.= $this->content;
                    $this->content = $content;

                }
                else {

                    $this->content.= $content;

                }

            }
            else {

                $this->setContent( $content );

            }

        }
        else {

            moFilesManager\moFilesManager::addLog('No content has been added because the provided string is empty.', 'WARNING');

        }

        return $this;
    }

    /**
	 * Copy the current file
     * 
     * @param   string  $copy_path  The path of the copy
     * @param   boolean  $replace  True to replace the current file if it already exist. Default : TRUE
	 *
	 * @return  bool  TRUE in case of success, else FALSE
	 *
	 * @since   1.0.0
	 */
    public function copy(string $copy_path, bool $replace = TRUE) : bool {

        if($this->file_exist === FALSE) {

            moFilesManager\moFilesManager::addLog('The current file "'.$this->path.'" does not exist. Return : FALSE.', 'ERROR');
            return FALSE;

        }

        $copy_path = $this->formatPath( $copy_path );

        if(is_file($copy_path) === TRUE AND $replace === FALSE) {

            moFilesManager\moFilesManager::addLog('the file "'.$copy_path.'" already exist and Replace is set to FALSE, so we return FALSE', 'WARNING');
            return FALSE;

        }

        // We check if the destination folder exist, anf if not, we create it
        if(!is_dir(dirname($copy_path))) {

            $folder_obj = new moFilesManager\Folder(dirname($copy_path));
            if($folder_obj->create() === FALSE) {

                moFilesManager\moFilesManager::addLog('the file "'.$this->path.'" has not been moved to "'.$copy_path.'" because Folder->create() has failed. Return : FALSE.', 'ERROR');
                return FALSE;
    
            }
            unset($folder_obj);       

        }

        if(copy($this->path, $copy_path) === FALSE) {

            moFilesManager\moFilesManager::addLog('the file "'.$this->path.'" has not been duplicated to "'.$copy_path.'" because copy() has failed. Return : FALSE.', 'ERROR');
            return FALSE;
            
        }

        moFilesManager\moFilesManager::addLog('the file "'.$this->path.'" has been well duplicated to "'.$copy_path.'"');
        return TRUE;

    }

    /**
	 * Rename the file
     * 
     * This method change the name of the current file, but it does not move the file
     * 
     * @param   string  $new_name  The new new of the file
     * @param   boolean  $replace  True to replace the current file if it already exist. Default : TRUE
	 *
	 * @return  bool  TRUE in case of success, else FALSE
	 *
	 * @since   1.0.0
	 */
    public function rename(string $new_name, bool $replace = TRUE) : bool {

        if($this->file_exist === FALSE) {

            moFilesManager\moFilesManager::addLog('The current file "'.$this->path.'" does not exist. Return : FALSE.', 'ERROR');
            return FALSE;

        }

        if(is_int(strpos($new_name, '/')) OR is_int(strpos($new_name, '\\'))) {

            moFilesManager\moFilesManager::addLog('DIRECTORY SEPARATOR detected, use File->move() instead of rename() if you want to change the location of the file "'.$this->path.'". Return FALSE', 'ERROR');
            return FALSE;

        }

        $new_path = $this->folder .DIRECTORY_SEPARATOR. $new_name;

        if(is_file($new_path) === TRUE AND $replace === FALSE) {

            moFilesManager\moFilesManager::addLog('the file "'.$new_path.'" already exist and Replace is set to FALSE, so we return FALSE', 'WARNING');
            return FALSE;

        }

        if(rename($this->path, $new_path) === FALSE) {

            moFilesManager\moFilesManager::addLog('the file "'.$this->path.'" has not been renamed to "'.$new_path.'" because remane() has failed. Return : FALSE.', 'ERROR');
            return FALSE;

        }

        moFilesManager\moFilesManager::addLog('the file "'.$this->path.'" has been well renamed to "'.$new_path.'"');
        $this->setPath($new_path);
        
        return TRUE;

    }

    /**
	 * Move the file
     * 
     * @param   string  $copy_path  The new path of the file
     * @param   boolean  $replace  True to replace the current file if it already exist. Default : TRUE
	 *
	 * @return  bool  TRUE in case of success, else FALSE
	 *
	 * @since   1.0.0
	 */
    public function move(string $new_path, bool $replace = TRUE) : bool {

        if($this->file_exist === FALSE) {

            moFilesManager\moFilesManager::addLog('The current file "'.$this->path.'" does not exist. Return : FALSE.', 'ERROR');
            return FALSE;

        }

        $new_path = $this->formatPath( $new_path );

        if(is_file($new_path) === TRUE AND $replace === FALSE) {

            moFilesManager\moFilesManager::addLog('the file "'.$new_path.'" already exist and Replace is set to FALSE, so we return FALSE', 'WARNING');
            return FALSE;

        }

        // We check if the destination folder exist, anf if not, we create it
        if(!is_dir(dirname($new_path))) {

            $folder_obj = new moFilesManager\Folder(dirname($new_path));
            if($folder_obj->create() === FALSE) {

                moFilesManager\moFilesManager::addLog('the file "'.$this->path.'" has not been moved to "'.$new_path.'" because Folder->create() has failed. Return : FALSE.', 'ERROR');
                return FALSE;
    
            }
            unset($folder_obj);         

        }

        if(rename($this->path, $new_path) === FALSE) {

            moFilesManager\moFilesManager::addLog('the file "'.$this->path.'" has not been moved to "'.$new_path.'" because remane() has failed. Return : FALSE.', 'ERROR');
            return FALSE;

        }

        moFilesManager\moFilesManager::addLog('the file "'.$this->path.'" has been well moved to "'.$new_path.'"');
        $this->setPath($new_path);
        
        return TRUE;

    }

    /**
	 * Create or replace the current file
     * 
     * @param   boolean  $replace  True to replace the current file if it already exist. Default : TRUE
	 *
	 * @return  bool  TRUE in case of success, else FALSE
	 *
	 * @since   1.0.0
	 */
    public function write(bool $replace = TRUE) : bool {

        if($this->file_exist === TRUE AND $replace === FALSE) {
            
            moFilesManager\moFilesManager::addLog('the file "'.$this->path.'" has not been written it already exist and Replace is set to FALSE. Return FALSE.', 'WARNING');
            return FALSE;
        
        }

        $stream = fopen($this->path, 'w+');
        if(fwrite($stream, $this->content) === FALSE) {

            moFilesManager\moFilesManager::addLog('the file "'.$this->path.'" has not been written because fwrite() has failed', 'ERROR');
            fclose($stream);
            return FALSE;

        }
        fclose($stream);

        moFilesManager\moFilesManager::addLog('the file "'.$this->path.'" has been well written');
        $this->setPath($this->path);

        return TRUE;

    }

    /**
	 * Delete the current file
	 *
	 * @return  bool  TRUE in case of success, else FALSE
	 *
	 * @since   1.0.0
	 */
    public function delete() : bool {

        if($this->file_exist === FALSE) {

            moFilesManager\moFilesManager::addLog('The current file "'.$this->path.'" does not exist. Return : FALSE.', 'ERROR');
            return FALSE;

        }

        if(unlink($this->path) === FALSE) {

            moFilesManager\moFilesManager::addLog('the file "'.$this->path.'" has not been deleted because unlink() has failed', 'ERROR');
            return FALSE;

        }

        moFilesManager\moFilesManager::addLog('the file "'.$this->path.'" has been well deleted');
        $this->setPath($this->path);

        return TRUE;
    
    }

    /**
	 * File uploading
     * The $max_size parameter must be provided in Octet. Note that this parameter don't overide the "max upload" parameters of your PHP configuration
	 *
     * @param   string  $tmp_name The file tmp_name
     * @param   boolean  $replace  True to replace the current file if it already exist. Default : TRUE
     * @param   array $allowed_types Provide one or more file type that will be accepted, if this param if empty, all types will be accepted
     * @param   integer $max_size Provide a file max size (in octet)
     * 
	 * @return  bool  TRUE in case of success, else FALSE
	 *
	 * @since   1.0.0
	 */
    public function upload(string $tmp_name, bool $replace = TRUE, array $allowed_types = array(), int $max_size = 0) : bool {

        $this->upload_error = array();
        
        if($this->file_exist === TRUE AND $replace === FALSE) {
            
            $this->upload_error[] = 'ALREADY_EXIST_ERROR';
        
        }

        if(is_file($tmp_name) === FALSE) {

            $this->upload_error[] = 'TMP_NAME_NOT_EXIST_ERROR';
            moFilesManager\moFilesManager::addLog('the provided tmp_file name "'.$tmp_name.'" does not exist.', 'ERROR');

        }

        // Check the file size
        // @see https://stackoverflow.com/questions/13076480/php-get-actual-maximum-upload-size
        $php_size_unit = strtolower(preg_replace('/[^bkmgtpezy]/i', '', ini_get('upload_max_filesize')));
        $php_size = (int) preg_replace('/[^0-9\.]/', '', ini_get('upload_max_filesize'));
        $upload_max_filesize = round($php_size * pow(1024, stripos('bkmgtpezy', $php_size_unit)));
        $tmp_file_size = filesize($tmp_name);
        if($max_size > 0) {

            if($tmp_file_size > $max_size OR $tmp_file_size > $upload_max_filesize) {
                
                $this->upload_error[] = 'SIZE_ERROR';
            
            }

        }
        else {

            if($tmp_file_size > $upload_max_filesize) {
                
                $this->upload_error[] = 'SIZE_ERROR';
            
            }

        }

        // Check the file type
        if( count($allowed_types) > 0) {

            if(!in_array( $this->mime_types_object->getType( mime_content_type($tmp_name) ), $allowed_types)) {

                $this->upload_error[] = 'TYPE_ERROR';

            }

        }

        // Check if the destination folder exist, and if not, create it
        if(!is_dir($this->folder)) {

            $folder_obj = new Folder($this->folder);
            if($folder_obj->create() === FALSE) {

                moFilesManager\moFilesManager::addLog('the destination folder "'.$this->folder.'" has not been created', 'ERROR');
                $this->upload_error[] = 'CREATE_FOLDER_ERROR';

            }
            else {

                moFilesManager\moFilesManager::addLog('the destination folder "'.$this->folder.'" has been well created');

            }
            unset($folder_obj);

        }

        if( count($this->upload_error) > 0)  {

            moFilesManager\moFilesManager::addLog('the file "'.$this->path.'" has not been uploaded. Recorded errors : "'.implode(' ,', $this->upload_error).'"', 'ERROR');
            return FALSE;

        }

        if(move_uploaded_file($tmp_name, $this->path) === FALSE) {
            
            $this->upload_error = 'MOVE_UPLOADED_FILE_ERROR';

            moFilesManager\moFilesManager::addLog('the file "'.$this->path.'" has not been uploaded because move_uploaded_file() has failded', 'ERROR');
            
            return FALSE;

        }

        moFilesManager\moFilesManager::addLog('the file "'.$this->path.'" has been well uploaded');
        $this->setPath($this->path);
    
        return TRUE;
        
    }

    public function __get($name) {

        if(array_key_exists($name, get_object_vars($this))) {

            return $this->$name;

        }

        return NULL;
    
    }

    public function __set($name, $value) {

        trigger_error('moFileManager object properties "'.$name.'" can\'t be setted', E_USER_WARNING);
    
    }
    
    public function __construct(string $path) {

        $this->mime_types_object = new FileMimeTypes();

        $this->setPath($path);

    }

}
?>
