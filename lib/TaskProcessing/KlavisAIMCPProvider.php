<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 MoreDKon/NormieTranslator
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Assistant\TaskProcessing;

use OCA\Assistant\AppInfo\Application;

/**
 * MCP Provider Adapter for Klavis AI
 *
 * Provides access to Klavis AI context and memory management
 */
class KlavisAIMCPProvider extends MCPProviderAdapter {

	protected function getProviderType(): string {
		return 'klavis';
	}

	public function getId(): string {
		return Application::APP_ID . '-mcp-klavis';
	}

	public function getName(): string {
		return 'Klavis AI';
	}

	public function getTaskTypeId(): string {
		return 'core:text2text:klavis';
	}

	public function getExpectedRuntime(): int {
		return 60;
	}

	protected function formatMCPRequest(array $input): array {
		// Format input for Klavis AI MCP endpoint
		// Klavis expects: operation type, data, and optional metadata
		return [
			'jsonrpc' => '2.0',
			'method' => $input['operation'] ?? 'query',
			'params' => [
				'data' => $input['data'] ?? '',
				'context' => $input['context'] ?? [],
				'metadata' => $input['metadata'] ?? [],
			],
			'id' => uniqid('klavis_', true),
		];
	}

	protected function formatMCPResponse(array $response): array {
		// Extract result from Klavis AI MCP response
		if (isset($response['error'])) {
			throw new \RuntimeException(
				$response['error']['message'] ?? 'Klavis AI operation failed'
			);
		}

		if (!isset($response['result'])) {
			throw new \RuntimeException('Invalid response from Klavis AI MCP');
		}

		// Return formatted output
		return [
			'output' => is_string($response['result']) ? $response['result'] : json_encode($response['result'], JSON_PRETTY_PRINT),
			'operation' => $response['result']['operation'] ?? 'unknown',
			'success' => true,
		];
	}
}
