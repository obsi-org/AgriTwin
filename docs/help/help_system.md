# AgriTwin Help System

## URL

The Help System is available at:

- `/agritwin3/agritwin_dashboard/help.php`

It supports the same language selector as the rest of AgriTwin:

- `/agritwin3/agritwin_dashboard/help.php?lang=en`
- `/agritwin3/agritwin_dashboard/help.php?lang=pt-PT`
- `/agritwin3/agritwin_dashboard/help.php?lang=es-ES`

## Content Structure

Short interface labels remain in the central translation dictionaries:

- `agritwin_dashboard/lang/en.php`
- `agritwin_dashboard/lang/pt-PT.php`
- `agritwin_dashboard/lang/es-ES.php`

Long-form help content is stored separately:

- `agritwin_dashboard/lang/help/en.php`
- `agritwin_dashboard/lang/help/pt-PT.php`
- `agritwin_dashboard/lang/help/es-ES.php`

Each help section uses:

- `id`: anchor used by contextual links.
- `title`: translated section title.
- `audience`: translated user audience.
- `admin`: optional flag for administrator-only guidance.
- `items`: translated help points.

## Contextual Links

Pages link to help sections with:

```php
agritwin_help_link('dashboard')
```

The helper preserves the selected language and points to the matching section anchor in `help.php`.

## Search

The Help page includes a client-side search input. It filters visible help cards by section title and content and requires no external service.

## Role-Aware Behaviour

All authenticated users can open the Help page. Administrative sections are visibly marked with the translated Administrator access notice. Access control for administrative actions remains enforced by the existing role checks on each protected page.

## Adding a Section

1. Add the same `id` to all three help content files.
2. Add translated title, audience and items.
3. Add contextual links from relevant pages with `agritwin_help_link('section-id')`.
4. Run `php scripts/i18n/audit_translations.php`.

## Verification

Recommended checks:

```bash
php scripts/i18n/audit_translations.php
php -l agritwin_dashboard/help.php
curl -L http://localhost/agritwin3/agritwin_dashboard/help.php?lang=en
curl -L http://localhost/agritwin3/agritwin_dashboard/help.php?lang=pt-PT
curl -L http://localhost/agritwin3/agritwin_dashboard/help.php?lang=es-ES
```
