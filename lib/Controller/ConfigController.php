<?php

/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Assistant\Controller;

use OCA\Assistant\AppInfo\Application;
use OCA\Assistant\Service\BrandingService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\AdminRequired;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\DataResponse;
use OCP\IAppConfig;
use OCP\IConfig;

use OCP\IRequest;
use OCP\PreConditionNotMetException;
use Psr\Log\LoggerInterface;

#[OpenAPI(scope: OpenAPI::SCOPE_IGNORE)]
class ConfigController extends Controller {

	public function __construct(
		string $appName,
		IRequest $request,
		private IConfig $config,
		private IAppConfig $appConfig,
		private BrandingService $brandingService,
		private LoggerInterface $logger,
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

	#[AdminRequired]
	public function getTrustedDomains(): DataResponse {
		return new DataResponse($this->buildTrustedDomainsPayload());
	}

	#[AdminRequired]
	public function addTrustedDomain(string $domain): DataResponse {
		$trustedDomain = $this->normalizeTrustedDomain($domain);
		if ($trustedDomain === null) {
			return new DataResponse(['message' => 'Invalid trusted domain'], Http::STATUS_BAD_REQUEST);
		}

		$trustedDomains = $this->getTrustedDomainsConfig();
		$knownDomains = array_map(fn (string $existingDomain): ?string => $this->normalizeTrustedDomain($existingDomain), $trustedDomains);
		if (!in_array($trustedDomain, $knownDomains, true)) {
			$trustedDomains[] = $trustedDomain;
			$this->config->setSystemValue('trusted_domains', array_values($trustedDomains));
			$this->logger->info('Added trusted domain through admin settings', [
				'app' => Application::APP_ID,
				'domain' => $trustedDomain,
				'userId' => $this->userId,
			]);
		}

		return new DataResponse($this->buildTrustedDomainsPayload());
	}

	#[AdminRequired]
	public function removeTrustedDomain(int $index): DataResponse {
		$trustedDomains = $this->getTrustedDomainsConfig();
		if (!array_key_exists($index, $trustedDomains)) {
			return new DataResponse(['message' => 'Trusted domain not found'], Http::STATUS_NOT_FOUND);
		}

		$removedDomain = (string)$trustedDomains[$index];
		unset($trustedDomains[$index]);
		$trustedDomains = array_values($trustedDomains);
		if ($trustedDomains === []) {
			$trustedDomains = ['localhost'];
		}

		$this->config->setSystemValue('trusted_domains', $trustedDomains);
		$this->logger->info('Removed trusted domain through admin settings', [
			'app' => Application::APP_ID,
			'domain' => $removedDomain,
			'userId' => $this->userId,
		]);

		return new DataResponse($this->buildTrustedDomainsPayload());
	}

	#[AdminRequired]
	public function autoAddCurrentTrustedDomain(): DataResponse {
		$currentHost = $this->getCurrentRequestHost();
		if ($currentHost === null) {
			return new DataResponse(['message' => 'Current request host is unavailable'], Http::STATUS_BAD_REQUEST);
		}

		return $this->addTrustedDomain($currentHost);
	}

	/**
	 * @return array{currentHost: string, domains: list<string>}
	 */
	private function buildTrustedDomainsPayload(): array {
		return [
			'currentHost' => $this->getCurrentRequestHost() ?? '',
			'domains' => $this->getTrustedDomainsConfig(),
		];
	}

	/**
	 * @return list<string>
	 */
	private function getTrustedDomainsConfig(): array {
		$trustedDomains = $this->config->getSystemValue('trusted_domains', []);
		if (!is_array($trustedDomains)) {
			return [];
		}

		return array_values(array_filter(array_map(static fn (mixed $domain): string => trim((string)$domain), $trustedDomains), static fn (string $domain): bool => $domain !== ''));
	}

	private function getCurrentRequestHost(): ?string {
		$host = $this->request->getHeader('host') ?? '';
		if ($host === '') {
			return null;
		}

		$segments = explode(',', $host);
		return $this->normalizeTrustedDomain(trim($segments[0]));
	}

	private function normalizeTrustedDomain(string $domain): ?string {
		$domain = strtolower(trim($domain));
		if ($domain === '' || str_contains($domain, '*') || str_contains($domain, '://')) {
			return null;
		}

		$parsedDomain = parse_url('http://' . $domain);
		if (!is_array($parsedDomain)
			|| !isset($parsedDomain['host'])
			|| isset($parsedDomain['user'])
			|| isset($parsedDomain['pass'])
			|| isset($parsedDomain['query'])
			|| isset($parsedDomain['fragment'])
			|| (($parsedDomain['path'] ?? '') !== '' && ($parsedDomain['path'] ?? '') !== '/')) {
			return null;
		}

		$host = strtolower((string)$parsedDomain['host']);
		$unwrappedHost = trim($host, '[]');
		$port = $parsedDomain['port'] ?? null;
		$isIpAddress = filter_var($unwrappedHost, FILTER_VALIDATE_IP) !== false;
		$isLocalhost = $host === 'localhost';
		$isHostname = preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)(?:\.(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?))*$/', $host) === 1;

		if (!$isIpAddress && !$isLocalhost && !$isHostname) {
			return null;
		}
		if ($port !== null && ($port < 1 || $port > 65535)) {
			return null;
		}

		$normalizedHost = $host;
		if ($isIpAddress && str_contains($unwrappedHost, ':')) {
			$normalizedHost = '[' . $unwrappedHost . ']';
		}

		return $normalizedHost . ($port !== null ? ':' . $port : '');
	}
}
