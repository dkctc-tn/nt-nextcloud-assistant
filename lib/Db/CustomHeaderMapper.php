<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 MoreDKon/NormieTranslator
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Assistant\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<CustomHeader>
 */
class CustomHeaderMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'assistant_custom_headers', CustomHeader::class);
	}

	/**
	 * Find a specific custom header by ID for a user
	 *
	 * @param string $userId
	 * @param int $headerId
	 * @return CustomHeader
	 * @throws \OCP\DB\Exception
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function find(string $userId, int $headerId): CustomHeader {
		$qb = $this->db->getQueryBuilder();
		$qb->select(CustomHeader::$columns)
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createPositionalParameter($headerId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('user_id', $qb->createPositionalParameter($userId, IQueryBuilder::PARAM_STR)));

		return $this->findEntity($qb);
	}

	/**
	 * Find a specific custom header by user, provider, and header name
	 *
	 * @param string $userId
	 * @param string $providerName
	 * @param string $headerName
	 * @return CustomHeader
	 * @throws \OCP\DB\Exception
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function findByProviderAndName(string $userId, string $providerName, string $headerName): CustomHeader {
		$qb = $this->db->getQueryBuilder();
		$qb->select(CustomHeader::$columns)
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createPositionalParameter($userId, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->eq('provider_name', $qb->createPositionalParameter($providerName, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->eq('header_name', $qb->createPositionalParameter($headerName, IQueryBuilder::PARAM_STR)));

		return $this->findEntity($qb);
	}

	/**
	 * Find all custom headers for a user
	 *
	 * @param string $userId
	 * @return CustomHeader[]
	 * @throws \OCP\DB\Exception
	 */
	public function findAllForUser(string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select(CustomHeader::$columns)
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createPositionalParameter($userId, IQueryBuilder::PARAM_STR)))
			->orderBy('provider_name', 'ASC')
			->addOrderBy('header_name', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * Find all enabled custom headers for a user and provider
	 *
	 * @param string $userId
	 * @param string $providerName
	 * @return CustomHeader[]
	 * @throws \OCP\DB\Exception
	 */
	public function findEnabledForProvider(string $userId, string $providerName): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select(CustomHeader::$columns)
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createPositionalParameter($userId, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->eq('provider_name', $qb->createPositionalParameter($providerName, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->eq('enabled', $qb->createPositionalParameter(true, IQueryBuilder::PARAM_BOOL)))
			->orderBy('header_name', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * Find all custom headers for a specific provider (all users - admin only)
	 *
	 * @param string $providerName
	 * @return CustomHeader[]
	 * @throws \OCP\DB\Exception
	 */
	public function findByProvider(string $providerName): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select(CustomHeader::$columns)
			->from($this->getTableName())
			->where($qb->expr()->eq('provider_name', $qb->createPositionalParameter($providerName, IQueryBuilder::PARAM_STR)))
			->orderBy('user_id', 'ASC')
			->addOrderBy('header_name', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * Delete a custom header by ID for a user
	 *
	 * @param string $userId
	 * @param int $headerId
	 * @return int Number of deleted rows
	 * @throws \OCP\DB\Exception
	 */
	public function deleteById(string $userId, int $headerId): int {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createPositionalParameter($headerId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('user_id', $qb->createPositionalParameter($userId, IQueryBuilder::PARAM_STR)));

		return $qb->executeStatement();
	}

	/**
	 * Delete all custom headers for a user
	 *
	 * @param string $userId
	 * @return int Number of deleted rows
	 * @throws \OCP\DB\Exception
	 */
	public function deleteAllForUser(string $userId): int {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createPositionalParameter($userId, IQueryBuilder::PARAM_STR)));

		return $qb->executeStatement();
	}
}
