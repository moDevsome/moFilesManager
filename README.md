**moFilesManager is a small library for handling files and folders more easier in PHP.  
The library provide two objects, one for handling files and one for handling folders.**

## Required Configuration
php: >=7.0.0  
Fileinfo PHP extension

## How to use

### Installation
The better way for adding moFilesManager to your application is to use Composer by launching it in your dev environment, here's the  URL of the Packagist page : https://packagist.org/packages/modevsome/mofilesmanager  
  
You can also download the last release.

### Tests
Once moFilesManager is added into your application, don't hesitate to play some tests for checking the basic functions of the library. Open you command line tool and execute the following command :
```cli
cd [YOUR APP PATH]\vendor\modevsome\mofilesmanager\tests
php run.php
```
You can ask a log file of the tests by adding ths param : -log

### Basic usage
```php
// Handle a folder
$folder_object = new moFilesManager\Folder( $folder_path );

// Handle a file
$file_object = new moFilesManager\File( $file_path );
```
You can read the method summary below to find out all the features.

### ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) IMPORTANTS ADVICES ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+)
* write and test your script in a local dev environment
* avoid using moFilesManager for very complex files tree, with too many files and nested subfolder, it prevent crash caused by too much recursivity, "out of time" or "out of memory" execution (for example, dispatch your task if you have a high quantity of files to process)
* don't forget to remove the "tests" folder before pushing your application to your production environment


## moFilesManager\Folder methodes summary

### Create a Folder instance
```php
$folder_object = new moFilesManager\Folder( $folder_path );
```
This method return an instance of the Folder class.
The path is optionnal at this step, and can be changed later.

### Change the path
```php
$folder_object->setPath( $new_folder_path );
```
This method change the path value of the Folder object.
This method is fluent, it return the current object.

### Create the folder
```php
$folder_object->create();
```
This method create the folder on the disk.
This method return TRUE in case of success, else FALSE.

### Copy the folder
```php
$folder_object->copy(string $copy_path, $replace);
```
This method recursively duplicat the current folder at the provided location defined in the required $copy_path param. All the content of the folder will be duplicated, including files and subfolders.  
The boolean param $replace is optionnal. If the param is equal to TRUE, the folder we be replaced if it already exist, if tthe param is equal to FALSE, the existing folder will be not replaced. By default the param is equal to TRUE.  
This method return TRUE in case of success, else FALSE.

### Rename the folder
```php
$folder_object->rename(string $new_name, $replace);
```
This method change the name of the current folder. The provided $new_name should not be a path, just a folder name.  
The boolean param $replace is optionnal. If the param is equal to TRUE, the folder we be replaced if it already exist, if tthe param is equal to FALSE, the existing folder will be not replaced. By default the param is equal to TRUE.  
This method return TRUE in case of success, else FALSE.

### Move the folder
```php
$folder_object->move(string $new_path, $replace);
```
This method duplicat the current folder at the provided location defined in the required $copy_path param. All the content of the folder will be moved, including files and subfolders.  
The boolean param $replace is optionnal. If the param is equal to TRUE, the folder we be replaced if it already exist, if tthe param is equal to FALSE, the existing folder will be not replaced. By default the param is equal to TRUE.  
This method return TRUE in case of success, else FALSE.

### Drain the folder
```php
$folder_object->drain();
```
This method recursively remove all items contained into the folder.  
A rollback function minimise the impact of eventual errors occurred during the process.  
This method return TRUE in case of success, else FALSE.

### Delete the folder
```php
$folder_object->delete();
```
This method delete the folder, even if there's files or subfolders contained in the folder.  
A rollback function minimise the impact of eventual errors occurred during the process.  
This method return TRUE in case of success, else FALSE.

### Make a Zip archive of the folder
```php
$folder_object->zip(string $archive_path = '', $replace, $options);
```
All the items will be recursively added in the archive.    
@param string  $archive_name : The path of the zip file that will be made. Default : The folder parent path    
@param boolean  $replace : True to replace the current file if it already exist. Default : TRUE    
@param array  $options : An array containing some options : 'compression_method', 'compression_level'    
This method return TRUE in case of success, else FALSE.

