#!/usr/bin/env bash
set -e

MAGENTO_ADMIN_FIRSTNAME="Andy"
MAGENTO_ADMIN_LASTNAME="Parsons"
MAGENTO_ADMIN_EMAIL="andrew@sozodesign.co.uk"
MAGENTO_ADMIN_USER="prozoAndy"
MAGENTO_ADMIN_PASSWORD="Shannon.175"
MAGENTO_URL="mageee.localhost"
MAGENTO_BASE_URL="http://$MAGENTO_URL/"
MAGENTO_BASE_URL_SECURE="https://$MAGENTO_URL/"
MAGENTO_DB_HOST="/Applications/MAMP/tmp/mysql/mysql.sock"
MAGENTO_DB_NAME="mageee"
MAGENTO_DB_USER="root"
MAGENTO_DB_PASSWORD="root"
MAGENTO_BACKEND_FRONTNAME="sozoadmin"
MAGENTO_SESSION_SAVE="redis"
MAGENTO_PAGE_CACHE="redis"
MAGENTO_DISABLE_MODULES="Magento_AuthorizenetAcceptjs,Magento_AuthorizenetCardinal,Magento_AdminAnalytics,Magento_Analytics,Magento_QuoteAnalytics,Magento_PageBuilderAnalytics,Magento_CatalogAnalytics,Magento_CatalogPageBuilderAnalytics,Magento_WishlistAnalytics,Magento_SalesAnalytics,Magento_ReviewAnalytics,Magento_CustomerAnalytics,Magento_CatalogPageBuilderAnalyticsStaging,Magento_CmsPageBuilderAnalytics,Magento_CmsPageBuilderAnalyticsStaging,Magento_BannerPageBuilderAnalytics,Magento_BundleSampleData,Magento_CatalogRuleSampleData,Magento_CatalogSampleData,Magento_CmsSampleData,Magento_ConfigurableSampleData,Magento_CustomerSampleData,Magento_DownloadableSampleData,Magento_GiftCardSampleData,Magento_GiftRegistrySampleData,Magento_GroupedProductSampleData,Magento_MsrpSampleData,Magento_MultipleWishlistSampleData,Magento_OfflineShippingSampleData,Magento_ProductLinksSampleData,Magento_ReviewSampleData,Magento_SalesRuleSampleData,Magento_SalesSampleData,Magento_TargetRuleSampleData,Magento_TaxSampleData,Magento_ThemeSampleData,Magento_WidgetSampleData,Magento_WishlistSampleData,Magento_Tinymce3,Magento_Tinymce3Banner"

# Database server host
DB_HOST="${MAGENTO_DB_HOST:-127.0.0.1}"
# Database name
DB_NAME="${MAGENTO_DB_NAME:-magento2}"
# Database server username
DB_USER="${MAGENTO_DB_USER:-root}"
# Database server password
DB_PASSWORD="${MAGENTO_DB_PASSWORD:-root}"

# Database initial set of commands
DB_INIT_STATEMENTS="${MAGENTO_DB_INIT_STATEMENTS:-SET NAMES utf8;}"
# Database server engine
DB_ENGINE="${MAGENTO_DB_ENGINE:-innodb}"
# Database type
DB_MODEL="${MAGENTO_DB_MODEL:-mysql4}"
# Database table prefix
DB_PREFIX="${MAGENTO_DB_PREFIX:-}"
ENABLE_DEBUG_LOGGING="${MAGENTO_ENABLE_DEBUG_LOGGING:-true}"
ENABLE_SYSLOG_LOGGING="${MAGENTO_ENABLE_SYSLOG_LOGGING:-true}"
BACKEND_FRONTNAME="${MAGENTO_BACKEND_FRONTNAME:-sozoadmin}"

