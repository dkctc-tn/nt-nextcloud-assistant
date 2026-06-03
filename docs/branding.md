# Branding Customization Guide

## Overview

The NormieTranslator Assistant fork includes comprehensive branding customization features, allowing you to tailor the assistant's appearance to match your organization's identity.

## Features

- **Custom App Name**: Change "NormieTranslator Assistant" to your organization's name
- **Custom Logo**: Upload or specify a custom logo image
- **Color Theming**: Set primary color to match your brand
- **Header Text**: Add custom header or welcome text
- **White-Label Mode**: Remove all references to original branding

## Admin Configuration

### Accessing Branding Settings

1. Navigate to **Settings → Administration → NormieTranslator Assistant**
2. Click on the **Branding** tab
3. You'll see the branding configuration options

### Customization Options

#### App Name

**Default**: `NormieTranslator Assistant`

Change the display name shown throughout the interface:
```
Custom App Name: Acme AI Assistant
```

This name appears in:
- App menu
- Page titles
- Notifications
- System messages

#### App Logo

**Default**: Sobo the cat icon

Options for custom logo:
1. **File Upload**: Upload an image file (PNG, SVG, JPG)
2. **File Path**: Specify path to existing file in Nextcloud

**Recommended specifications:**
- Format: SVG (preferred) or PNG
- Size: 32x32 pixels minimum
- Transparent background recommended
- Max file size: 1MB

Example:
```
Logo Path: /var/www/nextcloud/apps/nt_assistant/img/custom-logo.svg
```

#### Primary Color

**Default**: `#0082c9` (Nextcloud blue)

Set your brand's primary color:
```
Color: #FF5733
```

This color is used for:
- Buttons and links
- Active states
- Highlights
- Progress indicators

**Color format**: Hex code (#RRGGBB)

#### Header Text

**Default**: None

Add custom welcome or instruction text:
```
Header Text: Welcome to Acme AI Assistant - Your intelligent productivity companion
```

This text appears:
- At the top of the assistant interface
- In welcome screens
- As context for users

#### White-Label Mode

**Default**: `Disabled`

Enable to remove all references to:
- "Nextcloud Assistant"
- "NormieTranslator"
- Original branding elements
- Attribution text (where permitted by license)

**Note**: AGPL license still requires source code attribution in footer.

## Configuration via API

### Get Current Branding

```bash
curl -X GET \
  https://your-nextcloud.com/apps/nt_assistant/api/config/branding \
  -H "Authorization: ******"
```

Response:
```json
{
  "appName": "NormieTranslator Assistant",
  "appLogo": null,
  "appColor": "#0082c9",
  "headerText": null,
  "whiteLabelMode": false
}
```

### Set Branding (Admin Only)

```bash
curl -X POST \
  https://your-nextcloud.com/apps/nt_assistant/api/config/branding \
  -H "Authorization: ******" \
  -H "Content-Type: application/json" \
  -d '{
    "appName": "Acme AI Assistant",
    "appColor": "#FF5733",
    "headerText": "Your intelligent assistant",
    "whiteLabelMode": true
  }'
```

### Reset to Defaults (Admin Only)

```bash
curl -X POST \
  https://your-nextcloud.com/apps/nt_assistant/api/config/branding/reset \
  -H "Authorization: ******"
```

## Branding Best Practices

### App Name
- Keep it concise (2-4 words)
- Make it descriptive and memorable
- Consider your audience
- Avoid special characters

### Logo
- Use vector format (SVG) when possible
- Ensure good contrast with both light and dark themes
- Test at different sizes
- Keep design simple and recognizable

### Color
- Choose a color with good accessibility
- Ensure sufficient contrast with white/black text
- Test with colorblind simulation tools
- Consider your brand guidelines

### Header Text
- Keep it welcoming but brief
- Provide value or context
- Update seasonally if desired
- Test different languages if applicable

## CSS Customization

For advanced theming, you can add custom CSS through Nextcloud's theming app:

```css
/* Custom branding styles */
.nt-assistant-header {
    background-color: var(--nt-brand-color);
}

.nt-assistant-logo {
    filter: brightness(1.1);
}
```

## Multi-Tenancy

For multi-tenant installations:
- Each tenant can have different branding
- Branding is stored at the app config level
- Use Nextcloud's multi-instance features
- Consider per-instance databases

## Troubleshooting

### Logo Not Displaying

**Problem**: Custom logo doesn't show up

**Solutions**:
1. Check file path is absolute and correct
2. Verify file permissions (readable by web server)
3. Ensure image format is supported
4. Clear browser cache
5. Check browser console for 404 errors

### Color Not Applying

**Problem**: Custom color doesn't change interface

**Solutions**:
1. Verify hex color format (#RRGGBB)
2. Clear browser and Nextcloud caches
3. Check for CSS conflicts
4. Ensure white-label mode is properly configured

### White-Label Mode Issues

**Problem**: Original branding still visible

**Solutions**:
1. Clear all caches (browser, Nextcloud, Redis if used)
2. Check if templates are cached
3. Rebuild frontend assets (`npm run build`)
4. Verify white-label mode is enabled in database

## Legal Considerations

### AGPL Compliance

Even in white-label mode, you must:
- Display source code link in app footer
- Preserve copyright notices in source code
- Make modified source available
- Include AGPL license text

### Trademark

- Don't use Nextcloud or NormieTranslator trademarks
- Don't claim official affiliation without permission
- Respect original authors' credits

## Examples

### Corporate Branding

```json
{
  "appName": "Acme AI Assistant",
  "appLogo": "/var/www/nextcloud/custom/acme-logo.svg",
  "appColor": "#003366",
  "headerText": "Acme Corporation - AI-Powered Productivity",
  "whiteLabelMode": true
}
```

### Educational Institution

```json
{
  "appName": "University AI Helper",
  "appLogo": "/var/www/nextcloud/custom/university-seal.png",
  "appColor": "#8B0000",
  "headerText": "Your Academic AI Companion",
  "whiteLabelMode": false
}
```

### Non-Profit Organization

```json
{
  "appName": "Community Assistant",
  "appLogo": "/var/www/nextcloud/custom/heart-icon.svg",
  "appColor": "#2ECC71",
  "headerText": "Helping our community thrive",
  "whiteLabelMode": true
}
```

## Support

For issues or questions:
- Fork Repository: https://github.com/dkctc-tn/nt-nextcloud-assistant/issues

---

**Related Guides:**
- [Custom Headers Guide](custom-headers.md)
- [MCP Integration Guide](mcp-integration.md)
