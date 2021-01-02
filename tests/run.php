<?php

require_once 'FolderTest.php';
require_once 'FileTest.php';

$success_text_output =  "\033[32m -> SUCCESS\033[37m\r\n";
$failed_text_output = "\033[31m -> FAILED\033[37m\r\n";

// Check if we want a trace_log output
$use_log = FALSE;
if($argc === 2) {

    if($argv[1] === '-log') { // we use the default log file : [VENDOR_PATH]\moFilesManager\tests\tests_traces.log

        moFilesManager\moFilesManager::setDebugState(TRUE);
        $use_log = TRUE;

    }

}

$error = 0;

echo '-----------------------------------------------------------'.PHP_EOL;
echo '  RUNNING FOLDER TESTS'.PHP_EOL;
echo '-----------------------------------------------------------'.PHP_EOL;

$folder_tests = new FolderTest;
foreach(get_class_methods($folder_tests) as $test_case) {

    echo 'Running "'.str_ireplace('test_','Folder::', $test_case).'()" test case';
    if($folder_tests->$test_case() === TRUE) { echo $success_text_output; } else { echo $failed_text_output; $error++; }

}

echo '-----------------------------------------------------------'.PHP_EOL;
echo '  RUNNING FILE TESTS'.PHP_EOL;
echo '-----------------------------------------------------------'.PHP_EOL;

$file_tests = new FileTest;
foreach(get_class_methods($file_tests) as $test_case) {

    echo 'Running "'.str_ireplace('test_','File::', $test_case).'()" test case';
    if($file_tests->$test_case() === TRUE) { echo $success_text_output; } else { echo $failed_text_output; $error++; }

}

echo '-- END OF RUN'.PHP_EOL.PHP_EOL;

if($error > 0) {

    echo "\033[31mSome tests has been executed with error, ask a log file rendering add check it\033[37m\r\n";

}
else {
  
    echo "\033[32mAll the tests has been executed with success\033[37m\r\n";

}

if($use_log === TRUE) {

    $log_file_path = dirname(__FILE__)  .DIRECTORY_SEPARATOR. 'tests_traces.log';
    $current_content = '';

    if(is_file($log_file_path)) {

        $current_content = file_get_contents($log_file_path).PHP_EOL.PHP_EOL;

    }

    $current_content.= '/// TESTS RUN TRACES ----------------------------------------'.PHP_EOL;
    $current_content.= implode(PHP_EOL, moFilesManager\moFilesManager::getLogs());

    $log_file_stream = fopen($log_file_path,'w+');
    fwrite($log_file_stream, $current_content);
    fclose($log_file_stream);

}
die();
?>