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
 * MCP Provider Adapter for Composio For You MCP
 *
 * Provides access to Composio tools and integrations through the MCP protocol
 */
class ComposioMCPProvider extends MCPProviderAdapter {

	protected function getProviderType(): string {
		return 'composio';
	}

	public function getId(): string {
		return Application::APP_ID . '-mcp-composio';
	}

	public function getName(): string {
		return 'Composio For You MCP';
	}

	public function getTaskTypeId(): string {
		return 'core:text2text:composio';
	}

	public function getExpectedRuntime(): int {
		return 90; // Composio operations may take longer
	}

	protected function formatMCPRequest(array $input): array {
		// Format input for Composio MCP endpoint
		// Composio expects: tool name, parameters, and optional context
		return [
			'jsonrpc' => '2.0',
			'method' => 'tools/call',
			'params' => [
				'name' => $input['tool'] ?? 'default',
				'arguments' => $input['arguments'] ?? [],
				'context' => $input['context'] ?? null,
			],
			'id' => uniqid('composio_', true),
		];
	}

	protected function formatMCPResponse(array $response): array {
		// Extract result from Composio MCP response
		if (isset($response['error'])) {
			throw new \RuntimeException(
				$response['error']['message'] ?? 'Composio tool execution failed'
			);
		}

		if (!isset($response['result'])) {
			throw new \RuntimeException('Invalid response from Composio MCP');
		}

		// Return formatted output
		return [
			'output' => json_encode($response['result'], JSON_PRETTY_PRINT),
			'toolName' => $response['result']['tool'] ?? 'unknown',
			'success' => $response['result']['success'] ?? true,
		];
	}
}
