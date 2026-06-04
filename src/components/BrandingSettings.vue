<!--
  - SPDX-FileCopyrightText: 2026 DK Consultants & Technologies Corp and MoreDKon contributors
  - SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<div class="branding-settings">
		<h3>
			{{ t('assistant', 'Custom Branding') }}
		</h3>
		<NcNoteCard type="info">
			{{ t('assistant', 'Customize the appearance and branding of the NormieTranslator Assistant interface.') }}
		</NcNoteCard>
		<div class="branding-form">
			<!-- App Name -->
			<div class="branding-field">
				<label for="app-name">{{ t('assistant', 'Application Name') }}</label>
				<NcTextField id="app-name"
					:value.sync="branding.app_name"
					:placeholder="t('assistant', 'NormieTranslator Assistant')"
					@update:value="updateBranding" />
				<p class="hint">
					{{ t('assistant', 'Custom name displayed throughout the interface') }}
				</p>
			</div>

			<!-- Header Text -->
			<div class="branding-field">
				<label for="header-text">{{ t('assistant', 'Header Text') }}</label>
				<NcTextArea id="header-text"
					:value.sync="branding.header_text"
					:placeholder="t('assistant', 'Welcome to NormieTranslator')"
					rows="3"
					@update:value="updateBranding" />
				<p class="hint">
					{{ t('assistant', 'Custom greeting or header message for users') }}
				</p>
			</div>

			<!-- Primary Color -->
			<div class="branding-field">
				<label for="app-color">{{ t('assistant', 'Primary Color') }}</label>
				<div class="color-picker-wrapper">
					<input id="app-color"
						v-model="branding.app_color"
						type="color"
						@change="updateBranding">
					<NcTextField :value="branding.app_color"
						:placeholder="t('assistant', '#0082C9')"
						@update:value="onColorTextChange" />
				</div>
				<p class="hint">
					{{ t('assistant', 'Primary theme color for the interface') }}
				</p>
			</div>

			<!-- Logo Upload -->
			<div class="branding-field">
				<label for="app-logo">{{ t('assistant', 'Custom Logo') }}</label>
				<div class="logo-upload-wrapper">
					<img v-if="branding.app_logo"
						:src="branding.app_logo"
						alt="Logo preview"
						class="logo-preview">
					<NcButton @click="openLogoUpload">
						<template #icon>
							<UploadIcon :size="20" />
						</template>
						{{ branding.app_logo ? t('assistant', 'Change Logo') : t('assistant', 'Upload Logo') }}
					</NcButton>
					<NcButton v-if="branding.app_logo"
						type="error"
						@click="removeLogo">
						<template #icon>
							<DeleteIcon :size="20" />
						</template>
						{{ t('assistant', 'Remove Logo') }}
					</NcButton>
					<input ref="logoInput"
						type="file"
						accept="image/*"
						style="display: none;"
						@change="onLogoChange">
				</div>
				<p class="hint">
					{{ t('assistant', 'Upload a custom logo (recommended: SVG, PNG with transparency)') }}
				</p>
			</div>

			<!-- White Label Mode -->
			<div class="branding-field">
				<NcCheckboxRadioSwitch :checked.sync="branding.white_label_mode"
					@update:checked="updateBranding">
					{{ t('assistant', 'White Label Mode') }}
				</NcCheckboxRadioSwitch>
				<p class="hint">
					{{ t('assistant', 'Hide "Powered by Nextcloud" and similar attributions') }}
				</p>
			</div>

			<!-- Save Status -->
			<div class="save-status">
				<NcNoteCard v-if="saveStatus === 'success'" type="success">
					{{ t('assistant', 'Branding settings saved successfully') }}
				</NcNoteCard>
				<NcNoteCard v-if="saveStatus === 'error'" type="error">
					{{ t('assistant', 'Failed to save branding settings. Please try again.') }}
				</NcNoteCard>
			</div>
		</div>
	</div>
</template>

<script>
import NcButton from '@nextcloud/vue/components/NcButton'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import NcTextArea from '@nextcloud/vue/components/NcTextArea'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'

import UploadIcon from 'vue-material-design-icons/Upload.vue'
import DeleteIcon from 'vue-material-design-icons/Delete.vue'

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showSuccess, showError } from '@nextcloud/dialogs'

