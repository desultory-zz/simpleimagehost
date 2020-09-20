Edit config parameters in config.php

**$KEYS** and **$FILE_LOCATION** must be changed  
**$ALLOWED_FILE_TYPES** and **$PROTECTED_FILES** should be changed accordinglly    

**Response type** in sharex will be "**Response text**"  
**Destination type** in sharex will be "**POST**"  
**URL** in sharex will be "**$json:data.link$**"  
**Deletion URL** in sharex will be "**$json:data.delete$**"  
**Request** URL in sharex will be "**https://example.com/i/upload.php**"  
arguments in sharex will be "**name**" = secret and "**value**" = key you set  


You can change the "**$KEY**" variable in migrate.sh to a key you use and it will add all files other than the database file and OMIT array to your database, this is useful if you are replacing an old script.  
You can check the database by running "**sqlite3 db.sqlite**"  then "**SELECT * FROM files;**" and quitting with "**.q**"
