Installer følgende pakker:
php-ldap php-xml libapache2-mod-php

Sette opp LDAPS:
Lag sertifikat med Domain Controller Authentication og legg inn på domenekontroller
Lag mappe under /usr/local/share/ca-certificates og plasser alle sertifikater i kjeden der.
Sertifikatene skal være base64 kodet og ha navn som slutter på .crt
Når sertifikatene er lagt inn kjøres update-ca-certificates
Referanse: https://www.brightbox.com/blog/2014/03/04/add-cacert-ubuntu-debian/

Sett opp cronjob for caching av data fra stamdata3:
15 5 * * * php /var/www/html/skolepassord/pifu-php/build_cache.php>/dev/null

Oppdatere til ny versjon:
git pull
git submodule update