# NormieTranslator Assistant User Guide

**Version 3.5.0-nt.1**

Welcome to the NormieTranslator Assistant! This guide will help you get the most out of your customized Nextcloud Assistant experience with custom branding, headers, and MCP tool integration.

---

## Table of Contents

1. [Introduction](#introduction)
2. [Administrator Guide](#administrator-guide)
   - [Customizing Branding](#customizing-branding)
   - [Managing Custom Headers](#managing-custom-headers)
   - [Configuring MCP Providers](#configuring-mcp-providers)
3. [User Guide](#user-guide)
   - [Selecting MCP Tools](#selecting-mcp-tools)
   - [Using the Assistant](#using-the-assistant)
4. [Troubleshooting](#troubleshooting)
5. [FAQ](#faq)

---

## Introduction

The NormieTranslator Assistant is a fork of Nextcloud Assistant that adds powerful customization features:

- **Custom Branding:** Personalize the look and feel of the Assistant
- **Custom Headers:** Add authentication and routing headers for LLM providers
- **MCP Integration:** Extend capabilities with Model Context Protocol tools

This guide covers both administrator configuration and end-user usage.

---

## Administrator Guide

### Accessing Admin Settings

1. Log in to Nextcloud as an administrator
2. Navigate to **Settings** → **Administration** → **NormieTranslator Assistant**
3. Scroll to the **NormieTranslator Customizations** section

---

### Customizing Branding

Make the Assistant your own with custom branding.

#### Application Name

1. Enter your custom name in the **Application Name** field
2. Example: "Acme Corp AI Assistant" or "NormieTranslator Pro"
3. This name appears throughout the interface

#### Header Text

1. Add a custom greeting or header message
2. Example: "Welcome to your intelligent assistant!"
3. This appears when users open the Assistant

#### Primary Color

1. Click the color picker to choose your brand color
2. Or enter a hex code directly (e.g., `#FF5722`)
3. The interface will update with your chosen theme color

#### Custom Logo

1. Click **Upload Logo**
2. Select an image file (SVG, PNG, or JPEG)
3. Maximum file size: 2MB
4. Recommended: SVG or PNG with transparency
5. Logo displays in the Assistant header

**To remove a logo:**
1. Click **Remove Logo**
2. Confirm the action

#### White Label Mode

Enable this option to hide "Powered by Nextcloud" and similar attributions.

**Note:** You must still comply with AGPL-3.0 license requirements by providing source code access.

#### Saving Settings

- Settings auto-save 1 second after you stop typing
- You'll see a success message when saved
- Changes apply immediately for all users

---

### Managing Custom Headers

Add custom HTTP headers to LLM provider requests for authentication, rate limiting, or routing.

#### Adding a Header

1. Select a provider from the dropdown
2. Click **Add Header**
3. Enter the header name (e.g., `X-API-Key` or `Authorization`)
4. Enter the header value
5. Check **Encrypt this header value** for sensitive data (recommended)
6. Click **Add**

#### Editing a Header

1. Click the pencil icon next to the header
2. Modify the name or value
3. Click **Update**

**Note:** Encrypted header values cannot be viewed after saving for security.

#### Deleting a Header

1. Click the trash icon next to the header
2. Confirm the deletion

#### Testing Connection

1. Select a provider with configured headers
2. Click **Test Connection**
3. Wait for the test to complete
4. Review success or error message

**Use cases for custom headers:**

- **Authentication:** Add API keys for provider access
- **Organization IDs:** Route requests to specific organizations
- **Rate Limiting:** Add tier or quota identifiers
- **Debugging:** Include tracking headers for support

---

### Configuring MCP Providers

Model Context Protocol (MCP) providers extend the Assistant with external tools and capabilities.

#### Built-in Providers

Three providers come pre-configured:

1. **Composio For You MCP** - Tool execution and workflow automation
2. **Klavis AI** - Advanced context and memory management
3. **Pipedream** - Event-based workflow integration

#### Enabling a Provider

1. Locate the provider card
2. Toggle the **Enabled** switch
3. The provider configuration expands

#### Configuring Endpoint

1. Enter the provider's endpoint URL
2. Example: `https://api.composio.dev/mcp`
3. Consult provider documentation for correct URL

#### Authentication

Choose the authentication method:

**None:** No authentication required

**API Key:**
1. Select "API Key" from dropdown
2. Enter your API key
3. Key is encrypted automatically
4. Click **Show/Hide** to view/mask the key

**OAuth 2.0:**
1. Select "OAuth 2.0" from dropdown
2. Click **Connect with OAuth**
3. A popup window opens for OAuth flow
4. Log in and authorize the application
5. Popup closes automatically when complete
6. "Connected" status appears

**To disconnect OAuth:**
1. Click **Disconnect** in the OAuth status area
2. Confirm the action

#### Testing Provider Connection

1. Click **Test Connection**
2. Wait for the test to complete
3. Review the result:
   - ✅ Success: Provider is correctly configured
   - ❌ Error: Check endpoint URL and authentication

#### Refreshing Capabilities

After configuring a provider:

1. Click **Refresh Capabilities**
2. The system queries the provider for available tools
3. Tools appear in the **Available Tools** section

#### Viewing Available Tools

Each enabled provider shows its tools:
- **Tool Name:** Name of the capability
- **Description:** What the tool does
- **Parameters:** Required inputs (hover for details)

#### Adding Custom Providers

Beyond the built-in providers, you can add custom MCP-compatible services:

1. Click **Add Custom MCP Provider**
2. Enter a name (e.g., "Internal Tools API")
3. Add a description
4. Provide the endpoint URL
5. Click **Add Provider**
6. Configure authentication as above
7. Test and refresh capabilities

---

## User Guide

### Accessing Personal Settings

1. Log in to Nextcloud
2. Navigate to **Settings** → **Personal** → **NormieTranslator Assistant**
3. Configure your preferences

### Selecting MCP Tools

If your administrator has enabled MCP providers, you can choose which tools to use.

#### Viewing Available Tools

1. Locate the **MCP Tools** section
2. Click to expand the tool selector
3. Browse available providers using the tabs (if multiple)

#### Selecting Tools

1. Click on a tool card to select it
2. Selected tools are highlighted
3. The tool also appears in the **Selected Tools** summary

**Tool Information:**
- **Name:** What the tool is called
- **Description:** What the tool does
- **Parameters:** What inputs it requires

#### Deselecting Tools

**Method 1 - Click the card:**
1. Click a selected tool card again to deselect

**Method 2 - Remove from summary:**
1. Find the tool chip in the Selected Tools section
2. Click the X to remove it

**Method 3 - Clear all:**
1. Click **Clear All** to deselect everything

#### Saving Tool Selection

- Your selection saves automatically when changed
- A success message confirms the save
- Selected tools are available in your Assistant sessions

---

### Using the Assistant

#### Opening the Assistant

1. Click the Assistant icon in the Nextcloud header
2. Or access it from the main menu
3. Or use the smart picker (Ctrl+K or ⌘+K)

#### Using MCP Tools

When MCP tools are selected:

1. Open a conversation in the Assistant
2. Type your request normally
3. The Assistant automatically uses relevant tools
4. Tool usage appears in the conversation
5. Results are integrated into responses

**Example:**

> **You:** "Create a new Trello card for 'Update documentation'"
> 
> **Assistant:** *[Using Composio MCP - Trello Create Card]*
> 
> I've created a new card titled "Update documentation" in your Trello board.

#### Tool Suggestions

The Assistant may suggest tools when appropriate:

> **You:** "I need to schedule a meeting next week"
> 
> **Assistant:** "I can help with that! I have access to calendar tools. Would you like me to create a calendar event?"

---

## Troubleshooting

### Branding Not Appearing

**Problem:** Custom branding doesn't show after saving

**Solutions:**
1. Clear your browser cache (Ctrl+Shift+Delete or ⌘+Shift+Delete)
2. Hard refresh the page (Ctrl+F5 or ⌘+Shift+R)
3. Check admin settings to confirm branding was saved
4. Ensure you have administrator permissions

---

### Custom Headers Not Working

**Problem:** Provider requests fail with authentication errors

**Solutions:**
1. Verify the header name is correct (check provider docs)
2. Ensure the header value is valid (not expired)
3. Use **Test Connection** to diagnose the issue
4. Check provider logs for specific error messages
5. Try re-entering the header value

---

### MCP Provider Won't Connect

**Problem:** Provider shows "Connection test failed"

**Solutions:**
1. Verify the endpoint URL is correct
2. Check that the provider service is online
3. Confirm authentication credentials are valid
4. For OAuth: Try disconnecting and reconnecting
5. For API Key: Regenerate the key if possible
6. Check firewall/network settings

---

### MCP Tools Not Available

**Problem:** User doesn't see MCP tools in personal settings

**Solutions:**
1. Confirm administrator has enabled at least one provider
2. Ensure provider capabilities were refreshed
3. Check that the provider connection test passes
4. Verify provider has available tools
5. Try refreshing the personal settings page

---

### OAuth Popup Blocked

**Problem:** OAuth window doesn't open

**Solutions:**
1. Allow popups for your Nextcloud domain
2. Try clicking **Connect with OAuth** again
3. Check browser popup blocker settings
4. Try a different browser
5. Contact your administrator for alternative auth methods

---

### Selected Tools Not Working

**Problem:** Assistant doesn't use selected MCP tools

**Solutions:**
1. Verify tools were successfully saved (check for success message)
2. Refresh the Assistant page
3. Start a new conversation
4. Check if the provider is still enabled (admin settings)
5. Verify provider connection is healthy

---

## FAQ

### General Questions

**Q: What is MCP?**  
A: Model Context Protocol is a standard for connecting LLMs to external tools and data sources. It allows the Assistant to perform actions beyond text generation.

**Q: Do I need to be an administrator to use MCP tools?**  
A: No. Administrators configure the providers, but any user can select which tools to use in their personal settings.

**Q: Are custom headers secure?**  
A: Yes. When you mark a header as encrypted, its value is stored securely and never displayed in the interface. However, it is transmitted to the provider with each request.

**Q: Can I use multiple MCP providers simultaneously?**  
A: Yes! You can enable multiple providers and select tools from all of them.

**Q: What happens if I remove a logo?**  
A: The interface reverts to the default Assistant icon.

---

### Advanced Questions

**Q: Can I export my configuration?**  
A: Not currently, but this feature is planned for a future release.

**Q: How do I create a custom MCP provider?**  
A: You'll need to implement the MCP protocol specification. See the MCP documentation at https://modelcontextprotocol.io for details.

**Q: Can I set different headers for different users?**  
A: Currently, custom headers are global for each provider. User-specific headers are planned for future versions.

**Q: Does white label mode affect licensing?**  
A: No. While it hides UI attributions, you must still comply with the AGPL-3.0 license by making source code available.

**Q: What happens to my settings if I downgrade?**  
A: If you install the standard Nextcloud Assistant, custom branding, headers, and MCP settings will be ignored but not deleted.

---

### Provider-Specific Questions

**Q: How do I get Composio API credentials?**  
A: Sign up at https://composio.dev and generate an API key in your dashboard.

**Q: What tools does Klavis AI provide?**  
A: Klavis AI specializes in memory management, context retrieval, and conversation summarization.

**Q: Can Pipedream trigger webhooks?**  
A: Yes! Configure webhook endpoints in Pipedream and use them as MCP tools.

---

## Getting Help

### Support Resources

- **Documentation:** See `/docs` directory in the repository
- **Issues:** Report bugs on GitHub Issues
- **Community:** Join discussions in GitHub Discussions
- **Source Code:** https://github.com/dkctc-tn/nt-nextcloud-assistant

### Reporting Issues

When reporting a problem, include:

1. **Version:** 3.5.0-nt.1
2. **Nextcloud Version:** Your NC version
3. **Browser:** Name and version
4. **Steps to Reproduce:** What you did
5. **Expected Result:** What should happen
6. **Actual Result:** What actually happened
7. **Screenshots:** If applicable
8. **Console Errors:** Browser console output

### Contributing

We welcome contributions! See `CONTRIBUTING.md` for guidelines.

---

## License and Attribution

This software is licensed under AGPL-3.0-or-later.

**Attribution:**
- Original work: Nextcloud GmbH and contributors
- Fork enhancements: DK Consultants & Technologies Corp and MoreDKon contributors

Source code available at: https://github.com/dkctc-tn/nt-nextcloud-assistant

---

**Version:** 3.5.0-nt.1  
**Last Updated:** 2026-06-03  
**Maintained by:** NormieTranslator Team
