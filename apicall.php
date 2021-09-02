<?php 
	
STORE_URL='https://my-brand.be'
ENDPOINT='/wc-auth/v1/authorize'
PARAMS="app_name=o3&scope=read_write&user_id=27&return_url=https://platform.crimson.dev&callback_url=https://platform.crimson.dev"
QUERY_STRING="$(perl -MURI::Escape -e 'print uri_escape($ARGV[0]);' "$PARAMS")"
QUERY_STRING=$(echo $QUERY_STRING | sed -e "s/%20/\+/g" -e "s/%3D/\=/g" -e "s/%26/\&/g")

        echo "$STORE_URL$ENDPOINT?$QUERY_STRING"

?>