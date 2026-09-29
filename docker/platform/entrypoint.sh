#!/bin/sh
# Cek apakah folder vendor kosong
if [ ! -d "vendor" ] || [ -z "$(ls -A vendor)" ]; then
    echo "Folder vendor kosong. Menjalankan composer install..."
    composer install --no-interaction
fi

# Lanjutkan menjalankan perintah utama container
exec "$@"