# Statamic ActiveCampaign Integration

[![Latest Version](https://img.shields.io/packagist/v/lwekuiper/statamic-activecampaign.svg?style=flat-square)](https://packagist.org/packages/lwekuiper/statamic-activecampaign)
[![Total Downloads](https://img.shields.io/packagist/dt/lwekuiper/statamic-activecampaign.svg?style=flat-square)](https://packagist.org/packages/lwekuiper/statamic-activecampaign)

A powerful Statamic addon that seamlessly integrates your forms with ActiveCampaign, featuring automatic contact synchronization, custom field mapping, and multi-site support.

## ✨ Features

### 🆓 Lite Edition
- **Form Integration**: Connect any Statamic form to ActiveCampaign lists
- **Contact Sync**: Automatically create or update contacts in ActiveCampaign
- **Custom Fields**: Map form fields to ActiveCampaign custom fields
- **Consent Management**: Built-in GDPR compliance with consent field support
- **Tag Assignment**: Automatically tag contacts upon form submission
- **Array Field Support**: Handle multi-select fields and arrays seamlessly

### 🚀 Pro Edition
- **Multi-Site Support**: Configure different ActiveCampaign settings per site
- **Site-Specific Lists**: Route form submissions to different lists based on the current site
- **Localized Configurations**: Manage separate configurations for each locale

## 📋 Requirements

- **PHP**: 8.3 or higher
- **Laravel**: 12.0 or higher
- **Statamic**: 6.0 or higher
- **ActiveCampaign Account**: With API access enabled

> **Note**: For Statamic 4.x and 5.x support, use version 2.x of this addon.

## 🚀 Installation

### Via Statamic Control Panel
1. Navigate to **Tools > Addons** in your Statamic control panel
2. Search for "ActiveCampaign"
3. Click **Install**
ca
### Via Composer
```bash
composer require lwekuiper/statamic-activecampaign
```

The package will automatically register itself.

## ⚙️ Configuration

### 1. ActiveCampaign API Setup

Add your ActiveCampaign credentials to your `.env` file:

```env
ACTIVECAMPAIGN_API_KEY=your-api-key-here
ACTIVECAMPAIGN_API_URL=https://youraccountname.api-us1.com
```

> **💡 Tip**: You can find your API key and URL in your ActiveCampaign account under **Settings > Developer**.

### 2. Publish Configuration (Optional)

To customize the addon settings, publish the configuration file:

```bash
php artisan vendor:publish --tag=activecampaign-config
```

This creates `config/activecampaign.php` where you can modify default settings.

## 🎯 Pro Edition

> **🔥 Pro Features Available**
> Unlock multi-site capabilities with the Pro edition. Requires **Statamic Pro**.

### Upgrading to Pro

After purchasing the Pro edition, enable it in your `config/statamic/editions.php`:

```php
'addons' => [
    'lwekuiper/statamic-activecampaign' => 'pro'
],
```

### Pro Benefits
- **Multi-Site Management**: Different ActiveCampaign configurations per site
- **Site-Specific Routing**: Route submissions based on the current site
- **Enhanced Flexibility**: Perfect for agencies managing multiple client sites

## 📖 Usage Guide

### 1. Create Your Form

First, create your Statamic [form](https://statamic.dev/forms#content) as usual. Here's an example form blueprint:

```yaml
title: Newsletter Signup
fields:
  - handle: email
    field:
      type: email
      display: Email Address
      validate: required|email
  - handle: first_name
    field:
      type: text
      display: First Name
  - handle: interests
    field:
      type: checkboxes
      display: Interests
      options:
        sports: Sports
        music: Music
        technology: Technology
  - handle: consent
    field:
      type: toggle
      display: I agree to receive marketing emails
      validate: required|accepted
```

### 2. Configure ActiveCampaign Integration

1. Navigate to **Tools > ActiveCampaign** in your control panel
2. Click on the form you want to configure
3. Configure the integration settings:
   - **ActiveCampaign List**: Choose the target list
   - **Email Field**: Map to your form's email field
   - **Consent Field**: Map to your consent checkbox (for GDPR compliance)
   - **Custom Fields**: Map form fields to ActiveCampaign custom fields
   - **Tags**: Optionally assign tags to new contacts

### 3. Field Mapping

The addon supports mapping various field types:

#### Standard Fields
- Email → ActiveCampaign Email
- First Name → ActiveCampaign First Name
- Last Name → ActiveCampaign Last Name
- Phone → ActiveCampaign Phone

#### Custom Fields
Map any form field to ActiveCampaign custom fields:
- Text fields → Text custom fields
- Select/Radio → Single-value custom fields
- Checkboxes/Arrays → Comma-separated values

#### Array Field Handling
Multi-select fields are automatically converted to comma-separated strings:
```php
// Form submission: ['Sports', 'Music', 'Technology']
// Sent to ActiveCampaign: "Sports, Music, Technology"
```

### 4. Front-End Implementation

Use your form in templates as normal:

```antlers
{{ form:newsletter_signup }}
    {{ if errors }}
        <div class="alert alert-danger">
            {{ errors }}
                <p>{{ value }}</p>
            {{ /errors }}
        </div>
    {{ /if }}

    {{ if success }}
        <div class="alert alert-success">
            <p>Thank you! You've been subscribed to our newsletter.</p>
        </div>
    {{ /if }}

    <div>
        <label for="email">Email Address *</label>
        <input type="email" name="email" id="email" required>
    </div>

    <div>
        <label for="first_name">First Name</label>
        <input type="text" name="first_name" id="first_name">
    </div>

    <div>
        <label>
            <input type="checkbox" name="consent" value="1" required>
            I agree to receive marketing emails
        </label>
    </div>

    <button type="submit">Subscribe</button>
{{ /form:newsletter_signup }}
```

## 🔧 Advanced Configuration

### Multi-Site Setup (Pro Edition)

For multi-site setups, configure different settings per site:

1. Each site can have its own ActiveCampaign list
2. Different field mappings per locale
3. Site-specific tags and custom fields

### Custom Field Types

The addon handles various Statamic field types:
- **Text/Textarea**: Direct mapping
- **Select/Radio**: Single value mapping
- **Checkboxes**: Comma-separated string
- **Date**: ISO format string
- **Toggle**: Boolean to string conversion

### Error Handling

The addon gracefully handles:
- Invalid API credentials
- Network timeouts
- Missing required fields
- Invalid email addresses

## 🐛 Troubleshooting

### Common Issues

**Form submissions not appearing in ActiveCampaign:**
1. Check your API credentials in `.env`
2. Verify the form configuration exists
3. Ensure the email field is properly mapped
4. Check if consent field is required and properly set

**Multi-site configuration not working:**
1. Confirm Pro edition is enabled
2. Verify Statamic Pro is installed
3. Check site-specific configurations

**Array fields not formatting correctly:**
1. Ensure fields contain valid, non-empty values
2. Check ActiveCampaign custom field accepts text input

### Debug Mode

Enable logging by adding to your `.env`:
```env
LOG_LEVEL=debug
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This addon requires a license for use in production. You may use it without a license while developing locally.

## 🆘 Support

- **Documentation**: [Full documentation](https://github.com/lwekuiper/statamic-activecampaign)
- **Issues**: [GitHub Issues](https://github.com/lwekuiper/statamic-activecampaign/issues)
- **Discussions**: [GitHub Discussions](https://github.com/lwekuiper/statamic-activecampaign/discussions)

---

Made with ❤️ for the Statamic community
