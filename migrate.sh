#!/bin/bash

DB_FILE="db.sqlite"
OMIT=("functions.php" "upload.php" "config.php" "migrate.sh")
KEY="sadf"

for f in *
do
	if [[ ! "$f" == "db.sqlite" ]] && [[ ! ${OMIT[*]} =~ "$f" ]]; then
		hashedKey=$(php -r "echo(password_hash( '$KEY', PASSWORD_DEFAULT ));")
		sqlite3 $DB_FILE "INSERT INTO files (key, name) VALUES ('$hashedKey', '$f');"
	fi
done
