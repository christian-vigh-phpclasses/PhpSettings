<?php
	/****************************************************************************************************

		This example file demonstrates the use of the PhpSettings class.

	 ****************************************************************************************************/
	require_once ( 'PhpSettings.phpclass' ) ;

	if  ( php_sapi_name ( )  !=  'cli' )
		echo ( '<pre>' ) ;

	// Load settings defined in file "example.php.ini"
	$settings	=  new PhpSettings ( 'example.php.ini' ) ;

	// Change the "memory_limit" setting.
	// Note that we could have used :
	//	$settings -> Set ( 'memory_limit', '127M' ) ;
	$settings -> memory_limit	=  '127M' ;
	echo ( "Memory limit set to 127M instead of 1024M\n\n" ) ;

	// Enable extensions that are already listed in file 'example.php.ini', but are commented out
	echo ( "Enabling disabled extensions 'mbstring' and 'exif'\n\n" ) ;
	$settings -> EnableExtension ( 'mbstring' ) ;
	$settings -> EnableExtension ( 'exif' ) ;

	// Enable an extension that is not listed in file 'example.php.ini'
	echo ( "Enabling undefined extension 'unknown'\n\n" ) ;
	$settings -> EnableExtension ( 'unknown' ) ;

	// Display the list of currently defined extensions ; you should see the 'mbstring' and 'exif' extensions,
	// which were initially listed in the 'example.php.ini' file but commented out, along with the 'unknown'
	// extension, which has just been added
	echo ( "List of enabled extensions :\n" ) ;
	echo ( "--------------------------\n" ) ;
	echo ( "\t" . implode ( "\n\t", $settings -> GetEnabledExtensions ( ) ) . "\n\n" ) ;

	// Save the modified contents to file 'example.php.out' 
	$settings -> SaveTo ( 'example.php.out' ) ;
	echo ( "Modifications saved to file example.php.out\n\n" ) ;

	// Run a comparison between the original version ('example.php.ini') and the modified one ('example.php.out')
	exec ( 'diff -Z example.php.ini example.php.out', $output ) ;
	echo ( "Comparison results :\n" ) ;
	echo ( "------------------\n" ) ;
	echo ( "\t" . implode ( "\n\t", $output ) ) ;

