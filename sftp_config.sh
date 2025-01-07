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
done

# Avvia il server SFTP
exec /usr/sbin/sshd -D -e