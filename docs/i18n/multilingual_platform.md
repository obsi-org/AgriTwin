# Multilingual Platform

AgriTwin supports English (`en`), Portuguese from Portugal (`pt-PT`) and Spanish from Spain (`es-ES`).

## Structure

Translations are plain PHP arrays:

- `agritwin_dashboard/lang/en.php`
- `agritwin_dashboard/lang/pt-PT.php`
- `agritwin_dashboard/lang/es-ES.php`

Shared helpers live in `agritwin_dashboard/includes/hmi_data.php`:

- `agritwin_supported_languages()`
- `agritwin_current_language()`
- `agritwin_t()`

## Selection Persistence

The language selector is rendered in the shared header. Selection is persisted in the session and an `agritwin_lang` cookie scoped to `/agritwin3/`. Logged-in users also carry a `preferred_language` database field.

## Adding Strings

Add the same key to all three language files and render it with `agritwin_t('key')`. Organisation names such as AgriTwin, OpenAgri, Fotovoltaica Macotera SL and OBSIDIAN remain untranslated.
