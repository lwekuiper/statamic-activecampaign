# Changelog

All notable changes to `statamic-activecampaign` will be documented in this file.

## 3.2.1 (2026-02-13)

### What's changed
- Added `declare(strict_types=1)` to all PHP files
- Simplified fieldtype comboboxes to use direct v-model binding

## 3.2.0 (2026-02-12)

### What's fixed
- Memoize API calls with Blink to eliminate redundant requests within a single lifecycle
- Use collection endpoints in fieldtype lookups to prevent N+1 API calls

### What's changed
- Renamed internal `listTags` and `listCustomFields` methods to `getTags` and `getCustomFields`

## 3.1.0 (2026-02-09)

### What's new
- Support for multiple lists and tags per form configuration

### What's changed
- `list_id` field renamed to `list_ids` (now accepts multiple lists)
- `tag_id` field renamed to `tag_ids` (now accepts multiple tags)

## 3.0.0 (2026-02-06)

### What's new
- Statamic 6 support
- Inertia.js pages replacing Blade views
- Vue 3 composition API components

### What's changed
- Requires Statamic 6 and Laravel 12
- Dropped support for Statamic 4/5 and Laravel 10/11

## 2.1.1 (2025-07-14)

### What's fixed
- Convert array field values to string before sending to ActiveCampaign

## 2.1.0 (2025-05-05)

### What's new
- Add Laravel 12 support

## 2.0.1 (2025-05-05)

### What's fixed
- Use email field from form configuration

## 2.0.0 (2025-01-10)

### What's new
- Custom Stache store for form configurations
- New `FormConfig` and `FormConfigCollection` data classes
- New `FormConfigRepository` and `FormConfigStore` for data persistence
- Form configuration listing in the Control Panel
- Publishable config file
- CI/CD with GitHub Actions
- Comprehensive test suite

### What's changed
- Renamed `ActiveCampaignService` to `ActiveCampaignConnector`
- Renamed `FormFields` fieldtype to `StatamicFormFields`
- Replaced `ActiveCampaignController` with `FormConfigController`

## 1.0.2 (2024-09-03)

### What's new
- Get all tags and lists from ActiveCampaign

## 1.0.1 (2024-09-02)

### What's changed
- Publish the compiled production assets

## 1.0.0 (2024-08-29)

### What's new
- Initial release
