# Profile Card Contact Display — Design Spec

**Date:** 2026-04-13

## Goal

Surface phone/email contact info directly in the directory listing cards, with professional icons, and add a "Contacter" shortcut button so visitors don't have to open the full profile to initiate contact.

## Scope

Two surfaces are in scope:

1. **`resources/views/livewire/profile/profile-card.blade.php`** — the card used in the annuaire grid
2. ~~`resources/views/profile/show.blade.php`~~ — already implemented in the previous feature (Task 7)

The `ProfileCard` Livewire component (`app/Livewire/Profile/ProfileCard.php`) is a dumb pass-through; all new logic is pure Blade.

## Architecture

No new PHP classes or database changes are needed. Both additions are Blade-only changes to the profile card template, reading from already-available model attributes (`show_phone`, `phone`, `show_email_contact`, `email_contact`) that were added in the previous migration.

## Profile Card Changes

### 1. Contact info strip

Positioned between the skills preview block and the action buttons. Only renders when `($profile->show_phone && $profile->phone) || ($profile->show_email_contact && $profile->email_contact)` is true.

- **Email row:** envelope icon (SVG, `w-3.5 h-3.5`, gray) + `email_contact` as a `mailto:` link, `truncate` class to prevent overflow, `text-xs text-[#0066CC]`
- **Phone row:** phone icon (SVG, `w-3.5 h-3.5`, gray) + `phone` as a `tel:{{ preg_replace('/\s+/', '', $profile->phone) }}` link, `text-xs text-[#0066CC]`

Each row: `flex items-center gap-1.5`.

The strip has a top border (`border-t border-gray-100 pt-3 mt-3`) to visually separate from skills.

### 2. Action buttons

Replace the single full-width "Voir le profil" anchor with a flex row of two buttons.

**"Voir le profil"** (always visible):
- Style: `border border-[#0066CC] text-[#0066CC] hover:bg-[#0066CC] hover:text-white transition`
- `flex-1`

**"Contacter"** (conditional):
- `@auth` and not own profile (`auth()->id() !== $profile->user_id`): links to `route('profile.show', $profile) . '#contact-form'`
- `@guest`: links to `route('login')`
- Own profile (`auth()->id() === $profile->user_id`): button not rendered; "Voir le profil" takes full width
- Style: `bg-[#0066CC] text-white hover:bg-blue-800 transition`
- `flex-1`

When both buttons are shown the wrapper is `flex gap-2`. When only "Voir le profil" is shown it reverts to `block` (full width, as before).

## Data availability

`SearchDirectory` already eager-loads `skills` and `sector` on profiles. The new fields (`phone`, `email_contact`, `show_phone`, `show_email_contact`) are already on the `profiles` table and loaded via the default Eloquent select — no query changes needed.

## Testing

One new feature test: `tests/Feature/Directory/ProfileCardTest.php`

- Authenticated user sees "Contacter" link pointing to `profile.show#contact-form` on another member's card
- Guest sees "Contacter" link pointing to `login`
- Profile owner sees only "Voir le profil" (no "Contacter")
- Card shows email when `show_email_contact = true` and email is set
- Card hides email when `show_email_contact = false`
- Card shows phone when `show_phone = true` and phone is set
