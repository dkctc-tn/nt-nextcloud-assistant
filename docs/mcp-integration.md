# MCP (Model Context Protocol) Integration Guide

## Overview

The NormieTranslator Assistant fork includes built-in support for MCP (Model Context Protocol) providers, enabling advanced AI capabilities through external tools and integrations.

## Supported MCP Providers

### 1. Composio For You MCP
Access to Composio's extensive tool library and integrations.

**Capabilities:**
- Execute actions across 100+ integrated apps
- Access to pre-built workflows
- Context-aware tool selection
- Real-time data synchronization

### 2. Klavis AI
Context and memory management for enhanced AI interactions.

**Capabilities:**
- Long-term conversation memory
- Context injection and retrieval
- Knowledge base management
- Semantic search

### 3. Pipedream
Workflow automation and event-driven integrations.

**Capabilities:**
- Trigger custom workflows
- Event-based automation
- Integration with 1000+ services
- Real-time data processing

## Admin Configuration

### Enabling MCP Providers

1. Navigate to **Settings → Administration → NormieTranslator Assistant → MCP Providers**
2. For each provider you want to enable:
   - Toggle the **Enabled** switch
   - Enter the **Endpoint URL** (provided by your MCP service)
   - Click **Save**

### Endpoint Configuration

Each MCP provider requires a valid endpoint URL:

**Composio For You MCP:**
```
https://your-composio-instance.com/mcp
```

**Klavis AI:**
```
https://your-klavis-instance.com/api/mcp
```

**Pipedream:**
```
https://your-pipedream-instance.pipedream.net/mcp
```

### Testing Connections

Use the **Test Connection** button to verify:
- Endpoint is reachable
- Authentication is working
- MCP service is responding correctly

## User Configuration

### Setting Up Authentication

1. Navigate to **Settings → Personal → NormieTranslator Assistant → MCP Providers**
2. For each enabled provider:
   - Click **Configure Authentication**
   - Choose authentication type:
     - ******** For OAuth/JWT authentication
     - **API Key**: For custom API key headers
   - Enter your credentials
   - Click **Save**

### Authentication Types

********
```json
{
  "type": "bearer",
  "token": "your-oauth-token-here"
}
```

**API Key:**
```json
{
  "type": "apikey",
  "key": "X-API-Key",
  "value": "your-api-key-here"
}
```

## Using MCP Providers

### In the Assistant UI

1. Open the Nextcloud Assistant
2. Select a task type that supports MCP
3. Choose your MCP provider from the dropdown
4. Enter your input
5. Submit the task

### Via API

```bash
curl -X POST \
  https://your-nextcloud.com/apps/nt_assistant/api/tasks \
  -H "Authorization: ******" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "core:text2text:composio",
    "input": {
      "tool": "gmail_send_email",
      "arguments": {
        "to": "recipient@example.com",
        "subject": "Test",
        "body": "Hello from Composio MCP!"
      }
    }
  }'
```

## Task Types

### Composio MCP
**Task Type:** `core:text2text:composio`

**Input Format:**
```json
{
  "tool": "tool_name",
  "arguments": {
    "param1": "value1",
    "param2": "value2"
  },
  "context": "optional context"
}
```

### Klavis AI
**Task Type:** `core:text2text:klavis`

**Input Format:**
```json
{
  "operation": "query|store|retrieve",
  "data": "your data",
  "context": {},
  "metadata": {}
}
```

### Pipedream
**Task Type:** `core:text2text:pipedream`

**Input Format:**
```json
{
  "workflow_id": "your_workflow_id",
  "trigger_data": {
    "key": "value"
  },
  "config": {}
}
```

## Security Considerations

### Authentication Storage
- All MCP authentication credentials are encrypted at rest
- Credentials are stored per-user and never shared
- Admin cannot view user credentials

### Network Security
- All MCP requests are made over HTTPS
- Custom headers support for additional security layers
- Request/response logging for audit trails

### Access Control
- Only users with configured authentication can use MCP providers
- Admin controls which providers are available
- Per-provider enable/disable controls

## Troubleshooting

### Provider Not Available

**Problem**: MCP provider doesn't appear in the assistant

**Solutions**:
1. Check if provider is enabled in admin settings
2. Verify endpoint URL is configured
3. Ensure you have configured authentication
4. Check Nextcloud logs for errors

### Authentication Failures

**Problem**: MCP requests fail with 401/403 errors

**Solutions**:
1. Verify your authentication credentials are correct
2. Check if tokens have expired and need renewal
3. Test connection using the built-in test tool
4. Review MCP provider's authentication documentation

### Timeout Errors

**Problem**: MCP requests timeout or take too long

**Solutions**:
1. Check network connectivity to MCP endpoint
2. Verify MCP service is running and healthy
3. Review MCP provider logs for bottlenecks
4. Consider increasing timeout in provider configuration

## Advanced Configuration

### Custom Headers for MCP

You can add custom headers specifically for MCP providers using the Custom Headers feature. See [Custom Headers Guide](custom-headers.md) for details.

Example:
```
Provider Name: composio
Header Name: X-Request-ID
Header Value: nextcloud-assistant-{timestamp}
Encrypt: No
```

### MCP Provider Health Checks

Monitor MCP provider health through the admin panel:
- Last successful request timestamp
- Error rate statistics
- Average response time
- Connection status

## Development

### Adding New MCP Providers

To add a new MCP provider:

1. Create a new provider class extending `MCPProviderAdapter`:
```php
class MyMCPProvider extends MCPProviderAdapter {
    protected function getProviderType(): string {
        return 'myprovider';
    }
    
    // Implement required methods...
}
```

2. Register in `Application.php`:
```php
$context->registerTaskProcessingProvider(MyMCPProvider::class);
```

3. Add configuration constants to `Application.php`
4. Update MCPConfigService with new provider support

## Support

For issues or questions:
- Fork Repository: https://github.com/dkctc-tn/nt-nextcloud-assistant/issues
- MCP Specification: https://spec.modelcontextprotocol.io/

---

**Related Guides:**
- [Custom Headers Guide](custom-headers.md)
- [Branding Customization](branding.md)
