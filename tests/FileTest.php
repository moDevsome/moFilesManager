<?php

if( !defined('TESTS_SCRIPT_PATH') ) {

    define('TESTS_SCRIPT_PATH', dirname(__FILE__));
    moFilesManager\moFilesManager::setDebugState(TRUE);

}

/**
 * Tests for the moFilesManager\File class
 * 
 * @since       1.1.3
 */
class FileTest {

    private $file_obj;

    private $file_content = array('prepend' => 'Bonjour tout le monde'.PHP_EOL,
                                  'body' => 'Hello world'.PHP_EOL,
                                  'append' => 'Hola todo el mundo'.PHP_EOL,
                                  'write_test' => 'Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit...');

    /**
     * Test of the moFilesManager\Folder->setPath() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function test_setPath() : bool {

        $new_path = TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'running' .DIRECTORY_SEPARATOR. 'file_test.txt';

        $this->file_obj = new moFilesManager\File( TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'elements' .DIRECTORY_SEPARATOR. 'path-to-replace' );
        $this->file_obj->setPath( $new_path );

        return $new_path === $this->file_obj->path;

    }

    /**
     * Test of the moFilesManager\File->write() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function test_write() : bool {

        if($this->file_obj->setContent($this->file_content['write_test'])->write() === TRUE) {

            if(is_file($this->file_obj->path)) {

                return $this->file_content['write_test'] === file_get_contents($this->file_obj->path);

            }

        }

        return FALSE;

    }

    /**
     * Test of the moFilesManager\File->setContent() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function test_setContent() : bool {

        if($this->file_obj->setContent($this->file_content['body'])->write() === TRUE) {

            if(is_file($this->file_obj->path)) {

                return $this->file_content['body'] === file_get_contents($this->file_obj->path);

            }

        }

        return FALSE;

    }

    /**
     * Test of the moFilesManager\File->getContent() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function test_getContent() : bool {

        return $this->file_obj->getContent() === file_get_contents($this->file_obj->path);

    }

    /**
     * Test of the moFilesManager\File->addContent() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function test_addContent() : bool {

        $error = 0;

        // Append content test
        if($this->file_obj->addContent($this->file_content['append'])->write() === TRUE) {

            if(is_file($this->file_obj->path)) {

                $expected_content = $this->file_content['body'].$this->file_content['append']; 

                if($expected_content !== file_get_contents($this->file_obj->path)) { $error++; }

            }
            else {

                $error++;
    
            }

        }
        else {

            $error++;

        }

        // Prepend content test
        if($this->file_obj->addContent($this->file_content['prepend'], TRUE)->write() === TRUE) {

            if(is_file($this->file_obj->path)) {

                $expected_content = $this->file_content['prepend'].$this->file_content['body'].$this->file_content['append']; 

                if($expected_content !== file_get_contents($this->file_obj->path)) { $error++; }

            }
            else {

                $error++;
    
            }

        }
        else {

            $error++;

        }

        return $error === 0;

    }

     /**
     * Test of the moFilesManager\File->rename() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function test_rename() : bool {

        $new_name = 'file_test_renamed.txt';
        $new_path = str_ireplace('file_test.txt', 'file_test_renamed.txt', $this->file_obj->path);

        if($this->file_obj->rename($new_name) === TRUE) {

            return is_file($new_path);

        }

        return FALSE;
        
    }

    /**
     * Test of the moFilesManager\File->copy() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function test_copy() {

        $copy_path = TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'running' .DIRECTORY_SEPARATOR. 'copy-folder' .DIRECTORY_SEPARATOR. 'file_test_duplicat.txt';

        if($this->file_obj->copy($copy_path) === TRUE) {

            if(is_file($copy_path)) {

                return file_get_contents($this->file_obj->path) === file_get_contents($copy_path);

            }

        }

        return FALSE;

    }

    /**
     * Test of the moFilesManager\File->move() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function test_move() : bool {
        
        $this->file_obj->setPath(TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'running' .DIRECTORY_SEPARATOR. 'copy-folder' .DIRECTORY_SEPARATOR. 'file_test_duplicat.txt');

        $new_path = TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'running' .DIRECTORY_SEPARATOR. 'move-folder' .DIRECTORY_SEPARATOR. 'file_test_moved.txt';

        if($this->file_obj->move($new_path) === TRUE) {

            if(is_file($new_path)) {

                return file_get_contents(TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'running' .DIRECTORY_SEPARATOR. 'file_test_renamed.txt') === file_get_contents($new_path);

            }

        }

        return FALSE;
        
    }

    /**
     * Test of the moFilesManager\File->delete() method
     *
     * @return void
     * 
     * @since       1.0.0
     */
    public function test_delete() : bool {

        if($this->file_obj->delete() === TRUE) {

            return !is_file($this->file_obj->path);

        }

        return FALSE;

    }

    /**
     * Test of the moFilesManager\File->getStream() method
     *
     * @return void
     *
     * @since       1.1.4
     */
    public function test_getStream() : bool {

        // Init
        $file_content = '<p>hello world</p>';
        $file_path = TESTS_SCRIPT_PATH .DIRECTORY_SEPARATOR. 'running' .DIRECTORY_SEPARATOR. 'file_stream_test.txt';
        $file_obj = new moFilesManager\File($file_path);

        // Test w+
        $w_stream = $file_obj->getStream('w+', TRUE);
        if($w_stream === FALSE) {

            return FALSE;

        }
        fwrite($w_stream, $file_content);
        fclose($w_stream);

        // Test r
        $r_stream = $file_obj->getStream('r');
        if($r_stream === FALSE) {

            return FALSE;

        }
        $result = fread($r_stream, strlen($file_content)) === $file_content;
        fclose($r_stream);

        $file_obj->delete();

        return $result;

    }

}
?>