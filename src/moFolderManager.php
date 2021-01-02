<?php

/*
 * moFilesManager\Folder
 * 
 * @author Mickaël Outhier <contact@mickael-outhier.fr>
 *
 * @copyright (c) 2021 Mickaël Outhier (contact@mickael-outhier.fr)
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
 * Folder handler class.
 *
 * @since       1.1.0
 * @version     Last change on 1.1.3 // 2020-12-20
 */
class Folder {

    private $path = FALSE;
    private $folder_exist = FALSE; // TRUE if the current folder exist, else FALSE
    private $content = array('files' => array(), 'folders' => array());

    private $tmp_backup_folder_path = ''; // The path of a saved folder

    private $deleted_elements = array('folders' => array(), 'files' => array());

    private $folder_hierarchy_security = TRUE; // Keep it at TRUE !! For ever !

    /** The following properties are associated with the zip() and __zip() methods
     *  Default compression method and default compression level can be overrided by passing $comp_options to the zip() method
     *  The values are reseted to the default values at the begining of each zip() method call 
    */
    private $zip_compression_method = 'deflate'; // Compression method
    private $zip_compression_level = 4; // Compression level
    private $zip_compression_index = 0; // Incremented each time that ZipArchive::addFile() is called

    /**
	 * Private method which restore the elements of a drained or deleted folder
     * 
     * @param   string  $initial_path  The initial path of the folder
     * 
     * @return  array  An array that contain the number of restored files and restored folders
	 *
	 * @since   1.1.0
	 */
    private function rollBack($initial_path) : array {

        $total_restored_folders = 0;
        $total_restored_files = 0;

        $total_deleted_folders = count( $this->deleted_elements['folders'] );
        $total_deleted_files = count( $this->deleted_elements['files'] );

        $initial_path_exist = is_dir($initial_path);

        if( $initial_path_exist === FALSE ) {

            $initial_path_exist = mkdir($initial_path);

        }

        if(is_dir($this->tmp_backup_folder_path) AND $initial_path_exist) {

            // First we recreate the deleted folders
            foreach($this->deleted_elements['folders'] as $deleted_folder) {

                if( !is_dir($deleted_folder) ) {

                    if(mkdir($deleted_folder) === TRUE) {

                        $total_restored_folders++;

                    }

                }

            }

            // Then we bring back the saved files from the backup folder to the original folder
            foreach($this->deleted_elements['files'] as $deleted_file) {

                if( !is_file($deleted_file) ) {

                    $backup_path = str_ireplace($initial_path, $this->tmp_backup_folder_path, $deleted_file);
                    if( rename($backup_path, $deleted_file) === TRUE ) {

                        $total_restored_files++;

                    }

                }

            }

        }

        // Delete the backup folder
        $this->setPath($this->tmp_backup_folder_path)->__delete();
        $this->setPath($initial_path);

        $results = array('total_deleted_folders', 'total_restored_folders', 'total_deleted_files', 'total_restored_files');
        list($total_deleted_folders, $total_restored_folders, $total_deleted_files, $total_restored_files) = $results;

        return $results;

    }

    /**
	 * Private method which make a temporary backup folder of a folder, it will be usefull in case of rollback
     * 
     * @param   string  $path  The path of the folder
     * 
     * @return  bool  TRUE in case of success, else FALSE 
	 *
	 * @since   1.1.0
	 */
    private function backupFolder(string $folder_path) : bool {

        $initial_object_path = $this->path;

        $parent_path = dirname($folder_path);
        $random_string = moFilesManager\moFilesManager::getRandomString();

        $backup_path = $parent_path .DIRECTORY_SEPARATOR. basename($folder_path) . '_'.$random_string.'_mofilesmanager_tmp';
        
        $this->setPath($folder_path);
        if( $this->__copy($backup_path) === TRUE ) {

            $this->tmp_backup_folder_path = $backup_path;
            $this->setPath($initial_object_path);            
            
            return TRUE;

        }

        $this->tmp_backup_folder_path = '';
        $this->setPath($initial_object_path);   

        return FALSE;

    }

    /**
     * Private method which recursively copy files and subfolders of a parent folder
     * 
     * @return  bool  Result
	 *
	 * @since   1.1.0
     */
    private function __copy(string $copy_path) : bool {
        
        $initial_object_path = $this->path;
        $error = FALSE;

        // We create the folder and the subfolders
        $error = !mkdir($copy_path);

        // We create the subfolders
        foreach($this->content['folders'] as $folder) {

            if($error === FALSE) {

                $source_path = $initial_object_path .DIRECTORY_SEPARATOR. $folder;
                $destination_path = $copy_path .DIRECTORY_SEPARATOR. basename($folder);

                if($this->setPath( $source_path )->__copy( $destination_path ) === FALSE) {

                    $error = TRUE;
                    moFilesManager\moFilesManager::addLog('the file "'.$source_path.'" has not been duplicated to "'.$destination_path.'"', 'ERROR');

                }

            }

        }
        $this->setPath($initial_object_path);

        // We copy the files
        foreach($this->content['files'] as $file) {

            if($error === FALSE) {

                $source_path = $initial_object_path .DIRECTORY_SEPARATOR. $file;
                $destination_path = $copy_path .DIRECTORY_SEPARATOR. $file;

                if(copy($source_path, $destination_path) === FALSE) {

                    $error = TRUE;
                    moFilesManager\moFilesManager::addLog('the file "'.$source_path.'" has not been duplicated because copy("'.$source_path.','.$destination_path.'") has failed', 'ERROR');

                }

            }

        }

        $this->setPath($initial_object_path);
        return !$error;

    }

