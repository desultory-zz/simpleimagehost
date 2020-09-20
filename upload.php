<?php
include 'config.php';
include 'functions.php';

//Check if a key is set
if( isset( $_POST[$KEY_ARG] )) {
	$secret = $_POST[$KEY_ARG];
} elseif( isset( $_GET[$KEY_ARG] )) {
	$secret = $_GET[$KEY_ARG];
} else {
	echo("Key not set");
	exit(1);
}

//Check if delete flag is set
if( isset( $_POST[$DELETE_ARG] )) {
	$delete = $_POST[$DELETE_ARG];
} elseif( isset( $_GET[$DELETE_ARG] )) {
	$delete = $_GET[$DELETE_ARG];
}

//Check if key is in array
if( !in_array( $secret, $KEYS )) {
	echo("Unauthorized key");
	exit(1);
} else {
	$authorized = true;
}

//Check if file is passed
if( !isset( $_FILES[$FILE_ARG] ) && !isset( $delete )) {
	echo("No file set");
	exit(1);
} elseif( isset( $_FILES[$FILE_ARG] )) {
	$file = $_FILES[$FILE_ARG];
}

if( $authorized ) {
	//create DB
	if( !file_exists( 'db.sqlite' )) {
		initdb();
	}

	//Handle deletion
	if( isset( $delete )) {
		deletefile( $secret, $delete );
		exit(0);
	}

	if( $fileLocation = addFile( $secret, $file )) {
		$response = craftResponse( $secret, $fileLocation );
		echo($response);
	} else {
		echo("Unable to upload");
		exit(1);
	}
}
