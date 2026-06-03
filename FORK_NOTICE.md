# NormieTranslator Assistant - Fork Notice

**SPDX-FileCopyrightText: 2026 MoreDKon/NormieTranslator**  
**SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors**  
**SPDX-License-Identifier: AGPL-3.0-or-later**

## Fork Information

This project is a fork of [Nextcloud Assistant](https://github.com/nextcloud/assistant), originally developed by Nextcloud GmbH and contributors.

**Original Project**: Nextcloud Assistant  
**Original Author**: Julien Veyssier  
**Original Repository**: https://github.com/nextcloud/assistant  
**Original License**: AGPL-3.0-or-later

**Fork Maintainer**: MoreDKon/NormieTranslator  
**Fork Repository**: https://github.com/dkctc-tn/nt-nextcloud-assistant  
**Fork Version**: 3.5.0-nt.1

## AGPL-3.0 License Compliance

This fork complies with the GNU Affero General Public License v3.0 or later (AGPL-3.0-or-later).

### Source Code Availability

The complete source code for this fork is publicly available at:  
**https://github.com/dkctc-tn/nt-nextcloud-assistant**

As required by AGPL Section 13, users interacting with this software over a network have the right to receive the complete corresponding source code.

### License Requirements

1. This software is free software under the AGPL-3.0-or-later license
2. You may redistribute and modify it under the terms of the AGPL-3.0
3. Any modifications must also be licensed under AGPL-3.0-or-later
4. Source code must be made available to users who interact with the software over a network
5. All copyright notices and license information must be preserved

## Modifications Made in This Fork

This fork extends the original Nextcloud Assistant with the following enhancements:

### 1. Custom Branding Support
- Configurable app name, logo, and color theming
- White-label mode for custom deployments
- Admin and user-level branding configuration UI
- `BrandingService` for managing custom branding settings

### 2. Custom Headers for LLM Requests
- Per-provider custom header configuration
- Encrypted storage for sensitive header values (API keys, tokens)
- `CustomHeadersService` for header management
- Admin UI for managing custom headers per provider
- Automatic header injection before LLM requests

### 3. MCP (Model Context Protocol) Integration
- Support for Composio For You MCP
- Support for Klavis AI MCP
- Support for Pipedream MCP
- `MCPProviderAdapter` base class for MCP tool integration
- Provider-specific adapters: `ComposioMCPProvider`, `KlavisAIMCPProvider`, `PipedreamMCPProvider`
- `MCPConfigService` for MCP endpoint and authentication management
- MCP tool configuration UI components

### 4. Additional Services and Controllers
- `lib/Service/BrandingService.php` - Custom branding management
- `lib/Service/CustomHeadersService.php` - Custom header management
- `lib/Service/MCPConfigService.php` - MCP configuration management
- `lib/Controller/MCPController.php` - MCP-specific API endpoints
- Database migrations for custom_headers storage
- MCP provider adapters in `lib/TaskProcessing/`

### 5. Frontend Enhancements
- MCP tool selector component
- Custom headers management UI
- Branding configuration UI
- Enhanced admin and personal settings

### 6. Documentation
- Fork-specific documentation in `docs/`
- MCP integration guide
- Custom headers configuration guide
- Branding customization guide

## Original Credits

We gratefully acknowledge the original Nextcloud Assistant project and its contributors:
- Julien Veyssier (original author)
- Nextcloud GmbH
- All Nextcloud Assistant contributors

Their excellent work provided the foundation for this enhanced fork.

## Contact

**Fork Issues**: https://github.com/dkctc-tn/nt-nextcloud-assistant/issues  
**Original Project Issues**: https://github.com/nextcloud/assistant/issues

For issues specific to the fork enhancements (custom branding, custom headers, MCP integration), please file issues in the fork repository. For issues related to the core assistant functionality, consider reporting to the upstream project.

## License Text

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.

---

**Last Updated**: 2026-06-03  
**Fork Version**: 3.5.0-nt.1
