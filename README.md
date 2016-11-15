# Introduction #

The **PhpSettings** class is intended to manage settings coming from a *php.ini* file.

It can modify settings, enable or disable PHP extensions and save the results back to the original input file or to another file. 

It tries to enforce type checking on the setting values, taking type information from either the **PhpSettings.csv** file (see the *PhpSettings.csv file* section in this document) or from the values found in the supplied *php.ini* file. An exception will be thrown if you try to change a setting using a value whose type is not compatible with the original value ; for example, you will not be able to set the *memory\_limit* setting to "hello world", since it accepts an integer value which can also be expressed as a byte quantity.

Formatting and comments are preserved when writing back the modified contents. Additionally, quoted values will remain quoted, and unquoted values will remain unquoted. Great care has been taken for preserving the initial formatting, so that running the *diff* command on the input and output files will only show the modifications you made.

In the same way, most *php.ini* files contain commented-out directives, such as :

	;realpath_cache_size = 16k

The **PhpSettings** class perfectly handles this kind of situation ; if you try to set the value of the *realpath\_cache\_size* setting to, say, "32k" then, instead of appending a new setting value to the end of file, the original - commented-out - setting will be reused, uncommented and its value will be change, to give the following :

	realpath_cache_size = 32k 


# Dependencies #

This class uses the **Path Utiities** package ([http://www.phpclasses.org/package/10018-PHP-Platform-independent-path-management-utitlities.html](http://www.phpclasses.org/package/10018-PHP-Platform-independent-path-management-utitlities.html "http://www.phpclasses.org/package/10018-PHP-Platform-independent-path-management-utitlities.html")). A copy of file *Path.phpclass* has been provided here for your convenience, but it may not be the latest release.

# Limitations #

This class only handles changes to *php.ini* settings ; it is not intended to administrate an Apache server. After every change, you will need to restart your server manually for the changes to take effect.

# Examples #

Before digging into the class' details, a few examples will give you an overview of its capabilities.

## Getting the value of a setting ##

Getting the value of a setting is really straightforward ; just load the *php.ini* file of your choice, then access the setting as if it was a property of the **PhpSettings** object :

	<?php
		require ( 'PhpSettings.phpclass' ) ;

		$settings	=  new PhpSettings ( 'php.ini' ) ;
		echo "Memory limit is : " . $settings -> memory_limit ;

Note that you can also use the *Get()* method for that :

		echo "Memory limit is : " . $settings -> Get ( 'memory_limit' ) ;
	

## Changing a setting value and writing back the results ##

The following example reads file *php.ini*, modifies the *memory\_limit* setting, then writes the contents back to file *php.ini.out* :

	<?php
		require ( 'PhpSettings.phpclass' ) ;

		$settings	=  new PhpSettings ( 'php.ini' ) ;
		$settings -> memory_limit 	=  '128M' ;
		$settings -> SaveTo ( 'php.ini.out' ) ; 

As for retrieving a setting value, there is a counterpart method called *Set()* that allows you to change the value of a setting :

		$settings -> Set ( 'memory_limit', '128M' ) ; 

## Enabling an extension ##

Extensions are special settings that can occur several times throughout a *php.ini* file, with different values. For example :

	extension=mbstring.so
	extension=exif.so

on Unix systems ; the equivalent on Windows platforms would be :

	extension=php_mbstring.dll
	extension=php_exif.dll

To enable an extension, just specify its name ; you don't have to worry about the file extension (".so" on Unix systems, ".dll" on Windows) as well as the "php_" prefix that is added on Windows platforms :

	<?php
		require ( 'PhpSettings.phpclass' ) ;

		$settings	=  new PhpSettings ( 'php.ini' ) ;
		$settings -> EnableExtension ( 'mbstring' ) ; 

If the extension is already present in the input *php.ini* file, the concerned line will be reused and uncommented if necessary. If the extension is not already present, it will be inserted just after the very last extension specified in the input file.

This method also handles Zend extensions. See the description of the *EnableExtension()* method for more information on this topic.

## Value checking ##

The **PhpSettings** class enforces type-checking, which is either deduced from the setting value specified in the *php.ini* file, or from the description found in the *PhpSettings.csv* optional file that is provided with this package.

Changing a setting using an incorrect value will throw an exception ; for example, there is a setting called *engine* whose value should be boolean. If you try the following :

		$settings -> engine 		=  "hello world" ;

then you will get the following **PhpSettingsException** :

		The value "hello world" supplied for the "engine" boolean setting is not a valid boolean value.

# Reference #

This section provides a reference for the methods, properties and constants defined by the **PhpSettings** class.

## Constructor ##

	$settings 	=  new PhpSettings ( $file, $options = PhpSettings::OPTIONS\_NONE ) ;

Loads a *php.ini* file specified by the *$file* parameter. After loading, all the settings will be accessible as properties of the *$settings* object, or through the *Get()*, *Set()* or extension management methods.

See the **Constants** section later in this chapter for an explanation about the flags that can be specified for the *$options* parameter. 

## Methods ##

### AsString ###

	$contents	=  $settings -> AsString ( ) ;

Returns the contents of the modified *php.ini* file, as a string.

### DisableExtension ###

	$status		=  $settings -> DisableExtension ( $name, $zend = false, $prefix = true ) ;

Disables the specified extension. Parameters are the following :

- **$name** (*string*) : Extension name. For non-zend extensions, it can be only a subpart of the extension itself ; for example, specifying "mbstring" will yield to :
	- "php_mbstring.dll" on Windows platforms
	- "mbstring.so" on Unix platforms
- **$zend** (*boolean*) : When false, the extension will be searched in the "extension=" settings ; when true, the
"zend_extension=" settings will be searched. In this case, no special processing will be performed on the extension name.
- **$prefix** (*boolean*) : *(Windows platforms only)* When false, no *"php_"* string will be prepended to the extension name.

Returns *false* if the extension was already disabled, *true* otherwise.

### EnableExtension ###

	$status		=  $settings -> EnableExtension ( $name, $zend = false, $prefix = true ) ;

Enables the specified extension. Parameters are the following :

- **$name** (*string*) : Extension name. For non-zend extensions, it can be only a subpart of the extension itself ; for example, specifying "mbstring" will yield to :
	- "php_mbstring.dll" on Windows platforms
	- "mbstring.so" on Unix platforms
- **$zend** (*boolean*) : When false, the extension will be searched in the "extension=" settings ; when true, the
"zend_extension=" settings will be searched. In this case, no special processing will be performed on the extension name.
- **$prefix** (*boolean*) : *(Windows platforms only)* When false, no *"php_"* string will be prepended to the extension name.

Returns *false* if the extension was already enabled, *true* otherwise.

### Get ###

	$value 		=  $settings -> Get ( $setting, $default = null ) ;

Returns the value of the specified PHP .ini setting, converted to the appropriate type, or the value returned by the *ini_get()* function.

Whatever the result, the function returns *null* if the setting is definitely not defined in the php.ini
file.

Settings can also be accessed as if they were properties of the **PhpSettings** class ; for example :

	$value 	=  $settings -> memory_limit ;

For settings containing characters not authorized in PHP identifiers, you can also use the following form :

	$value 	=  $setting -> {'mbstring.strict_detection'}  ;

### GetEnabledExtensions ###

	$array	=  $settings -> GetEnabledExtensions ( $zend = false ) ;

Returns the list of enabled extensions as an array of strings. If the *$zend* parameter is false, only regular PHP extensions will be returned (the ones that have been specified using the *extension* setting). When true, only Zend extensions will be returned (*zend_extension* settings).

### IsExtensionEnabled ###

	$status		=  $settings -> IsExtensionEnabled ( $name, $zend = false, $prefix = true ) ;

Checks if an extension is enabled. Parameters are the following :

- **$name** (*string*) : Extension name. For non-zend extensions, it can be only a subpart of the extension itself ; for example, specifying *"mbstring"* will yield to :
	- "php_mbstring.dll" on Windows platforms
	- "mbstring.so" on Unix platforms
- **$zend** (*boolean*) : When false, the extension will be searched in the *"extension="* settings ; when true, the *"zend_extension="* settings will be searched. In this case, no special processing will be performed on the extension name.
- **$prefix** (*boolean*) : *(Windows platforms only)* When false, no *"php_"* string will be prepended to the extension name.


### Save ###

	$settings -> Save ( ) ;

Writes back the modified contents of the supplied input file.

No saving will occur if no setting has been modified, ie when the *IsDirty* property is *false*.

Returns *true* if contents have been saved, *false* otherwise.

This method sets the *IsDirty* property to *false*.

### SaveTo ###

	$settings -> SaveTo ( $output_file ) ;

Saves the .INI file contents to the specified output file, either modified or not.

This method does not affect the *isDirty* property.

### Set ###

        $settings -> $setting	=  $value ;
		$settings -> $setting	=  [ $value, $section ] ;
		$settings -> Set ( $setting, $value, $section = false ) ;

Changes the value of an existing setting or creates a new one.

Care is taken about the supplied value type ; when a setting is modified, its target type is taken from the setting found in the .INI file, or from the settings loaded from the **PhpSettings.csv** file.

When a new setting is created, the information loaded from the **PhpSettings.csv** file is used to determine its type ; if not found, the type will be guessed from the specified value.

The parameters are the following :

- **$setting** (*string*) : Setting name to be modified or created.
- **$value** (*string*) : New setting value. Specifying a *null* value will unset the specified setting.
- **$section** (*string*) : When specified AND the setting does not exist, it will be appended to the specified .INI section ; otherwise, it will be appended to the end of the file.

You do not need to care about quoting the value you specified : this will automatically be done if the string contains characters that needs to be quoted in a *php.ini* file.

### Unset ###

	$settings -> $setting 		=  null ;
	$settings -> Unset ( $setting ) ;

Undefines the specified setting.
 
If the setting already exists in the INI file, it will simply be commented out.

If it is defined multiple times (which should never happen), only the last non-commented occurrence will be commented out.


## Properties ##

### BackupFile ###

This property is set when a backup copy has been made and gives the path of the backup file.

This path will have the following form :

- *name*.**x**.bak if the *OPTION\_NUMBERED\_BACKUPS* option has been set in the *$options* parameter of the constructor (*name* is the supplied input file, and **x** a unique, sequential number).
- *name*.bak, if the *OPTION\_CREATE\_BACKUP* flag is set.
- An empty string, if none of the above options has been specified.

### Filename ###

Absolute path of the file that has been supplied to the class constructor.

### IsDirty ###

This property will be set to *true* whenever a setting has been changed using either the *Set()* or *EnableExtension* method.

It is used by the *Save()* method to determine if file contents should be written back or not.


## Constants ##

### OPTION\_\* flags ###

A combination of the following flags can be specified to the *$options* parameter of the class constructor :

- *OPTION\_NONE* : No option specified.
- *OPTION\_CREATE\_BACKUP* : Creates a backup file, having the same name as the supplied input file, with the *.bak* extension appended.
- *OPTION\_NUMBERED\_BACKUPS* : Creates a backup file, having the same name as the supplied input file, plus a unique integer number and the *.bak* extension.

Backup files are always created in the same directory as the supplied input file. 

### PHP\_INI\_\* constants ###

These constants define the locations where a setting can be modified :

- **PHP\_INI\_USER** : Entry can be set in user scripts (like with ini_set()) or in the Windows registry. Since PHP 5.3, entry can be set in .user.ini
- **PHP\_INI\_PERDIR** : Entry can be set in php.ini, .htaccess, httpd.conf or .user.ini (since PHP 5.3)
- **PHP\_INI\_SYSTEM** : Entry can be set in php.ini or httpd.conf
- **PHP\_INI\_ALL** : Entry can be set anywhere

### VALUE\_TYPE\_\* constants ###

These constants specify the type of a setting :

- **VALUE\_TYPE\_NONE** : the setting value type has not been determined.
- **VALUE\_TYPE\_INTEGER** : integer value.
- **VALUE\_TYPE\_FLOAT** : floating-point value.
- **VALUE\_TYPE\_BOOLEAN** : boolean value. Recognized values are *"On"*, *"Yes"* and *"True"* for *true* values, and *"Off"*, *"No"*, *"False"* and *"None"* for *false* values (strings are not case-sensitive). Additionally, an integer value of 0 means *false* and 1 means *true*.
- **VALUE\_TYPE\_STRING** : Generic string, quoted or not.
- **VALUE\_TYPE\_QUANTITY** : An integer, specified as a byte quantity, such as "1024K", "128M" or "1G". When retrieving such a setting, its value will automatically be converted to an integer value. 
- **VALUE\_TYPE\_PERCENTAGE** : An integer specified as a percentage, such as "23%". Getting such a setting will return floating point values, such as : 0.23
- **VALUE\_TYPE\_HEXADECIMAL\_INTEGER** : An integer, specified as a hexadecimal value. The retrieved value will always be an integer.
- **VALUE\_TYPE\_OCTAL\_INTEGER** : An integer, specified as an octal value. The retrieved value will always be an integer.


# PhpSettings.csv file #

The **PhpSettings.csv** file is completely optional ; when present, it must reside in the same directory as the **PhpSettings.phpclass** file.

It contains a description of the PHP settings, taken from the following address : 
[http://php.net/manual/en/ini.list.php](http://php.net/manual/en/ini.list.php "http://php.net/manual/en/ini.list.php").

This is a CSV file whose fields are separated by semicolons, and contains the following columns :

- **Setting** : setting name
- **Value type** : setting value type (see the *VALUE\_TYPE\_\* constants)
- **Default value** : default setting value
- **Location** : Location(s) where the setting can be modified (see the *PHP\_INI\_\* constants)
- **Comment** : optional comment
- **Min PHP version** : specifies the version where this setting was introduced. This parameter is optional.
- **Max PHP version** : specified the version where this setting was removed. This parameter is optional.
- **Module** : related module. This parameter is optional.
- **Min module version** : specifies the module version where this setting was introduced. This parameter is optional.
- **Max module version** : specifies the module version where this setting was removed. This parameter is optional.
