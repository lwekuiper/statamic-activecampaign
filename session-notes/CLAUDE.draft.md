# statamic-activecampaign

Statamic addon that syncs form submissions to ActiveCampaign (contacts, lists, tags, merge fields).

## Branches

- `main` is v3.x: Statamic 6.5+, Laravel 12/13, PHP 8.3+, Vue 3 `<script setup>` with `ui-combobox`.
- `2.x` is the legacy line: Statamic 4/5, Vue 2 `v-select`. It still gets backported fixes (e.g. v2.4.2).
- The two lines diverged, so port fixes and features by hand. Never blind cherry-pick; the blueprint and code differ.
- Ask whether a fix should also land on `2.x`. Keep minor versions roughly in step across the lines.
- Branch from `origin/main`. Fresh checkouts may have a stale local `master`.
- One feature per branch, named after its content. Follow-up ideas go on a separate branch.

## Commands

- `composer install && vendor/bin/phpunit` (fresh containers have no `vendor/`).
- Run `composer dump-autoload` after adding a test directory.
- Frontend: `npm install && npm run build`. `resources/dist` is committed as its own `build: compile frontend assets` commit.
- CI runs phpunit only: PHP 8.3–8.5 × Laravel 12/13 (testbench 10/11). There is no Pint config and no coverage driver.

## Commits and releases

- Conventional prefixes: `feat:`, `fix:`, `refactor:`, `test:`, `docs:`, `build:`, `chore:`. One commit per logical step.
- The changelog entry is its own commit: `docs: add changelog entry for vX.Y.Z`.
- CHANGELOG format: `## X.Y.Z (YYYY-MM-DD)` with "What's new / What's changed / What's fixed".
- User docs live in `DOCUMENTATION.md`. Update the requirements in README.md and DOCUMENTATION.md when constraints change.
- Raising PHP/Statamic minimums alongside new framework support is a minor release. State the version bump and why.
- Tag `vX.Y.Z` on whichever line the release is cut from.

## Architecture

- `FormConfig` / `FormConfigStore` / `FormConfigRepository` live in the Stache under the handle `activecampaign-form-configs`. Run `php please stache:clear` after upgrading.
- `FormConfigStore` holds the legacy data migrations (`list_id`→`list_ids`, `tag_id`→`tag_ids`). Writing directly to the model skips them.
- The form config blueprint is built in PHP in `FormConfigController` (`Blueprint::make()->setContents()`). `resources/blueprints/config.yaml` no longer exists; `Blueprint::find()` could return null.
- Connector reads are memoized with `Blink::once`. The connector uses `->throw()` and has an `isConfigured()` guard.
- Lists, tags and automations are Relationship fieldtypes.
- Editions live in `composer.json` under `extra.statamic.editions`. Multi-site support is Pro-only and uses the `AddonConfig` site-origin map.
- Sibling addons (statamic-hubspot, statamic-acumbamail, statamic-salesforce, statamic-connect) share this architecture. Compare with them and mirror fixes.

## Gotchas

- Laravel 13: `ServiceProvider::register()` must call `registerSerializableClasses([FormConfig::class])`. Without it, cached configs come back as `__PHP_Incomplete_Class`.
- `tests/TestCase.php` uses the namespace `...\StatamicActiveCampaign\Tests` (capital C), but PSR-4 maps `StatamicActivecampaign`. It only works because of load order.
- `AddonConfig::save()` writes a real file. Tests that save it need a `tearDown` that deletes `path()` and calls `fresh()`.
- Because the connector throws, a bare `Http::fake()` breaks tests. Fake `*/api/3/lists*`, `*/api/3/tags*` and `*/api/3/fields*` with explicit JSON.
- The first entry in `editions` becomes the default edition in tests.
- `$this->authorize()` with custom addon permissions can 403, because permissions boot before the addon registers them. `hasPermission()` is flat: a child permission doesn't grant its parent.
- Blueprint `if:` conditions only hide fields in the control panel; the backend still validates them. Pair with `required_if`. A button_group `default` is the option key, not the label. Fieldtype `defaultValue()` must be public.
- Checkbox values arrive wrapped in an array; toggles arrive truthy or falsy.
- `UserRegistered` fires only for front-end sign-ups; users created in the control panel fire `UserCreated`.
- ActiveCampaign double opt-in is a list-level setting, not an API parameter.

## Working with the maintainer

- For design work, offer alternatives and research how other addons (Mailchimp, MailerLite, Brevo, Gravity Forms…) do it before building. Ask open design questions first.
- Labels must make sense to marketing users. Handle names should match existing ones (e.g. `list_fields` parallels `merge_fields`).
- Pays close attention to control-panel layout: field widths, sections, instructions.
- Don't test the obvious. Add test coverage on a dedicated branch with logical commits.
