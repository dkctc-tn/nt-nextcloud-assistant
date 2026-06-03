# Custom Headers Configuration Guide

## Overview

The Custom Headers feature allows you to add custom HTTP headers to LLM (Large Language Model) provider requests. This is useful for:

- API authentication and authorization
- Custom tracking and analytics
- Rate limiting identification
- Provider-specific requirements
- Custom middleware integration

## Features

- **Per-Provider Configuration**: Different headers for different AI providers
- **Encrypted Storage**: Sensitive header values (API keys, tokens) are encrypted at rest
- **User-Level Control**: Each user manages their own custom headers
- **Enable/Disable**: Toggle headers without deleting them
- **Secure Management**: Never exposes encrypted values in API responses

## Configuration

### Accessing Custom Headers Settings

1. Navigate to **Settings → Personal → NormieTranslator Assistant**
2. Click on the **Custom Headers** tab
3. You'll see a list of your existing custom headers (if any)

### Adding a Custom Header

1. Click **Add Custom Header**
2. Fill in the form:
   - **Provider Name**: The AI provider identifier (e.g., `openai`, `anthropic`, `nt_endpoint`)
   - **Header Name**: The HTTP header name (e.g., `X-API-Key`, `Authorization`)
   - **Header Value**: The value to send (e.g., your API key)
   - **Encrypt Value**: Check this box for sensitive values like API keys
3. Click **Save**

### Common Use Cases

#### API Key Authentication

Many AI providers require an API key in a custom header:

```
Provider Name: openai
Header Name: X-API-Key
Header Value: sk-proj-xxxxxxxxxxxx
Encrypt Value: ✓ (checked)
```

#### ****** Authorization

For providers using OAuth or bearer tokens:

```
Provider Name: anthropic
Header Name: Authorization
Header Value: ******
Encrypt Value: ✓ (checked)
```

## Security Best Practices

### Always Encrypt Sensitive Values

- ✅ **DO**: Encrypt API keys, tokens, passwords, and secrets
- ❌ **DON'T**: Store sensitive data unencrypted

### Use Least Privilege

- Only add headers that are absolutely necessary
- Remove headers when they're no longer needed
- Use provider-specific headers rather than global ones when possible

## Support

For issues or questions:
- Fork Repository: https://github.com/dkctc-tn/nt-nextcloud-assistant/issues

---

**Next**: [MCP Integration Guide](mcp-integration.md)
