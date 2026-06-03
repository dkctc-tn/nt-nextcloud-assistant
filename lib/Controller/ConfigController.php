<?php

/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Assistant\Controller;

use OCA\Assistant\AppInfo\Application;
use OCA\Assistant\Service\BrandingService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\DataResponse;
use OCP\IAppConfig;
use OCP\IConfig;

use OCP\IRequest;
use OCP\PreConditionNotMetException;

#[OpenAPI(scope: OpenAPI::SCOPE_IGNORE)]
class ConfigController extends Controller {

	public function __construct(
		string $appName,
		IRequest $request,
		private IConfig $config,
		private IAppConfig $appConfig,
		private BrandingService $brandingService,
		private ?string $userId,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Set config values
	 *
	 * @param array $values key/value pairs to store in config
	 * @return DataResponse
	 * @throws PreConditionNotMetException
	 */
	#[NoAdminRequired]
	public function setConfig(array $values): DataResponse {
		foreach ($values as $key => $value) {
			$this->config->setUserValue($this->userId, Application::APP_ID, $key, $value);
		}
		return new DataResponse(1);
	}

	/**
	 * @param string $key
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	public function getConfigValue(string $key): DataResponse {
		$value = $this->config->getUserValue($this->userId, Application::APP_ID, $key);
		return new DataResponse($value);
	}

	/**
	 * Set admin config values
	 *
	 * @param array $values key/value pairs to store in app config
	 * @return DataResponse
	 */
	public function setAdminConfig(array $values): DataResponse {
		foreach ($values as $key => $value) {
			if ($key == 'assistant_enabled') {
				// do not lazy store assistant_enabled as it is needed for capabilities
				$this->appConfig->setValueString(Application::APP_ID, $key, $value);
			} else {
				$this->appConfig->setValueString(Application::APP_ID, $key, $value, lazy: true);
			}
		}
		return new DataResponse(1);
	}

	/**
	 * Get all branding configuration
	 *
	 * @return DataResponse
	 */
	#[NoAdminRequired]
	public function getBranding(): DataResponse {
		return new DataResponse($this->brandingService->getAllBranding());
	}

	/**
	 * Set branding configuration (admin only)
	 *
	 * @param string|null $appName Custom app name
	 * @param string|null $appLogo Custom app logo path
	 * @param string|null $appColor Custom app color
	 * @param string|null $headerText Custom header text
	 * @param bool|null $whiteLabelMode White label mode
	 * @return DataResponse
	 */
	public function setBranding(
		?string $appName = null,
		?string $appLogo = null,
		?string $appColor = null,
		?string $headerText = null,
		?bool $whiteLabelMode = null
	): DataResponse {
		if ($appName !== null) {
			$this->brandingService->setAppName($appName);
		}
		if ($appLogo !== null) {
			$this->brandingService->setAppLogo($appLogo);
		}
		if ($appColor !== null) {
			$this->brandingService->setAppColor($appColor);
		}
		if ($headerText !== null) {
			$this->brandingService->setHeaderText($headerText);
		}
		if ($whiteLabelMode !== null) {
			$this->brandingService->setWhiteLabelMode($whiteLabelMode);
		}
		return new DataResponse(['success' => true]);
	}

	/**
	 * Reset branding to defaults (admin only)
	 *
	 * @return DataResponse
	 */
	public function resetBranding(): DataResponse {
		$this->brandingService->resetBranding();
		return new DataResponse(['success' => true]);
	}
}
