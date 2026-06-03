# Frontend Components Documentation

**NormieTranslator Assistant Fork - Vue.js Components**

This document describes the custom Vue.js components added to the NormieTranslator Assistant fork for managing branding, custom headers, and MCP provider integration.

---

## Component Overview

### BrandingSettings.vue
**Location:** `/src/components/BrandingSettings.vue`

**Purpose:** Allows administrators to customize the visual appearance and branding of the Assistant interface.

**Features:**
- Custom application name
- Custom header text/greeting
- Primary color theme customization
- Logo upload and management
- White label mode toggle

**API Endpoints:**
- `GET /apps/assistant/branding` - Load current branding settings
- `POST /apps/assistant/branding` - Save branding settings
- `POST /apps/assistant/branding/logo` - Upload custom logo
- `DELETE /apps/assistant/branding/logo` - Remove custom logo

**Props:** None

**Events:** None (self-contained)

**Usage:**
```vue
<BrandingSettings />
```

**Data Structure:**
```javascript
{
	app_name: String,        // Custom application name
	app_logo: String,        // URL to uploaded logo
	app_color: String,       // Hex color code (e.g., '#0082C9')
	header_text: String,     // Custom header/greeting text
	white_label_mode: Boolean // Hide attributions
}
```

**Notes:**
- Settings are auto-saved with 1-second debounce
- Logo uploads are limited to 2MB
- Supports SVG, PNG, JPEG formats
- Color picker with text input validation

---

### CustomHeadersSettings.vue
**Location:** `/src/components/CustomHeadersSettings.vue`

**Purpose:** Manages custom HTTP headers sent with LLM provider requests.

**Features:**
- Provider-specific header configuration
- Header name/value management
- Secure encryption for sensitive values
- Connection testing
- Visual indicator for encrypted headers

**API Endpoints:**
- `GET /apps/assistant/providers` - List available providers
- `GET /apps/assistant/custom-headers` - Get headers for a provider
- `POST /apps/assistant/custom-headers` - Create new header
- `PUT /apps/assistant/custom-headers/{id}` - Update existing header
- `DELETE /apps/assistant/custom-headers/{id}` - Delete header
- `POST /apps/assistant/custom-headers/test` - Test connection with headers

**Props:** None

**Events:** None (self-contained)

**Usage:**
```vue
<CustomHeadersSettings />
```

**Data Structure:**
```javascript
{
	provider: String,     // Provider ID
	name: String,         // Header name (e.g., 'X-API-Key')
	value: String,        // Header value
	encrypted: Boolean    // Whether to encrypt the value
}
```

**Security Considerations:**
- Sensitive header values should always be encrypted
- Encrypted values are masked in the UI
- Connection testing validates headers without exposing values

---

### MCPProviderSettings.vue
**Location:** `/src/components/MCPProviderSettings.vue`

**Purpose:** Configures Model Context Protocol (MCP) providers and their authentication.

**Features:**
- Enable/disable MCP providers
- Configure provider endpoints
- Multiple authentication methods (API Key, OAuth 2.0)
- Test provider connections
- Refresh provider capabilities
- View available tools per provider
- Add custom MCP providers

**API Endpoints:**
- `GET /apps/assistant/mcp/providers` - List MCP providers
- `POST /apps/assistant/mcp/providers` - Add custom provider
- `PUT /apps/assistant/mcp/providers/{id}` - Update provider settings
- `POST /apps/assistant/mcp/providers/{id}/test` - Test connection
- `POST /apps/assistant/mcp/providers/{id}/capabilities` - Refresh capabilities
- `GET /apps/assistant/mcp/providers/{id}/oauth` - Start OAuth flow
- `DELETE /apps/assistant/mcp/providers/{id}/oauth` - Disconnect OAuth
- `GET /apps/assistant/mcp/providers/{id}/oauth/status` - Check OAuth status

**Props:** None

**Events:** None (self-contained)

**Usage:**
```vue
<MCPProviderSettings />
```

**Data Structure:**
```javascript
{
	id: String,
	name: String,
	description: String,
	enabled: Boolean,
	endpoint: String,
	auth_type: {
		label: String,
		value: 'none' | 'api_key' | 'oauth'
	},
	api_key: String,           // For API key auth
	oauth_connected: Boolean,  // For OAuth auth
	tools: [                   // Available tools
		{
			id: String,
			name: String,
			description: String,
			parameters: Array
		}
	]
}
```

