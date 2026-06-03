<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 MoreDKon/NormieTranslator
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Assistant\TaskProcessing;

use OCA\Assistant\Service\CustomHeadersService;
use OCA\Assistant\Service\MCPConfigService;
use OCP\Http\Client\IClientService;
use OCP\TaskProcessing\ISynchronousProvider;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Base adapter for MCP (Model Context Protocol) providers
 *
 * This abstract class provides common functionality for integrating
 * MCP tools as TaskProcessing providers
 */
abstract class MCPProviderAdapter implements ISynchronousProvider {

	protected string $providerType;
	protected string $taskTypeId;

	public function __construct(
		protected MCPConfigService $mcpConfig,
		protected CustomHeadersService $customHeaders,
		protected IClientService $clientService,
		protected LoggerInterface $logger,
	) {
	}

	/**
	 * Get the provider type identifier
	 *
	 * @return string One of: 'composio', 'klavis', 'pipedream'
	 */
	abstract protected function getProviderType(): string;

	/**
	 * Get the task type ID this provider handles
	 *
	 * @return string
	 */
	abstract public function getTaskTypeId(): string;

	/**
	 * Get the display name for this provider
	 *
	 * @return string
	 */
	abstract public function getName(): string;

	/**
	 * Get the unique provider ID
	 *
	 * @return string
	 */
	abstract public function getId(): string;

	/**
	 * Process MCP-specific request formatting
	 *
	 * @param array $input Task input data
	 * @return array Formatted request for MCP endpoint
	 */
	abstract protected function formatMCPRequest(array $input): array;

	/**
	 * Process MCP-specific response formatting
	 *
	 * @param array $response Response from MCP endpoint
	 * @return array Formatted output for TaskProcessing
	 */
	abstract protected function formatMCPResponse(array $response): array;

	/**
	 * Get expected runtime in seconds
	 *
	 * @return int
	 */
	public function getExpectedRuntime(): int {
		return 60; // Default 60 seconds
	}

	/**
	 * Get input shape enum values
	 *
	 * @return array
	 */
	public function getInputShapeEnumValues(): array {
		return [];
	}

	/**
	 * Get input shape defaults
	 *
	 * @return array
	 */
	public function getInputShapeDefaults(): array {
		return [];
	}

	/**
	 * Get optional input shape
	 *
	 * @return array
	 */
	public function getOptionalInputShape(): array {
		return [];
	}

	/**
	 * Get optional input shape enum values
	 *
	 * @return array
	 */
	public function getOptionalInputShapeEnumValues(): array {
		return [];
	}

	/**
	 * Get optional input shape defaults
	 *
	 * @return array
	 */
	public function getOptionalInputShapeDefaults(): array {
		return [];
	}

	/**
	 * Get output shape enum values
	 *
	 * @return array
	 */
	public function getOutputShapeEnumValues(): array {
		return [];
	}

	/**
	 * Get optional output shape
	 *
	 * @return array
	 */
	public function getOptionalOutputShape(): array {
		return [];
	}

	/**
	 * Get optional output shape enum values
	 *
	 * @return array
	 */
	public function getOptionalOutputShapeEnumValues(): array {
		return [];
	}

	/**
	 * Process a task through the MCP endpoint
	 *
	 * @param string|null $userId
	 * @param array $input
	 * @param callable $reportProgress
	 * @return array
	 * @throws RuntimeException
	 */
	public function process(?string $userId, array $input, callable $reportProgress): array {
		$providerType = $this->getProviderType();

		// Check if provider is enabled
		if (!$this->mcpConfig->isProviderEnabled($providerType)) {
			throw new RuntimeException("MCP provider '$providerType' is not enabled");
		}

		// Get endpoint
		$endpoint = $this->mcpConfig->getProviderEndpoint($providerType);
		if ($endpoint === null) {
			throw new RuntimeException("No endpoint configured for MCP provider '$providerType'");
		}

		// Format request
		$mcpRequest = $this->formatMCPRequest($input);

		// Prepare headers
		$headers = [
			'Content-Type' => 'application/json',
			'User-Agent' => 'NormieTranslator-Assistant/3.5.0-nt.1',
		];

		// Add authentication
		if ($userId !== null) {
			$auth = $this->mcpConfig->getProviderAuth($userId, $providerType);
			if ($auth !== null && isset($auth['type'])) {
				if ($auth['type'] === 'bearer' && isset($auth['token'])) {
					$headers['Authorization'] = 'Bearer ' . $auth['token'];
				} elseif ($auth['type'] === 'apikey' && isset($auth['key'], $auth['value'])) {
					$headers[$auth['key']] = $auth['value'];
				}
			}

			// Add custom headers for this provider
			$customHeaders = $this->customHeaders->getCustomHeaders($userId, $providerType);
			$headers = array_merge($headers, $customHeaders);
		}

		try {
			$client = $this->clientService->newClient();
			
			$reportProgress(0.1);

			// Make request to MCP endpoint
			$response = $client->post($endpoint, [
				'headers' => $headers,
				'json' => $mcpRequest,
				'timeout' => 120,
			]);

			$reportProgress(0.8);

			if ($response->getStatusCode() !== 200) {
				throw new RuntimeException(
					"MCP endpoint returned status " . $response->getStatusCode()
				);
			}

			$responseData = json_decode($response->getBody(), true);
			if ($responseData === null) {
				throw new RuntimeException("Invalid JSON response from MCP endpoint");
			}

			$reportProgress(0.9);

			// Format response
			$output = $this->formatMCPResponse($responseData);

			$reportProgress(1.0);

			return $output;

		} catch (\Exception $e) {
			$this->logger->error('MCP provider request failed', [
				'provider' => $providerType,
				'endpoint' => $endpoint,
				'exception' => $e->getMessage(),
			]);
			
			throw new RuntimeException(
				"MCP request failed: " . $e->getMessage(),
				0,
				$e
			);
		}
	}
}
