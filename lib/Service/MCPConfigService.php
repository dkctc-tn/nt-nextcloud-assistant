<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 MoreDKon/NormieTranslator
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Assistant\Service;

use OCA\Assistant\AppInfo\Application;
use OCP\Http\Client\IClientService;
use OCP\IAppConfig;
use OCP\IConfig;
use OCP\Security\ICrypto;
use Psr\Log\LoggerInterface;

/**
 * Service for managing MCP (Model Context Protocol) provider configuration
 *
 * This service handles configuration, authentication, and connectivity testing
 * for MCP providers like Composio For You MCP, Klavis AI, and Pipedream
 */
class MCPConfigService {

	public function __construct(
		private IAppConfig $appConfig,
		private IConfig $config,
		private ICrypto $crypto,
		private IClientService $clientService,
		private LoggerInterface $logger,
	) {
	}

	/**
	 * Check if a specific MCP provider is enabled
	 *
	 * @param string $providerType One of: 'composio', 'klavis', 'pipedream'
	 * @return bool
	 */
	public function isProviderEnabled(string $providerType): bool {
		$configKey = match ($providerType) {
			'composio' => Application::MCP_COMPOSIO_ENABLED,
			'klavis' => Application::MCP_KLAVIS_ENABLED,
			'pipedream' => Application::MCP_PIPEDREAM_ENABLED,
			default => null,
		};

		if ($configKey === null) {
			return false;
		}

		return $this->appConfig->getValueBool(Application::APP_ID, $configKey, false);
	}

	/**
	 * Enable or disable a specific MCP provider
	 *
	 * @param string $providerType One of: 'composio', 'klavis', 'pipedream'
	 * @param bool $enabled
	 * @return void
	 */
	public function setProviderEnabled(string $providerType, bool $enabled): void {
		$configKey = match ($providerType) {
			'composio' => Application::MCP_COMPOSIO_ENABLED,
			'klavis' => Application::MCP_KLAVIS_ENABLED,
			'pipedream' => Application::MCP_PIPEDREAM_ENABLED,
			default => null,
		};

		if ($configKey !== null) {
			$this->appConfig->setValueBool(Application::APP_ID, $configKey, $enabled);
		}
	}

	/**
	 * Get the endpoint URL for a specific MCP provider
	 *
	 * @param string $providerType One of: 'composio', 'klavis', 'pipedream'
	 * @return string|null
	 */
	public function getProviderEndpoint(string $providerType): ?string {
		$configKey = match ($providerType) {
			'composio' => Application::MCP_COMPOSIO_ENDPOINT,
			'klavis' => Application::MCP_KLAVIS_ENDPOINT,
			'pipedream' => Application::MCP_PIPEDREAM_ENDPOINT,
			default => null,
		};

		if ($configKey === null) {
			return null;
		}

		$endpoint = $this->appConfig->getValueString(Application::APP_ID, $configKey, '');
		return $endpoint !== '' ? $endpoint : null;
	}

	/**
	 * Set the endpoint URL for a specific MCP provider
	 *
	 * @param string $providerType One of: 'composio', 'klavis', 'pipedream'
	 * @param string $endpoint
	 * @return void
	 */
	public function setProviderEndpoint(string $providerType, string $endpoint): void {
		$configKey = match ($providerType) {
			'composio' => Application::MCP_COMPOSIO_ENDPOINT,
			'klavis' => Application::MCP_KLAVIS_ENDPOINT,
			'pipedream' => Application::MCP_PIPEDREAM_ENDPOINT,
			default => null,
		};

		if ($configKey !== null) {
			$this->appConfig->setValueString(Application::APP_ID, $configKey, $endpoint);
		}
	}

	/**
	 * Get authentication credentials for a user and provider (decrypted)
	 *
	 * @param string $userId
	 * @param string $providerType One of: 'composio', 'klavis', 'pipedream'
	 * @return array|null Array with auth data or null if not set
	 */
	public function getProviderAuth(string $userId, string $providerType): ?array {
		$configKey = "mcp_{$providerType}_auth";
		$encrypted = $this->config->getUserValue($userId, Application::APP_ID, $configKey, '');
		
		if ($encrypted === '') {
			return null;
		}

		try {
			$decrypted = $this->crypto->decrypt($encrypted);
			return json_decode($decrypted, true);
		} catch (\Exception $e) {
			$this->logger->error('Failed to decrypt MCP auth for provider: ' . $providerType, [
				'exception' => $e,
				'userId' => $userId,
			]);
			return null;
		}
	}

