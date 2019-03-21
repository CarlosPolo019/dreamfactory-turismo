

##------------------------------------------------------------------------------pplication Settings
##------------------------------------------------------------------------------

APP_ENV=prod
APP_DEBUG=FALSE
## Use 'php artisan key:generate' to generate a new key. Key size must be 16, 24 or 32.
APP_KEY=base64:7D4LH27r8+kdzm8dr+WnXDGq4Lt/7ocV0NjzRJs6aAk=
APP_LOG=single

##------------------------------------------------------------------------------
## Database Settings
##------------------------------------------------------------------------------

DB_DRIVER=mysql
DB_HOST=airlinku-prod-cluster.cluster-c22kxeq3weos.us-east-1.rds.amazonaws.com
DB_DATABASE=dreamfactory
DB_USERNAME=dreamfactory
DB_PASSWORD=GfGUBHSh8DGkYqq
DB_PORT=3306

##------------------------------------------------------------------------------
## Cache and Session Settings
##------------------------------------------------------------------------------

CACHE_DRIVER=file
SESSION_DRIVER=array
MAIL_DRIVER=mail

##------------------------------------------------------------------------------
## Redis cache settings.  Uncomment if the CACHE_DRIVER is changed to redis and
## the default values are not appropriate
##------------------------------------------------------------------------------

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DATABASE=0
#REDIS_PASSWORD=

##------------------------------------------------------------------------------
## Memcached cache settings.  Uncomment if the CACHE_DRIVER is changed to memcached
## and the default values are not appropriate
##------------------------------------------------------------------------------

#MEMCACHED_HOST=127.0.0.1
#MEMCACHED_PORT=11211
#MEMCACHED_WEIGHT=100

##------------------------------------------------------------------------------
## DreamFactory Settings
##------------------------------------------------------------------------------

## LOG Level. This is hierarchical and goes in the following order.
## DEBUG -> INFO -> NOTICE -> WARNING -> ERROR -> CRITICAL -> ALERT -> EMERGENCY
## If you set log level to WARNING then all WARNING, ERROR, CRITICAL, ALERT, and EMERGENCY
## will be logged. Setting log level to DEBUG will log everything. Default is WARNING.
DF_LOG_LEVEL=WARNING

## Setup, see config/df.php for details and defaults
DF_INSTALL=GitHub

## If you are using an older installation of DreamFactory 2 and getting following error
## 'No supported encrypter found. The cipher and / or key length are invalid' then please
## uncomment the following line to use rijndael-128 cipher. Using this cipher requires the
## php mcrypt extension to be installed.
##DF_CIPHER=rijndael-128

##DF_INSTANCE_NAME=example.com
##DF_LOCAL_FILE_ROOT=app
##DF_LANDING_PAGE=/dreamfactory/dist/index.html
##DF_CACHE_TTL=300
DF_ALLOW_FOREVER_SESSIONS=true
##DF_DB_MAX_RECORDS_RETURNED=1000
##DF_SQLITE_STORAGE=/opt/sqlite/databases
##DF_SCRIPTING_DISABLE=
##DF_NODEJS_PATH=/usr/local/bin/node
##DF_CACHE_PATH=/tmp/.df-cache

## FreeTDS configuration (Linux and OS X only), see config/df.php
##DF_FREETDS_SQLSRV=/usr/share/freetds/freetds.conf
##DF_FREETDS_SQLANYWHERE=/usr/share/freetds/freetds.conf
##DF_FREETDS_DUMP=/tmp/freetds.log
##DF_FREETDS_DUMPCONFIG=/tmp/freetdsconfig.log

## Session management, see config/jwt.php
##JWT_SECRET=
DF_JWT_TTL=525600
##DF_JWT_REFRESH_TTL=20160

##------------------------------------------------------------------------------
## Managed Settings
##------------------------------------------------------------------------------

##DF_MANAGED=false
##DF_MANAGED_LOG_PATH=/data/logs/instance
##DF_MANAGED_CACHE_PATH=/tmp/.df-cache
##DF_MANAGED_LOG_ROTATE_COUNT=5
##DF_MANAGED_LOG_FILE_NAME=
##DF_LIMITS_CACHE_STORE=dfe-limits
##DF_LIMITS_CACHE_PATH=/tmp/.df-cache/.limits



FIREBASE_AUTH_DOMAIN=airlinku-1e710.firebaseapp.com
FIREBASE_DATABASE_URL=https://airlinku-1e710.firebaseio.com
FIREBASE_STORAGE_BUCKET=airlinku-1e710.appspot.com
FIREBASE_SECRET=sJSALxYV0YIriVZmG40RufEAq1PCW7ugj8MyARxM
FIREBASE_API_KEY=AIzaSyC5PJb9pDa05jY3WpiVmZx5txTcHVYtg0w
