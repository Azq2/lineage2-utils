#!/bin/bash
cd $(dirname $0)

mkdir -p "$1"

revision=($(find www/static/js www/static/css -iname '*.js' -o -iname '*.css' -exec cat {} \; | md5sum))

rsync -rpCv --checksum --delete "./" "$1/" --exclude 'www/tmp/*' --exclude 'www/files/*'
rsync -rpCv --checksum --delete "./core/" "$1/core/"
rsync -rpCv --checksum  "www/files/" "$1/www/files/"
tar -C "$1/www/files" -xf "$1/www/files/files.tar.gz"

# костылище :D
tmp_dir="H.'../www/tmp/'"
tmp_dir=${tmp_dir//\//\\\/}
sed -i "s/const STATIC_REVISOIN = [^;]*;/const STATIC_REVISOIN = '$revision';/g" "$1/www/index.php"
sed -i "s/'L2_TMP_DIR', \"[^\"]*\"/'L2_TMP_DIR', $tmp_dir/g" "$1/www/index.php"

echo "TPL..."
find $1/www/templates -iname '*.xhtml.php' -exec perl -i -p -e 's/\s+/ /mig' {} \;
echo "JS..."
find $1/www/static -iname '*.js' -exec uglifyjs {} -o {} -c -m \;
echo "CSS..."
find $1/www/static -iname '*.css' -exec csso -i {} -o {} \;

mkdir "$1/www/tmp"
chmod 0777 "$1/www/tmp"
