<!--
  - SPDX-FileCopyrightText: 2026 DK Consultants & Technologies Corp and MoreDKon contributors
  - SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<div class="custom-headers-settings">
		<h3>
			{{ t('assistant', 'Custom LLM Request Headers') }}
		</h3>
		<NcNoteCard type="info">
			{{ t('assistant', 'Configure custom HTTP headers to be sent with LLM provider requests. Useful for authentication, rate limiting, or custom routing.') }}
		</NcNoteCard>

		<!-- Provider Selection -->
		<div class="header-controls">
			<div class="provider-select">
				<label for="provider-select">{{ t('assistant', 'Select Provider') }}</label>
				<NcSelect id="provider-select"
					v-model="selectedProvider"
					:options="providerOptions"
					:placeholder="t('assistant', 'Choose a provider...')"
					@input="loadHeaders" />
			</div>

			<NcButton v-if="selectedProvider"
				type="primary"
				@click="showAddHeaderDialog = true">
				<template #icon>
					<PlusIcon :size="20" />
				</template>
				{{ t('assistant', 'Add Header') }}
			</NcButton>
		</div>

		<!-- Headers Table -->
		<div v-if="selectedProvider && headers.length > 0" class="headers-table">
			<table>
				<thead>
					<tr>
						<th>{{ t('assistant', 'Header Name') }}</th>
						<th>{{ t('assistant', 'Header Value') }}</th>
						<th>{{ t('assistant', 'Encrypted') }}</th>
						<th>{{ t('assistant', 'Actions') }}</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="header in headers" :key="header.id">
						<td>{{ header.name }}</td>
						<td>
							<span v-if="header.encrypted" class="encrypted-value">
								{{ '•'.repeat(20) }}
							</span>
							<span v-else>{{ header.value }}</span>
						</td>
						<td>
							<CheckIcon v-if="header.encrypted"
								:size="20"
								class="success-icon" />
							<span v-else>—</span>
						</td>
						<td>
							<div class="action-buttons">
								<NcButton type="tertiary"
									@click="editHeader(header)">
									<template #icon>
										<PencilIcon :size="20" />
									</template>
								</NcButton>
								<NcButton type="error"
									@click="deleteHeader(header)">
									<template #icon>
										<DeleteIcon :size="20" />
									</template>
								</NcButton>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<NcNoteCard v-else-if="selectedProvider && headers.length === 0" type="info">
			{{ t('assistant', 'No custom headers configured for this provider. Click "Add Header" to get started.') }}
		</NcNoteCard>

		<!-- Add/Edit Header Dialog -->
		<NcDialog v-if="showAddHeaderDialog"
			:name="editingHeader ? t('assistant', 'Edit Header') : t('assistant', 'Add Header')"
			@close="closeHeaderDialog">
			<div class="header-dialog">
				<div class="dialog-field">
					<label for="header-name">{{ t('assistant', 'Header Name') }}</label>
					<NcTextField id="header-name"
						v-model="newHeader.name"
						:placeholder="t('assistant', 'e.g., X-API-Key, Authorization')"
						@keyup.enter="saveHeader" />
				</div>

				<div class="dialog-field">
					<label for="header-value">{{ t('assistant', 'Header Value') }}</label>
					<NcTextField id="header-value"
						v-model="newHeader.value"
						:type="showHeaderValue ? 'text' : 'password'"
						:placeholder="t('assistant', 'Enter header value...')"
						@keyup.enter="saveHeader">
						<template #trailing-button-icon>
							<EyeIcon v-if="!showHeaderValue" :size="20" />
							<EyeOffIcon v-else :size="20" />
						</template>
						<template #trailing-button>
							<NcButton type="tertiary"
								@click="showHeaderValue = !showHeaderValue">
								{{ showHeaderValue ? t('assistant', 'Hide') : t('assistant', 'Show') }}
							</NcButton>
						</template>
					</NcTextField>
				</div>

				<div class="dialog-field">
					<NcCheckboxRadioSwitch v-model="newHeader.encrypted">
						{{ t('assistant', 'Encrypt this header value') }}
					</NcCheckboxRadioSwitch>
					<p class="hint">
						{{ t('assistant', 'Recommended for sensitive values like API keys and tokens') }}
					</p>
				</div>

				<div class="dialog-actions">
					<NcButton @click="closeHeaderDialog">
						{{ t('assistant', 'Cancel') }}
					</NcButton>
					<NcButton type="primary"
						@click="saveHeader">
						{{ editingHeader ? t('assistant', 'Update') : t('assistant', 'Add') }}
					</NcButton>
				</div>
			</div>
		</NcDialog>

		<!-- Test Connection -->
		<div v-if="selectedProvider" class="test-connection">
			<NcButton type="secondary"
				:disabled="testing"
				@click="testConnection">
				<template #icon>
					<LoadingIcon v-if="testing" :size="20" />
					<CheckNetworkIcon v-else :size="20" />
				</template>
				{{ testing ? t('assistant', 'Testing...') : t('assistant', 'Test Connection') }}
			</NcButton>
		</div>
	</div>
</template>

<script>
import NcButton from '@nextcloud/vue/components/NcButton'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import NcDialog from '@nextcloud/vue/components/NcDialog'

import PlusIcon from 'vue-material-design-icons/Plus.vue'
import DeleteIcon from 'vue-material-design-icons/Delete.vue'
import PencilIcon from 'vue-material-design-icons/Pencil.vue'
import CheckIcon from 'vue-material-design-icons/Check.vue'
import EyeIcon from 'vue-material-design-icons/Eye.vue'
import EyeOffIcon from 'vue-material-design-icons/EyeOff.vue'
import CheckNetworkIcon from 'vue-material-design-icons/CheckNetwork.vue'
import LoadingIcon from 'vue-material-design-icons/Loading.vue'

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showSuccess, showError } from '@nextcloud/dialogs'

