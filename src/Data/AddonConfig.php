<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Data;

use Illuminate\Support\Collection;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;

class AddonConfig
{
    protected ?Collection $sites = null;

    public function sites(): Collection
    {
        if ($this->sites !== null) {
            return $this->sites;
        }

        $path = $this->path();

        if (! file_exists($path)) {
            return $this->sites = $this->defaultSites();
        }

        $data = YAML::file($path)->parse();

        return $this->sites = collect($data['sites'] ?? []);
    }

    public function save(Collection $sites): void
    {
        $this->sites = $sites;

        $directory = dirname($this->path());

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents(
            $this->path(),
            YAML::dump(['sites' => $sites->all()])
        );
    }

    public function originFor(string $site): ?string
    {
        return $this->sites()->get($site);
    }

    public function hasOrigin(string $site): bool
    {
        return $this->originFor($site) !== null;
    }

    public function isEnabled(string $site): bool
    {
        return $this->sites()->has($site);
    }

    public function configFileExists(): bool
    {
        return file_exists($this->path());
    }

    public function path(): string
    {
        return base_path('resources/activecampaign/config.yaml');
    }

    public function fresh(): static
    {
        $this->sites = null;

        return $this;
    }

    protected function defaultSites(): Collection
    {
        return Site::all()->mapWithKeys(fn ($site) => [$site->handle() => null]);
    }
}