	/**
	 * Set authentication credentials for a user and provider (encrypted)
	 *
	 * @param string $userId
	 * @param string $providerType One of: 'composio', 'klavis', 'pipedream'
	 * @param array $authData Authentication data (will be encrypted)
	 * @return void
	 */
	public function setProviderAuth(string $userId, string $providerType, array $authData): void {
		$configKey = "mcp_{$providerType}_auth";
		$json = json_encode($authData);
		$encrypted = $this->crypto->encrypt($json);
		$this->config->setUserValue($userId, Application::APP_ID, $configKey, $encrypted);
	}

	/**
	 * Delete authentication credentials for a user and provider
	 *
	 * @param string $userId
	 * @param string $providerType One of: 'composio', 'klavis', 'pipedream'
	 * @return void
	 */
	public function deleteProviderAuth(string $userId, string $providerType): void {
		$configKey = "mcp_{$providerType}_auth";
		$this->config->deleteUserValue($userId, Application::APP_ID, $configKey);
	}

	/**
	 * Test connectivity to an MCP provider endpoint
	 *
	 * @param string $providerType One of: 'composio', 'klavis', 'pipedream'
	 * @param string|null $userId Optional user ID for user-specific auth
	 * @return array Array with 'success' boolean and 'message' string
	 */
	public function testProviderConnection(string $providerType, ?string $userId = null): array {
		$endpoint = $this->getProviderEndpoint($providerType);
		
		if ($endpoint === null) {
			return [
				'success' => false,
				'message' => 'No endpoint configured for this provider',
			];
		}

		try {
			$client = $this->clientService->newClient();
			
			// Prepare headers
			$headers = [
				'Content-Type' => 'application/json',
				'User-Agent' => 'NormieTranslator-Assistant/3.5.0-nt.1',
			];

			// Add authentication if user-specific
			if ($userId !== null) {
				$auth = $this->getProviderAuth($userId, $providerType);
				if ($auth !== null && isset($auth['type'])) {
					if ($auth['type'] === 'bearer' && isset($auth['token'])) {
						$headers['Authorization'] = 'Bearer ' . $auth['token'];
					} elseif ($auth['type'] === 'apikey' && isset($auth['key'], $auth['value'])) {
						$headers[$auth['key']] = $auth['value'];
					}
				}
			}

			// Make test request (health check or capabilities endpoint)
			$response = $client->get($endpoint . '/health', [
				'headers' => $headers,
				'timeout' => 5,
			]);

			if ($response->getStatusCode() === 200) {
				return [
					'success' => true,
					'message' => 'Connection successful',
				];
			} else {
				return [
					'success' => false,
					'message' => 'Unexpected status code: ' . $response->getStatusCode(),
				];
			}
		} catch (\Exception $e) {
			$this->logger->warning('MCP provider connection test failed', [
				'provider' => $providerType,
				'endpoint' => $endpoint,
				'exception' => $e->getMessage(),
			]);
			
			return [
				'success' => false,
				'message' => 'Connection failed: ' . $e->getMessage(),
			];
		}
	}

	/**
	 * Get available MCP providers and their status
	 *
	 * @return array
	 */
	public function getAvailableProviders(): array {
		return [
			'composio' => [
				'enabled' => $this->isProviderEnabled('composio'),
				'endpoint' => $this->getProviderEndpoint('composio'),
				'name' => 'Composio For You MCP',
				'description' => 'Access to Composio tools and integrations',
			],
			'klavis' => [
				'enabled' => $this->isProviderEnabled('klavis'),
				'endpoint' => $this->getProviderEndpoint('klavis'),
				'name' => 'Klavis AI',
				'description' => 'Klavis AI context and memory management',
			],
			'pipedream' => [
				'enabled' => $this->isProviderEnabled('pipedream'),
				'endpoint' => $this->getProviderEndpoint('pipedream'),
				'name' => 'Pipedream',
				'description' => 'Pipedream workflow automation and integrations',
			],
		];
	}

	/**
	 * Get all configuration for a specific provider
	 *
	 * @param string $providerType
	 * @param string|null $userId
	 * @return array
	 */
	public function getProviderConfig(string $providerType, ?string $userId = null): array {
		$config = [
			'enabled' => $this->isProviderEnabled($providerType),
			'endpoint' => $this->getProviderEndpoint($providerType),
		];

		if ($userId !== null) {
			$auth = $this->getProviderAuth($userId, $providerType);
			$config['hasAuth'] = $auth !== null;
		}

		return $config;
	}
}
