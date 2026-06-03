<!--
  - SPDX-FileCopyrightText: 2026 DK Consultants & Technologies Corp and MoreDKon contributors
  - SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<div class="mcp-provider-settings">
		<h3>
			{{ t('assistant', 'MCP Tool Providers') }}
		</h3>
		<NcNoteCard type="info">
			{{ t('assistant', 'Configure Model Context Protocol (MCP) providers to extend Assistant capabilities with external tools and integrations.') }}
		</NcNoteCard>

		<!-- MCP Providers List -->
		<div class="providers-list">
			<div v-for="provider in mcpProviders"
				:key="provider.id"
				class="provider-card"
				:class="{ 'provider-enabled': provider.enabled, 'provider-disabled': !provider.enabled }">
				<div class="provider-header">
					<div class="provider-info">
						<h4>{{ provider.name }}</h4>
						<p class="provider-description">{{ provider.description }}</p>
					</div>
					<div class="provider-toggle">
						<NcCheckboxRadioSwitch :checked.sync="provider.enabled"
							@update:checked="toggleProvider(provider)">
							{{ provider.enabled ? t('assistant', 'Enabled') : t('assistant', 'Disabled') }}
						</NcCheckboxRadioSwitch>
					</div>
				</div>

				<!-- Provider Configuration (shown when enabled) -->
				<div v-if="provider.enabled" class="provider-config">
					<div class="config-field">
						<label :for="`endpoint-${provider.id}`">{{ t('assistant', 'Endpoint URL') }}</label>
						<NcTextField :id="`endpoint-${provider.id}`"
							v-model="provider.endpoint"
							:placeholder="t('assistant', 'https://api.example.com/mcp')"
							@update:model-value="updateProvider(provider)" />
					</div>

					<!-- Authentication Type -->
					<div class="config-field">
						<label :for="`auth-type-${provider.id}`">{{ t('assistant', 'Authentication Type') }}</label>
						<NcSelect :id="`auth-type-${provider.id}`"
							v-model="provider.auth_type"
							:options="authTypeOptions"
							@input="updateProvider(provider)" />
					</div>

					<!-- API Key Auth -->
					<div v-if="provider.auth_type?.value === 'api_key'" class="config-field">
						<label :for="`api-key-${provider.id}`">{{ t('assistant', 'API Key') }}</label>
						<NcTextField :id="`api-key-${provider.id}`"
							v-model="provider.api_key"
							:type="showApiKey[provider.id] ? 'text' : 'password'"
							:placeholder="t('assistant', 'Enter API key...')"
							@update:model-value="updateProvider(provider)">
							<template #trailing-button-icon>
								<EyeIcon v-if="!showApiKey[provider.id]" :size="20" />
								<EyeOffIcon v-else :size="20" />
							</template>
							<template #trailing-button>
								<NcButton type="tertiary"
									@click="toggleApiKeyVisibility(provider.id)">
									{{ showApiKey[provider.id] ? t('assistant', 'Hide') : t('assistant', 'Show') }}
								</NcButton>
							</template>
						</NcTextField>
					</div>

					<!-- OAuth Configuration -->
					<div v-if="provider.auth_type?.value === 'oauth'" class="config-field">
						<div class="oauth-config">
							<NcButton v-if="!provider.oauth_connected"
								type="primary"
								@click="connectOAuth(provider)">
								<template #icon>
									<LinkIcon :size="20" />
								</template>
								{{ t('assistant', 'Connect with OAuth') }}
							</NcButton>
							<div v-else class="oauth-status">
								<CheckCircleIcon :size="20" class="success-icon" />
								<span>{{ t('assistant', 'Connected') }}</span>
								<NcButton type="tertiary"
									@click="disconnectOAuth(provider)">
									{{ t('assistant', 'Disconnect') }}
								</NcButton>
							</div>
						</div>
					</div>

					<!-- Test Connection -->
					<div class="config-actions">
						<NcButton type="secondary"
							:disabled="testingProvider[provider.id]"
							@click="testProviderConnection(provider)">
							<template #icon>
								<LoadingIcon v-if="testingProvider[provider.id]" :size="20" />
								<CheckNetworkIcon v-else :size="20" />
							</template>
							{{ testingProvider[provider.id] ? t('assistant', 'Testing...') : t('assistant', 'Test Connection') }}
						</NcButton>

						<NcButton v-if="provider.enabled"
							@click="refreshCapabilities(provider)">
							<template #icon>
								<RefreshIcon :size="20" />
							</template>
							{{ t('assistant', 'Refresh Capabilities') }}
						</NcButton>
					</div>

					<!-- Available Tools -->
					<div v-if="provider.tools && provider.tools.length > 0" class="provider-tools">
						<h5>{{ t('assistant', 'Available Tools') }}</h5>
						<div class="tools-list">
							<div v-for="tool in provider.tools"
								:key="tool.id"
								class="tool-item">
								<ToolIcon :size="16" />
								<span class="tool-name">{{ tool.name }}</span>
								<span class="tool-description">{{ tool.description }}</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Add Custom Provider -->
		<div class="add-provider">
			<NcButton @click="showAddProviderDialog = true">
				<template #icon>
					<PlusIcon :size="20" />
				</template>
				{{ t('assistant', 'Add Custom MCP Provider') }}
			</NcButton>
		</div>

		<!-- Add Provider Dialog -->
		<NcDialog v-if="showAddProviderDialog"
			:name="t('assistant', 'Add Custom MCP Provider')"
			@close="closeAddProviderDialog">
			<div class="add-provider-dialog">
				<div class="dialog-field">
					<label for="new-provider-name">{{ t('assistant', 'Provider Name') }}</label>
					<NcTextField id="new-provider-name"
						v-model="newProvider.name"
						:placeholder="t('assistant', 'My Custom Provider')" />
				</div>

				<div class="dialog-field">
					<label for="new-provider-description">{{ t('assistant', 'Description') }}</label>
					<NcTextArea id="new-provider-description"
						v-model="newProvider.description"
						:placeholder="t('assistant', 'Brief description of this provider...')"
						rows="3" />
				</div>

				<div class="dialog-field">
					<label for="new-provider-endpoint">{{ t('assistant', 'Endpoint URL') }}</label>
					<NcTextField id="new-provider-endpoint"
						v-model="newProvider.endpoint"
						:placeholder="t('assistant', 'https://api.example.com/mcp')" />
				</div>

				<div class="dialog-actions">
					<NcButton @click="closeAddProviderDialog">
						{{ t('assistant', 'Cancel') }}
					</NcButton>
					<NcButton type="primary"
						@click="addCustomProvider">
						{{ t('assistant', 'Add Provider') }}
					</NcButton>
				</div>
			</div>
		</NcDialog>
	</div>