export default {
	name: 'CustomHeadersSettings',

	components: {
		NcButton,
		NcSelect,
		NcTextField,
		NcCheckboxRadioSwitch,
		NcNoteCard,
		NcDialog,
		PlusIcon,
		DeleteIcon,
		PencilIcon,
		CheckIcon,
		EyeIcon,
		EyeOffIcon,
		CheckNetworkIcon,
		LoadingIcon,
	},

	data() {
		return {
			selectedProvider: null,
			providerOptions: [],
			headers: [],
			showAddHeaderDialog: false,
			editingHeader: null,
			newHeader: {
				name: '',
				value: '',
				encrypted: true,
			},
			showHeaderValue: false,
			testing: false,
		}
	},

	mounted() {
		this.loadProviders()
	},

	methods: {
		async loadProviders() {
			try {
				const response = await axios.get(generateUrl('/apps/assistant/providers'))
				this.providerOptions = response.data.map(provider => ({
					label: provider.name,
					value: provider.id,
				}))
			} catch (error) {
				console.error('Failed to load providers:', error)
				showError(t('assistant', 'Failed to load providers'))
			}
		},

		async loadHeaders() {
			if (!this.selectedProvider) return

			try {
				const response = await axios.get(
					generateUrl('/apps/assistant/custom-headers'),
					{ params: { provider: this.selectedProvider.value } },
				)
				this.headers = response.data
			} catch (error) {
				console.error('Failed to load headers:', error)
				showError(t('assistant', 'Failed to load headers'))
			}
		},

		editHeader(header) {
			this.editingHeader = header
			this.newHeader = {
				name: header.name,
				value: '', // Don't populate encrypted values
				encrypted: header.encrypted,
			}
			this.showAddHeaderDialog = true
		},

		async deleteHeader(header) {
			try {
				await axios.delete(
					generateUrl('/apps/assistant/custom-headers/{id}', { id: header.id }),
				)
				this.loadHeaders()
				showSuccess(t('assistant', 'Header deleted successfully'))
			} catch (error) {
				console.error('Failed to delete header:', error)
				showError(t('assistant', 'Failed to delete header'))
			}
		},

		async saveHeader() {
			// Validate inputs
			if (!this.newHeader.name || !this.newHeader.value) {
				showError(t('assistant', 'Please fill in all fields'))
				return
			}

			try {
				if (this.editingHeader) {
					// Update existing header
					await axios.put(
						generateUrl('/apps/assistant/custom-headers/{id}', { id: this.editingHeader.id }),
						{
							name: this.newHeader.name,
							value: this.newHeader.value,
							encrypted: this.newHeader.encrypted,
						},
					)
					showSuccess(t('assistant', 'Header updated successfully'))
				} else {
					// Create new header
					await axios.post(
						generateUrl('/apps/assistant/custom-headers'),
						{
							provider: this.selectedProvider.value,
							name: this.newHeader.name,
							value: this.newHeader.value,
							encrypted: this.newHeader.encrypted,
						},
					)
					showSuccess(t('assistant', 'Header added successfully'))
				}

				this.closeHeaderDialog()
				this.loadHeaders()
			} catch (error) {
				console.error('Failed to save header:', error)
				showError(t('assistant', 'Failed to save header'))
			}
		},

		closeHeaderDialog() {
			this.showAddHeaderDialog = false
			this.editingHeader = null
			this.newHeader = {
				name: '',
				value: '',
				encrypted: true,
			}
			this.showHeaderValue = false
		},

		async testConnection() {
			if (!this.selectedProvider) return

			this.testing = true
			try {
				const response = await axios.post(
					generateUrl('/apps/assistant/custom-headers/test'),
					{ provider: this.selectedProvider.value },
				)

				if (response.data.success) {
					showSuccess(t('assistant', 'Connection test successful'))
				} else {
					showError(t('assistant', 'Connection test failed: {error}', { error: response.data.error }))
				}
			} catch (error) {
				console.error('Failed to test connection:', error)
				showError(t('assistant', 'Connection test failed'))
			} finally {
				this.testing = false
			}
		},
	},
}
</script>

<style scoped lang="scss">
.custom-headers-settings {
	margin-bottom: 30px;

	h3 {
		margin-bottom: 10px;
	}

	.header-controls {
		display: flex;
		gap: 15px;
		align-items: flex-end;
		margin-top: 20px;
		margin-bottom: 20px;

		.provider-select {
			flex: 1;

			label {
				display: block;
				font-weight: bold;
				margin-bottom: 8px;
			}
		}
	}

	.headers-table {
		margin-top: 20px;
		overflow-x: auto;

		table {
			width: 100%;
			border-collapse: collapse;
			border: 1px solid var(--color-border);

			th, td {
				padding: 12px;
				text-align: left;
				border-bottom: 1px solid var(--color-border);
			}

			th {
				background-color: var(--color-background-dark);
				font-weight: bold;
			}

			.encrypted-value {
				color: var(--color-text-maxcontrast);
			}

			.success-icon {
				color: var(--color-success);
			}

			.action-buttons {
				display: flex;
				gap: 5px;
			}
		}
	}

	.header-dialog {
		padding: 20px;

		.dialog-field {
			margin-bottom: 20px;

			label {
				display: block;
				font-weight: bold;
				margin-bottom: 8px;
			}

			.hint {
				margin-top: 5px;
				color: var(--color-text-maxcontrast);
				font-size: 0.9em;
			}
		}

		.dialog-actions {
			display: flex;
			justify-content: flex-end;
			gap: 10px;
			margin-top: 20px;
		}
	}

	.test-connection {
		margin-top: 20px;
	}
}
</style>
