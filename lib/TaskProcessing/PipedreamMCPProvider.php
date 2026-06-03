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
 * MCP Provider Adapter for Pipedream
 *
 * Provides access to Pipedream workflow automation and integrations
 */
class PipedreamMCPProvider extends MCPProviderAdapter {

	protected function getProviderType(): string {
		return 'pipedream';
	}

	public function getId(): string {
		return Application::APP_ID . '-mcp-pipedream';
	}

	public function getName(): string {
		return 'Pipedream';
	}

	public function getTaskTypeId(): string {
		return 'core:text2text:pipedream';
	}

	public function getExpectedRuntime(): int {
		return 120; // Workflow executions may take longer
	}

	protected function formatMCPRequest(array $input): array {
		// Format input for Pipedream MCP endpoint
		// Pipedream expects: workflow ID, trigger data, and optional configuration
		return [
			'jsonrpc' => '2.0',
			'method' => 'workflows/execute',
			'params' => [
				'workflow_id' => $input['workflow_id'] ?? '',
				'trigger_data' => $input['trigger_data'] ?? [],
				'config' => $input['config'] ?? [],
			],
			'id' => uniqid('pipedream_', true),
		];
	}

	protected function formatMCPResponse(array $response): array {
		// Extract result from Pipedream MCP response
		if (isset($response['error'])) {
			throw new \RuntimeException(
				$response['error']['message'] ?? 'Pipedream workflow execution failed'
			);
		}

		if (!isset($response['result'])) {
			throw new \RuntimeException('Invalid response from Pipedream MCP');
		}

		// Return formatted output
		return [
			'output' => json_encode($response['result'], JSON_PRETTY_PRINT),
			'workflowId' => $response['result']['workflow_id'] ?? 'unknown',
			'executionId' => $response['result']['execution_id'] ?? 'unknown',
			'success' => $response['result']['success'] ?? true,
		];
	}
}