    /**
     * Private method which recursively drain subfolders of a parent folder
     * 
     * @return  bool  Result
	 *
	 * @since   1.1.0
     */
    private function __drain() : bool {

        $initial_object_path = $this->path;
        $error = FALSE;

        // We delete the files
        foreach($this->content['files'] as $file) {

            $file_path = $this->path .DIRECTORY_SEPARATOR. basename($file);

            if($error === FALSE) {

                if(unlink($file_path) === FALSE) {

                    $error = TRUE;
                    moFilesManager\moFilesManager::addLog('the file "'.$file_path.'" cannot be deleted.', 'ERROR');
    
                }
                else {
    
                    $this->deleted_elements['files'][] = $file_path;
    
                }

            }

        }

        // We delete the subfolders
        foreach($this->content['folders'] as $folder) {

            $folder_path = $this->path .DIRECTORY_SEPARATOR. $folder;

            if($error === FALSE) {

                if($this->setPath($folder_path)->__delete() === FALSE) {

                    $error = TRUE;
                    moFilesManager\moFilesManager::addLog('the folder "'.$folder_path.'" cannot be deleted.', 'ERROR');

                }
                else {

                    $this->setPath($initial_object_path);
                    $this->deleted_elements['folders'][] = $folder_path;

                }

            }

        }

        $this->setPath($initial_object_path);

        return !$error;
    }

    /**
     * Private method which recursively delete files and subfolders of a parent folder
     * 
     * @return  bool  Result
	 *
	 * @since   1.1.0
     */
    private function __delete() : bool {

        if($this->__drain() === FALSE) {

            moFilesManager\moFilesManager::addLog('The folder '.$this->path.' cannot be drained', 'ERROR');
            return FALSE; 

        }

        if(rmdir($this->path) === FALSE) {

            moFilesManager\moFilesManager::addLog('rmdir('.$this->path.') as failed', 'ERROR');
            return FALSE; 

        }
 
        $this->setPath($this->path);
        
        return TRUE;
    
    }

    /**
     * Private method which recursively add the subfolders in the current ZipArchive object
     * 
     * @param   string  $folder_path  The path of the folder to add
     * @param   string  $initial_folder_path  The path of the initial folderfrom which the archive will be created
     * @param   object  $current_zipper_obj The current ZipArchive object initialized by zip()
     * @param   string  $archive_path The current Archive path
     * 
     * @return  object  The updated current ZipArchive object in case of success, Else a stdClass empty object
	 *
	 * @since   1.1.0
     * @version Last change on 1.1.3 // 2021-01-02
     */
    private function __zip(string $folder_path, string $initial_folder_path, object $current_zipper_obj, string $archive_path) : object {

        if(get_class($current_zipper_obj) !== 'ZipArchive') {

            return (object) array();

        }

        $initial_object_path = $this->path;
        $local_path = str_ireplace(dirname($initial_folder_path), '', $folder_path);
        
        // @Since 1.1.3
        $local_path = ltrim($local_path, DIRECTORY_SEPARATOR);
        $local_path = str_ireplace(DIRECTORY_SEPARATOR, '/', $local_path);
        
        $this->setPath($folder_path);

        foreach($this->content['files'] as $file) {


            $file_path = $this->path .DIRECTORY_SEPARATOR. $file;

            if($current_zipper_obj->addFile($file_path, $local_path .'/'. $file) === TRUE) {

                // Add the compression method for this file
                // @see https://github.com/php/php-src/commit/3a55ea02
                if($this->zip_compression_method === 'deflate') {

                    $current_zipper_obj->setCompressionName($file_path, \ZipArchive::CM_DEFLATE);
                    $current_zipper_obj->setCompressionIndex($this->zip_compression_index, \ZipArchive::CM_DEFLATE);
                    $this->zip_compression_index++;

                }
                elseif($this->zip_compression_method === 'store') {

                    $current_zipper_obj->setCompressionName($file_path, \ZipArchive::CM_STORE);
                    $current_zipper_obj->setCompressionIndex($this->zip_compression_index, \ZipArchive::CM_STORE);
                    $this->zip_compression_index++;

                }

                moFilesManager\moFilesManager::addLog('the file "'.$file_path.'" has been well added to the archive "'.$archive_path.'"');

            }
            else {

                moFilesManager\moFilesManager::addLog('the file "'.$file_path.'" has been not added to the archive "'.$archive_path.'"', 'ERROR');
                $error = TRUE;
            
            }

        }

        foreach($this->content['folders'] as $folder) {

            $dirname = $local_path .'/'. $folder;

            if($current_zipper_obj->addEmptyDir( $dirname ) === TRUE) {

                moFilesManager\moFilesManager::addLog('the folder "'.$this->path .DIRECTORY_SEPARATOR. $folder.'" has been well added to the archive "'.$archive_path.'"');
                $current_zipper_obj = $this->__zip( $this->path .DIRECTORY_SEPARATOR. $folder, $initial_folder_path, $current_zipper_obj, $archive_path);

            }
            else {

                moFilesManager\moFilesManager::addLog('the folder "'.$this->path .DIRECTORY_SEPARATOR. $folder.'" has been not added to the archive "'.$archive_path.'".', 'ERROR');
                $current_zipper_obj = (object) array();

            }

        }

        $this->setPath($initial_object_path);

        return $current_zipper_obj;

    }

    /**
	 * Just an alias of moFilesManager::formatPath()

     * @param   string  $path  The path to format
     * 
     * @return  string $path
	 *
	 * @since   1.1.0
	 */
    private function formatPath(string $path) : string {
        
        return moFilesManager\moFilesManager::formatPath( $path ); 

    }

