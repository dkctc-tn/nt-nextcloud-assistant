<!--
  - SPDX-FileCopyrightText: 2026 DK Consultants & Technologies Corp and MoreDKon contributors
  - SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<div class="section admin-system-settings">
		<h2>{{ t('assistant', 'System Configuration') }}</h2>
		<NcNoteCard type="info">
			{{ t('assistant', 'Manage trusted domains for this Nextcloud instance without editing config.php by hand.') }}
		</NcNoteCard>

		<div class="current-host-card">
			<div>
				<h3>{{ t('assistant', 'Current request host') }}</h3>
				<p class="host-value">
					<code>{{ currentHostDisplay }}</code>
				</p>
			</div>
			<NcButton type="primary"
				:disabled="loading || !currentHost"
				@click="autoAddCurrentDomain">
				<template #icon>
					<PlusIcon :size="20" />
				</template>
				{{ t('assistant', 'Quick Add') }}
			</NcButton>
		</div>

		<div class="add-domain-row">
			<NcTextField v-model.trim="newDomain"
				:label="t('assistant', 'Trusted domain')"
				:placeholder="browserHostname || t('assistant', 'example.com')"
				:disabled="loading"
				@keyup.enter="addTrustedDomain()"
				@update:model-value="clearMessages" />
			<NcButton type="primary"
				:disabled="loading || !newDomain"
				@click="addTrustedDomain()">
				<template #icon>
					<PlusIcon :size="20" />
				</template>
				{{ t('assistant', 'Add') }}
			</NcButton>
		</div>

		<NcNoteCard v-if="errorMessage" type="error">
			{{ errorMessage }}
		</NcNoteCard>
		<NcNoteCard v-else-if="successMessage" type="success">
			{{ successMessage }}
		</NcNoteCard>

		<div class="domains-list">
			<h3>{{ t('assistant', 'Trusted domains') }}</h3>
			<NcNoteCard v-if="!domains.length && !loading" type="warning">
				{{ t('assistant', 'No trusted domains are configured yet.') }}
			</NcNoteCard>
			<ul v-else class="domain-items">
				<li v-for="(domain, index) in domains"
					:key="`${domain}-${index}`"
					class="domain-item">
					<code>{{ domain }}</code>
					<NcButton type="tertiary"
						:disabled="loading"
						@click="removeTrustedDomain(index)">
						<template #icon>
							<DeleteIcon :size="20" />
						</template>
						{{ t('assistant', 'Delete') }}
					</NcButton>
				</li>
			</ul>
		</div>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import DeleteIcon from 'vue-material-design-icons/Delete.vue'
import PlusIcon from 'vue-material-design-icons/Plus.vue'

const ntAppName = 'assistant'

export default {
	name: 'AdminSystemSettings',

	components: {
		DeleteIcon,
		NcButton,
		NcNoteCard,
		NcTextField,
		PlusIcon,
	},

	data() {
		return {
			currentHost: window.location.host || '',
			domains: [],
			errorMessage: '',
			loading: false,
			newDomain: '',
			successMessage: '',
		}
	},

	computed: {
		browserHostname() {
			return window.location.hostname || ''
		},
		currentHostDisplay() {
			return this.currentHost || this.t('assistant', 'Unavailable')
		},
	},

	mounted() {
		this.fetchTrustedDomains()
	},

	methods: {
		applyResponse(payload) {
			this.domains = payload.domains ?? []
			this.currentHost = payload.currentHost || this.currentHost
		},
		clearMessages() {
			this.errorMessage = ''
			this.successMessage = ''
		},
		getErrorMessage(error, fallback) {
			return error?.response?.data?.message || fallback
		},
		async fetchTrustedDomains() {
			this.loading = true
			this.clearMessages()
			try {
				const response = await axios.get(generateUrl(`/apps/${ntAppName}/api/config/trusted-domains`))
				this.applyResponse(response.data)
			} catch (error) {
				this.errorMessage = this.getErrorMessage(error, this.t('assistant', 'Failed to load trusted domains'))
				showError(this.errorMessage)
			} finally {
				this.loading = false
			}
		},
		async addTrustedDomain(domain = this.newDomain) {
			const normalizedDomain = domain.trim()
			if (!normalizedDomain) {
				return
			}

			this.loading = true
			this.clearMessages()
			try {
				const response = await axios.post(generateUrl(`/apps/${ntAppName}/api/config/trusted-domains`), {
					domain: normalizedDomain,
				})
				this.applyResponse(response.data)
				this.newDomain = ''
				this.successMessage = this.t('assistant', 'Trusted domain added')
				showSuccess(this.successMessage)
			} catch (error) {
				this.errorMessage = this.getErrorMessage(error, this.t('assistant', 'Failed to add trusted domain'))
				showError(this.errorMessage)
			} finally {
				this.loading = false
			}
		},
		async removeTrustedDomain(index) {
			this.loading = true
			this.clearMessages()
			try {
				const response = await axios.delete(generateUrl(`/apps/${ntAppName}/api/config/trusted-domains/{index}`, {
					index,
				}))
				this.applyResponse(response.data)
				this.successMessage = this.t('assistant', 'Trusted domain removed')
				showSuccess(this.successMessage)
			} catch (error) {
				this.errorMessage = this.getErrorMessage(error, this.t('assistant', 'Failed to remove trusted domain'))
				showError(this.errorMessage)
			} finally {
				this.loading = false
			}
		},
		async autoAddCurrentDomain() {
			this.loading = true
			this.clearMessages()
			try {
				const response = await axios.post(generateUrl(`/apps/${ntAppName}/api/config/trusted-domains/auto-add-current`))
				this.applyResponse(response.data)
				this.successMessage = this.t('assistant', 'Current request host added')
				showSuccess(this.successMessage)
			} catch (error) {
				this.errorMessage = this.getErrorMessage(error, this.t('assistant', 'Failed to add the current request host'))
				showError(this.errorMessage)
			} finally {
				this.loading = false
			}
		},
	},
}
</script>

<style scoped>
.admin-system-settings {
	display: flex;
	flex-direction: column;
	gap: 16px;
}

.current-host-card,
.add-domain-row,
.domain-item {
	display: flex;
	align-items: center;
	gap: 12px;
}

.current-host-card,
.domains-list {
	padding: 16px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
}

.current-host-card {
	justify-content: space-between;
	flex-wrap: wrap;
}

.current-host-card h3,
.domains-list h3 {
	margin: 0 0 8px;
}

.host-value {
	margin: 0;
}

.add-domain-row :deep(.text-field__main-wrapper),
.add-domain-row :deep(.input-field) {
	width: 100%;
}

.add-domain-row :deep(.text-field) {
	flex: 1;
}

.domain-items {
	display: flex;
	flex-direction: column;
	gap: 12px;
	padding: 0;
	margin: 0;
	list-style: none;
}

.domain-item {
	justify-content: space-between;
	padding: 12px 0;
	border-top: 1px solid var(--color-border);
}

.domain-item:first-child {
	padding-top: 0;
	border-top: 0;
}

.domain-item code,
.host-value code {
	font-size: 14px;
	word-break: break-all;
}
</style>
