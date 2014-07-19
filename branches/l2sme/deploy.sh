#!/bin/bash
cd $(dirname $0)
rsync -rpCv --checksum --delete "www/" "$1/" --exclude 'tmp/*' --exclude 'files/*'
rsync -rpCv --checksum  "www/files/" "$1/files/"
tar -C "$1/files" -xf "$1/files/files.tar.gz"
chmod 0777 "$1/tmp"
