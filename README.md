# Subscribe forms to ActiveCampaign

> This package provides an easy way to integrate ActiveCampaign with Statamic forms.

## Features

This addon allows you to:

- Configure Statamic forms to subscribe to a ActiveCampaign list
- Use multi-site to add form configurations per localization

## Requirements

* PHP 8.2+
* Laravel 10.0+
* Statamic 4.0+

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

``` bash
composer require lwekuiper/statamic-activecampaign
```

The package will automatically register itself.

## Configuration

Set your ActiveCampaign API Key and URL in your `.env` file.

```yaml
ACTIVECAMPAIGN_API_KEY=your-key-here
ACTIVECAMPAIGN_API_URL=your-url-here
```

## How to Use

Create your Statamic [forms](https://statamic.dev/forms#content) as usual. Don't forget to add the consent field to your blueprint.
