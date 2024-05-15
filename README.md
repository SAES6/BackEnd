# Installer la base de donnée

## Avec Docker

```bash
docker run --name mysql -e MYSQL_USER=sae -e MYSQL_PASSWORD=password -e MYSQL_ROOT_PASSWORD=my-secret-pw -p 3306:3306 -d mysql:latest
```

Donc le mot de passe pour root est "my-secret-pw"

```bash
docker run --name phpmyadmin -d -e UPLOAD_LIMIT=300M --link mysql:db -p 8080:80 phpmyadmin
```

Une fois sur votre phpMyAdmin, connectez-vous avec root et "my-secret-pw" et créez la base de donnée "sae-s6".

# Modifier le fichier .env du backend

Changez le fichier .env du backend comme ceci :

```bash

APP_NAME=SAE-S6-Backend
APP_ENV=local
APP_KEY=base64:ngzf8RyEp57ZXA+stFyqWratpRXjlHckhHliHi9M0Ew=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost

APP_LOCALE=fr
APP_FALLBACK_LOCALE=fr
APP_FAKER_LOCALE=fr_FR

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sae-s6
DB_USERNAME=root
DB_PASSWORD=my-secret-pw

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=reverb
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"

REVERB_APP_ID=959255
REVERB_APP_KEY=mh2dkkpfg8wcxwqkr2kf
REVERB_APP_SECRET=ekvvxmbhymzkz5ub34cm
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

```
