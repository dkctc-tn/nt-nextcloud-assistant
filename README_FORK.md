# NormieTranslator Assistant - Fork of Nextcloud Assistant

**Version:** 3.5.0-nt.1  
**Fork Maintainer:** MoreDKon/NormieTranslator  
**Original Project:** [Nextcloud Assistant](https://github.com/nextcloud/assistant)  
**License:** AGPL-3.0-or-later

## Overview

NormieTranslator Assistant is an enhanced fork of Nextcloud Assistant that adds custom branding, custom LLM request headers, and MCP (Model Context Protocol) integration for advanced AI capabilities.

This fork maintains full compatibility with the original Nextcloud Assistant while adding powerful customization features specifically designed for the NormieTranslator system.

## What's New in This Fork

### 🎨 Custom Branding
- **White-Label Support**: Fully customize the app name, logo, and colors
- **Flexible Configuration**: Admin and user-level branding controls
- **Professional Appearance**: Match your organization's brand identity

### 🔐 Custom Headers for LLM Requests
- **Per-Provider Headers**: Different headers for different AI providers
- **Encrypted Storage**: Sensitive header values are encrypted at rest
- **User-Level Control**: Each user can configure their own headers
- **Flexible Integration**: Support for API keys, tokens, and custom authentication

### 🛠️ MCP (Model Context Protocol) Integration
- **Composio For You MCP**: Access to Composio tools and integrations
- **Klavis AI**: Context and memory management capabilities
- **Pipedream**: Workflow automation and event-driven integrations
- **Extensible Architecture**: Easy to add new MCP providers

## Installation

### Prerequisites
- Nextcloud 33, 34, or 35
- PHP 8.1 or higher
- PostgreSQL, MySQL, or SQLite database

### Installation Steps

1. **Download the app:**
   ```bash
   cd /path/to/nextcloud/apps
   git clone https://github.com/dkctc-tn/nt-nextcloud-assistant.git nt_assistant
   ```

2. **Install dependencies:**
   ```bash
   cd nt_assistant
   npm install
   composer install
   ```

3. **Build the frontend:**
   ```bash
   npm run build
   ```

4. **Enable the app:**
   ```bash
   sudo -u www-data php /path/to/nextcloud/occ app:enable nt_assistant
   ```

5. **Run database migrations:**
   ```bash
   sudo -u www-data php /path/to/nextcloud/occ maintenance:update:all
   ```

## Configuration

### Custom Branding

Access the admin settings at: **Settings → Administration → NormieTranslator Assistant → Branding**

Configure:
- Custom app name
- Custom logo (upload or file path)
- Primary color theme
- Custom header text
- White-label mode

### MCP Providers

Access the admin settings at: **Settings → Administration → NormieTranslator Assistant → MCP Providers**

For each provider (Composio, Klavis AI, Pipedream):
1. Enable the provider
2. Set the endpoint URL
3. Configure authentication (user-level)
4. Test the connection

### Custom Headers

Users can configure custom headers at: **Settings → Personal → NormieTranslator Assistant → Custom Headers**

For each provider:
1. Add header name and value
2. Choose whether to encrypt sensitive values
3. Enable/disable headers individually

## API Documentation

### REST API Endpoints

#### Branding
- `GET /apps/nt_assistant/api/config/branding` - Get branding configuration
- `POST /apps/nt_assistant/api/config/branding` - Set branding (admin)
- `POST /apps/nt_assistant/api/config/branding/reset` - Reset to defaults (admin)

#### MCP Providers
- `GET /apps/nt_assistant/api/mcp/providers` - List available providers
- `GET /apps/nt_assistant/api/mcp/provider/{type}` - Get provider config
- `POST /apps/nt_assistant/api/mcp/provider/{type}/enable` - Enable/disable provider (admin)
- `POST /apps/nt_assistant/api/mcp/provider/{type}/endpoint` - Set endpoint (admin)
- `POST /apps/nt_assistant/api/mcp/provider/{type}/auth` - Set authentication (user)
- `POST /apps/nt_assistant/api/mcp/provider/{type}/test` - Test connection

#### Custom Headers
- `GET /apps/nt_assistant/api/mcp/headers` - Get all custom headers (user)
- `POST /apps/nt_assistant/api/mcp/headers` - Set custom header (user)
- `DELETE /apps/nt_assistant/api/mcp/headers` - Remove custom header (user)
- `PUT /apps/nt_assistant/api/mcp/headers/enable` - Enable/disable header (user)

## Development

### Building from Source

```bash
# Install dependencies
npm install
composer install

# Development build with hot reload
npm run watch

# Production build
npm run build
```

### Running Tests

```bash
# PHP tests
composer test

# JavaScript tests
npm test

# Lint
npm run lint
composer run lint
```

## Architecture

### Service Layer
- **BrandingService**: Manages custom branding configuration
- **CustomHeadersService**: Handles custom header storage and encryption
- **MCPConfigService**: Manages MCP provider configuration and authentication

### Database
- **assistant_custom_headers**: Stores custom headers with encryption support

### Task Processing
- **MCPProviderAdapter**: Base class for MCP provider adapters
- **ComposioMCPProvider**: Composio For You MCP integration
- **KlavisAIMCPProvider**: Klavis AI integration
- **PipedreamMCPProvider**: Pipedream integration

### Controllers
- **ConfigController**: Extended with branding endpoints
- **MCPController**: MCP provider and custom header management

## Security

### Encryption
- Custom header values can be encrypted at rest using Nextcloud's crypto service
- MCP authentication credentials are encrypted by default
- All sensitive data is protected with industry-standard encryption

### Authentication
- All API endpoints respect Nextcloud's authentication and authorization
- Admin-only endpoints require administrator privileges
- User-level endpoints only allow access to own data

### Data Privacy
- Custom headers are stored per-user and not shared
- Encrypted values are never exposed in API responses
- Audit logs track configuration changes

## Troubleshooting

### Common Issues

**App won't enable:**
- Check PHP version (8.1+ required)
- Verify dependencies are installed
- Check Nextcloud version compatibility

**MCP providers not working:**
- Verify endpoint URLs are correct
- Check authentication credentials
- Test connection using built-in test tool
- Review Nextcloud logs for errors

**Custom headers not being sent:**
- Verify headers are enabled
- Check header encryption/decryption
- Ensure provider name matches exactly

### Logs

Check Nextcloud logs:
```bash
tail -f /path/to/nextcloud/data/nextcloud.log
```

Enable debug mode in `config.php`:
```php
'debug' => true,
```

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

### Areas for Contribution
- Additional MCP provider adapters
- Frontend UI improvements
- Documentation enhancements
- Bug fixes and optimizations

## License

This fork is licensed under the GNU Affero General Public License v3.0 or later (AGPL-3.0-or-later), the same license as the original Nextcloud Assistant.

See [COPYING](COPYING) for the full license text.

## Credits

### Original Project
- **Nextcloud Assistant** by Nextcloud GmbH and contributors
- Original Author: Julien Veyssier
- Repository: https://github.com/nextcloud/assistant

### Fork Enhancements
- **NormieTranslator Team**
- Maintainer: MoreDKon
- Repository: https://github.com/dkctc-tn/nt-nextcloud-assistant

## Support

- **Fork Issues**: https://github.com/dkctc-tn/nt-nextcloud-assistant/issues
- **Original Project**: https://github.com/nextcloud/assistant/issues
- **Documentation**: See `docs/` directory

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for detailed version history.

---

**For detailed documentation on specific features, see the `docs/` directory:**
- [Custom Headers Guide](docs/custom-headers.md)
- [MCP Integration Guide](docs/mcp-integration.md)
- [Branding Customization](docs/branding.md)