</template>

<script>
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js'
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js'
import NcTextArea from '@nextcloud/vue/dist/Components/NcTextArea.js'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import NcNoteCard from '@nextcloud/vue/dist/Components/NcNoteCard.js'
import NcDialog from '@nextcloud/vue/dist/Components/NcDialog.js'

import PlusIcon from 'vue-material-design-icons/Plus.vue'
import LinkIcon from 'vue-material-design-icons/Link.vue'
import CheckCircleIcon from 'vue-material-design-icons/CheckCircle.vue'
import CheckNetworkIcon from 'vue-material-design-icons/CheckNetwork.vue'
import RefreshIcon from 'vue-material-design-icons/Refresh.vue'
import LoadingIcon from 'vue-material-design-icons/Loading.vue'
import ToolIcon from 'vue-material-design-icons/Tool.vue'
import EyeIcon from 'vue-material-design-icons/Eye.vue'
import EyeOffIcon from 'vue-material-design-icons/EyeOff.vue'

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showSuccess, showError } from '@nextcloud/dialogs'

export default {
	name: 'MCPProviderSettings',

	components: {
		NcButton,
		NcSelect,
		NcTextField,
		NcTextArea,
		NcCheckboxRadioSwitch,
		NcNoteCard,
		NcDialog,
		PlusIcon,
		LinkIcon,
		CheckCircleIcon,
		CheckNetworkIcon,
		RefreshIcon,
		LoadingIcon,
		ToolIcon,
		EyeIcon,
		EyeOffIcon,
	},

	data() {
		return {
			mcpProviders: [],
			authTypeOptions: [
				{ label: t('assistant', 'None'), value: 'none' },
				{ label: t('assistant', 'API Key'), value: 'api_key' },
				{ label: t('assistant', 'OAuth 2.0'), value: 'oauth' },
			],
			showApiKey: {},
			testingProvider: {},
			showAddProviderDialog: false,
			newProvider: {
				name: '',
				description: '',
				endpoint: '',
			},
		}
	},

	mounted() {
		this.loadMCPProviders()
	},

	methods: {
		async loadMCPProviders() {
			try {
				const response = await axios.get(generateUrl('/apps/assistant/mcp/providers'))
				this.mcpProviders = response.data
			} catch (error) {
				console.error('Failed to load MCP providers:', error)
				showError(t('assistant', 'Failed to load MCP providers'))
			}
		},

		async toggleProvider(provider) {
			try {
				await axios.put(
					generateUrl('/apps/assistant/mcp/providers/{id}', { id: provider.id }),
					{ enabled: provider.enabled }
				)
				showSuccess(
					provider.enabled
						? t('assistant', 'Provider enabled')
						: t('assistant', 'Provider disabled')
				)
			} catch (error) {
				console.error('Failed to toggle provider:', error)
				showError(t('assistant', 'Failed to update provider'))
				// Revert the toggle
				provider.enabled = !provider.enabled
			}
		},

		async updateProvider(provider) {
			try {
				await axios.put(
					generateUrl('/apps/assistant/mcp/providers/{id}', { id: provider.id }),
					provider
				)
			} catch (error) {
				console.error('Failed to update provider:', error)
				showError(t('assistant', 'Failed to update provider'))
			}
		},

		toggleApiKeyVisibility(providerId) {
			this.$set(this.showApiKey, providerId, !this.showApiKey[providerId])
		},

		async testProviderConnection(provider) {
			this.$set(this.testingProvider, provider.id, true)
			try {
				const response = await axios.post(
					generateUrl('/apps/assistant/mcp/providers/{id}/test', { id: provider.id })
				)

				if (response.data.success) {
					showSuccess(t('assistant', 'Connection test successful'))
				} else {
					showError(t('assistant', 'Connection test failed: {error}', { error: response.data.error }))
				}
			} catch (error) {
				console.error('Failed to test provider:', error)
				showError(t('assistant', 'Connection test failed'))
			} finally {
				this.$set(this.testingProvider, provider.id, false)
			}
		},

		async refreshCapabilities(provider) {
			try {
				const response = await axios.post(
					generateUrl('/apps/assistant/mcp/providers/{id}/capabilities', { id: provider.id })
				)
				provider.tools = response.data.tools
				showSuccess(t('assistant', 'Capabilities refreshed'))
			} catch (error) {
				console.error('Failed to refresh capabilities:', error)
				showError(t('assistant', 'Failed to refresh capabilities'))
			}
		},

		async connectOAuth(provider) {
			try {
				const response = await axios.get(
					generateUrl('/apps/assistant/mcp/providers/{id}/oauth', { id: provider.id })
				)
				// Open OAuth flow in new window
				window.open(response.data.auth_url, '_blank', 'width=600,height=700')
				// Poll for connection status
				this.pollOAuthStatus(provider)
			} catch (error) {
				console.error('Failed to start OAuth flow:', error)
				showError(t('assistant', 'Failed to start OAuth connection'))
			}
		},

		async disconnectOAuth(provider) {
			try {
				await axios.delete(
					generateUrl('/apps/assistant/mcp/providers/{id}/oauth', { id: provider.id })
				)
				provider.oauth_connected = false
				showSuccess(t('assistant', 'OAuth disconnected'))
			} catch (error) {
				console.error('Failed to disconnect OAuth:', error)
				showError(t('assistant', 'Failed to disconnect OAuth'))
			}
		},

		pollOAuthStatus(provider) {
			const pollInterval = setInterval(async () => {
				try {
					const response = await axios.get(
						generateUrl('/apps/assistant/mcp/providers/{id}/oauth/status', { id: provider.id })
					)
					if (response.data.connected) {
						provider.oauth_connected = true
						showSuccess(t('assistant', 'OAuth connected successfully'))
						clearInterval(pollInterval)
					}
				} catch (error) {
					clearInterval(pollInterval)
				}
			}, 2000)

			// Stop polling after 5 minutes
			setTimeout(() => clearInterval(pollInterval), 300000)
		},

		closeAddProviderDialog() {
			this.showAddProviderDialog = false
			this.newProvider = {
				name: '',
				description: '',
				endpoint: '',
			}
		},

		async addCustomProvider() {
			if (!this.newProvider.name || !this.newProvider.endpoint) {
				showError(t('assistant', 'Please fill in all required fields'))
				return
			}

			try {
				await axios.post(
					generateUrl('/apps/assistant/mcp/providers'),
					this.newProvider
				)
				showSuccess(t('assistant', 'Custom provider added'))
				this.closeAddProviderDialog()
				this.loadMCPProviders()
			} catch (error) {
				console.error('Failed to add custom provider:', error)
				showError(t('assistant', 'Failed to add custom provider'))
			}
		},
	},
}
</script>