**Built-in Providers:**
1. **Composio For You MCP** - Tool execution and workflow automation
2. **Klavis AI** - Context/memory management
3. **Pipedream** - Event-based workflows

**OAuth Flow:**
1. User clicks "Connect with OAuth"
2. OAuth window opens
3. Component polls for connection status every 2 seconds
4. Connection confirmed when OAuth completes
5. Polling stops after 5 minutes

---

### MCPToolSelector.vue
**Location:** `/src/components/MCPToolSelector.vue`

**Purpose:** Allows users to select which MCP tools to use with the Assistant.

**Features:**
- Collapsible tool selection interface
- Multi-provider support with tabs
- Tool cards with descriptions and parameters
- Selected tools summary
- Batch selection/deselection

**API Endpoints:**
- `GET /apps/assistant/mcp/providers?enabled_only=true` - Get enabled providers
- `POST /apps/assistant/mcp/tools/batch` - Load tool details by IDs

**Props:**
- `value` (Array) - Initial selected tool IDs
- `autoExpand` (Boolean) - Auto-expand on mount (default: false)

**Events:**
- `input` - Emitted when selection changes (tool IDs array)
- `change` - Emitted when selection changes (full tool objects)

**Usage:**
```vue
<MCPToolSelector 
	v-model="selectedTools"
	:auto-expand="true"
	@change="onToolsChange" />
```

**Methods:**
- `toggleExpanded()` - Toggle expanded/collapsed state
- `selectProvider(providerId)` - Switch to different provider tab
- `toggleTool(tool)` - Select/deselect a tool
- `removeTool(toolId)` - Remove a selected tool
- `clearAllTools()` - Clear all selections

**Tool Data Structure:**
```javascript
{
	id: String,
	name: String,
	description: String,
	parameters: [
		{
			name: String,
			type: String,
			required: Boolean,
			description: String
		}
	]
}
```

---

## Integration with Settings Pages

### AdminSettings.vue Updates

The AdminSettings component now includes a new "NormieTranslator Customizations" section that contains:

```vue
<div class="nt-customizations">
	<h3>{{ t('assistant', 'NormieTranslator Customizations') }}</h3>
	
	<BrandingSettings />
	<CustomHeadersSettings />
	<MCPProviderSettings />
</div>
```

**Styling:**
- Background: `var(--color-background-dark)`
- Border radius: `var(--border-radius-large)`
- Padding: 20px
- Margin: 30px (top and bottom)

### PersonalSettings.vue Updates

The PersonalSettings component now includes an MCP tools selection section:

```vue
<div v-if="mcpToolsAvailable" class="mcp-tools-section">
	<h3>{{ t('assistant', 'MCP Tools') }}</h3>
	<p>{{ t('assistant', 'Select which MCP tools you want to use with the Assistant:') }}</p>
	<MCPToolSelector 
		v-model="selectedMCPTools"
		:auto-expand="false"
		@change="onMCPToolsChange" />
</div>
```

**New Methods:**
- `loadMCPToolsAvailability()` - Check if any MCP providers are enabled
- `loadSelectedMCPTools()` - Load user's current tool selection
- `onMCPToolsChange()` - Save tool selection via API

**API Endpoints:**
- `GET /apps/assistant/user/mcp-tools` - Get user's selected tools
- `PUT /apps/assistant/user/mcp-tools` - Save user's tool selection

---

## Styling Guidelines

All components follow Nextcloud's design system:

### Colors
- Primary: `var(--color-primary-element)`
- Success: `var(--color-success)`
- Error: `var(--color-error)`
- Background: `var(--color-background-dark)`
- Text: `var(--color-text-maxcontrast)` for hints
- Border: `var(--color-border)`

### Border Radius
- Standard: `var(--border-radius)`
- Large cards: `var(--border-radius-large)`

### Spacing
- Standard gap: 10-15px
- Section margin: 20-30px
- Field margin: 15-25px

### Interactive Elements
- Buttons use `NcButton` component
- Form fields use `NcTextField`, `NcTextArea`, `NcSelect`
- Switches use `NcCheckboxRadioSwitch`
- Info cards use `NcNoteCard`
- Chips use `NcChip`

---

## Error Handling

All components implement consistent error handling:

1. **Try-Catch Blocks:** All API calls wrapped in try-catch
2. **User Feedback:** Success/error messages via `showSuccess()`/`showError()`
3. **Console Logging:** Errors logged to console for debugging
4. **Graceful Degradation:** Components remain functional if API calls fail
5. **Loading States:** Disabled buttons and loading indicators during operations

**Example:**
```javascript
async saveData() {
	try {
		await axios.post(url, data)
		showSuccess(t('assistant', 'Data saved'))
	} catch (error) {
		console.error('Failed to save data:', error)
		showError(t('assistant', 'Failed to save data'))
	}
}
```

---

## Internationalization (i18n)

All user-facing strings use the `t()` helper:

```javascript
{{ t('assistant', 'English text') }}
{{ t('assistant', 'Text with {placeholder}', { placeholder: value }) }}
```

Translation strings are automatically extracted during build and stored in locale files.

---

## Performance Considerations

### Debouncing
BrandingSettings implements 1-second debounce on auto-save to prevent excessive API calls:

```javascript
this.saveTimeout = setTimeout(async () => {
	await this.saveBranding()
}, 1000)
```

### Lazy Loading
Components load data only when mounted or when user interaction requires it:

```javascript
mounted() {
	this.loadProviders()
	this.loadSettings()
}
```

### Efficient Updates
Vue's reactivity system ensures only changed data triggers re-renders. Use `$set()` for dynamic property updates:

```javascript
this.$set(this.testingProvider, providerId, true)
```

---

## Testing Recommendations

### Unit Tests
- Test component mounting/unmounting
- Test data loading from API
- Test user interactions (clicks, input changes)
- Test error handling paths

### Integration Tests
- Test component interaction with backend APIs
- Test OAuth flow completion
- Test file upload functionality
- Test form validation

### E2E Tests
- Test complete admin configuration workflow
- Test user tool selection workflow
- Test branding changes reflect in UI
- Test custom headers sent with requests

---

## Future Enhancements

Potential improvements for future versions:

1. **Branding Preview:** Live preview of branding changes
2. **Header Templates:** Predefined header sets for common providers
3. **MCP Tool Search:** Search/filter tools by name or category
4. **Tool Dependencies:** Automatic selection of dependent tools
5. **Bulk Import/Export:** Import/export configurations as JSON
6. **Usage Analytics:** Track which MCP tools are most used
7. **Tool Recommendations:** Suggest tools based on user behavior

---

## Support and Troubleshooting

### Common Issues

**1. Components not rendering:**
- Check that components are properly imported in parent
- Verify component registration in `components:` section
- Check browser console for JavaScript errors

**2. API calls failing:**
- Verify backend controllers are properly configured
- Check network tab for error responses
- Ensure user has appropriate permissions

**3. Styling issues:**
- Clear browser cache
- Rebuild frontend assets (`npm run build`)
- Check for CSS conflicts with other apps

**4. OAuth not connecting:**
- Verify OAuth credentials in provider settings
- Check popup blocker settings
- Review OAuth callback URL configuration

---

## Component Dependencies

All components depend on:

**Vue.js:** 2.x (Nextcloud standard)

**Nextcloud Vue Components:**
- `NcButton`
- `NcTextField`
- `NcTextArea`
- `NcSelect`
- `NcCheckboxRadioSwitch`
- `NcNoteCard`
- `NcDialog`
- `NcChip`
- `NcFormGroup`
- `NcFormBox`
- `NcFormBoxSwitch`

**Vue Material Design Icons:**
- Upload, Delete, Plus, Link, Check, CheckCircle
- CheckNetwork, Refresh, Loading, Tool
- Eye, EyeOff, Pencil
- ChevronDown, ChevronUp

**Nextcloud Libraries:**
- `@nextcloud/router` - URL generation
- `@nextcloud/axios` - HTTP client
- `@nextcloud/dialogs` - Toast notifications
- `@nextcloud/initial-state` - Server-side state loading

---

## License

All components are dual-licensed:

- **SPDX-FileCopyrightText:** 2026 DK Consultants & Technologies Corp and MoreDKon contributors
- **SPDX-FileCopyrightText:** 2023 Nextcloud GmbH and Nextcloud contributors
- **SPDX-License-Identifier:** AGPL-3.0-or-later

This maintains compliance with the original Nextcloud Assistant AGPL-3.0 license while adding our custom branding and features.

---

**Last Updated:** 2026-06-03  
**Version:** 3.5.0-nt.1
