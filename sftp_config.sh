#!/bin/sh

# Aggiungi l'utente e imposta la password
echo "$SFTP_USERS" | while IFS=':' read -r username password uid gid dir; do
    addgroup -g "$gid" "$username"
    adduser -h "/home/$username" -u "$uid" -G "$username" -s /bin/sh -D "$username"
    echo "$username:$password" | chpasswd

    # Crea la directory di upload e imposta i permessi corretti
    mkdir -p "/home/$username/$dir"
    chown "$username:$username" "/home/$username/$dir"
    chmod 777 "/home/$username/$dir"

    # Crea la cartella .ssh se non esiste
    mkdir -p /home/$username/.ssh
    chmod 700 /home/$username/.ssh
    chown -R "$username:$username" /home/$username/.ssh

    # Copia la chiave pubblica SSH all'interno del file authorized_keys
    if [ -f "/home/$username/.ssh/ftps.pub" ]; then
        cat "/home/$username/.ssh/ftps.pub" >> "/home/$username/.ssh/authorized_keys"
        chmod 600 "/home/$username/.ssh/authorized_keys"
        chown "$username:$username" "/home/$username/.ssh/authorized_keys"
    else
        echo "Chiave pubblica non trovata: /home/$username/.ssh/ftps.pub"
    fi
done

# Avvia il server SFTP
exec /usr/sbin/sshd -D -e
