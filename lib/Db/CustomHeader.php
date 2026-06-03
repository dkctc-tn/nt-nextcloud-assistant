<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 MoreDKon/NormieTranslator
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Assistant\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getProviderName()
 * @method void setProviderName(string $providerName)
 * @method string getHeaderName()
 * @method void setHeaderName(string $headerName)
 * @method string getHeaderValue()
 * @method void setHeaderValue(string $headerValue)
 * @method bool getEncrypted()
 * @method void setEncrypted(bool $encrypted)
 * @method bool getEnabled()
 * @method void setEnabled(bool $enabled)
 * @method int getCreatedAt()
 * @method void setCreatedAt(int $createdAt)
 * @method int getUpdatedAt()
 * @method void setUpdatedAt(int $updatedAt)
 */
class CustomHeader extends Entity implements \JsonSerializable {
	/** @var string */
	protected $userId;
	
	/** @var string */
	protected $providerName;
	
	/** @var string */
	protected $headerName;
	
	/** @var string */
	protected $headerValue;
	
	/** @var bool */
	protected $encrypted;
	
	/** @var bool */
	protected $enabled;
	
	/** @var int */
	protected $createdAt;
	
	/** @var int */
	protected $updatedAt;

	public static $columns = [
		'id',
		'user_id',
		'provider_name',
		'header_name',
		'header_value',
		'encrypted',
		'enabled',
		'created_at',
		'updated_at',
	];

	public static $fields = [
		'id',
		'userId',
		'providerName',
		'headerName',
		'headerValue',
		'encrypted',
		'enabled',
		'createdAt',
		'updatedAt',
	];

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('userId', 'string');
		$this->addType('providerName', 'string');
		$this->addType('headerName', 'string');
		$this->addType('headerValue', 'string');
		$this->addType('encrypted', 'boolean');
		$this->addType('enabled', 'boolean');
		$this->addType('createdAt', 'integer');
		$this->addType('updatedAt', 'integer');
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'userId' => $this->userId,
			'providerName' => $this->providerName,
			'headerName' => $this->headerName,
			// Don't expose the actual header value in JSON for security
			'encrypted' => $this->encrypted,
			'enabled' => $this->enabled,
			'createdAt' => $this->createdAt,
			'updatedAt' => $this->updatedAt,
		];
	}
}