HTTP_CACHE_HOSTS="${MAGENTO_HTTP_CACHE_HOSTS:-}"
BASE_URL="${MAGENTO_BASE_URL:-http://localhost/}"
BASE_URL_SECURE="${MAGENTO_BASE_URL_SECURE:-https://localhost/}"
LANGUAGE="${MAGENTO_LANGUAGE:-en_GB}"
TIMEZONE="${MAGENTO_TIMEZONE:-Europe/London}"
CURRENCY="${MAGENTO_CURRENCY:-GBP}"
USE_REWRITES="${MAGENTO_USE_REWRITES:-1}"
USE_SECURE="${MAGENTO_USE_SECURE:-0}"
USE_SECURE_ADMIN="${MAGENTO_USE_SECURE_ADMIN:-0}"
ADMIN_USE_SECURITY_KEY="${MAGENTO_ADMIN_USE_SECURITY_KEY:-0}"
ADMIN_USER="${MAGENTO_ADMIN_USER:-prozoMAG}"
ADMIN_PASSWORD="${MAGENTO_ADMIN_PASSWORD:-localH0st}"
ADMIN_EMAIL="${MAGENTO_ADMIN_EMAIL:-magento@sozodesign.co.uk}"
ADMIN_FIRSTNAME="${MAGENTO_ADMIN_FIRSTNAME:-Sozo}"
ADMIN_LASTNAME="${MAGENTO_ADMIN_LASTNAME:-Admin}"
SALES_ORDER_INCREMENT_PREFIX="${MAGENTO_SALES_ORDER_INCREMENT_PREFIX:-Admin}"

# Session cache, [files,db,redis] (defaults to files)
SESSION_SAVE="${MAGENTO_SESSION_SAVE:-files}"
###
### if SESSION_SAVE == 'redis'
###
# Fully qualified hostname, IP address, or absolute path if using UNIX sockets
SESSION_SAVE_REDIS_HOST="${MAGENTO_SESSION_SAVE_REDIS_HOST:-127.0.0.1}"
# Redis server listen port
SESSION_SAVE_REDIS_PORT="${MAGENTO_SESSION_SAVE_REDIS_PORT:-6379}"
# Specifies a password if your Redis server requires authentication
SESSION_SAVE_REDIS_PASSWORD="${MAGENTO_SESSION_SAVE_REDIS_PASSWORD:-}"
# Connection timeout, in seconds
SESSION_SAVE_REDIS_TIMEOUT="${MAGENTO_SESSION_SAVE_REDIS_TIMEOUT:-2.5}"
# Unique string to enable persistent connections (for example, sess-db0)
SESSION_SAVE_REDIS_PERSISTENT_ID="${MAGENTO_SESSION_SAVE_REDIS_PERSISTENT_ID:-}"
# Unique Redis database number, which is recommended to protect against data loss
# Important: If you use Redis for more than one type of caching, the database numbers must be different
#            It is recommended that you assign the default caching database number to 0,
#            the page caching database number to 1, and the session storage database number to 2.
SESSION_SAVE_REDIS_DB="${MAGENTO_SESSION_SAVE_REDIS_DB_ID:-2}"
# Set to 0 to disable compression
SESSION_SAVE_REDIS_COMPRESSION_THRESHOLD="${MAGENTO_SESSION_SAVE_REDIS_COMPRESSION_THRESHOLD:-2048}"
# Compression library to use [snappy,lzf,l4z,zstd,gzip]  (leave blank to determine automatically)
SESSION_SAVE_REDIS_COMPRESSION_LIB="${MAGENTO_SESSION_SAVE_REDIS_COMPRESSION_LIB:-}"
# 0 (emergency) - 7 (debug)
SESSION_SAVE_REDIS_LOG_LEVEL="${MAGENTO_SESSION_SAVE_REDIS_LOG_LEVEL:-1}"
# Maximum number of processes that can wait for a lock on one session.
# For large production clusters, set this to at least 10% of the number of PHP processes
SESSION_SAVE_REDIS_MAX_CONCURRENCY="${MAGENTO_SESSION_SAVE_REDIS_MAX_CONCURRENCY:-6}"
# Number of seconds to wait before trying to break the lock for frontend (that is, storefront) session
SESSION_SAVE_REDIS_BREAK_AFTER_FRONTEND="${MAGENTO_SESSION_SAVE_REDIS_BREAK_AFTER_FRONTEND:-5}"
# Number of seconds to wait before trying to break the lock for an adminhtml (that is, Magento Admin) session
SESSION_SAVE_REDIS_BREAK_AFTER_ADMINHTML="${MAGENTO_SESSION_SAVE_REDIS_BREAK_AFTER_ADMINHTML:-30}"
# Lifetime, in seconds, of session for non-bots on the first write, or use 0 to disable
SESSION_SAVE_REDIS_FIRST_LIFETIME="${MAGENTO_SESSION_SAVE_REDIS_FIRST_LIFETIME:-600}"
# Lifetime, in seconds, of session for bots on the first write, or use 0 to disable
SESSION_SAVE_REDIS_BOT_FIRST_LIFETIME="${MAGENTO_SESSION_SAVE_REDIS_BOT_FIRST_LIFETIME:-60}"
# Lifetime, in seconds, of session for bots on subsequent writes, or use 0 to disable
SESSION_SAVE_REDIS_BOT_LIFETIME="${MAGENTO_SESSION_SAVE_REDIS_BOT_LIFETIME:-7200}"
# Disable session locking entirely
SESSION_SAVE_REDIS_DISABLE_LOCKING="${MAGENTO_SESSION_SAVE_REDIS_DISABLE_LOCKING:-0}"
# Minimum session lifetime, in seconds
SESSION_SAVE_REDIS_MIN_LIFETIME="${MAGENTO_SESSION_SAVE_REDIS_MIN_LIFETIME:-60}"
# Maximum session lifetime, in seconds
SESSION_SAVE_REDIS_MAX_LIFETIME="${MAGENTO_SESSION_SAVE_REDIS_MAX_LIFETIME:-2592000}"

