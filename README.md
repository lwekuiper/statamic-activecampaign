# Statamic ActiveCampaign Integration

[![Latest Version](https://img.shields.io/packagist/v/lwekuiper/statamic-activecampaign.svg?style=flat-square)](https://packagist.org/packages/lwekuiper/statamic-activecampaign)
[![Total Downloads](https://img.shields.io/packagist/dt/lwekuiper/statamic-activecampaign.svg?style=flat-square)](https://packagist.org/packages/lwekuiper/statamic-activecampaign)

A powerful Statamic addon that seamlessly integrates your forms with ActiveCampaign, featuring automatic contact synchronization, custom field mapping, and multi-site support.

> 💡 **Have an idea?** We'd love to hear it! Please [open a feature request](https://github.com/lwekuiper/statamic-activecampaign/issues/new?labels=enhancement) on GitHub.

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

## 📖 Documentation

For the full usage guide — including form setup, field mapping, consent management, multi-site configuration, troubleshooting, and more — see [DOCUMENTATION.md](DOCUMENTATION.md).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This addon requires a license for use in production. You may use it without a license while developing locally.

## 🆘 Support

- **Documentation**: [DOCUMENTATION.md](DOCUMENTATION.md)
- **Issues**: [GitHub Issues](https://github.com/lwekuiper/statamic-activecampaign/issues)
- **Discussions**: [GitHub Discussions](https://github.com/lwekuiper/statamic-activecampaign/discussions)

## ⚖️ Disclaimer

This addon is a third-party integration and is **not** affiliated with, endorsed by, or officially connected to ActiveCampaign, LLC. "ActiveCampaign" is a registered trademark of ActiveCampaign, LLC. All product names, logos, and brands are property of their respective owners.

---

Made with ❤️ for the Statamic community
