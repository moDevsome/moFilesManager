moFilesManager is a small library for handling files and folders more easier in PHP.

The library provide two objects, one for handling files and one for handling folders.

## moFilesManager\File class summary

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
@param $max_size : The allowed maximum file size (must be provided in OCTET).  
This method return TRUE in case of success, else FALSE.