# Redis Sentinel master name
SESSION_SAVE_REDIS_SENTINEL_MASTER="${MAGENTO_SESSION_SAVE_REDIS_SENTINEL_MASTER:-}"
# List of Redis Sentinel servers, comma separated
SESSION_SAVE_REDIS_SENTINEL_SERVERS="${MAGENTO_SESSION_SAVE_REDIS_SENTINEL_SERVERS:-}"
# Verify Redis Sentinel master status flag
SESSION_SAVE_REDIS_SENTINEL_VERIFY_MASTER="${MAGENTO_SESSION_SAVE_REDIS_SENTINEL_VERIFY_MASTER:-0}"
# Connection retries for sentinels
SESSION_SAVE_REDIS_SENTINEL_CONNECT_RETIRES="${MAGENTO_SESSION_SAVE_REDIS_SENTINEL_CONNECT_RETIRES:-5}"



# Redis page cache, set to blank to disable
CACHE_BACKEND="${MAGENTO_CACHE_BACKEND:-}"
# ID prefix for cache keys
CACHE_ID_PREFIX="${MAGENTO_CACHE_ID_PREFIX:-$DB_NAME}"
###
### if CACHE_BACKEND == 'redis'
###
# Fully qualified hostname, IP address, or an absolute path to a UNIX socket.
# The default value of 127.0.0.1 indicates Redis is installed on the Magento server.
CACHE_BACKEND_REDIS_SERVER="${MAGENTO_CACHE_BACKEND_REDIS_SERVER:-127.0.0.1}"
# Important: If you use Redis for more than one type of caching, the database numbers must be different
#            It is recommended that you assign the default caching database number to 0,
#            the page caching database number to 1, and the session storage database number to 2.
CACHE_BACKEND_REDIS_DB="${MAGENTO_CACHE_BACKEND_REDIS_DB:-0}"
# Redis server listen port
CACHE_BACKEND_REDIS_PORT="${MAGENTO_CACHE_BACKEND_REDIS_PORT:-6379}"
# Specifies a password if your Redis server requires authentication
CACHE_BACKEND_REDIS_PASSWORD="${MAGENTO_CACHE_BACKEND_REDIS_PASSWORD:-}"
# Set to 1 to compress the full page cache (use 0 to disable)
CACHE_BACKEND_REDIS_COMPRESS_DATA="${MAGENTO_CACHE_BACKEND_REDIS_COMPRESS_DATA:-1}"
# Compression library to use [snappy,lzf,l4z,zstd,gzip]  (leave blank to determine automatically)
CACHE_BACKEND_REDIS_COMPRESSION_LIB="${MAGENTO_CACHE_BACKEND_REDIS_COMPRESSION_LIB:-}"


