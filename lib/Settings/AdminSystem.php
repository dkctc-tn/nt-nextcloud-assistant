<?php

/**
 * SPDX-FileCopyrightText: 2026 DK Consultants & Technologies Corp and MoreDKon contributors
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Assistant\Settings;

use OCA\Assistant\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;

class AdminSystem implements ISettings {

	public function getForm(): TemplateResponse {
		return new TemplateResponse(Application::APP_ID, 'admin-system');
	}

	public function getSection(): string {
		return 'server';
	}

	public function getPriority(): int {
		return 75;
	}
}