<style scoped lang="scss">
.mcp-provider-settings {
	margin-bottom: 30px;

	h3 {
		margin-bottom: 10px;
	}

	.providers-list {
		margin-top: 20px;

		.provider-card {
			border: 2px solid var(--color-border);
			border-radius: var(--border-radius-large);
			padding: 20px;
			margin-bottom: 15px;
			transition: border-color 0.2s;

			&.provider-enabled {
				border-color: var(--color-success);
			}

			&.provider-disabled {
				opacity: 0.7;
			}

			.provider-header {
				display: flex;
				justify-content: space-between;
				align-items: flex-start;
				margin-bottom: 15px;

				.provider-info {
					flex: 1;

					h4 {
						margin: 0 0 5px 0;
					}

					.provider-description {
						color: var(--color-text-maxcontrast);
						margin: 0;
					}
				}

				.provider-toggle {
					margin-left: 20px;
				}
			}

			.provider-config {
				border-top: 1px solid var(--color-border);
				padding-top: 15px;

				.config-field {
					margin-bottom: 15px;

					label {
						display: block;
						font-weight: bold;
						margin-bottom: 5px;
					}

					.oauth-config {
						.oauth-status {
							display: flex;
							align-items: center;
							gap: 10px;

							.success-icon {
								color: var(--color-success);
							}
						}
					}
				}

				.config-actions {
					display: flex;
					gap: 10px;
					margin-top: 15px;
				}

				.provider-tools {
					margin-top: 20px;
					padding-top: 15px;
					border-top: 1px solid var(--color-border);

					h5 {
						margin-bottom: 10px;
					}

					.tools-list {
						display: grid;
						gap: 8px;

						.tool-item {
							display: flex;
							align-items: center;
							gap: 8px;
							padding: 8px;
							background: var(--color-background-dark);
							border-radius: var(--border-radius);

							.tool-name {
								font-weight: bold;
								min-width: 150px;
							}

							.tool-description {
								color: var(--color-text-maxcontrast);
								font-size: 0.9em;
							}
						}
					}
				}
			}
		}
	}

	.add-provider {
		margin-top: 20px;
	}

	.add-provider-dialog {
		padding: 20px;

		.dialog-field {
			margin-bottom: 20px;

			label {
				display: block;
				font-weight: bold;
				margin-bottom: 8px;
			}
		}

		.dialog-actions {
			display: flex;
			justify-content: flex-end;
			gap: 10px;
			margin-top: 20px;
		}
	}
}
</style>
