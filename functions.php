<?php

function initdb() {
	include 'config.php';

        $db = new PDO( 'sqlite:' . $DB_NAME );
        $dbcmds = ['CREATE TABLE IF NOT EXISTS files(
                key TEXT NOT NULL,
                name TEXT NOT NULL
                )',
        ];
        foreach ( $dbcmds as $cmd ) {
                $db->exec( $cmd );
        }

        $clean = 1;

        foreach ( $db->errorInfo() as $error ) {
                if ($error != 0) {
                        $clean = $error;
                }
        }

        return $clean;
}

function randomName( $length ) {
	$VALID_CHARS = array_merge( range( 0, 9 ), range( 'a', 'z' ), range( 'A', 'Z' ));

	$buffer = '';

	for( $i = 0; $i < $length; $i++ ) {
		$buffer .= $VALID_CHARS[mt_rand( 0, count( $VALID_CHARS ) - 1 )];
	}

	$name = $buffer;
	return $name;
}

function cleanString( $input ) {
	$output = preg_replace("/[^A-Za-z0-9.]/", '_', $input);
	return $output;
}
function passHash( $input ) {
	$output = password_hash( $input, PASSWORD_DEFAULT );
	return $output;
}

function getFiles() {
	include 'config.php';

	$db = new PDO('sqlite:' . $DB_NAME);
	$query = $db->prepare( 'SELECT name FROM files' );
	$query->execute();
	$result = $query->fetchAll(PDO::FETCH_COLUMN, 0);
	return $result;
}

//takes file name as input and returns owner
function getFileOwner( $file ) {
	include 'config.php';

	$db = new PDO('sqlite:' . $DB_NAME);
	$query = $db->prepare( 'SELECT key FROM files WHERE name=:file' );
	$query->bindValue(':file', $file, PDO::PARAM_STR);
	$query->execute();
	$result = $query->fetch(PDO::FETCH_ASSOC);
	return $result;
}

//owner is secret key
//file is _FILE object
function addFile( $owner, $file ) {
	if( $file['error'] === 1 ) {
		echo("File too large");
		exit(1);
	}

	include 'config.php';

	$files = getFiles();
	$fileLocation = $file['tmp_name'];
	$fileExtension = pathinfo( $file['name'], PATHINFO_EXTENSION );
	$fileExtension = strtolower( $fileExtension );
	$fileType = $file['type'];
	$destFile = '';

	//Check if file is allowed type
	if( !in_array( $fileExtension, $ALLOWED_FILE_TYPES )) {
		echo("Disallowed file type");
		exit(1);
	}

	//Check if file name should be randomized
	foreach( $RANDOMIZE_FILE_TYPES as $type ) {
		if( strpos( $fileType, $type ) !== false ) {
			$randomize = true;
		}
	}

	if( $randomize ) {
		//Ensure file by that name doesn't already exist
		do {
			$destName = randomName( $FILE_NAME_LENGTH );
			$destFile = $destName . '.' . $fileExtension;
		}
		while( in_array( $destFile, $files ));
	} else {
		$destFile = cleanString( $file['name'] );
		if( in_array( $destFile, $files )) {
			echo("A file by that name already exists");
			exit(1);
		}
	}

	//Check if file is protected
	if( in_array( $destFile, $PROTECTED_FILES )) {
		echo("File blacklisted");
		exit(1);
	}

	//exit if unable to move file
	if( !move_uploaded_file( $fileLocation, $destFile )) {
		echo("Unable to move file, please check permissions");
		exit(1);
	}

	//add file to database
	$hashedOwner = passHash( $owner );
	$db = new PDO( 'sqlite:' . $DB_NAME );
	$query = $db->prepare( 'INSERT INTO files (key, name) VALUES (:owner, :file)' );
	$query->bindValue( ':owner', $hashedOwner, PDO::PARAM_STR );
	$query->bindValue( ':file', $destFile, PDO::PARAM_STR );
	$query->execute();

	return $destFile;
}

//owner is secret key
//file is file name
function deleteFile( $owner, $file ) {
	include 'config.php';

	//Check if file is protected
	if( in_array( $file, $PROTECTED_FILES )) {
		echo("File blacklisted");
		exit(1);
	}

	$files = getFiles();
	//check that the file is in the database
	if( !in_array( $file, $files )) {
		echo("File does not exist in db");
		exit(1);
	}

	//checks that the correct owner is attempting to delete the file
	$fileOwner = getFileOwner( $file );
	$fileOwner = $fileOwner['key'];
	if( !password_verify( $owner, $fileOwner )) {
		echo("You cannot delete files you did not upload");
		exit(1);
	}

	//delete file
	if( !unlink( $file )) {
		echo("Unable to delete file");
		exit(1);
	}

	//remove file from database
	$db = new PDO( 'sqlite:' . $DB_NAME );
	$query = $db->prepare( 'DELETE FROM files WHERE name=:file' );
	$query->bindValue( ':file', $file, PDO::PARAM_STR );
	$query->execute();
	echo("Deleted file $file from database");
}

//owner is secret key
//fileName is the name of the new file
function craftResponse( $owner, $fileName ) {
	include 'config.php';

	$link = $FILE_LOCATION . $fileName;
	$deleteLink = $FILE_LOCATION . $SCRIPT_NAME . '?' . $KEY_ARG . '=' . $owner . '&' . $DELETE_ARG . '=' . $fileName;
	$response = array(
		'data' => array(
			'link' => $link,
			'delete' => $deleteLink
		)
	);
	$response = json_encode( $response );
	return $response;
}
