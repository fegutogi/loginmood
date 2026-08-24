#!/bin/zsh

set -euo pipefail

required_variables=(
	LOGINMOOD_LOCAL_SITE_PATH
	LOGINMOOD_LOCAL_MYSQL_SOCKET
	LOGINMOOD_LOCAL_PHP
	LOGINMOOD_LOCAL_MYSQL
	LOGINMOOD_LOCAL_MYSQLDUMP
)

for variable_name in "${required_variables[@]}"; do
	if [[ -z "${(P)variable_name:-}" ]]; then
		print -u2 "Missing required environment variable: $variable_name"
		exit 1
	fi
done

site_path="$LOGINMOOD_LOCAL_SITE_PATH"
mysql_socket="$LOGINMOOD_LOCAL_MYSQL_SOCKET"
php_runtime="$LOGINMOOD_LOCAL_PHP"
mysql_bin="$LOGINMOOD_LOCAL_MYSQL"
mysqldump_bin="$LOGINMOOD_LOCAL_MYSQLDUMP"
mysql_user="${LOGINMOOD_LOCAL_DB_USER:-root}"
export MYSQL_PWD="${LOGINMOOD_LOCAL_DB_PASSWORD:-root}"
source_db="${LOGINMOOD_LOCAL_DB:-local}"
compat_db='loginmood_compat_wordfence_rc3'
compat_url='http://127.0.0.1:9490'
clone_root="$(mktemp -d /tmp/loginmood-wordfence-clone.XXXXXX)"
clone_public="$clone_root/public"
wp_cli="$clone_root/wp-cli.phar"
server_pid=''

cleanup_wordfence_clone() {
	if [[ -n "$server_pid" ]]; then
		kill "$server_pid" >/dev/null 2>&1 || true
	fi
	"$mysql_bin" --protocol=socket --socket="$mysql_socket" -u"$mysql_user" -e "DROP DATABASE IF EXISTS $compat_db;" >/dev/null 2>&1 || true
	case "$clone_root" in
		/tmp/loginmood-wordfence-clone.*) rm -rf -- "$clone_root" ;;
	esac
}

trap cleanup_wordfence_clone EXIT INT TERM

for required_path in "$site_path" "$mysql_socket" "$php_runtime" "$mysql_bin" "$mysqldump_bin"; do
	if [[ ! -e "$required_path" ]]; then
		print -u2 "Missing LocalWP dependency: $required_path"
		exit 1
	fi
done

mkdir -p "$clone_public"
rsync -a "$site_path/" "$clone_public/"
curl -fsSL 'https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar' -o "$wp_cli"

"$php_runtime" "$wp_cli" --path="$clone_public" config set DB_NAME "$compat_db" --type=constant >/dev/null
"$php_runtime" "$wp_cli" --path="$clone_public" config set DISABLE_WP_CRON true --raw >/dev/null
"$mysql_bin" --protocol=socket --socket="$mysql_socket" -u"$mysql_user" -e "DROP DATABASE IF EXISTS $compat_db; CREATE DATABASE $compat_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
"$mysqldump_bin" --protocol=socket --socket="$mysql_socket" -u"$mysql_user" --single-transaction --skip-lock-tables "$source_db" | "$mysql_bin" --protocol=socket --socket="$mysql_socket" -u"$mysql_user" "$compat_db"

wp_clone() {
	"$php_runtime" -d "mysql.default_socket=$mysql_socket" -d "mysqli.default_socket=$mysql_socket" "$wp_cli" --path="$clone_public" "$@"
}

wp_clone option update home "$compat_url" >/dev/null
wp_clone option update siteurl "$compat_url" >/dev/null
wp_clone user create admin 'loginmood-qa@example.invalid' --role=administrator --user_pass=password >/dev/null
wp_clone plugin install wordfence --activate >/dev/null
"$php_runtime" "$wp_cli" --path="$clone_public" config set WP_HTTP_BLOCK_EXTERNAL true --raw >/dev/null
"$mysql_bin" --protocol=socket --socket="$mysql_socket" -u"$mysql_user" -D "$compat_db" -e "INSERT INTO wp_wfconfig (name,val,autoload) VALUES ('serverDNS', CONCAT(UNIX_TIMESTAMP(), ';86400;127.0.0.1'), 'yes') ON DUPLICATE KEY UPDATE val=VALUES(val),autoload='yes';"

wordfence_version="$(wp_clone plugin get wordfence --field=version)"
"$php_runtime" -d 'max_execution_time=300' -d "mysql.default_socket=$mysql_socket" -d "mysqli.default_socket=$mysql_socket" -S '127.0.0.1:9490' -t "$clone_public" >"$clone_root/php-server.log" 2>&1 &
server_pid=$!

ready='no'
for attempt in {1..60}; do
	if curl --max-time 10 -fsS "$compat_url/wp-login.php" | rg -q 'loginmood-login'; then
		ready='yes'
		break
	fi
	sleep 1
done

if [[ "$ready" != 'yes' ]]; then
	sed -n '1,240p' "$clone_root/php-server.log"
	exit 1
fi

LOGINMOOD_QA_BASE_URL="$compat_url" LOGINMOOD_COMPAT_NAME="Wordfence Security $wordfence_version" ./node_modules/.bin/playwright test tests/e2e/compatibility.spec.js --project=desktop-chromium
print "Wordfence $wordfence_version compatibility passed in a disposable LocalWP clone."
