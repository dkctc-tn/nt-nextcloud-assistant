<?php

/**
 * SPDX-FileCopyrightText: 2026 MoreDKon/NormieTranslator
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Assistant\Service;

use OCA\Assistant\AppInfo\Application;
use OCP\IAppConfig;
use OCP\IConfig;

/**
 * Service for managing custom branding configuration
 * 
 * This service handles storage and retrieval of custom branding settings
 * including app name, logo, colors, and white-label mode
 */
class BrandingService {

	public function __construct(
		private IAppConfig $appConfig,
		private IConfig $config,
	) {
	}

	/**
	 * Get the custom app name or default to NormieTranslator Assistant
	 *
	 * @return string
	 */
	public function getAppName(): string {
		return $this->appConfig->getValueString(
			Application::APP_ID,
			Application::BRANDING_APP_NAME,
			'NormieTranslator Assistant'
		);
	}

	/**
	 * Set the custom app name
	 *
	 * @param string $appName
	 * @return void
	 */
	public function setAppName(string $appName): void {
		$this->appConfig->setValueString(
			Application::APP_ID,
			Application::BRANDING_APP_NAME,
			$appName
		);
	}

	/**
	 * Get the custom app logo path
	 *
	 * @return string|null
	 */
	public function getAppLogo(): ?string {
		$logo = $this->appConfig->getValueString(
			Application::APP_ID,
			Application::BRANDING_APP_LOGO,
			''
		);
		return $logo !== '' ? $logo : null;
	}

	/**
	 * Set the custom app logo path
	 *
	 * @param string $logoPath
	 * @return void
	 */
	public function setAppLogo(string $logoPath): void {
		$this->appConfig->setValueString(
			Application::APP_ID,
			Application::BRANDING_APP_LOGO,
			$logoPath
		);
	}

	/**
	 * Get the custom app primary color
	 *
	 * @return string
	 */
	public function getAppColor(): string {
		return $this->appConfig->getValueString(
			Application::APP_ID,
			Application::BRANDING_APP_COLOR,
			'#0082c9'  // Default Nextcloud blue
		);
	}

	/**
	 * Set the custom app primary color
	 *
	 * @param string $color Hex color code
	 * @return void
	 */
	public function setAppColor(string $color): void {
		// Validate hex color format
		if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
			$this->appConfig->setValueString(
				Application::APP_ID,
				Application::BRANDING_APP_COLOR,
				$color
			);
		}
	}

	/**
	 * Get the custom header text
	 *
	 * @return string|null
	 */
	public function getHeaderText(): ?string {
		$text = $this->appConfig->getValueString(
			Application::APP_ID,
			Application::BRANDING_HEADER_TEXT,
			''
		);
		return $text !== '' ? $text : null;
	}

	/**
	 * Set the custom header text
	 *
	 * @param string $text
	 * @return void
	 */
	public function setHeaderText(string $text): void {
		$this->appConfig->setValueString(
			Application::APP_ID,
			Application::BRANDING_HEADER_TEXT,
			$text
		);
	}

	/**
	 * Check if white-label mode is enabled
	 *
	 * @return bool
	 */
	public function isWhiteLabelMode(): bool {
		return $this->appConfig->getValueBool(
			Application::APP_ID,
			Application::BRANDING_WHITE_LABEL_MODE,
			false
		);
	}

	/**
	 * Set white-label mode status
	 *
	 * @param bool $enabled
	 * @return void
	 */
	public function setWhiteLabelMode(bool $enabled): void {
		$this->appConfig->setValueBool(
			Application::APP_ID,
			Application::BRANDING_WHITE_LABEL_MODE,
			$enabled
		);
	}

	/**
	 * Get all branding configuration as an array
	 *
	 * @return array
	 */
	public function getAllBranding(): array {
		return [
			'appName' => $this->getAppName(),
			'appLogo' => $this->getAppLogo(),
			'appColor' => $this->getAppColor(),
			'headerText' => $this->getHeaderText(),
			'whiteLabelMode' => $this->isWhiteLabelMode(),
		];
	}

	/**
	 * Reset all branding to defaults
	 *
	 * @return void
	 */
	public function resetBranding(): void {
		$this->appConfig->deleteKey(Application::APP_ID, Application::BRANDING_APP_NAME);
		$this->appConfig->deleteKey(Application::APP_ID, Application::BRANDING_APP_LOGO);
		$this->appConfig->deleteKey(Application::APP_ID, Application::BRANDING_APP_COLOR);
		$this->appConfig->deleteKey(Application::APP_ID, Application::BRANDING_HEADER_TEXT);
		$this->appConfig->deleteKey(Application::APP_ID, Application::BRANDING_WHITE_LABEL_MODE);
	}
}
