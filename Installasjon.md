# Installasjon #
Løsningen er utviklet og testet med apache på debian linux, så instruksjonene er tilpasset for det.

Løsningen bør kunne kjøres på alle plattformer der man får installert PHP med modulene for ldap og xml. 

## Klargjør serveren: ##
**Installer nødvendige pakker:**

    apt-get install git php-ldap php-xml libapache2-mod-php

**Gå til apaches rotmappe:**

    cd /var/www/html

**Hent scriptet fra github:**

    git clone --recursive https://github.com/datagutten/skolepassord.git

**Sett opp LDAPS på domenekontroller:**

Lag sertifikat med Domain Controller Authentication og legg inn på domenekontroller
Lag mappe under /usr/local/share/ca-certificates og plasser alle sertifikater i kjeden der.
Sertifikatene skal være base64 kodet og ha navn som slutter på .crt
Når sertifikatene er lagt inn kjøres update-ca-certificates

Referanse: [https://www.brightbox.com/blog/2014/03/04/add-cacert-ubuntu-debian/](https://www.brightbox.com/blog/2014/03/04/add-cacert-ubuntu-debian/)


**Kopiere inn PIFU fil fra skolesystem**
I undermappen pifu-php skal det ligge en PIFU XML fil med navnet pifuData.xml

Det anbefales å sette opp en cronjob som oppdaterer denne automatisk med ønsket mellomrom.

Ettersom PIFU filen er såpass stor er det nødvendig å generere mindre filer for de enkelte skoler og klasser.
Dette gjøres av en cronjob som settes opp ved hjelp av kommandoen

    crontab -e

Lim inn følgende i editoren som åpnes:

    15 5 * * * php /var/www/html/skolepassord/pifu-php/build_cache.php>/dev/null
I eksempelet over er den satt til å gå 05:15 hver dag, men tidspunktet må tilpasses når PIFU filen hentes så genereringen skjer etter at filen er hentet.

Generer filene manuelt første gang og se at det ikke kommer noen feilmeldinger:

    php /var/www/html/skolepassord/pifu-php/build_cache.php

## Tilpass konfigurasjon: ##
**Mail (SMTP):**

Kopier *config\_mail\_sample.php* til *config\_mail.php* og tilpass med opplysninger for aktuell SMTP server.

Se dokumentasjon for PHPMailer for hvilke parametere som er mulig å bruke.

**LDAP (AD)**

Kopier domains_sample.php til domains.php og fyll inn opplysninger for ditt domene. Løsningen har støtte for at lærere og elever er i separate domener, er de i samme domene kan samme opplysninger fylles inn for begge.

**Tilgangsstyring**

For å styre hvem som får tilgang til hvilken skole må det lages en knytning mellom ID for skolen i IST og gruppe i AD.
Eksempel på dette finnes i school_groups_sample.php, kopier den til school_groups.php og gjør endringer der.

## Oppdatere til ny versjon: ##

    git pull
    git submodule update