    /**
	 * Set Folderpath on the fly
     * 
     * @param   string  $path  The path of this object
     * 
     * @return  object  The current folder object
	 *
	 * @since   1.1.0
	 */
    public function setPath(string $path) : object {
        
        if( strlen($path) > 0 ) {

            $this->path = $this->formatPath($path);

        }

        $this->folder_exist = is_dir($this->path);
        $this->content = array('files' => array(), 'folders' => array());

        if($this->folder_exist === TRUE) {

            // Get the folder content
            foreach(array_diff(scandir($this->path), array('..','.')) as $content) {

                if(is_dir($this->path .DIRECTORY_SEPARATOR. $content)) {

                    $this->content['folders'][] = $content;
                
                }
                elseif(is_file($this->path .DIRECTORY_SEPARATOR. $content)) {
    
                    $this->content['files'][] = $content;
    
                }

            }

        }
        else {

            $this->content = array('files' => array(), 'folders' => array());

        }
         
        return $this;

    }

    /**
	 * Copy the current folder
     * 
     * @param   string  $copy_path  The path of the copy
     * @param   boolean  $replace  True to replace the current folder if it already exist. Default : TRUE
	 *
	 * @return  bool  TRUE in case of success, else FALSE
	 *
	 * @since   1.1.0
	 */
    public function copy(string $copy_path, bool $replace = TRUE) : bool {

        $error = FALSE;
        $initial_object_path = $this->path;

        if($this->folder_exist === FALSE) {

            moFilesManager\moFilesManager::addLog('the curent folder "'.$this->path.'" does not exist. Return FALSE', 'ERROR');
            return FALSE;

        }

        // Define the full path if the path is not complete
        $copy_path = $this->formatPath( $copy_path );

        // !!! Check if th current path is inside the copy path
        $current_path_segments = explode( DIRECTORY_SEPARATOR , $this->path );
        $copy_path_segments = explode( DIRECTORY_SEPARATOR , $copy_path );
        $count_copy_path_segments = count($copy_path_segments);
        if($count_copy_path_segments <= count($current_path_segments) AND $this->folder_hierarchy_security === TRUE) {

            do {

                array_pop($current_path_segments);

            }
            while( count($current_path_segments) > $count_copy_path_segments);

            if(implode( DIRECTORY_SEPARATOR, $current_path_segments ) === implode( DIRECTORY_SEPARATOR, $copy_path_segments )) {

                moFilesManager\moFilesManager::addLog('the folder "'.$this->path.'" is a child of "'.$copy_path.'", so it cannot replace his parent folder. Return : FALSE.', 'ERROR');
                return FALSE;

            }

        }

        if(is_dir($copy_path)) {

            if($replace === FALSE) {

                moFilesManager\moFilesManager::addLog('the folder "'.$copy_path.'" already exist and Replace is set to FALSE, so we return FALSE', 'WARNING');
                return FALSE;

            }
            else {

                // the folder is duplicated to temporary folder which will be renamed after that the original folder has been deleted
                $tmp_copy_path = dirname($copy_path) .DIRECTORY_SEPARATOR. basename($copy_path).'_'.moFilesManager\moFilesManager::getRandomString().'_mofilesmanager_tmp';
                if($this->__copy($tmp_copy_path) === FALSE) {

                    if(is_dir($tmp_copy_path)) {

                        $this->setPath($tmp_copy_path)->__delete();
                        $this->setPath($initial_object_path);

                    }

                    moFilesManager\moFilesManager::addLog('the temporary folder "'.$this->path.'" cannot be duplicated to a temporary folder. Return : FALSE','ERROR');

                    return FALSE;

                }

                // the replaced folder is saved in a temporary backup folder, it will be usefull in case of rollback
                if( $this->backupFolder($copy_path) === TRUE) {
                    
                    $folder_obj = new Folder($copy_path);
                    if($folder_obj->__delete() === TRUE) {

                        moFilesManager\moFilesManager::addLog('the folder "'.$copy_path.'" has been deleted for to be replaced');
                        unset($folder_obj);

                        // Rename the temporary folder
                        if(rename( $tmp_copy_path, $copy_path ) === TRUE) {

                            moFilesManager\moFilesManager::addLog('The folder "'.$initial_object_path.'" has been well duplicated to "'.$copy_path.'". Return : TRUE.');

                            // Delete the backup folder
                            $this->setPath($this->tmp_backup_folder_path)->__delete();
                            $this->setPath($initial_object_path);

                            return TRUE;

                        }
                        else {

                            moFilesManager\moFilesManager::addLog('the temporary folder "'.$tmp_copy_path.'" cannot be renamed to "'.$copy_path.'". Rollback, and Return : FALSE','ERROR');

                            // RollBack !!
                            $rollback_results = $this->rollBack($initial_object_path);

                            moFilesManager\moFilesManager::addLog('Folders RollBack result : '.$rollback_results['total_restored_folders'].' restored folders ON '.$rollback_results['total_deleted_folders'].' deleted folders');
                            moFilesManager\moFilesManager::addLog('Files RollBack result : '.$rollback_results['total_restored_files'].' restored files ON '.$rollback_results['total_deleted_files'].' deleted files');

                            // Delete the temporary folder
                            $this->setPath($tmp_copy_path)->__delete();
                            $this->setPath($initial_object_path);

                            // Delete the backup folder
                            $this->setPath($this->tmp_backup_folder_path)->__delete();
                            $this->setPath($initial_object_path);

                            $this->deleted_elements = array('folders' => array(), 'files' => array());
                            
                            return FALSE;

                        }

                    }
                    else {

                        moFilesManager\moFilesManager::addLog('the folder "'.$copy_path.'" cannot be deleted for to be replaced. Rollback, and Return : FALSE','ERROR');

                        // RollBack !!
                        $rollback_results = $this->rollBack($initial_object_path);

                        moFilesManager\moFilesManager::addLog('Folders RollBack result : '.$rollback_results['total_restored_folders'].' restored folders ON '.$rollback_results['total_deleted_folders'].' deleted folders');
                        moFilesManager\moFilesManager::addLog('Files RollBack result : '.$rollback_results['total_restored_files'].' restored files ON '.$rollback_results['total_deleted_files'].' deleted files');

                        // Delete the temporary folder
                        $this->setPath($tmp_copy_path)->__delete();
                        $this->setPath($initial_object_path);

                        // Delete the backup folder
                        $this->setPath($this->tmp_backup_folder_path)->__delete();
                        $this->setPath($initial_object_path);

                        $this->deleted_elements = array('folders' => array(), 'files' => array());
                        
                        return FALSE;

                    }

                }
                else {

                    moFilesManager\moFilesManager::addLog('the folder "'.$copy_path.'" can\'t be saved before delete, so we return FALSE', 'WARNING');
                    return FALSE;

                }
                
            }

        }
        else {

            // We create the folder
            if($this->setPath($copy_path)->create() === FALSE) {

                moFilesManager\moFilesManager::addLog('the folder "'.$initial_object_path.'" has not been duplicated because moFilesManager\Folder->create() has failed. Return : FALSE.', 'ERROR');

                $this->setPath($initial_object_path);
                return FALSE;

            }
            else {

                // The folder has been well created
                $this->setPath($initial_object_path);

            }

            // We copy the subfolders
            foreach($this->content['folders'] as $folder) {

                if($error === FALSE) {

                    $source_path = $initial_object_path .DIRECTORY_SEPARATOR. $folder;
                    $destination_path = $copy_path .DIRECTORY_SEPARATOR. basename($folder);

                    if($this->setPath( $source_path )->__copy( $destination_path ) === FALSE) {

                        $error = TRUE;
                        moFilesManager\moFilesManager::addLog('the file "'.$source_path.'" has not been duplicated to "'.$destination_path.'"', 'ERROR');

                    }

                }

            }
            $this->setPath($initial_object_path);

            // We copy the files
            foreach($this->content['files'] as $file) {

                if($error === FALSE) {

                    $source_path = $initial_object_path .DIRECTORY_SEPARATOR. $file;
                    $destination_path = $copy_path .DIRECTORY_SEPARATOR. $file;

                    if(copy($source_path, $destination_path) === FALSE) {

                        $error = TRUE;
                        moFilesManager\moFilesManager::addLog('the file "'.$source_path.'" has not been duplicated because copy("'.$source_path.'","'.$destination_path.'") has failed', 'ERROR');

                    }

                }

            }

            // We rollback in case of error
            if($error === TRUE) {
        
                moFilesManager\moFilesManager::addLog('ERROR occurred while copying the folder "'.$this->path.'" to "'.$copy_path.'". RollBack !!','ERROR');

                $this->setPath($copy_path)->__delete();
                
            }
            else {

                moFilesManager\moFilesManager::addLog('The folder "'.$initial_object_path.'" has been well duplicated to "'.$copy_path.'".');

                // Delete the backup folder
                if(is_dir($this->tmp_backup_folder_path)) {

                    $this->setPath($this->tmp_backup_folder_path)->__delete();
                    $this->tmp_backup_folder_path = '';

                }

            }

        }

        $this->setPath($initial_object_path);

        return !$error;

    }

