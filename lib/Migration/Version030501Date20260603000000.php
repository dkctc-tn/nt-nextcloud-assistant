<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 MoreDKon/NormieTranslator
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Assistant\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Migration to create custom_headers table for storing custom LLM request headers
 */
class Version030501Date20260603000000 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();
		$schemaChanged = false;

		// Create custom_headers table for storing per-user, per-provider custom headers
		if (!$schema->hasTable('assistant_custom_headers')) {
			$schemaChanged = true;
			$table = $schema->createTable('assistant_custom_headers');
			
			// Primary key
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			
			// User who owns this header configuration
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			
			// Provider name (e.g., 'openai', 'anthropic', 'nt_endpoint')
			$table->addColumn('provider_name', Types::STRING, [
				'notnull' => true,
				'length' => 256,
			]);
			
			// Header name (e.g., 'Authorization', 'X-Custom-Key')
			$table->addColumn('header_name', Types::STRING, [
				'notnull' => true,
				'length' => 256,
			]);
			
			// Header value (encrypted for sensitive values)
			$table->addColumn('header_value', Types::TEXT, [
				'notnull' => true,
			]);
			
			// Whether this value is encrypted
			$table->addColumn('encrypted', Types::BOOLEAN, [
				'notnull' => true,
				'default' => false,
			]);
			
			// Whether this header is enabled
			$table->addColumn('enabled', Types::BOOLEAN, [
				'notnull' => true,
				'default' => true,
			]);
			
			// Timestamp for tracking
			$table->addColumn('created_at', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			
			$table->addColumn('updated_at', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			
			// Indexes
			$table->setPrimaryKey(['id']);
			$table->addIndex(['user_id'], 'assistant_ch_user_id');
			$table->addIndex(['user_id', 'provider_name'], 'assistant_ch_user_provider');
			$table->addIndex(['user_id', 'provider_name', 'header_name'], 'assistant_ch_unique');
			$table->addUniqueIndex(['user_id', 'provider_name', 'header_name'], 'assistant_ch_uniq_constraint');
		}

		return $schemaChanged ? $schema : null;
	}
}
