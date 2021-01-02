<?php

define('TESTS_SCRIPT_PATH', dirname(__FILE__));
$parent_path = str_ireplace(DIRECTORY_SEPARATOR.'tests', '', TESTS_SCRIPT_PATH);

require_once $parent_path.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'moFilesManager.php';

// moFilesManager\moFilesManager::setDebugState(TRUE);

/**
 * Tests for the moFilesManager\Folder class
 * 
 * @since       1.1.3
 */
class FolderTest {

    private $folder_obj;

    /**
     * Content comparaison between two folders
     *
     * @return bool
     * 
     * @since       1.1.3
     */
    private function merge(string $merge_folder_path) : bool {

        $original_tests_elements = TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements';
        $error = 0;

        // We're checking if each files present at the root of the original folder are present at the root of the tested folder
        if(is_file(TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR.'elements'.DIRECTORY_SEPARATOR.'flower-729512_1920.jpg') === FALSE
            OR
            is_file(TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR.'elements'.DIRECTORY_SEPARATOR.'marguerite-729510_1920.jpg') === FALSE) {

            $error++;

        }

        // We're checking if each subfolders present in the original folder are present in the tested folder
        $sub_folders = array('input_data',
                             'input_img'.DIRECTORY_SEPARATOR.'landscapes',
                             'input_img'.DIRECTORY_SEPARATOR.'others',
                             'input_img'.DIRECTORY_SEPARATOR.'others'.DIRECTORY_SEPARATOR.'others-subfolder');
        foreach($sub_folders as $sub_folder) {

            $sub_folder_path = $original_tests_elements .DIRECTORY_SEPARATOR. $sub_folder;

            if(is_dir($merge_folder_path.DIRECTORY_SEPARATOR.$sub_folder)) {

                // We're checking if each files present in the original folder are present in the tested folder
                foreach( scandir($sub_folder_path) as $sub_folder_file) {

                    // We're checking only the files, we're skipping the folders
                    if(!is_file($sub_folder_path.DIRECTORY_SEPARATOR.$sub_folder_file)) { continue; }

                    if(is_file($merge_folder_path.DIRECTORY_SEPARATOR.$sub_folder.DIRECTORY_SEPARATOR.$sub_folder_file)) {

                        $original_file_stat = stat($sub_folder_path.DIRECTORY_SEPARATOR.$sub_folder_file); 
                        $tested_file_stat = stat($merge_folder_path.DIRECTORY_SEPARATOR.$sub_folder.DIRECTORY_SEPARATOR.$sub_folder_file);
                        
                        // We're checking if the files has the same size
                        if($original_file_stat['size'] !== $tested_file_stat['size']) { $error++; }

                        // We're checking if the files has the same content
                        $original_file_content = file_get_contents($sub_folder_path.DIRECTORY_SEPARATOR.$sub_folder_file); 
                        $tested_file_content = file_get_contents($merge_folder_path.DIRECTORY_SEPARATOR.$sub_folder.DIRECTORY_SEPARATOR.$sub_folder_file); 
                        if($original_file_content !== $tested_file_content) { $error++; }

                    }
                    else {

                        $error++;

                    }

                }
    
            }
            else {
    
                $error++;
    
            }

        }

        return $error === 0;

    }

    /**
     * Test of the moFilesManager\Folder->setPath() method
     *
     * @return bool
     * 
     * @since       1.1.3
     */
    public function test_setPath() : bool {

        $new_path = TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements';

        $this->folder_obj = new moFilesManager\Folder( TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'path-to-replace' );
        $this->folder_obj->setPath( $new_path );

        return $new_path === $this->folder_obj->path;

    }

    /**
     * Test of the moFilesManager\Folder->create() method
     *
     * @return bool
     * 
     * @since       1.1.3
     */
    public function test_create() : bool {

        $path_to_create = TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'running';
        $this->folder_obj->setPath($path_to_create);

        if($this->folder_obj->create() === TRUE) {

            return is_dir($path_to_create);

        }

        return FALSE;

    }

    /**
     * Test of the moFilesManager\Folder->rename() method
     *
     * @return bool
     * 
     * @since       1.1.3
     */
    public function test_rename() : bool {

        $current_path = $this->folder_obj->path;
        $new_name = 'running-test';

        if($this->folder_obj->rename($new_name) === TRUE) {

            $new_path = str_ireplace('running','running-test',$current_path);
            
            return is_dir($new_path) AND $new_path === $this->folder_obj->path;

        }

        return FALSE;

    }

    /**
     * Test of the moFilesManager\Folder->copy() method
     *
     * @return bool
     * 
     * @since       1.1.3
     */
    public function test_copy() : bool {

        $current_path = $this->folder_obj->path;
        $copy_path = $current_path .DIRECTORY_SEPARATOR. 'duplicat';
        
        $original_folder_path = TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements';
        
        $this->folder_obj->setPath($original_folder_path);

        if($this->folder_obj->copy($copy_path) === TRUE) {
            
            return $this->merge($copy_path);

        }

        return FALSE;

    }

    /**
     * Test of the moFilesManager\Folder->move() method
     *
     * @return bool
     * 
     * @since       1.1.3
     */
    public function test_move() : bool {

        $this->folder_obj->setPath(TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'running-test' .DIRECTORY_SEPARATOR. 'duplicat');
        $new_path = TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'running-test' .DIRECTORY_SEPARATOR. 'moving' .DIRECTORY_SEPARATOR. 'test';

        if($this->folder_obj->move($new_path) === TRUE) {

            if(is_dir($new_path)) {

                return $this->merge($new_path) === TRUE AND $this->folder_obj->path === $new_path;

            }

        }

        return FALSE;

    }

    /**
     * Test of the moFilesManager\Folder->drain() method
     *
     * @return bool
     * 
     * @since       1.1.3
     */
    public function test_drain() : bool {

        if($this->folder_obj->drain() === TRUE) {

            $folder_content = array_diff(scandir( $this->folder_obj->path), array('.','..'));
            return count($folder_content) === 0;

        }

        return FALSE;

    }

    /**
     * Test of the moFilesManager\Folder->delete() method
     *
     * @return bool
     * 
     * @since       1.1.3
     */
    public function test_delete() : bool {

        $this->folder_obj->setPath(TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'running-test');

        if($this->folder_obj->delete() === TRUE) {

            return !is_dir(TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'running-test');

        }

        return FALSE;

    }

    /**
     * Test of the moFilesManager\Folder->zip() method
     *
     * @return bool
     * 
     * @since       1.1.3
     */
    public function test_zip() : bool {

        $this->folder_obj->setPath(TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements');
        $zip_test_path = TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements_zip_test.zip';

        if($this->folder_obj->zip($zip_test_path) === TRUE) {

            return is_file($zip_test_path);
            
        }

        return FALSE;

    }

    /**
     * Test of the moFilesManager\Folder->unzip() method
     *
     * @return bool
     * 
     * @since       1.1.3
     */
    public function test_unzip() : bool {

        $this->folder_obj->setPath(TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements_extract');
        $zip_test_path = TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements_zip_test.zip';

        if($this->folder_obj->unzip($zip_test_path) === TRUE) {

            if(is_dir(TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements_extract') === TRUE) {

                return $this->merge(TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements_extract'.DIRECTORY_SEPARATOR.'elements');

            }
            
        }

        return FALSE;

    }

}
?>