    /**
	 * Move the current folder
     * 
     * @param   string  $new_path  The new path of the current folder
     * @param   boolean  $replace  True to replace the current folder if it already exist. Default : TRUE
	 *
	 * @return  bool  TRUE in case of success, else FALSE
	 *
	 * @since   1.1.0
	 */
    public function move(string $new_path, bool $replace = TRUE) : bool {

        if($this->folder_exist === FALSE) {

            moFilesManager\moFilesManager::addLog('the curent folder "'.$this->path.'" does not exist. Return FALSE', 'ERROR');
            return FALSE;

        }

        $new_path = $this->formatPath( $new_path );
        
        // We create a copy of the folder
        $this->folder_hierarchy_security = FALSE;
        if($this->copy($new_path, $replace) === FALSE) {

            moFilesManager\moFilesManager::addLog('the folder "'.$this->path.'" has not been moved to "'.$new_path.'" because Folder->copy() has failed', 'ERROR');
            return FALSE;

        }
        $this->folder_hierarchy_security = TRUE;

        // We delete the old folder
        if($this->delete($new_path, $replace) === FALSE) {

            moFilesManager\moFilesManager::addLog('the folder "'.$this->path.'" has not been deleted because Folder->delete() has failed', 'ERROR');
            return FALSE;

        }
        
        moFilesManager\moFilesManager::addLog('the folder "'.$this->path.'" has been well moved to "'.$new_path.'"');

        $this->setPath($new_path);
        return TRUE;

    }