export default {
	name: 'BrandingSettings',

	components: {
		NcButton,
		NcTextField,
		NcTextArea,
		NcCheckboxRadioSwitch,
		NcNoteCard,
		UploadIcon,
		DeleteIcon,
	},

	data() {
		return {
			branding: {
				app_name: '',
				app_logo: '',
				app_color: '#0082C9',
				header_text: '',
				white_label_mode: false,
			},
			saveStatus: null,
			saveTimeout: null,
		}
	},

	mounted() {
		this.loadBranding()
	},

	methods: {
		async loadBranding() {
			try {
				const response = await axios.get(generateUrl('/apps/assistant/branding'))
				if (response.data) {
					this.branding = { ...this.branding, ...response.data }
				}
			} catch (error) {
				console.error('Failed to load branding settings:', error)
			}
		},

		async updateBranding() {
			// Clear existing timeout
			if (this.saveTimeout) {
				clearTimeout(this.saveTimeout)
			}

			// Debounce save - wait 1 second after last change
			this.saveTimeout = setTimeout(async () => {
				try {
					await axios.post(generateUrl('/apps/assistant/branding'), this.branding)
					this.saveStatus = 'success'
					showSuccess(t('assistant', 'Branding settings saved'))
					// Clear success message after 3 seconds
					setTimeout(() => {
						this.saveStatus = null
					}, 3000)
				} catch (error) {
					console.error('Failed to save branding settings:', error)
					this.saveStatus = 'error'
					showError(t('assistant', 'Failed to save branding settings'))
				}
			}, 1000)
		},

		onColorTextChange(newColor) {
			// Validate hex color
			if (/^#[0-9A-F]{6}$/i.test(newColor)) {
				this.branding.app_color = newColor
				this.updateBranding()
			}
		},

		openLogoUpload() {
			this.$refs.logoInput.click()
		},

		async onLogoChange(event) {
			const file = event.target.files[0]
			if (!file) return

			// Validate file type
			if (!file.type.startsWith('image/')) {
				showError(t('assistant', 'Please upload an image file'))
				return
			}

			// Validate file size (max 2MB)
			if (file.size > 2 * 1024 * 1024) {
				showError(t('assistant', 'Image file must be smaller than 2MB'))
				return
			}

			try {
				// Upload the logo
				const formData = new FormData()
				formData.append('logo', file)

				const response = await axios.post(
					generateUrl('/apps/assistant/branding/logo'),
					formData,
					{
						headers: {
							'Content-Type': 'multipart/form-data',
						},
					},
				)

				this.branding.app_logo = response.data.url
				this.updateBranding()
				showSuccess(t('assistant', 'Logo uploaded successfully'))
			} catch (error) {
				console.error('Failed to upload logo:', error)
				showError(t('assistant', 'Failed to upload logo'))
			}
		},

		async removeLogo() {
			try {
				await axios.delete(generateUrl('/apps/assistant/branding/logo'))
				this.branding.app_logo = ''
				this.updateBranding()
				showSuccess(t('assistant', 'Logo removed'))
			} catch (error) {
				console.error('Failed to remove logo:', error)
				showError(t('assistant', 'Failed to remove logo'))
			}
		},
	},
}
</script>

<style scoped lang="scss">
.branding-settings {
	margin-bottom: 30px;

	h3 {
		margin-bottom: 10px;
	}

	.branding-form {
		margin-top: 20px;

		.branding-field {
			margin-bottom: 25px;

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

			.color-picker-wrapper {
				display: flex;
				gap: 10px;
				align-items: center;

				input[type="color"] {
					width: 50px;
					height: 38px;
					border: 1px solid var(--color-border);
					border-radius: var(--border-radius);
					cursor: pointer;
				}
			}

			.logo-upload-wrapper {
				display: flex;
				gap: 10px;
				align-items: center;
				flex-wrap: wrap;

				.logo-preview {
					max-width: 200px;
					max-height: 100px;
					border: 1px solid var(--color-border);
					border-radius: var(--border-radius);
					padding: 10px;
					background: var(--color-background-dark);
				}
			}
		}

		.save-status {
			margin-top: 20px;
		}
	}
}
</style>
