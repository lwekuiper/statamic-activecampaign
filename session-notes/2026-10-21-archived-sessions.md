---
date: 2026-10-21
repo: lwekuiper/statamic-activecampaign
type: session-close
tags: [statamic-activecampaign, session-close, backlog]
---

# statamic-activecampaign: findings from 13 archived Claude sessions

On 2026-10-21, 13 open cloud sessions for this repo were archived without running `/session-close`. Afterwards their transcripts were read in full and checked against GitHub. This note holds what they would have produced. A draft repo CLAUDE.md sits next to this file as `CLAUDE.draft.md`.

**Copy into the vault:** this note → `System/Session-log/2026-10-21.md` (as a `## Session close` section) or a project note; the tasks below → `System/Tasks.md` under `## Open`.

## Tasks

- [ ] **Urgent: rescue unreferenced commits** before GitHub garbage-collects them. Their branches were deleted; for each, run `git fetch origin <sha> && git push origin <sha>:refs/heads/<name>`:
  - `11409439ef8a0d91aef0670998fa7c1f1ad3b652`: feat: add site tracking Antlers tag (`{{ activecampaign:tracking }}`, `ACTIVECAMPAIGN_TRACKING_ID`, identifies logged-in users) → `claude/site-tracking-xNt0v`
  - `de5dffe128fc51a3b30272f4033d8d73e5291622`: feat: add automation triggers on form submission (`activecampaign_automation` fieldtype, `automation_ids`, `getAutomations`/`addContactToAutomation` via `contactAutomations`) → `claude/automation-triggers-xNt0v`
  - `ea7b48b95252ac4022233efeec8f7fc5f01fc1cf`: `RESEARCH-activecampaign-features.md`, a feature-gap analysis across CMS plugins → `claude/research-activecampaign-features-xNt0v`
  - Both features also edit the YAML blueprint, which main has since deleted (see below).
