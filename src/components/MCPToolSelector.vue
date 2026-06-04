<!--
  - SPDX-FileCopyrightText: 2026 DK Consultants & Technologies Corp and MoreDKon contributors
  - SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<div class="mcp-tool-selector">
		<div class="tool-selector-header">
			<h4>{{ t('assistant', 'MCP Tools') }}</h4>
			<NcButton type="tertiary"
				:aria-label="t('assistant', 'Toggle MCP tools')"
				@click="toggleExpanded">
				<template #icon>
					<ChevronDownIcon v-if="!expanded" :size="20" />
					<ChevronUpIcon v-else :size="20" />
				</template>
			</NcButton>
		</div>

		<div v-if="expanded" class="tool-selector-content">
			<NcNoteCard v-if="!hasEnabledProviders" type="info">
				{{ t('assistant', 'No MCP providers are currently enabled. Enable providers in the admin settings to use MCP tools.') }}
			</NcNoteCard>

			<div v-else class="providers-tools">
				<!-- Provider Tabs -->
				<div v-if="enabledProviders.length > 1" class="provider-tabs">
					<NcButton v-for="provider in enabledProviders"
						:key="provider.id"
						:type="selectedProviderId === provider.id ? 'primary' : 'tertiary'"
						@click="selectProvider(provider.id)">
						{{ provider.name }}
						<template #icon>
							<span class="tool-count">{{ provider.tools?.length || 0 }}</span>
						</template>
					</NcButton>
				</div>

				<!-- Tools List -->
				<div v-if="selectedProvider" class="tools-container">
					<div v-if="!selectedProvider.tools || selectedProvider.tools.length === 0"
						class="no-tools">
						<NcNoteCard type="info">
							{{ t('assistant', 'No tools available for this provider.') }}
						</NcNoteCard>
					</div>

					<div v-else class="tools-grid">
						<div v-for="tool in selectedProvider.tools"
							:key="tool.id"
							class="tool-card"
							:class="{ 'tool-selected': isToolSelected(tool.id) }"
							@click="toggleTool(tool)">
							<div class="tool-header">
								<NcCheckboxRadioSwitch :checked="isToolSelected(tool.id)"
									@update:checked="toggleTool(tool)" />
								<ToolboxIcon :size="20" class="tool-icon" />
								<span class="tool-name">{{ tool.name }}</span>
							</div>
							<p class="tool-description">
								{{ tool.description }}
							</p>
							<div v-if="tool.parameters && tool.parameters.length > 0" class="tool-params">
								<span class="params-label">{{ t('assistant', 'Parameters:') }}</span>
								<span class="params-list">{{ formatParameters(tool.parameters) }}</span>
							</div>
						</div>
					</div>
				</div>

				<!-- Selected Tools Summary -->
				<div v-if="selectedTools.length > 0" class="selected-summary">
					<h5>{{ t('assistant', 'Selected Tools ({count})', { count: selectedTools.length }) }}</h5>
					<div class="selected-tools-list">
						<NcChip v-for="tool in selectedTools"
							:key="tool.id"
							:aria-label="tool.name"
							@close="removeTool(tool.id)">
							{{ tool.name }}
						</NcChip>
					</div>
					<div class="summary-actions">
						<NcButton type="tertiary"
							@click="clearAllTools">
							{{ t('assistant', 'Clear All') }}
						</NcButton>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import NcButton from '@nextcloud/vue/components/NcButton'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import NcChip from '@nextcloud/vue/components/NcChip'

import ChevronDownIcon from 'vue-material-design-icons/ChevronDown.vue'
import ChevronUpIcon from 'vue-material-design-icons/ChevronUp.vue'
import ToolboxIcon from 'vue-material-design-icons/Toolbox.vue'

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs'

