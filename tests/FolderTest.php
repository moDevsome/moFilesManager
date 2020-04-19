<?php

use PHPUnit\Framework\TestCase;

require_once './src/moFilesManager.php';

define('TESTS_SCRIPT_PATH', dirname(__FILE__));
moFilesManager\moFilesManager::setDebugState(TRUE);

/**
 * Tests for the moFilesManager\Folder class
 * 
 * @since       1.0.0
 */
class FolderTest extends TestCase {

    /**
     * Test of the moFilesManager\Folder->setPath() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function testSetPath() {

        $new_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements';

        $folder_obj = new moFilesManager\Folder( 'tests' .DIRECTORY_SEPARATOR. 'path-to-replace' );
        $folder_obj->setPath( $new_path );

        $this->assertSame($new_path, $folder_obj->path);

    }

    /**
     * Test of the moFilesManager\Folder->create() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function testCreate() {

        $create_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'create-folder-test';

        $folder_obj = new moFilesManager\Folder( $create_path );
        $folder_obj->create();
        
        $this->assertSame($folder_obj->create(), TRUE);
        $this->assertSame(is_dir($create_path), TRUE);

    }

    /**
     * Test of the moFilesManager\Folder->copy() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function testCopy() {

        $original_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'input_img';
        $copy_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'copy_img';

        $folder_obj = new moFilesManager\Folder($original_path);

        // We try to copy the folder, expected result : TRUE
        $this->assertSame($folder_obj->copy($copy_path), TRUE);
        $this->assertSame(is_dir($copy_path), TRUE);
        
        // We check elements copy on 2 levels
        $error = 0;
        foreach(scandir( $original_path ) as $element) {

            if(is_dir($original_path .DIRECTORY_SEPARATOR. $element)) {

                $original_path = $original_path .DIRECTORY_SEPARATOR. $element;
                $copy_path = $copy_path .DIRECTORY_SEPARATOR. $element;

                foreach(scandir( $original_path ) as $element) {

                    if(is_dir($original_path .DIRECTORY_SEPARATOR. $element)) {
        
                        if( !is_dir($copy_path .DIRECTORY_SEPARATOR. $element) ) {
        
                            $error++;
        
                        }
        
                    }
                    else {
        
                        if( !is_file($copy_path .DIRECTORY_SEPARATOR. $element) ) {
        
                            $error++;
        
                        }
        
                    }
        
                }

            }
            elseif(is_file($original_path .DIRECTORY_SEPARATOR. $element)) {

                if( !is_file($copy_path .DIRECTORY_SEPARATOR. $element) ) {

                    $error++;

                }

            }

        }

        $this->assertSame($error, 0);
    }

    /**
     * Test of the moFilesManager\Folder->move() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function testMove() {

        $original_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'copy_img' .DIRECTORY_SEPARATOR. 'others' .DIRECTORY_SEPARATOR. 'others-subfolder';
        $new_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'copy_img' .DIRECTORY_SEPARATOR. 'others-subfolder';

        // Store the path of the elements contained in the original folder
        $moved_elements = array();
        foreach(scandir( $original_path ) as $element) {

            if( is_file($original_path .DIRECTORY_SEPARATOR. $element) ) {

                $moved_elements[] = $element;

            }

        }

        $folder_obj = new moFilesManager\Folder($original_path);

        // We try to move the folder, expected result : TRUE
        $this->assertSame($folder_obj->move($new_path), TRUE);
        $this->assertSame(is_dir($new_path), TRUE);
        $this->assertSame(is_dir($original_path), FALSE);
        
        // We check the moved elements
        $error = 0;
        foreach($moved_elements as $moved_element) {

            if( !is_file($new_path .DIRECTORY_SEPARATOR. $element) ) {

                $error++;

            }

        }

        $this->assertSame($error, 0);

    }

    /**
     * Test of the moFilesManager\Folder->rename() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function testRename() {

        $original_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'copy_img' .DIRECTORY_SEPARATOR. 'others-subfolder';
        $new_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'copy_img' .DIRECTORY_SEPARATOR. 'subfolder-aftermove';

        $folder_obj = new moFilesManager\Folder($original_path);

        // We try to rename the folder, expected result : TRUE
        $this->assertSame($folder_obj->rename('subfolder-aftermove'), TRUE);
        $this->assertSame(is_dir($new_path), TRUE);
        $this->assertSame(is_dir($original_path), FALSE);
        
    }

    /**
     * Test of the moFilesManager\Folder->zip() AND moFilesManager\Folder->unzip() methods
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function testZipUnzip() {

        $src_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'copy_img';
        $archive1_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'copy_img.zip';;

        $folder_obj = new moFilesManager\Folder($src_path);

        // We try to zip the folder without providing an archive path, expected result : TRUE
        $this->assertSame($folder_obj->zip(), TRUE);
        $this->assertSame(is_file($archive1_path), TRUE);
        $this->assertSame(mime_content_type($archive1_path), 'application/zip');

        // We try to zip the folder by providing an archive path, expected result : TRUE
        $archive2_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'create-folder-test' .DIRECTORY_SEPARATOR. 'custompath.zip';
        $folder_obj->setPath(TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'input_data');
        $this->assertSame($folder_obj->zip($archive2_path, TRUE), TRUE);
        $this->assertSame(is_file($archive2_path), TRUE);
        $this->assertSame(mime_content_type($archive2_path), 'application/zip');

        // Then we try to unzip the folder, expected result : TRUE
        $archive1_extract_path = TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'extract_copy_img';
        $folder_obj->setPath($archive1_extract_path);
        $this->assertSame($folder_obj->unzip($archive1_path), TRUE);
        $this->assertSame(is_dir($archive1_extract_path), TRUE);

        // We check elements extraction on 2 levels
        $error = 0;
        foreach(scandir( $src_path ) as $element) {

            if(is_dir($src_path .DIRECTORY_SEPARATOR. $element)) {

                $src_path = $src_path .DIRECTORY_SEPARATOR. $element;
                $extract_path = $archive1_extract_path .DIRECTORY_SEPARATOR. $element;

                foreach(scandir( $src_path ) as $element) {

                    if(is_dir($src_path .DIRECTORY_SEPARATOR. $element)) {
        
                        if( !is_dir($extract_path .DIRECTORY_SEPARATOR. $element) ) {
        
                            $error++;
        
                        }
        
                    }
                    else {
        
                        if( !is_file($extract_path .DIRECTORY_SEPARATOR. $element) ) {
        
                            $error++;
        
                        }
        
                    }
        
                }

            }
            elseif(is_file($src_path .DIRECTORY_SEPARATOR. $element)) {

                if( !is_file($archive1_extract_path .DIRECTORY_SEPARATOR. $element) ) {

                    $error++;

                }

            }

        }

        $this->assertSame($error, 0);

    }
  
    /**
     * Test of the moFilesManager\Folder->drain() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function testDrain() {

        $folder_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'copy_img';
        $folder_obj = new moFilesManager\Folder($folder_path);

        // We try to drain the content of the output folder, expected result : TRUE
        $this->assertSame($folder_obj->drain(), TRUE);
        $this->assertSame(count( array_diff( scandir($folder_path), array('..','.') ) ), 0);
        $this->assertSame(rmdir($folder_path), TRUE);

    }

    /**
     * Test of the moFilesManager\Folder->delete() method
     *
     * @return void
     * 
     * @since       1.0.0
     */

    public function testDelete() {

        $folder_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'extract_copy_img';
        $folder_obj = new moFilesManager\Folder($folder_path);

        // We try to drain the content of the output folder, expected result : TRUE
        $this->assertSame($folder_obj->delete(), TRUE);
        $this->assertSame(is_dir($folder_path), FALSE);

    }

}
?>