- [ ] **Fix the TestCase namespace bug on main.** `tests/TestCase.php` declares `Lwekuiper\StatamicActiveCampaign\Tests` (capital C), but `composer.json` autoload-dev maps `Lwekuiper\StatamicActivecampaign\Tests`. It only works because of load order, and breaks when a new test file is added. Verified on `316b7af`.
- [ ] **Add a CLAUDE.md to the repo**, starting from `CLAUDE.draft.md`. There is none, so every session re-learned the same things.
- [ ] **Conditional lists on main (issue #7 shipped on 2.x only, in 2.4.0).** Two unmerged duplicates exist: `feat/conditional-lists` (newer, built on the PHP blueprint) and `claude/sync-branches-qfX0B` (YAML blueprint, conflicts). Keep `feat/conditional-lists`, rebase it onto main, re-version (its changelog says 3.4.0, which is now the Laravel 13 release; use 3.5.0 or the next free version), and delete the other.
- [ ] **Enable/disable toggle for form configs.** `claude/add-form-config-toggle-AIFjl` (`d8ede46`) is done and tested. Rebase it and move the `enabled` field from `config.yaml` into the PHP blueprint in `FormConfigController`, then add a changelog entry and open a PR. Its design: localizable toggle, default true; the listener checks `=== false` so old configs stay enabled; the listing shows disabled forms as "draft".
- [ ] **Test coverage branch.** `claude/improve-test-coverage-9Y4bY` takes the suite from 64 to 112 tests in 7 commits. It merges cleanly; re-run after the store-handle rename and Laravel 13 changes. Reword the commit messages to conventional style if wanted.
- [ ] **Three acumbamail-derived branches**, one commit each, 3 behind main, no changelog: `feature/is-configured-check`, `feature/free-edition` (needs the TestCase edition set to lite, because the first edition becomes the default), `feature/custom-permissions` (`authorize()` 403s, so it uses `abort_unless(isSuper || hasPermission)`). Merge, rebase or drop.
- [ ] **2.x field filtering + UX.** `claude/filter-form-fields-by-type-mnks0` (email selector shows only `input_type: email`, consent selector only toggles; `filter_form_fields` config switch) and `claude/ux-improvements-mnks0` (empty states, stale-field warning, field-type labels). These overlap and are not stacked. Both edit the deleted YAML blueprint and never rebuilt `resources/dist`. **A GitHub CI failure you reported there was never diagnosed.** Not ported to main.
- [ ] **Connector hardening** (from the competitor comparison): validate the email with `filter_var(..., FILTER_VALIDATE_EMAIL)` before calling the API, and set an explicit HTTP timeout (the competitor uses 10s).
- [ ] **Decide: deal/pipeline creation.** A design exists (getPipelines/getStages/createDeal, a reactive pipeline→stage fieldtype, `deal_*` blueprint fields, a `createDeal()` step at the end of `AddFromSubmission`). Five open questions: how the title is templated, static value vs value from a form field, how pipeline/stage are coupled, Pro-only vs Lite, owner assignment.
- [ ] **Decide: syncing registered users.** It needs its own config. Note that `UserRegistered` fires only for front-end sign-ups; CP-created users fire `UserCreated`.
- [ ] **Clean up stale branches** once the items above are decided.

## Feature backlog (from the research session, never acted on)

Deal/pipeline creation · embedded ActiveCampaign forms · inbound webhooks (ActiveCampaign → Statamic) · conditional content tags · contact notes · double opt-in (a list-level setting in ActiveCampaign, not an API parameter) · e-commerce deep data · contact activity dashboard in the CP · bulk/historical sync · contact scoring · syncing registered users. Abandoned idea: replicator-based value→list mapping (2.x shipped a grid instead) and tag mapping by field value.

## Product roadmap ideas (premium addons research)

- The FormConfig/Store/Repository + `Localization`/`HasOrigin` pattern is reusable.
- Suggested order: more form-to-CRM addons first (Brevo, Klaviyo, Kit, Drip; about $19 Lite / $39 Pro), then a per-collection, per-site search sync addon (Algolia/Meilisearch) as the flagship, then ports to Craft (via Formie) and WordPress.
- Statamic marketplace authors get a 75% revenue share. Multi-site support is the natural Pro feature.

## What each session did

| Session | Outcome |
|---|---|
| Laravel 13 support | Redone by hand on main as v3.4.0. Missed `registerSerializableClasses([FormConfig::class])`, which Laravel 13 needs |
| Learn from the acumbamail package | 3 `feature/*` branches, unmerged |
| Compare with the competing ActiveCampaign connector | Research only; ours is a superset; produced the connector hardening ideas |
| Patch release mirroring hubspot | Merged as 3.3.1 / 2.4.1; blueprint moved to PHP |
| Sync main and 2.x | Unmerged; superseded by `feat/conditional-lists` |
| Filter form fields by type (2.x) | 2 unmerged branches; CI failure never diagnosed |
| List field mapping with a replicator | Abandoned; 2.x shipped a grid |
| Checkbox/radio field mapping (2.x) | Shipped, reworked, in 2.4.0; closes #7 |
| Deal pipeline design | Design only; 5 open questions |
| Enable/disable toggle | Done; branch unmerged and conflicting |
| Premium addon research | Roadmap only |
| Test coverage | Done; branch unmerged |
| Feature-gap research | 2 features built; branches deleted, commits orphaned |

## Retro: things worth knowing next time

- **No session opened a PR.** Work piled up on branches and drifted from main; three branches were deleted with their work still unmerged. Next time, end each feature with a PR or an explicit "drop".
- **The Laravel 13 session never ran composer or phpunit**, and so missed the serialization fix. Always run the suite on the new framework version.
- **One feature per branch** had to be restated twice: Claude bundled features, then had to split them.
- **Version collisions across branches:** an unmerged branch claimed 3.4.0, and main used it first. Pick the version number at release time, not on the feature branch.
- **Running `/session-close` is per-session.** It has no `archive` mode. Run it inside each session before archiving, or have the archiving session run this kind of retrospective first.