    /**
	 * Create the current folder if not exist
     * 
	 * @return  bool  TRUE in case of success, else FALSE
	 *
	 * @since   1.1.0
     * @version Last change on 1.1.2 // 2020-07-26
	 */
    public function create() : bool {

        $initial_object_path = $this->path;

        if($this->folder_exist === TRUE) {

            moFilesManager\moFilesManager::addLog('the curent folder "'.$this->path.'" already exist. Return TRUE.');
            return TRUE;

        }

        // Create each parent folder if not exist
        $error = FALSE;
        $created_folder = array();

        // Create each path segment which no exist
        $parent_path = '';
        foreach(array_diff( explode(DIRECTORY_SEPARATOR, $this->path) , array('') )  as $path_segment ) {

	        //  2020-07-26. The following lines has been added for OS compatibility
            if(strlen($parent_path) === 0 AND PHP_OS === 'WINNT') {

                $parent_path.= $path_segment;
            
            }
            elseif(strlen($parent_path) === 0 AND PHP_OS !== 'WINNT') {
            
                $parent_path.= DIRECTORY_SEPARATOR.$path_segment;
            
            }
            else {

                $parent_path.= DIRECTORY_SEPARATOR.$path_segment;

            }

            if(!is_dir($parent_path) AND $error === FALSE) {

                if(mkdir($parent_path) === FALSE) {

                    $error = TRUE;
                    moFilesManager\moFilesManager::addLog('the folder "'.$parent_path.'" has not been created because mkdir() has failed', 'ERROR');

                }
                else {

                    $created_folder[] = $parent_path;
                    moFilesManager\moFilesManager::addLog('the folder "'.$parent_path.'" has been well created');

                }

            }
        }

        if($error === TRUE) {

            // Rollback !!
            moFilesManager\moFilesManager::addLog('ERROR occurred while creating the folder "'.$this->path.'". RollBack, and Return : FALSE', 'ERROR');
            foreach(array_reverse($created_folder) as $created_to_delete) {

                rmdir($created_to_delete);

            }
            return FALSE;

        }

        $this->setPath($initial_object_path);

        return TRUE;

    }

    /**
	 * Rename the folder
     * 
     * This method change the name of the current folder, but it does not move the folder
     * 
     * @param   string  $new_name  The new name of the folder
     * @param   boolean  $replace  True to replace the current file if it already exist. Default : TRUE
	 *
	 * @return  bool  TRUE in case of success, else FALSE
	 *
	 * @since   1.1.0
	 */
    public function rename(string $new_name, bool $replace = TRUE) : bool {

        if($this->folder_exist === FALSE) {

            moFilesManager\moFilesManager::addLog('the curent folder "'.$this->path.'" does not exist. Return FALSE', 'ERROR');
            return FALSE;

        }

        if(is_int(strpos($new_name, DIRECTORY_SEPARATOR)) OR is_int(strpos($new_name, '\\'))) {

            moFilesManager\moFilesManager::addLog('DIRECTORY SEPARATOR detected, use Folder->move() instead of Folder->rename() if you want to change the location of the folder "'.$this->path.'". Return FALSE', 'ERROR');
            return FALSE;

        }
        
        $parent_path = dirname($this->path);
        $new_path = $parent_path .DIRECTORY_SEPARATOR. $new_name;
        if(is_dir($new_path)) {

            if($replace === FALSE) {

                moFilesManager\moFilesManager::addLog('the folder "'.$new_path.'" already exist and Replace is set to FALSE, so we return FALSE', 'WARNING');
                return FALSE;

            }
            else {

                // the replaced folder is saved in a temporary backup folder, it will be usefull in case of rollback
                if( $this->backupFolder($new_path) === TRUE) {
                    
                    $folder_obj = new Folder($new_path);
                    if($folder_obj->__delete() === TRUE) {

                        moFilesManager\moFilesManager::addLog('the folder "'.$new_path.'" as been deleted for to be replaced');
                        unset($folder_obj);

                    }
                    else {

                        moFilesManager\moFilesManager::addLog('the folder "'.$new_path.'" cannot be deleted for to be replaced. Return : FALSE','ERROR');

                        // Rollback
                        $folder_obj->rollBack($new_path);
                        unset($folder_obj);

                        return FALSE;
                    }

                }
                else {

                    moFilesManager\moFilesManager::addLog('the folder "'.$new_path.'" can\'t be saved before delete, so we return FALSE', 'WARNING');
                    return FALSE;

                }
                
            }

        }

        if(rename($this->path, $new_path) === FALSE) {

            moFilesManager\moFilesManager::addLog('the folder "'.$this->path.'" has not been renamed because rename("'.$this->path.'","'.$new_path.'") has failed', 'ERROR');
            return FALSE;

        }

        // Delete the backup folder
        if(is_dir($this->tmp_backup_folder_path)) {

            $this->setPath($this->tmp_backup_folder_path)->__delete();
            $this->tmp_backup_folder_path = '';

        }

        moFilesManager\moFilesManager::addLog('the folder "'.$this->path.'" has been well renamed to "'.$new_name.'"');
        $this->setPath($new_path);

        return TRUE;
    }