# Redis page cache, set to blank to disable
PAGE_CACHE="${MAGENTO_PAGE_CACHE:-}"
# ID prefix for cache keys
PAGE_CACHE_ID_PREFIX="${MAGENTO_PAGE_CACHE_ID_PREFIX:-$DB_NAME}"
###
### if PAGE_CACHE == 'redis'
###
# Fully qualified hostname, IP address, or an absolute path to a UNIX socket.
# The default value of 127.0.0.1 indicates Redis is installed on the Magento server.
PAGE_CACHE_REDIS_SERVER="${MAGENTO_PAGE_CACHE_REDIS_SERVER:-127.0.0.1}"
# Required if you use Redis for both the default and full page cache
# Important: If you use Redis for more than one type of caching, the database numbers must be different
#            It is recommended that you assign the default caching database number to 0,
#            the page caching database number to 1, and the session storage database number to 2.
PAGE_CACHE_REDIS_DB="${MAGENTO_PAGE_CACHE_REDIS_DB:-1}"
# Redis server listen port
PAGE_CACHE_REDIS_PORT="${MAGENTO_PAGE_CACHE_REDIS_PORT:-6379}"
# Specifies a password if your Redis server requires authentication
PAGE_CACHE_REDIS_PASSWORD="${MAGENTO_PAGE_CACHE_REDIS_PASSWORD:-}"
# Set to 1 to compress the full page cache (use 0 to disable)
PAGE_CACHE_REDIS_COMPRESS_DATA="${MAGENTO_PAGE_CACHE_REDIS_COMPRESS_DATA:-1}"
# Compression library to use [snappy,lzf,l4z,zstd,gzip]  (leave blank to determine automatically)
PAGE_CACHE_REDIS_COMPRESSION_LIB="${MAGENTO_PAGE_CACHE_REDIS_COMPRESSION_LIB:-}"

# Amqp server host
AMQP_HOST="${MAGENTO_AMQP_HOST:-}"
# Amqp server port
AMQP_PORT="${MAGENTO_AMQP_PORT:-5672}"
# Amqp server username
AMQP_USER="${MAGENTO_AMQP_USER:-}"
# Amqp server password
AMQP_PASSWORD="${MAGENTO_AMQP_PASSWORD:-}"
# Amqp virtualhost
AMQP_VIRTUALHOST="${MAGENTO_AMQP_VIRTUALHOST:-/}"
# Amqp SSL
AMQP_SSL="${MAGENTO_AMQP_SSL:-}"
# Amqp SSL Options (JSON)
AMQP_SSL_OPTIONS="${MAGENTO_AMQP_SSL_OPTIONS:-}"

ENABLE_MODULES="${MAGENTO_ENABLE_MODULES:-}"
DISABLE_MODULES="${MAGENTO_DISABLE_MODULES:-}"
MAGENTO_INIT_PARAMS="${MAGENTO_INIT_PARAMS:-}"


function addParamIfDefined() {
    if [[ -n "$2" ]]
    then
        INSTALL_LINE="$INSTALL_LINE --$1='$2'"
    fi
}

function configSetDb() {
    if [[ -n "$1" ]]
    then
      echo "Setting '$1' as '${2:-}'"
      /usr/local/opt/php@7.2/bin/php -dmemory_limit=-1 bin/magento config:set "$1" "${2:-}"
    fi
}

function configSetLock() {
    if [[ -n "$1" ]]
    then
      echo "Setting '$1' as '${2:-}'"
      /usr/local/opt/php@7.2/bin/php -dmemory_limit=-1 bin/magento config:set --lock-config "$1" "${2:-}"
    fi
}

function configSetEnv() {
    if [[ -n "$1" ]]
    then
      echo "Setting '$1' as '${2:-}'"
      /usr/local/opt/php@7.2/bin/php -dmemory_limit=-1 bin/magento config:set --lock-env "$1" "${2:-}"
    fi
}



# php -f dev/tools/checkDbState.php "$DB_USER" "$DB_PASSWORD" "$DB_NAME" "$DB_HOST" || exit 1;

