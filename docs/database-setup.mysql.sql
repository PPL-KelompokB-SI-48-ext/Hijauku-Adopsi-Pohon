# Setup MySQL lokal Hijauku
# Jalankan sekali dengan akun MySQL yang boleh membuat database dan user.
# Ganti password contoh ini sebelum dipakai. Jangan commit password lokal.

CREATE DATABASE hijauku_dev
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE USER 'hijauku_dev'@'127.0.0.1'
    IDENTIFIED BY 'ganti-password-lokal-ini';

GRANT ALL PRIVILEGES ON hijauku_dev.* TO 'hijauku_dev'@'127.0.0.1';
FLUSH PRIVILEGES;

# Isi .env lokal dengan:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=hijauku_dev
# DB_USERNAME=hijauku_dev
# DB_PASSWORD=password-lokal-yang-anda-pilih
