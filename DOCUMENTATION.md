# Statamic ActiveCampaign Documentation

## Table of Contents

- [Overview](#overview)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Field Mapping](#field-mapping)
- [GDPR Consent](#gdpr-consent)
- [Multi-Site Support (Pro)](#multi-site-support-pro)
- [Permissions](#permissions)
- [Troubleshooting](#troubleshooting)

---

## Overview

Statamic ActiveCampaign is an addon that connects your Statamic forms to ActiveCampaign. When a form is submitted, the addon automatically creates or updates a contact in ActiveCampaign, subscribes them to lists, assigns tags, and maps custom fields.

### Editions

The addon is available in two editions:

**Lite** (default):
- Connect any Statamic form to ActiveCampaign
- Automatic contact creation and updating
- Map form fields to ActiveCampaign standard and custom fields
- GDPR consent field support
- Tag assignment on form submission
- Array/multi-select field handling

**Pro** (requires Statamic Pro):
- Everything in Lite
- Per-site ActiveCampaign configurations
- Site-specific list routing
- Localized form configurations per locale

## Requirements

- PHP 8.3+
- Laravel 12.0+
- Statamic 6.0+
- An ActiveCampaign account with API access

For Statamic 4.x/5.x support, use version 2.x of this addon.

## Installation

### Via Statamic Control Panel

1. Go to **Tools > Addons**
2. Search for "ActiveCampaign"
3. Click **Install**

### Via Composer

```bash
composer require lwekuiper/statamic-activecampaign
```

The addon auto-registers via Laravel's package discovery. The configuration file is published automatically on install.

## Configuration

### API Credentials

Add your ActiveCampaign API credentials to your `.env` file:

```env
ACTIVECAMPAIGN_API_URL=https://youraccountname.api-us1.com
ACTIVECAMPAIGN_API_KEY=your-api-key-here
```

You can find these values in your ActiveCampaign account under **Settings > Developer**.

### Configuration File

The configuration file is published to `config/statamic/activecampaign.php` during installation. You can also publish it manually:

```bash
php artisan vendor:publish --tag=statamic-activecampaign-config
```

The config file contains two settings:

```php
return [
    'api_url' => env('ACTIVECAMPAIGN_API_URL'),
    'api_key' => env('ACTIVECAMPAIGN_API_KEY'),
];
```

### Enabling Pro Edition

To use multi-site features, enable the Pro edition in `config/statamic/editions.php`:

```php
'addons' => [
    'lwekuiper/statamic-activecampaign' => 'pro',
],
```

## Usage

### Step 1: Create a Form

Create a Statamic form with the fields you want to send to ActiveCampaign. At minimum, you need an email field. Example blueprint:

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
  - handle: last_name
    field:
      type: text
      display: Last Name
  - handle: consent
    field:
      type: toggle
      display: I agree to receive marketing emails
      validate: required|accepted
```

### Step 2: Configure the Integration

1. Navigate to **Tools > ActiveCampaign** in the control panel
2. Click on the form you want to configure
3. Fill in the configuration:
   - **Email Field** (required): Select the form field containing the subscriber's email address
   - **Consent Field** (optional): Select a boolean/toggle field for GDPR consent
   - **Lists** (required): Choose one or more ActiveCampaign lists to subscribe the contact to
   - **Tags** (optional): Choose tags to apply to the contact
   - **Merge Fields** (optional): Map form fields to ActiveCampaign fields

### Step 3: Use the Form in Templates

Use the form in your Antlers templates as usual. No special markup is needed for the ActiveCampaign integration:

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
        <p>Thank you for subscribing!</p>
    {{ /if }}

    <div>
        <label for="email">Email Address</label>
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

## Field Mapping

Use merge fields to map your Statamic form fields to ActiveCampaign contact fields. Both standard fields (`firstName`, `lastName`, `phone`) and custom fields from your ActiveCampaign account are available in the merge fields dropdown.

Multi-select fields (checkboxes, multi-select, etc.) are automatically converted to comma-separated strings. Fields with empty or null values are filtered out and not sent to ActiveCampaign.

## GDPR Consent

The consent field provides GDPR compliance by requiring explicit opt-in before sending data to ActiveCampaign.

**How it works:**

- If a consent field is configured, the submission is only sent to ActiveCampaign when the field has a truthy value
- If consent is not given, the Statamic form submission is still saved, but no data is sent to ActiveCampaign
- If no consent field is configured, all submissions are processed

## Multi-Site Support (Pro)

With the Pro edition and Statamic's multi-site enabled, you can configure different ActiveCampaign integrations per site. Each site gets its own form configuration with separate lists, tags, and field mappings.

The control panel shows a site selector on both the listing and edit pages. When a form is submitted, the addon automatically detects which site it belongs to.

## Permissions

Access to the ActiveCampaign control panel section requires one of:

- **Super admin** status
- The **`configure forms`** permission

The navigation item is visible to users who can `index` forms.

## Troubleshooting

### Submissions not appearing in ActiveCampaign

1. **Check API credentials**: Verify `ACTIVECAMPAIGN_API_URL` and `ACTIVECAMPAIGN_API_KEY` in your `.env` file
2. **Check the form configuration**: Navigate to **Tools > ActiveCampaign** and verify the form has a configuration with the email field and at least one list
3. **Check consent**: If a consent field is configured, ensure it resolves to a truthy value in the submission
4. **Check logs**: Look for error messages in `storage/logs/laravel.log`

### Configuration not saving

1. Ensure the `resources/activecampaign/` directory exists and is writable
2. Clear the Stache cache: `php please stache:clear`

### Lists or tags not loading in the control panel

1. Verify your API credentials are correct
2. Check that your ActiveCampaign account has lists/tags created
3. Check logs for API error messages

### Multi-site not working

1. Confirm the Pro edition is enabled in `config/statamic/editions.php`
2. Confirm Statamic Pro is installed and multi-site is configured
3. Verify site-specific configuration files exist in the correct locale subdirectories

### Debug logging

Enable detailed logging by setting `LOG_LEVEL=debug` in your `.env` file. API errors are always logged regardless of log level.