### Extract an archive zip into the the current folder
```php
$folder_object->unzip(string $archive_path = '', $replace);
```
The path of the zip file must be provided throw the param $archive_path.  
The boolean param $replace is optionnal. If the param is equal to TRUE, the folder we be replaced if it already exist, if the param is equal to FALSE, the existing folder will be not replaced. By default the param is equal to TRUE.  
A rollback function minimise the impact of eventual errors occurred during the process. 
This method return TRUE in case of success, else FALSE.

IMPORTANT : The extracted archive need to be outside the target folder.

## moFilesManager\File methodes summary

### Create a File instance
```php
$file_object = new moFilesManager\File( $file_path );
```
This method return an instance of the File class.
The path is optionnal at this step, and can be changed later.

### Change the path
```php
$file_object->setPath( $new_file_path );
```
This method change the path value of the File object.
This method is fluent, it return the current object.

### Get the current file content
```php
$file_object->getContent();
```
Return the file content as a string.

### Set the current file content
```php
$file_object->setContent(string $content);
```
This method change the content of the file.  
The content of the file is NOT saved as long as the method write() is not called.  
This method is fluent, it return the current object.

### Add content to the current file
```php
$file_object->addContent(string $content, bool $prepend = FALSE);
```
This method Append or Prepend the provided content to the current content string.
The content of the file is NOT saved as long as the method write() is not called.  
This method is fluent, it return the current object.

### Create or replace the current file on the disk
```php
$file_object->write(bool $replace);
```
This method save the current file on the disk.  
The boolean param $replace is optionnal. If the param is equal to TRUE, the file we be replaced if it already exist, if tthe param is equal to FALSE, the existing file will be not replaced. By default the param is equal to TRUE.  
This method return TRUE in case of success, else FALSE.

### Copy the current file
```php
$file_object->copy(string $copy_path, bool $replace);
```
This method duplicat the current at the provided location defined in the required $copy_path param.  
The boolean param $replace is optionnal. If the param is equal to TRUE, the file we be replaced if it already exist, if tthe param is equal to FALSE, the existing file will be not replaced. By default the param is equal to TRUE.  
This method return TRUE in case of success, else FALSE.

### Rename the current file
```php
$file_object->rename(string $new_name, bool $replace);
```
This method change the name of the current file, BUT it does not move the file. The provided $new_name should not be a path, just a file name.  
The boolean param $replace is optionnal. If the param is equal to TRUE, the file we be replaced if it already exist, if tthe param is equal to FALSE, the existing file will be not replaced. By default the param is equal to TRUE.  
This method return TRUE in case of success, else FALSE.

### Move the current file
```php
$file_object->move(string $new_path, bool $replace);
```
This method move the current file at the provided location defined in the required $new_path param.   
The boolean param $replace is optionnal. If the param is equal to TRUE, the file we be replaced if it already exist, if tthe param is equal to FALSE, the existing file will be not replaced. By default the param is equal to TRUE.  
This method return TRUE in case of success, else FALSE.

### Delete the current file
```php
$file_object->delete();
```
This method delete the current file.
This method return TRUE in case of success, else FALSE.

### Upload a file
```php
$file_object->upload($tmp_name, $replace, $allowed_types, $max_size);
```
This method upload a file.  
@param $tmp_name : The file tmp_name (required). It can be found in the associeted row of the superglobal $_FILES.  
@param $replace : TRUE to replace the current file if it already exist. Default : TRUE  
@param $allowed_types : An array which contain one or more file type that will be accepted, if this param if empty, all types will be accepted.  
@param $max_size : The allowed maximum file size (must be provided in BYTES).  
This method return TRUE in case of success, else FALSE.

## moFilesManager\moFilesManager methodes summary

**The following methods must be called statically.**

### Enable/Disable debuging
By default debuging is disabled, set debuging to TRUE if you want to use the trace log
```php
moFilesManager\moFilesManager::setDebugState(bool $state);
```
Return nothing (void)

### Get the current debuging state
```php
moFilesManager\moFilesManager::getDebugState();
```
### Get all recorded traces
```php
moFilesManager\moFilesManager::getLogs();
```
Return an array containing all recorded traces during the several process

### Get the last recorded trace
```php
moFilesManager\moFilesManager::getLastLog();
```
Return a string which contain the last logged content

### Rebuild and Secure a given path by removing illegitimate directory separator
```php
moFilesManager\moFilesManager::formatPath(string $path);
```
Return a string which contain the cleaned path
