<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 MoreDKon/NormieTranslator
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Assistant\Service;

use OCA\Assistant\Db\CustomHeader;
use OCA\Assistant\Db\CustomHeaderMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IConfig;
use OCP\Security\ICrypto;

/**
 * Service for managing custom headers for LLM requests
 *
 * This service handles storage, retrieval, and encryption of custom headers
 * that are added to LLM provider requests on a per-user, per-provider basis
 */
class CustomHeadersService {

	public function __construct(
		private CustomHeaderMapper $mapper,
		private ICrypto $crypto,
		private IConfig $config,
	) {
	}

	/**
	 * Set a custom header for a user and provider
	 *
	 * @param string $userId
	 * @param string $providerName
	 * @param string $headerName
	 * @param string $headerValue
	 * @param bool $encrypt Whether to encrypt the header value
	 * @return CustomHeader
	 * @throws \OCP\DB\Exception
	 */
	public function setCustomHeader(
		string $userId,
		string $providerName,
		string $headerName,
		string $headerValue,
		bool $encrypt = false
	): CustomHeader {
		$now = time();
		
		// Check if header already exists
		try {
			$header = $this->mapper->findByProviderAndName($userId, $providerName, $headerName);
			// Update existing header
			$header->setHeaderValue($encrypt ? $this->crypto->encrypt($headerValue) : $headerValue);
			$header->setEncrypted($encrypt);
			$header->setUpdatedAt($now);
			return $this->mapper->update($header);
		} catch (DoesNotExistException $e) {
			// Create new header
			$header = new CustomHeader();
			$header->setUserId($userId);
			$header->setProviderName($providerName);
			$header->setHeaderName($headerName);
			$header->setHeaderValue($encrypt ? $this->crypto->encrypt($headerValue) : $headerValue);
			$header->setEncrypted($encrypt);
			$header->setEnabled(true);
			$header->setCreatedAt($now);
			$header->setUpdatedAt($now);
			return $this->mapper->insert($header);
		}
	}

	/**
	 * Get a specific custom header (with decrypted value if encrypted)
	 *
	 * @param string $userId
	 * @param string $providerName
	 * @param string $headerName
	 * @return array|null Array with 'name' and 'value' keys, or null if not found
	 * @throws \OCP\DB\Exception
	 */
	public function getCustomHeader(string $userId, string $providerName, string $headerName): ?array {
		try {
			$header = $this->mapper->findByProviderAndName($userId, $providerName, $headerName);
			if (!$header->getEnabled()) {
				return null;
			}
			
			$value = $header->getHeaderValue();
			if ($header->getEncrypted()) {
				try {
					$value = $this->crypto->decrypt($value);
				} catch (\Exception $e) {
					// If decryption fails, return null
					return null;
				}
			}
			
			return [
				'name' => $header->getHeaderName(),
				'value' => $value,
			];
		} catch (DoesNotExistException $e) {
			return null;
		}
	}

	/**
	 * Get all enabled custom headers for a provider (returns array suitable for HTTP headers)
	 *
	 * @param string $userId
	 * @param string $providerName
	 * @return array Associative array of header names to values
	 * @throws \OCP\DB\Exception
	 */
	public function getCustomHeaders(string $userId, string $providerName): array {
		$headers = [];
		$customHeaders = $this->mapper->findEnabledForProvider($userId, $providerName);
		
		foreach ($customHeaders as $header) {
			$value = $header->getHeaderValue();
			if ($header->getEncrypted()) {
				try {
					$value = $this->crypto->decrypt($value);
				} catch (\Exception $e) {
					// Skip headers that fail to decrypt
					continue;
				}
			}
			$headers[$header->getHeaderName()] = $value;
		}
		
		return $headers;
	}

	/**
	 * Get all custom headers for a user (without values for security)
	 *
	 * @param string $userId
	 * @return array
	 * @throws \OCP\DB\Exception
	 */
	public function getAllCustomHeaders(string $userId): array {
		return array_map(function (CustomHeader $header) {
			return $header->jsonSerialize();
		}, $this->mapper->findAllForUser($userId));
	}

	/**
	 * Remove a custom header
	 *
	 * @param string $userId
	 * @param string $providerName
	 * @param string $headerName
	 * @return bool True if deleted, false if not found
	 * @throws \OCP\DB\Exception
	 */
	public function removeCustomHeader(string $userId, string $providerName, string $headerName): bool {
		try {
			$header = $this->mapper->findByProviderAndName($userId, $providerName, $headerName);
			$this->mapper->delete($header);
			return true;
		} catch (DoesNotExistException $e) {
			return false;
		}
	}

	/**
	 * Enable or disable a custom header
	 *
	 * @param string $userId
	 * @param string $providerName
	 * @param string $headerName
	 * @param bool $enabled
	 * @return bool True if updated, false if not found
	 * @throws \OCP\DB\Exception
	 */
	public function setHeaderEnabled(string $userId, string $providerName, string $headerName, bool $enabled): bool {
		try {
			$header = $this->mapper->findByProviderAndName($userId, $providerName, $headerName);
			$header->setEnabled($enabled);
			$header->setUpdatedAt(time());
			$this->mapper->update($header);
			return true;
		} catch (DoesNotExistException $e) {
			return false;
		}
	}

	/**
	 * Delete all custom headers for a user
	 *
	 * @param string $userId
	 * @return int Number of deleted headers
	 * @throws \OCP\DB\Exception
	 */
	public function deleteAllForUser(string $userId): int {
		return $this->mapper->deleteAllForUser($userId);
	}

	/**
	 * Check if custom headers are enabled globally
	 *
	 * @return bool
	 */
	public function areCustomHeadersEnabled(): bool {
		return $this->config->getSystemValueBool('assistant.custom_headers_enabled', true);
	}
}
