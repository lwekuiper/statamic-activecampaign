# Changelog

All notable changes to `statamic-activecampaign` will be documented in this file.

## 2.3.0 (2026-02-19)

### What's new
- Multi-site support with addon config for managing enabled sites and origins
- ActiveCampaignSites fieldtype for site selection
- Auto-creation of form config localizations when forms are saved
- Form listing now shows all forms with published/draft status and summary counts

### What's changed
- Aligned Vue components with Statamic core patterns
- Restructured route names with form-config prefix
- Removed defensive error handling from connector

## 2.2.0 (2026-02-18)

### What's new
- Support multiple lists and tags per form

### What's fixed
- Fix list and tag counts in listing columns
- Require both fields in merge_fields grid rows
- Update listing test assertions for count values
- Convert array field values to string

### What's changed
- Rename API methods, add Blink memoization, optimize fieldtype lookups
- Add `declare(strict_types=1)` to all PHP files
- Update README with Pro edition section

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

## 1.0.0 (2024-08-29)

### What's new
- This addon! 🎉
