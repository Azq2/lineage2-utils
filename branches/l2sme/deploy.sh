#!/bin/bash
cd $(dirname $0)

rev0=($(md5sum www/static/css/main.css))
rev1=($(md5sum www/static/js/functions.js))
revision="$rev0-$rev1"
revision=($(echo "$revision" | md5sum))

rsync -rpCv --checksum --delete "www/" "$1/" --exclude 'tmp/*' --exclude 'files/*'
rsync -rpCv --checksum  "www/files/" "$1/files/"
tar -C "$1/files" -xf "$1/files/files.tar.gz"

# костылище :D
sed -i "s/const STATIC_REVISOIN = [^;]*;/const STATIC_REVISOIN = '$revision';/g" "$1/index.php"

chmod 0777 "$1/tmp"