    /**
	 * Recursively remove all items contained into the folder
     * 
	 * @return  bool  TRUE in case of success, else FALSE
	 *
	 * @since   1.1.0
	 */
    public function drain() : bool {

        $this->deleted_elements = array('folders' => array(), 'files' => array());
        $initial_object_path = $this->path;
        $error = FALSE;

        if($this->folder_exist === FALSE) {

            moFilesManager\moFilesManager::addLog('the folder "'.$this->path.'" has not been drained because it does not exist. Return FALSE.', 'WARNING');
            return FALSE;
            
        }

        // We copy the current folder, that's will be usefull in case of rollback
        if( $this->backupFolder($this->path) ) {

            moFilesManager\moFilesManager::addLog('the folder "'.$this->path.'" has been well saved before drain');

        }
        else {

            moFilesManager\moFilesManager::addLog('the folder "'.$this->path.'" cannot be saved before drain. Return : FALSE.','ERROR');
            return FALSE;

        }

        // We delete the files
        foreach($this->content['files'] as $file) {

            $file_path = $this->path .DIRECTORY_SEPARATOR. basename($file);

            if($error === FALSE) {

                if(unlink($file_path) === FALSE) {

                    $error = TRUE;
                    moFilesManager\moFilesManager::addLog('the file "'.$file_path.'" cannot be deleted.', 'ERROR');
    
                }
                else {
    
                    $this->deleted_elements['files'][] = $file_path;
    
                }

            }

        }

        // We delete the subfolders
        foreach($this->content['folders'] as $folder) {

            $folder_path = $this->path .DIRECTORY_SEPARATOR. $folder;

            if($error === FALSE) {

                if($this->setPath($folder_path)->__delete() === FALSE) {

                    $error = TRUE;
                    moFilesManager\moFilesManager::addLog('the folder "'.$folder_path.'" cannot be deleted.', 'ERROR');

                }
                else {

                    $this->setPath($initial_object_path);
                    $this->deleted_elements['folders'][] = $folder_path;

                }

            }

        }
        
        if($error === TRUE) {

            moFilesManager\moFilesManager::addLog('the folder "'.$initial_object_path.'" cannot be drained. RollBack and Return : FALSE.', 'ERROR');

            // RollBack !!
            $rollback_results = $this->rollBack($initial_object_path);

            moFilesManager\moFilesManager::addLog('Folders RollBack result : '.$rollback_results['total_restored_folders'].' restored folders ON '.$rollback_results['total_deleted_folders'].' deleted folders');
            moFilesManager\moFilesManager::addLog('Files RollBack result : '.$rollback_results['total_restored_files'].' restored files ON '.$rollback_results['total_deleted_files'].' deleted files');

            // Delete the backup folder
            $this->setPath($this->tmp_backup_folder_path)->__delete();
            $this->setPath($initial_object_path);

            $this->deleted_elements = array('folders' => array(), 'files' => array());
            
            return FALSE;

        }

        // Delete the backup folder
        $this->setPath($this->tmp_backup_folder_path)->__delete();
        $this->setPath($initial_object_path);
        $this->deleted_elements = array('folders' => array(), 'files' => array());

        moFilesManager\moFilesManager::addLog('the folder "'.$this->path.'" has been well drained');
        
        return TRUE;
    }

    /**
	 * Delete the current folder
     * 
	 * @return  bool  TRUE in case of success, else FALSE
	 *
	 * @since   1.1.0
	 */
    public function delete() : bool {

        $initial_object_path = $this->path;

        if($this->folder_exist === FALSE) {

            moFilesManager\moFilesManager::addLog('the folder "'.$this->path.'" has not been deleted because it does not exist. Return TRUE.', 'WARNING');
            return TRUE;
            
        }

        if($this->drain() === FALSE) {

            moFilesManager\moFilesManager::addLog('the folder "'.$this->path.'" has not been deleted because Folder->drain() has failed', 'ERROR');
            return FALSE;

        }

        if(rmdir($this->path) === FALSE) {

            moFilesManager\moFilesManager::addLog('the folder "'.$this->folder_path.'" has not been deleted because rmdir() has failed', 'ERROR');
            return FALSE;

        }
        
        moFilesManager\moFilesManager::addLog('the folder "'.$this->path.'" has been well deleted');
 
        $this->setPath($initial_object_path);
        
        return TRUE;
    
    }