if [[ ! -f "./var/log/install.log" ]]
then
    INSTALL_LINE="/usr/local/opt/php@7.2/bin/php -dmemory_limit=-1 bin/magento setup:install"
    addParamIfDefined "enable-debug-logging" "$ENABLE_DEBUG_LOGGING"
    addParamIfDefined "enable-syslog-logging" "$ENABLE_SYSLOG_LOGGING"
    addParamIfDefined "backend-frontname" "$BACKEND_FRONTNAME"
    addParamIfDefined "db-host" "$DB_HOST"
    addParamIfDefined "db-name" "$DB_NAME"
    addParamIfDefined "db-user" "$DB_USER"
    addParamIfDefined "db-engine" "$DB_ENGINE"
    addParamIfDefined "db-password" "$DB_PASSWORD"
    addParamIfDefined "db-prefix" "$DB_PREFIX"
    addParamIfDefined "db-model" "$DB_MODEL"
    addParamIfDefined "db-init-statements" "$DB_INIT_STATEMENTS"
    addParamIfDefined "http-cache-hosts" "$HTTP_CACHE_HOSTS"
    addParamIfDefined "base-url" "$BASE_URL"
    addParamIfDefined "language" "$LANGUAGE"
    addParamIfDefined "timezone" "$TIMEZONE"
    addParamIfDefined "currency" "$CURRENCY"
    addParamIfDefined "use-rewrites" "$USE_REWRITES"
    addParamIfDefined "use-secure" "$USE_SECURE"
    addParamIfDefined "base-url-secure" "$BASE_URL_SECURE"
    addParamIfDefined "use-secure-admin" "$USE_SECURE_ADMIN"
    addParamIfDefined "admin-use-security-key" "$ADMIN_USE_SECURITY_KEY"
    addParamIfDefined "admin-user" "$ADMIN_USER"
    addParamIfDefined "admin-password" "$ADMIN_PASSWORD"
    addParamIfDefined "admin-email" "$ADMIN_EMAIL"
    addParamIfDefined "admin-firstname" "$ADMIN_FIRSTNAME"
    addParamIfDefined "admin-lastname" "$ADMIN_LASTNAME"
    addParamIfDefined "sales-order-increment-prefix" "$SALES_ORDER_INCREMENT_PREFIX"
    addParamIfDefined "session-save" "$SESSION_SAVE"

    if [[ "$SESSION_SAVE" == 'redis' ]]
    then
      addParamIfDefined "session-save-redis-host" "$SESSION_SAVE_REDIS_HOST"
      addParamIfDefined "session-save-redis-port" "$SESSION_SAVE_REDIS_PORT"
      addParamIfDefined "session-save-redis-password" "$SESSION_SAVE_REDIS_PASSWORD"
      addParamIfDefined "session-save-redis-timeout" "$SESSION_SAVE_REDIS_TIMEOUT"
      addParamIfDefined "session-save-redis-persistent-id" "$SESSION_SAVE_REDIS_PERSISTENT_ID"
      addParamIfDefined "session-save-redis-db" "$SESSION_SAVE_REDIS_DB"
      addParamIfDefined "session-save-redis-compression-threshold" "$SESSION_SAVE_REDIS_COMPRESSION_THRESHOLD"
      addParamIfDefined "session-save-redis-compression-lib" "$SESSION_SAVE_REDIS_COMPRESSION_LIB"
      addParamIfDefined "session-save-redis-log-level" "$SESSION_SAVE_REDIS_LOG_LEVEL"
      addParamIfDefined "session-save-redis-max-concurrency" "$SESSION_SAVE_REDIS_MAX_CONCURRENCY"
      addParamIfDefined "session-save-redis-break-after-frontend" "$SESSION_SAVE_REDIS_BREAK_AFTER_FRONTEND"
      addParamIfDefined "session-save-redis-break-after-adminhtml" "$SESSION_SAVE_REDIS_BREAK_AFTER_ADMINHTML"
      addParamIfDefined "session-save-redis-first-lifetime" "$SESSION_SAVE_REDIS_FIRST_LIFETIME"
      addParamIfDefined "session-save-redis-bot-first-lifetime" "$SESSION_SAVE_REDIS_BOT_FIRST_LIFETIME"
      addParamIfDefined "session-save-redis-bot-lifetime" "$SESSION_SAVE_REDIS_BOT_LIFETIME"
      addParamIfDefined "session-save-redis-disable-locking" "$SESSION_SAVE_REDIS_DISABLE_LOCKING"
      addParamIfDefined "session-save-redis-min-lifetime" "$SESSION_SAVE_REDIS_MIN_LIFETIME"
      addParamIfDefined "session-save-redis-max-lifetime" "$SESSION_SAVE_REDIS_MAX_LIFETIME"
      if [[ -n "$SESSION_SAVE_REDIS_SENTINEL_MASTER" ]]
      then
        addParamIfDefined "session-save-redis-sentinel-master" "$SESSION_SAVE_REDIS_SENTINEL_MASTER"
        addParamIfDefined "session-save-redis-sentinel-servers" "$SESSION_SAVE_REDIS_SENTINEL_SERVERS"
        addParamIfDefined "session-save-redis-sentinel-verify-master" "$SESSION_SAVE_REDIS_SENTINEL_VERIFY_MASTER"
        addParamIfDefined "session-save-redis-sentinel-connect-retires" "$SESSION_SAVE_REDIS_SENTINEL_CONNECT_RETIRES"
      fi
    fi

    if [[ "$CACHE_BACKEND" == 'redis' ]]
    then
      addParamIfDefined "cache-backend" "$CACHE_BACKEND"
      addParamIfDefined "cache-id-prefix" "$CACHE_ID_PREFIX"
      addParamIfDefined "cache-backend-redis-server" "$CACHE_BACKEND_REDIS_SERVER"
      addParamIfDefined "cache-backend-redis-db" "$CACHE_BACKEND_REDIS_DB"
      addParamIfDefined "cache-backend-redis-port" "$CACHE_BACKEND_REDIS_PORT"
      addParamIfDefined "cache-backend-redis-password" "$CACHE_BACKEND_REDIS_PASSWORD"
      addParamIfDefined "cache-backend-redis-compress-data" "$CACHE_BACKEND_REDIS_COMPRESS_DATA"
      addParamIfDefined "cache-backend-redis-compression-lib" "$CACHE_BACKEND_REDIS_COMPRESSION_LIB"
    fi

    if [[ "$PAGE_CACHE" == 'redis' ]]
    then
      addParamIfDefined "page-cache" "$PAGE_CACHE"
      addParamIfDefined "page-cache-id-prefix" "$PAGE_CACHE_ID_PREFIX"
      addParamIfDefined "page-cache-redis-server" "$PAGE_CACHE_REDIS_SERVER"
      addParamIfDefined "page-cache-redis-db" "$PAGE_CACHE_REDIS_DB"
      addParamIfDefined "page-cache-redis-port" "$PAGE_CACHE_REDIS_PORT"
      addParamIfDefined "page-cache-redis-password" "$PAGE_CACHE_REDIS_PASSWORD"
      addParamIfDefined "page-cache-redis-compress-data" "$PAGE_CACHE_REDIS_COMPRESS_DATA"
      addParamIfDefined "page-cache-redis-compression-lib" "$PAGE_CACHE_REDIS_COMPRESSION_LIB"
    fi

    if [[ -n "$AMQP_HOST" ]]
    then
      addParamIfDefined "amqp-host" "$AMQP_HOST"
      addParamIfDefined "amqp-port" "$AMQP_PORT"
      addParamIfDefined "amqp-user" "$AMQP_USER"
      addParamIfDefined "amqp-password" "$AMQP_PASSWORD"
      addParamIfDefined "amqp-virtualhost" "$AMQP_VIRTUALHOST"
      addParamIfDefined "amqp-ssl" "$AMQP_SSL"
      addParamIfDefined "amqp-ssl-options" "$AMQP_SSL_OPTIONS"
    fi

    addParamIfDefined "enable-modules" "$ENABLE_MODULES"
    addParamIfDefined "disable-modules" "$DISABLE_MODULES"
    addParamIfDefined "magento-init-params" "$MAGENTO_INIT_PARAMS"

    bash -c "$INSTALL_LINE"
    configSetEnv "admin/captcha/enable" "0"
    configSetLock "admin/security/admin_account_sharing" "1"
    configSetEnv "admin/security/lockout_failures" "0"
    configSetEnv "admin/security/password_lifetime" "0"
    configSetEnv "admin/security/lockout_threshold" "1"
    configSetEnv "admin/security/max_number_password_reset_requests" "0"
    configSetEnv "admin/security/min_time_between_password_reset_requests" "0"
    configSetEnv "admin/security/password_reset_protection_type" "3"
    configSetLock "admin/security/session_lifetime" "31536000"
    configSetLock "admin/security/use_case_sensitive_login" "0"
    configSetEnv "admin/security/use_form_key" "0"
    configSetLock "admin/security/use_form_key" "1"
    configSetDb "carriers/flatrate/active" "1"
    configSetDb "carriers/flatrate/showmethod" "1"
    configSetLock "carriers/flatrate/sort_order" "2"
    configSetDb "carriers/freeshipping/active" "1"
    configSetDb "carriers/freeshipping/showmethod" "1"
    configSetLock "carriers/freeshipping/sort_order" "1"
    configSetDb "carriers/tablerate/active" "1"
    configSetDb "carriers/tablerate/import" "1561619217,,,,4,0"
    configSetDb "carriers/tablerate/showmethod" "1"
    configSetLock "carriers/flatrate/sort_order" "3"
    configSetLock "catalog/custom_options/date_fields_order" "y,m,d"
    configSetLock "catalog/custom_options/use_calendar" "1"
    configSetLock "catalog/custom_options/year_range" ","
    configSetLock "catalog/downloadable/content_disposition" "inline"
    configSetLock "catalog/downloadable/shareable" "0"
    configSetLock "catalog/frontend/flat_catalog_product" "0"
    configSetLock "catalog/frontend/list_allow_all" "1"
    configSetLock "catalog/productalert/allow_price" "0"
    configSetLock "catalog/productalert_cron/frequency" "D"
    configSetLock "catalog/productalert_cron/time" "00,00,00"
    configSetEnv "catalog/search/elasticsearch6_enable_auth" "0"
    configSetEnv "catalog/search/elasticsearch6_index_prefix" "${DB_NAME}"
    configSetEnv "catalog/search/elasticsearch6_server_hostname" "localhost"
    configSetEnv "catalog/search/elasticsearch6_server_port" "9200"
    configSetEnv "catalog/search/elasticsearch6_server_timeout" "15"
    configSetLock "catalog/search/enable_eav_indexer" "1"
    configSetEnv "catalog/search/engine" "elasticsearch6"
    configSetLock "catalog/search/search_recommendations_enabled" "0"
    configSetLock "catalog/search/search_suggestion_enabled" "0"
    configSetLock "catalog/seo/category_canonical_tag" "1"
    configSetLock "catalog/seo/category_url_suffix" ""
    configSetLock "catalog/seo/product_canonical_tag" "1"
    configSetLock "catalog/seo/product_url_suffix" ""
    configSetEnv "catalog/seo/search_terms" "0"
    configSetLock "catalog/seo/title_separator" "|"
    configSetLock "cataloginventory/item_options/auto_return" "0"
    configSetLock "cataloginventory/options/show_out_of_stock" "1"
    configSetLock "currency/options/allow" "GBP"
    configSetLock "currency/options/base" "GBP"
    configSetLock "currency/options/default" "GBP"
    configSetLock "customer/address/company_show" "opt"
    configSetLock "customer/address/middlename_show" "0"
    configSetLock "customer/address/prefix_show" "opt"
    configSetLock "customer/address/taxvat_show" "opt"
    configSetLock "customer/address/telephone_show" "req"
    configSetLock "customer/captcha/enable" "0"
    configSetLock "customer/create_account/auto_group_assign" "0"
    configSetLock "customer/create_account/generate_human_friendly_id" "0"
    configSetLock "customer/create_account/viv_disable_auto_group_assign_default" "0"
    configSetLock "customer/online_customers/online_minutes_interval" "0"
    configSetLock "customer/online_customers/section_data_lifetime" "60"
    configSetEnv "customer/password/lockout_failures" "0"
    configSetEnv "customer/password/max_number_password_reset_requests" "0"
    configSetEnv "customer/password/min_time_between_password_reset_requests" "0"
    configSetEnv "customer/password/password_reset_protection_type" "3"
    configSetLock "design/head/includes" ""
    configSetEnv "dev/debug/template_hints_admin" "0"
    configSetEnv "dev/debug/template_hints_blocks" "0"
    configSetEnv "dev/debug/template_hints_storefront" "0"
    configSetEnv "dev/static/sign" "0"
    configSetEnv "dev/template/allow_symlink" "1"
    configSetEnv "dev/translate_inline/active" "0"
    configSetEnv "dev/translate_inline/active_admin" "0"
    configSetLock "general/country/allow" "GB,IE,IM,JE,GG"
    configSetLock "general/country/default" "GB"
    configSetLock "general/country/optional_zip_countries" "HK,IE,MO,PA"
    configSetLock "general/locale/code" "en_GB"
    configSetLock "general/locale/firstday" "1"
    configSetLock "general/locale/timezone" "Europe/London"
    configSetLock "general/locale/weight_unit" "kgs"
    configSetLock "general/region/display_all" "1"
    configSetLock "general/region/state_required" "US"
    configSetLock "general/country/destinations" "GB,US"
    configSetLock "general/single_store_mode/enabled" "0"
    configSetDb "payment/banktransfer/active" "1"
    configSetLock "payment/banktransfer/sort_order" "3"
    configSetDb "payment/cashondelivery/active" "1"
    configSetLock "payment/cashondelivery/sort_order" "2"
    configSetDb "payment/checkmo/active" "0"
    configSetLock "payment/checkmo/sort_order" "5"
    configSetDb "payment/free/active" "1"
    configSetLock "payment/free/sort_order" "1"
    configSetDb "payment/purchaseorder/active" "1"
    configSetLock "payment/purchaseorder/sort_order" "4"
    configSetEnv "reports/options/enabled" "0"
    configSetEnv "reports/options/product_compare_enabled" "0"
    configSetEnv "reports/options/product_send_enabled" "0"
    configSetEnv "reports/options/product_to_cart_enabled" "0"
    configSetEnv "reports/options/product_to_wishlist_enabled" "0"
    configSetEnv "reports/options/product_view_enabled" "0"
    configSetEnv "reports/options/wishlist_share_enabled" "0"
    configSetLock "sales/msrp/enabled" "0"
    configSetLock "shipping/origin/country_id" "GB"
    configSetLock "shipping/shipping_policy/enable_shipping_policy" "0"
    configSetEnv "system/backup/functionality_enabled" "0"
    configSetLock "system/bulk/lifetime" "60"
    configSetLock "system/currency/installed" "GBP"
    configSetLock "system/smtp/set_return_path" "0"
    configSetLock "system/upload_configuration/max_height" "2400"
    configSetLock "system/upload_configuration/max_width" "3840"
    configSetLock "tax/calculation/based_on" "origin"
    configSetLock "tax/calculation/cross_border_trade_enabled" "0"
    configSetLock "tax/defaults/country" "GB"
    configSetEnv "web/browser_capabilities/cookies" "0"
    configSetEnv "web/browser_capabilities/javascript" "0"
    configSetEnv "web/browser_capabilities/local_storage" "0"
    configSetLock "web/cookie/cookie_httponly" "1"
    configSetLock "web/cookie/cookie_path" "/"
    configSetEnv "web/cookie/cookie_domain" "$MAGENTO_URL"
    configSetLock "web/default_layouts/default_cms_layout" "1column"
    configSetEnv "web/secure/use_in_adminhtml" "0"
    configSetLock "web/secure/use_in_adminhtml" "1"
    configSetEnv "web/secure/use_in_frontend" "0"
    configSetLock "web/secure/use_in_frontend" "1"
    configSetLock "web/seo/use_rewrites" "1"
    configSetLock "web/url/use_store" "0"
    configSetLock "web/url/redirect_to_base" "1"
    configSetLock "web/secure/base_static_url" "{{unsecure_base_url}}pub/static/"
    configSetLock "web/secure/base_media_url" "{{unsecure_base_url}}pub/media/"
    configSetLock "web/unsecure/base_static_url" "{{unsecure_base_url}}pub/static/"
    configSetLock "web/unsecure/base_media_url" "{{unsecure_base_url}}pub/media/"
    configSetEnv "web/unsecure/base_url" "$BASE_URL"
    configSetEnv "web/secure/base_url" "$BASE_URL_SECURE"

    bash -c "/usr/local/opt/php@7.2/bin/php -dmemory_limit=-1 bin/magento app:config:import"
    bash -c "npm run refresh"
    bash -c "open $BASE_URL"
    bash -c "open $BASE_URL$BACKEND_FRONTNAME"
    echo "$(date)" > "./var/log/install.log"
else
  echo "var/log/install.log flag already set"
fi
