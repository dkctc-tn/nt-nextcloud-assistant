<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 MoreDKon/NormieTranslator
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Assistant\Controller;

use OCA\Assistant\Service\CustomHeadersService;
use OCA\Assistant\Service\MCPConfigService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

/**
 * Controller for MCP (Model Context Protocol) configuration and management
 */
class MCPController extends Controller {

	public function __construct(
		string $appName,
		IRequest $request,
		private MCPConfigService $mcpConfig,
		private CustomHeadersService $customHeaders,
		private ?string $userId,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Get all available MCP providers and their status
	 *
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	public function getProviders(): DataResponse {
		return new DataResponse($this->mcpConfig->getAvailableProviders());
	}

	/**
	 * Get configuration for a specific MCP provider
	 *
	 * @param string $providerType One of: 'composio', 'klavis', 'pipedream'
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	public function getProviderConfig(string $providerType): DataResponse {
		$config = $this->mcpConfig->getProviderConfig($providerType, $this->userId);
		return new DataResponse($config);
	}

	/**
	 * Enable or disable a specific MCP provider (admin only)
	 *
	 * @param string $providerType One of: 'composio', 'klavis', 'pipedream'
	 * @param bool $enabled
	 * @return DataResponse
	 */
	public function setProviderEnabled(string $providerType, bool $enabled): DataResponse {
		$this->mcpConfig->setProviderEnabled($providerType, $enabled);
		return new DataResponse(['success' => true]);
	}

	/**
	 * Set endpoint URL for a specific MCP provider (admin only)
	 *
	 * @param string $providerType One of: 'composio', 'klavis', 'pipedream'
	 * @param string $endpoint
	 * @return DataResponse
	 */
	public function setProviderEndpoint(string $providerType, string $endpoint): DataResponse {
		$this->mcpConfig->setProviderEndpoint($providerType, $endpoint);
		return new DataResponse(['success' => true]);
	}

	/**
	 * Set authentication credentials for a user and provider
	 *
	 * @param string $providerType One of: 'composio', 'klavis', 'pipedream'
	 * @param array $authData Authentication data (will be encrypted)
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	public function setProviderAuth(string $providerType, array $authData): DataResponse {
		if ($this->userId === null) {
			return new DataResponse(['error' => 'User not authenticated'], 401);
		}

		$this->mcpConfig->setProviderAuth($this->userId, $providerType, $authData);
		return new DataResponse(['success' => true]);
	}

	/**
	 * Delete authentication credentials for a user and provider
	 *
	 * @param string $providerType One of: 'composio', 'klavis', 'pipedream'
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	public function deleteProviderAuth(string $providerType): DataResponse {
		if ($this->userId === null) {
			return new DataResponse(['error' => 'User not authenticated'], 401);
		}

		$this->mcpConfig->deleteProviderAuth($this->userId, $providerType);
		return new DataResponse(['success' => true]);
	}

	/**
	 * Test connectivity to an MCP provider endpoint
	 *
	 * @param string $providerType One of: 'composio', 'klavis', 'pipedream'
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	public function testConnection(string $providerType): DataResponse {
		$result = $this->mcpConfig->testProviderConnection($providerType, $this->userId);
		return new DataResponse($result);
	}

	/**
	 * Get all custom headers for the current user
	 *
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	public function getCustomHeaders(): DataResponse {
		if ($this->userId === null) {
			return new DataResponse(['error' => 'User not authenticated'], 401);
		}

		try {
			$headers = $this->customHeaders->getAllCustomHeaders($this->userId);
			return new DataResponse(['headers' => $headers]);
		} catch (\Exception $e) {
			return new DataResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * Set a custom header for a provider
	 *
	 * @param string $providerName Provider name
	 * @param string $headerName Header name
	 * @param string $headerValue Header value
	 * @param bool $encrypt Whether to encrypt the value
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	public function setCustomHeader(
		string $providerName,
		string $headerName,
		string $headerValue,
		bool $encrypt = false
	): DataResponse {
		if ($this->userId === null) {
			return new DataResponse(['error' => 'User not authenticated'], 401);
		}

		try {
			$header = $this->customHeaders->setCustomHeader(
				$this->userId,
				$providerName,
				$headerName,
				$headerValue,
				$encrypt
			);
			return new DataResponse(['success' => true, 'header' => $header->jsonSerialize()]);
		} catch (\Exception $e) {
			return new DataResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * Remove a custom header
	 *
	 * @param string $providerName Provider name
	 * @param string $headerName Header name
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	public function removeCustomHeader(string $providerName, string $headerName): DataResponse {
		if ($this->userId === null) {
			return new DataResponse(['error' => 'User not authenticated'], 401);
		}

		try {
			$success = $this->customHeaders->removeCustomHeader(
				$this->userId,
				$providerName,
				$headerName
			);
			return new DataResponse(['success' => $success]);
		} catch (\Exception $e) {
			return new DataResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * Enable or disable a custom header
	 *
	 * @param string $providerName Provider name
	 * @param string $headerName Header name
	 * @param bool $enabled Whether to enable the header
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	public function setHeaderEnabled(
		string $providerName,
		string $headerName,
		bool $enabled
	): DataResponse {
		if ($this->userId === null) {
			return new DataResponse(['error' => 'User not authenticated'], 401);
		}

		try {
			$success = $this->customHeaders->setHeaderEnabled(
				$this->userId,
				$providerName,
				$headerName,
				$enabled
			);
			return new DataResponse(['success' => $success]);
		} catch (\Exception $e) {
			return new DataResponse(['error' => $e->getMessage()], 500);
		}
	}
}
