<?php
//change these, they should behave well with http GETs
$KEYS = ['asdf', '1234'];

//you should probably restrict access to this
//api keys are hashed using a password hash but someone could use this to see all files
$DB_NAME = 'db.sqlite';

//length of randomly generated files
$FILE_NAME_LENGTH = 5;

//example: https://example.com/i/
//example: https://i.example.com
$FILE_LOCATION = 'https://example.com/i/';
//name of file which will be used for uploading/deleting
$SCRIPT_NAME = 'upload.php';

//name of _FILES arg
$FILE_ARG = 'sharex';
//arg to be used for deletion file name
$DELETE_ARG = 'delete';
//arg where key is passed
$KEY_ARG = 'secret';

//mime types where file name should be randomized
$RANDOMIZE_FILE_TYPES = ['image'];

//allowed file extensions
$ALLOWED_FILE_TYPES = ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'txt'];

//files which cannot be overwritten
$PROTECTED_FILES = ['upload.php', 'db.sqlite', 'functions.php', 'config.php', 'migrate.sh'];
