#!/bin/bash

# Minta input nama file .htaccess lokal
read -p "Masukkan path file .htaccess yang akan digunakan: " HTACCESS_FILE

# Cek apakah file ada
if [[ ! -f "$HTACCESS_FILE" ]]; then
  echo "[!] File tidak ditemukan: $HTACCESS_FILE"
  exit 1
fi

# Minta input direktori target
read -p "Masukkan daftar direktori utama (pisahkan dengan spasi): " -a TARGET_DIRS

# Loop semua direktori
for base in "${TARGET_DIRS[@]}"; do
  if [[ -d "$base" ]]; then
    echo "[*] Menyebar .htaccess ke semua subfolder dalam: $base"

    # Temukan semua folder termasuk dirinya sendiri
    find "$base" -type d | while read subdir; do
      cp "$HTACCESS_FILE" "$subdir/.htaccess"
      echo "  └─ Ditulis ke: $subdir/.htaccess"
    done
  else
    echo "[!] Direktori tidak ditemukan: $base"
  fi
done