export default {
	name: 'MCPToolSelector',

	components: {
		NcButton,
		NcCheckboxRadioSwitch,
		NcNoteCard,
		NcChip,
		ChevronDownIcon,
		ChevronUpIcon,
		ToolboxIcon,
	},

	props: {
		// Initial selected tool IDs
		value: {
			type: Array,
			default: () => [],
		},
		// Auto-expand on mount
		autoExpand: {
			type: Boolean,
			default: false,
		},
	},

	data() {
		return {
			expanded: this.autoExpand,
			enabledProviders: [],
			selectedProviderId: null,
			selectedTools: [],
		}
	},

	computed: {
		hasEnabledProviders() {
			return this.enabledProviders.length > 0
		},

		selectedProvider() {
			return this.enabledProviders.find(p => p.id === this.selectedProviderId)
		},
	},

	watch: {
		value: {
			immediate: true,
			handler(newValue) {
				this.loadSelectedTools(newValue)
			},
		},
	},

	mounted() {
		this.loadEnabledProviders()
	},

	methods: {
		async loadEnabledProviders() {
			try {
				const response = await axios.get(
					generateUrl('/apps/assistant/mcp/providers'),
					{ params: { enabled_only: true } },
				)
				this.enabledProviders = response.data

				// Auto-select first provider
				if (this.enabledProviders.length > 0 && !this.selectedProviderId) {
					this.selectedProviderId = this.enabledProviders[0].id
				}
			} catch (error) {
				console.error('Failed to load enabled MCP providers:', error)
				showError(t('assistant', 'Failed to load MCP tools'))
			}
		},

		async loadSelectedTools(toolIds) {
			if (!toolIds || toolIds.length === 0) {
				this.selectedTools = []
				return
			}

			try {
				const response = await axios.post(
					generateUrl('/apps/assistant/mcp/tools/batch'),
					{ tool_ids: toolIds },
				)
				this.selectedTools = response.data
			} catch (error) {
				console.error('Failed to load selected tools:', error)
			}
		},

		toggleExpanded() {
			this.expanded = !this.expanded
		},

		selectProvider(providerId) {
			this.selectedProviderId = providerId
		},

		isToolSelected(toolId) {
			return this.selectedTools.some(t => t.id === toolId)
		},

		toggleTool(tool) {
			const index = this.selectedTools.findIndex(t => t.id === tool.id)
			if (index >= 0) {
				this.selectedTools.splice(index, 1)
			} else {
				this.selectedTools.push(tool)
			}
			this.emitChange()
		},

		removeTool(toolId) {
			const index = this.selectedTools.findIndex(t => t.id === toolId)
			if (index >= 0) {
				this.selectedTools.splice(index, 1)
				this.emitChange()
			}
		},

		clearAllTools() {
			this.selectedTools = []
			this.emitChange()
		},

		emitChange() {
			const toolIds = this.selectedTools.map(t => t.id)
			this.$emit('input', toolIds)
			this.$emit('change', this.selectedTools)
		},

		formatParameters(parameters) {
			return parameters.map(p => p.name).join(', ')
		},
	},
}
</script>

<style scoped lang="scss">
.mcp-tool-selector {
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	padding: 15px;
	margin: 15px 0;

	.tool-selector-header {
		display: flex;
		justify-content: space-between;
		align-items: center;

		h4 {
			margin: 0;
		}
	}

	.tool-selector-content {
		margin-top: 15px;

		.providers-tools {
			.provider-tabs {
				display: flex;
				gap: 5px;
				margin-bottom: 15px;
				flex-wrap: wrap;

				.tool-count {
					margin-left: 5px;
					padding: 2px 6px;
					background: var(--color-background-dark);
					border-radius: var(--border-radius);
					font-size: 0.8em;
				}
			}

			.tools-container {
				.no-tools {
					padding: 10px 0;
				}

				.tools-grid {
					display: grid;
					grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
					gap: 10px;
					margin-bottom: 15px;

					.tool-card {
						border: 1px solid var(--color-border);
						border-radius: var(--border-radius);
						padding: 12px;
						cursor: pointer;
						transition: all 0.2s;

						&:hover {
							border-color: var(--color-primary-element);
							background: var(--color-background-hover);
						}

						&.tool-selected {
							border-color: var(--color-primary-element);
							background: var(--color-primary-element-light);
						}

						.tool-header {
							display: flex;
							align-items: center;
							gap: 8px;
							margin-bottom: 8px;

							.tool-icon {
								color: var(--color-primary-element);
							}

							.tool-name {
								font-weight: bold;
								flex: 1;
							}
						}

						.tool-description {
							color: var(--color-text-maxcontrast);
							font-size: 0.9em;
							margin: 0 0 8px 0;
							line-height: 1.4;
						}

						.tool-params {
							font-size: 0.85em;
							color: var(--color-text-maxcontrast);

							.params-label {
								font-weight: bold;
								margin-right: 5px;
							}

							.params-list {
								font-family: monospace;
							}
						}
					}
				}
			}

			.selected-summary {
				border-top: 1px solid var(--color-border);
				padding-top: 15px;
				margin-top: 15px;

				h5 {
					margin: 0 0 10px 0;
				}

				.selected-tools-list {
					display: flex;
					flex-wrap: wrap;
					gap: 8px;
					margin-bottom: 10px;
				}

				.summary-actions {
					display: flex;
					justify-content: flex-end;
				}
			}
		}
	}
}
</style>
