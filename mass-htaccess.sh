#!/bin/bash

# URL RAW GitHub yang berisi .htaccess
HTACCESS_URL="https://raw.githubusercontent.com/username/repo/branch/path/to/.htaccess"

# Minta input dari user
read -p "Masukkan directory yang mau di-htaccess (pisahkan dengan spasi): " -a TARGET_DIRS

# Loop tiap folder yang diinput
for dir in "${TARGET_DIRS[@]}"; do
  if [[ -d "$dir" ]]; then
    echo "[*] Menyimpan .htaccess ke: $dir"

    curl -sSL "$HTACCESS_URL" -o "${dir}/.htaccess"

    if [[ -f "${dir}/.htaccess" ]]; then
      echo "[✓] Sukses: ${dir}/.htaccess"
    else
      echo "[x] Gagal menyimpan: ${dir}/.htaccess"
    fi
  else
    echo "[!] Folder tidak ditemukan: $dir"
  fi
done
