# Changelog

All notable changes to `statamic-activecampaign` will be documented in this file.

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