    /**
	 * Make an archive zip from the current folder 
     * 
     * @param   string  $archive_name  The path of the zip file that will be made. Default : The folder parent path
     * @param   boolean  $replace  True to replace the current file if it already exist. Default : TRUE
     * @param   array  $options  An array containing some options : 'compression_method', 'compression_level'
	 *
	 * @return  bool  TRUE in case of success, else FALSE
	 *
	 * @since   1.1.0
     * @version Last change on 1.1.3 // 2021-01-02
	 */
    public function zip(string $archive_path = '', bool $replace = TRUE, array $options = array()) : bool {

        if($this->folder_exist === FALSE) {

            moFilesManager\moFilesManager::addLog('the curent folder "'.$this->path.'" does not exist. Return FALSE', 'ERROR');
            return FALSE;

        }

        $initial_object_path = $this->path;
        $has_backup = FALSE;
        $error = FALSE;

        $parent_folder_real_path = dirname($this->path);
        if( strlen($archive_path) <= 0 ) {

            $archive_path = dirname($this->path) .DIRECTORY_SEPARATOR. basename($this->path).'.zip';

        }
        else {

            $pathinfo = pathinfo($archive_path);
            if(!isset($pathinfo['extension'])) {

                $archive_path = $archive_path.'.zip';

            }
            else {

                if($pathinfo['extension'] !== 'zip') { $archive_path = $archive_path.'.zip'; }

            }

        }

        if(is_file($archive_path) AND $replace === FALSE) {

            moFilesManager\moFilesManager::addLog('the archive "'.$archive_path.'" already exist and Replace is set to FALSE, so we return FALSE', 'WARNING');
            return FALSE;

        }
        elseif(is_file($archive_path) AND $replace === TRUE) {

            // Backup the current archive
            $current_archive_backup = dirname($archive_path) .DIRECTORY_SEPARATOR. basename($archive_path).'_'.moFilesManager\moFilesManager::getRandomString().'_mofilesmanager_tmp.zip';
            if( rename($archive_path, $current_archive_backup) === FALSE ) {

                moFilesManager\moFilesManager::addLog('the archive "'.$archive_path.'" cannot be saved before replace. Return :  FALSE.', 'ERROR');
                return FALSE;

            }

            $has_backup = TRUE;

        }

        // Set the compression options
        $this->zip_compression_method = 'deflate'; // Default compression method
        $this->zip_compression_level = 6; // Default compression level
        $this->zip_compression_index = 0; // Will be incremented each time that ZipArchive::addFile() is called
        if( isset($options['compression_method']) ) {

            if(in_array( $options['compression_method'], array('store','deflate')) === TRUE) {

                $this->zip_compression_method = $options['compression_method'];

            }
            else {

                moFilesManager\moFilesManager::addLog('Compression option error for the file "'.$archive_path.'". Allowed methods are : "deflate", "store", "none". The option is ignored.', 'WARNING');

            }

        }
        if( isset($options['compression_level']) ) {

            $comp_level = (int) $options['compression_level'];

            if(in_array( $comp_level, range(0, 9)) === TRUE) {

                $this->$zip_compression_level = $comp_level;

            }
            else {

                moFilesManager\moFilesManager::addLog('Compression option error for the file "'.$archive_path.'". The level must be an integer between 0 and 9. The option is ignored.', 'WARNING');

            }

        }
        
        $zipper = new \ZipArchive();
        if($zipper->open($archive_path, \ZipArchive::CREATE) === FALSE) {

            moFilesManager\moFilesManager::addLog('ZipArchive object has been not initialized. Rollback, and Return : FALSE.', 'ERROR');

            if($has_backup === TRUE) {

                rename($current_archive_backup, $archive_path);

            }

            return FALSE;

        }

        $zipper->addEmptyDir( basename($this->path) ); // @since   1.1.3

        foreach($this->content['files'] as $file) {

            $file_path = $this->path .DIRECTORY_SEPARATOR. $file;

            if($zipper->addFile($file_path, basename($this->path) .'/'. $file) === TRUE) {

                // Add the compression method
                // @see https://github.com/php/php-src/commit/3a55ea02
                if($this->zip_compression_method === 'deflate') {

                    $zipper->setCompressionName($file_path, \ZipArchive::CM_DEFLATE);
                    $zipper->setCompressionIndex($this->zip_compression_index, \ZipArchive::CM_DEFLATE);
                    $this->zip_compression_index++;

                }
                elseif($this->zip_compression_method === 'store') {

                    $zipper->setCompressionName($file_path, \ZipArchive::CM_STORE);
                    $zipper->setCompressionIndex($this->zip_compression_index, \ZipArchive::CM_STORE);
                    $this->zip_compression_index++;

                }
                
                moFilesManager\moFilesManager::addLog('the file "'.$file_path.'" has been well added to the archive "'.$archive_path.'"');

            }
            else {

                moFilesManager\moFilesManager::addLog('the file "'.$file_path.'" has been not added to the archive "'.$archive_path.'"', 'ERROR');
                $error = TRUE;
            
            }

        }

        foreach($this->content['folders'] as $folder) {

            $dirname = basename($this->path) .'/'. $folder;

            if($zipper->addEmptyDir( $dirname ) === TRUE) {

                moFilesManager\moFilesManager::addLog('the folder "'.$this->path .DIRECTORY_SEPARATOR. $folder.'" has been well added to the archive "'.$archive_path.'"');
                $zipper = $this->__zip( $this->path .DIRECTORY_SEPARATOR. $folder, $this->path, $zipper, $archive_path );

            }
            else {

                moFilesManager\moFilesManager::addLog('the file "'.$this->path .DIRECTORY_SEPARATOR. $folder.'" has been not added to the archive "'.$archive_path.'"', 'ERROR');
                $error = TRUE;
            
            }

        }

        $error = get_class($zipper) !== 'ZipArchive';

        if($error === FALSE) {

            moFilesManager\moFilesManager::addLog('ZipArchive status string : "' .$zipper->getStatusString(). '"');
            moFilesManager\moFilesManager::addLog('ZipArchive total files : ' .$zipper->numFiles );
            $zipper->close();

        }
                  
        if(!is_file($archive_path)) {

            moFilesManager\moFilesManager::addLog('The file "'.$archive_path.'" has been not created. Rollback, and Return : FALSE.', 'ERROR');

            if($has_backup === TRUE) {

                rename($current_archive_backup, $archive_path);

            }
            return FALSE;

        }

        if($error === TRUE) {

            moFilesManager\moFilesManager::addLog('the archive "'.$archive_path.'" has been created with error. Rollback, and Return : FALSE.', 'ERROR');

            unlink($archive_path);
            if($has_backup === TRUE) {

                rename($current_archive_backup, $archive_path);

            }
            return FALSE;

        }
        else {

            if($has_backup === TRUE) {

                unlink($current_archive_backup);

            }

            moFilesManager\moFilesManager::addLog('the archive "'.$archive_path.'" has been well created.');

        }

        $this->setPath($initial_object_path);
            
        return TRUE;
    }

