#!/bin/bash
set -e

is_nextcloud_installed() {
	# When Nextcloud is not installed, occ prints a warning and may exit non-zero.
	# We treat any failure or missing JSON as "not installed".
	local status_json
	status_json="$(php /var/www/html/occ status --output=json 2>/dev/null || true)"
	if [ -z "$status_json" ]; then
		return 1
	fi
	php -r '$j=json_decode(stream_get_contents(STDIN), true); exit((isset($j["installed"]) && $j["installed"]===true) ? 0 : 1);' \
		<<<"$status_json" \
		>/dev/null 2>&1 \
		&& return 0 \
		|| return 1
}

run_occ() {
	# Never let an occ failure crash the container.
	php /var/www/html/occ "$@" 2>/dev/null || true
}

add_domain_if_missing() {
	local domain="$1"
	if [ -z "$domain" ]; then
		return 0
	fi

	local current_domains
	current_domains="$(run_occ config:system:get trusted_domains)"

	if printf '%s\n' "$current_domains" | grep -Fxq "$domain"; then
		echo "✓ $domain already in trusted_domains"
		return 0
	fi

	echo "🌐 Adding $domain to trusted_domains..."
	local index=0
	while run_occ config:system:get trusted_domains "$index" >/dev/null 2>&1; do
		index=$((index + 1))
	done
	run_occ config:system:set trusted_domains "$index" --value="$domain"
	echo "✅ Added $domain at index $index"
}

configure_reverse_proxy() {
	# Railway acts as a reverse proxy with HTTPS termination.
	# Configure Nextcloud to handle this correctly to prevent 500 errors.
	if [ -f /var/www/html/config/config.php ]; then
		echo "🔧 Configuring reverse proxy settings..."
		
		# Set overwriteprotocol to https (Railway terminates TLS)
		run_occ config:system:set overwriteprotocol --value="https"
		
		# Set overwrite.cli.url for Railway domain
		if [ -n "$RAILWAY_PUBLIC_DOMAIN" ]; then
			run_occ config:system:set overwrite.cli.url --value="https://$RAILWAY_PUBLIC_DOMAIN"
		fi
		
		# Trust Railway's proxy network (Railway uses 100.64.0.0/10 for internal networking)
		run_occ config:system:set trusted_proxies 0 --value="100.64.0.0/10"
		
		echo "✅ Reverse proxy configuration complete"
	fi
}

post_install_tasks() {
	# During first-time setup, Nextcloud isn't installed yet and occ can't modify config.
	# Wait until installation completes, then apply trusted-domain sync + app enable once.
	set +e
	local max_attempts=120
	local attempt=0
	while [ "$attempt" -lt "$max_attempts" ]; do
		if is_nextcloud_installed; then
			echo "✅ Nextcloud is installed; running post-install tasks..."
			
			# Configure reverse proxy settings FIRST
			configure_reverse_proxy
			
			if [ -n "$RAILWAY_PUBLIC_DOMAIN" ]; then
				echo "🚂 Railway public domain detected: $RAILWAY_PUBLIC_DOMAIN"
				add_domain_if_missing "$RAILWAY_PUBLIC_DOMAIN"
			fi
			if [ -n "$RAILWAY_STATIC_URL" ]; then
				echo "🚂 Railway static URL detected: $RAILWAY_STATIC_URL"
				add_domain_if_missing "$RAILWAY_STATIC_URL"
			fi
			if [ -n "$CUSTOM_DOMAIN" ]; then
				echo "🌐 Custom domain detected: $CUSTOM_DOMAIN"
				add_domain_if_missing "$CUSTOM_DOMAIN"
			fi

			local app_info_path="/var/www/html/custom_apps/nt_assistant/appinfo/info.xml"
			if [ -f "$app_info_path" ]; then
				echo "🎨 Found nt_assistant app, enabling it..."
				run_occ app:enable nt_assistant
				echo "✅ App enable command finished."
				echo "⏳ Running maintenance update..."
				run_occ maintenance:update:all
				echo "✅ Maintenance update command finished."
			else
				echo "⚠️ nt_assistant app not found at $app_info_path; skipping enable step."
			fi
			return 0
		fi
		attempt=$((attempt + 1))
		sleep 5
	done
	echo "⚠️ Timed out waiting for Nextcloud to become installed; skipping post-install tasks for now."
}

post_install_tasks &

# Execute the official Nextcloud entrypoint in the foreground to preserve stdin/stdout and signal handling.
# If no arguments are provided (Railway sometimes starts containers this way), default to apache2-foreground.
echo "⏳ Handing control back to Nextcloud official entrypoint..."
if [ $# -eq 0 ]; then
	echo "⚠️ No CMD arguments detected, defaulting to apache2-foreground"
	exec /entrypoint.sh apache2-foreground
else
	exec /entrypoint.sh "$@"
fi
