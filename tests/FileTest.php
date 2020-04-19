<?php

use PHPUnit\Framework\TestCase;

if( !defined('TESTS_SCRIPT_PATH') ) {

    define('TESTS_SCRIPT_PATH', dirname(__FILE__));
    moFilesManager\moFilesManager::setDebugState(TRUE);

}

/**
 * Tests for the moFilesManager\File class
 * 
 * @since       1.0.0
 */
class FileTest extends TestCase {

    /**
     * Test of the moFilesManager\Folder->setPath() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function testSetPath() {

        $new_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'input_data' .DIRECTORY_SEPARATOR. 'lorem2.txt';

        $file_obj = new moFilesManager\File( 'tests' .DIRECTORY_SEPARATOR. 'path-to-replace' );
        $file_obj->setPath( $new_path );

        $this->assertSame($new_path, $file_obj->path);
        $this->assertSame($file_obj->file_exist, TRUE);

    }

    /**
     * Test of the moFilesManager\File->write() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function testWrite() {

        $file1_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'new_file.txt';
        $file1_obj = new moFilesManager\File( $file1_path );

        $file2_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'new_file2.txt';
        $file2_obj = new moFilesManager\File( $file2_path );
        $file2_content = 'That\'s one small step for man, one giant leap for mankind.';
        
        // Write file without content
        $this->assertSame($file1_obj->write(), TRUE);
        $this->assertSame(is_file($file1_path), TRUE);

        // Write file with content
        $file2_obj->setContent($file2_content);
        $this->assertSame($file2_obj->write(), TRUE);
        $this->assertSame(is_file($file2_path), TRUE);
        $this->assertSame(file_get_contents($file2_path), $file2_content);

    }

    /**
     * Test of the moFilesManager\File->getContent() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function testGetContent() {

        $file2_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'new_file2.txt';
        $file2_obj = new moFilesManager\File( $file2_path );
        $file2_content = 'That\'s one small step for man, one giant leap for mankind.';
        
        $this->assertSame($file2_obj->getContent(), $file2_content);

    }

    /**
     * Test of the moFilesManager\File->addContent() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function testAddContent() {

        $file1_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'new_file.txt';
        $file1_obj = new moFilesManager\File( $file1_path );
        $file1_content = 'I\'ve seen Rosa reading her book lyed down in the grass by a quiet april afternoon.';

        // Write the file
        $file1_obj->addContent( $file1_content );
        $this->assertSame($file1_obj->write(), TRUE);
        $this->assertSame(file_get_contents($file1_path), $file1_content);

        $file2_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'new_file2.txt';
        $file2_obj = new moFilesManager\File( $file2_path );
        $file2_content = 'That\'s one small step for man, one giant leap for mankind.';

        $file2_content_prepend = 'John Doe eat an orange while his wife prepare a pumpkin pie.' .PHP_EOL;
        $file2_content_append = PHP_EOL. 'Their son Sam is in the living room and playing chess with his friend Tony.';
        
        // Prepend content
        $file2_obj->addContent( $file2_content_prepend, TRUE );

        // Append content
        $file2_obj->addContent( $file2_content_append );

        // Write the file
        $this->assertSame($file2_obj->write(), TRUE);
        $this->assertSame(file_get_contents($file2_path), $file2_content_prepend . $file2_content . $file2_content_append);

    }

     /**
     * Test of the moFilesManager\File->rename() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function testRename() {

        $file1_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'new_file.txt';
        $file1_obj = new moFilesManager\File( $file1_path );

        $this->assertSame($file1_obj->rename('new_file_renamed.txt'), TRUE);
        $this->assertSame( is_file( dirname($file1_path) .DIRECTORY_SEPARATOR. 'new_file_renamed.txt') , TRUE);
        
    }

    /**
     * Test of the moFilesManager\File->copy() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function testCopy() {

        $file1_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'new_file_renamed.txt';
        $file1_obj = new moFilesManager\File( $file1_path );

        $copy_path = TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'create-folder-test' .DIRECTORY_SEPARATOR. 'new_file_copie.txt';

        // We try to copy the file, expected result : TRUE
        $this->assertSame($file1_obj->copy($copy_path), TRUE);
        $this->assertSame(is_file($copy_path), TRUE);

        $copy_obj = new moFilesManager\File( $copy_path );
        $this->assertSame($file1_obj->getContent(), $copy_obj->getContent());

    }

    /**
     * Test of the moFilesManager\File->move() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function testMove() {

        // Move the file without renaming
        $file1_path =  TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'new_file_renamed.txt';
        $file1_obj = new moFilesManager\File( $file1_path );

        $new_path = TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'create-folder-test' .DIRECTORY_SEPARATOR. 'mvd' .DIRECTORY_SEPARATOR. 'new_file_moved.txt';

        // We try to move the file, expected result : TRUE
        $this->assertSame($file1_obj->move($new_path), TRUE);
        $this->assertSame(is_file($new_path), TRUE);

        $moved_obj = new moFilesManager\File( $new_path );
        $this->assertSame($file1_obj->getContent(), $moved_obj->getContent());
        
    }

    /**
     * Test of the moFilesManager\File->delete() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function testDelete() {

        $file1_path = TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'create-folder-test' .DIRECTORY_SEPARATOR. 'mvd' .DIRECTORY_SEPARATOR. 'new_file_moved.txt';
        $file2_path = TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'new_file2.txt';

        $file_obj = new moFilesManager\File( $file1_path );

        // We delete the files, expected result : TRUE
        $this->assertSame($file_obj->delete(), TRUE);
        $this->assertSame($file_obj->setPath($file2_path)->delete(), TRUE);

        $this->assertSame(is_file($file1_path), FALSE);
        $this->assertSame(is_file($file2_path), FALSE);

        // That was the last test, now we clean the tests folder
        $this->assertSame($file_obj->setPath(TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'copy_img.zip')->delete(), TRUE);

        $folder_obj = new moFilesManager\Folder( TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'create-folder-test' );
        $this->assertSame($folder_obj->delete(), TRUE);

    }

}
?>