    /**
	 * Extract an archive zip into the the current folder  
     * 
     * @param   string  $zip_path  The path of the zip file that will be extracted into the current folder.
     * @param   boolean  $replace  True to replace the current file if it already exist. Default : TRUE
	 *
	 * @return  bool  TRUE in case of success, else FALSE
	 *
	 * @since   1.1.0
	 */
    public function unzip(string $zip_path = '', bool $replace = TRUE) : bool {

        $error = FALSE;
        $initial_object_path = $this->path;

        $zip_file = new moFilesManager\File($zip_path);
        if($zip_file->file_exist === FALSE) {

            moFilesManager\moFilesManager::addLog('the file "'.$zip_path.'" does not exist. Return FALSE', 'ERROR');
            return FALSE;

        }
        if($zip_file->is_readable === FALSE) {

            moFilesManager\moFilesManager::addLog('the file "'.$zip_path.'" is not readable. Please check the file perms. Return FALSE', 'ERROR');
            return FALSE;

        }
        if($zip_file->type !== 'zip' OR $zip_file->extension !== 'zip') {

            moFilesManager\moFilesManager::addLog('the file "'.$zip_path.'" is not a valid Zip archive. Return FALSE', 'ERROR');
            return FALSE;

        }
        unset($zip_file);

        if($this->folder_exist === FALSE) {

            // We create the folder
            if($this->create() === FALSE) {

                moFilesManager\moFilesManager::addLog('The extraction folder "'.$this->path.'" cannot be created. Return : FALSE.', 'ERROR');
                return FALSE;

            }
            $this->setPath($this->path);
            
        }
        else {

            if($replace === FALSE) {

                moFilesManager\moFilesManager::addLog('the folder "'.$this->path.'" already exist and Replace is set to FALSE, so we return FALSE', 'WARNING');
                return FALSE;
    
            }
            else {
    
                // the replaced folder is saved in a temporary backup folder, it will be usefull in case of rollback
                if( $this->backupFolder($this->path) === TRUE) {
                    
                    if($this->__delete() === TRUE) {

                        moFilesManager\moFilesManager::addLog('the folder "'.$this->path.'" as been deleted for to be replaced');
                        $this->setPath($this->path);

                    }
                    else {

                        moFilesManager\moFilesManager::addLog('the folder "'.$this->path.'" cannot be deleted for to be replaced. Rollback and Return : FALSE','ERROR');

                        // Rollback
                        $this->rollBack($this->path);

                        return FALSE;
                    }

                }
                else {
    
                    moFilesManager\moFilesManager::addLog('the current folder "'.$this->path.'" can\'t be saved before delete for replace. Return FALSE', 'ERROR');
                    return FALSE;
    
                }
                
            }

        }

        $unzipper = new \ZipArchive();
        if($unzipper->open($zip_path) === FALSE) {

            moFilesManager\moFilesManager::addLog('the file "'.$zip_path.'" can\'t be opened by ZipArchive. Rollback and Return : FALSE', 'ERROR');
            moFilesManager\moFilesManager::addLog('ZipArchive status string : "' .$unzipper->getStatusString(). '"');

            // Rollback
            $this->rollBack($this->path);

            return FALSE;

        }

        if($unzipper->extractTo($this->path) === FALSE) {

            moFilesManager\moFilesManager::addLog('the data contained in the file "'.$zip_path.'" can\'t be extracted by ZipArchive. Rollback and Return FALSE', 'ERROR');
            moFilesManager\moFilesManager::addLog('ZipArchive status string : "' .$unzipper->getStatusString(). '"');
            
            // Rollback
            $this->rollBack($this->path);

            return FALSE;

        }
        $this->setPath($this->path);

        // The content of the archived folder need to be moved in the current folder path
        if(count($this->content['folders']) <= 0) {

            moFilesManager\moFilesManager::addLog('Nothing has been extracted from the archive "'.$zip_path.'" to the folder "'.$this->path.'". Rollback and Return FALSE', 'ERROR');
            
            // Rollback
            $this->__delete();
            $this->rollBack($this->path);

            return FALSE;

        }
        
        $archived_folder_path = $this->path .DIRECTORY_SEPARATOR. $this->content['folders'][0];

        if(is_dir($archived_folder_path) === FALSE) {

            moFilesManager\moFilesManager::addLog('Nothing has been extracted from the archive "'.$zip_path.'" to the folder "'.$this->path.'". Rollback and Return FALSE', 'ERROR');
            
            // Rollback
            $this->__delete();
            $this->rollBack($this->path);

            return FALSE;

        }
        // Change since 1.1.3, we don't use the subfolder extracted in the extraction folder
        // $this->setPath($archived_folder_path);

        $tmp_folder_path = dirname($initial_object_path) .DIRECTORY_SEPARATOR. basename($initial_object_path).'_'.moFilesManager\moFilesManager::getRandomString().'_mofilesmanager_tmp';
        if($this->copy($tmp_folder_path) === FALSE) {

            moFilesManager\moFilesManager::addLog('Error occurred while copying the archived folder content to current folder path. Rollback and Return FALSE', 'ERROR');
            
            // Rollback
            $this->__delete();
            $this->rollBack($this->path);

            return FALSE;

        }
        
        // Delete the extraction folder
        $this->setPath($initial_object_path);
        $this->__delete();

        // Delete the backup folder if exist
        if(is_dir($this->tmp_backup_folder_path)) {

            $this->setPath( $this->tmp_backup_folder_path );
            $this->__delete();

        }

        // Rename the temporary folder
        $this->setPath($tmp_folder_path);
        $this->rename(basename($initial_object_path));

        moFilesManager\moFilesManager::addLog('the data contained in the file "'.$zip_path.'" has been well extracted in "'.$this->path.'"');
        moFilesManager\moFilesManager::addLog('ZipArchive status string : "' .$unzipper->getStatusString(). '"');

        return TRUE;

    }

    public function __get($name) {

        if(array_key_exists($name, get_object_vars($this))) {

            return $this->$name;

        }

        return NULL;
    
    }

    public function __set($name, $value) {

        trigger_error('moFolderManager object propertie "'.$name.'" can\'t be setted', E_USER_WARNING);
    
    }
    
    public function __construct(string $path) {

        $this->setPath($path);

    }
}
?>
