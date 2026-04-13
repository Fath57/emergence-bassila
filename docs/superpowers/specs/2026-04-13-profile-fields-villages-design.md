# Profile Fields + Villages — Design Spec

**Date:** 2026-04-13
**Branch:** 001-bassila-network-platform

---

## Overview

Add three new fields to the profile creation (and edit) form:
1. **Sexe** (`gender`) — Homme / Femme radio, stored as `M`/`F` enum on profiles
2. **Contact WhatsApp** (`whatsapp`) — separate number field from `phone`
3. **Village** (`village_id`) — FK to a new `villages` table, admin-managed via custom Livewire panel

---

## 1. Database

### `villages` table (new)

| Column | Type | Notes |
|---|---|---|
| `id` | bigIncrements | PK |
| `name` | string(150) | Village/quartier name |
| `arrondissement` | string(100) nullable | Grouping for the dropdown |
| `is_active` | boolean default true | Admin can disable without deleting |
| `sort_order` | unsignedSmallInteger default 0 | Controls display order |
| timestamps | — | — |

### `profiles` table — new columns (migration)

| Column | Type | Notes |
|---|---|---|
| `gender` | char(1) nullable | `'M'` or `'F'` |
| `whatsapp` | string(30) nullable | WhatsApp number |
| `village_id` | foreignId nullable | FK → villages.id, nullOnDelete |

---

## 2. Village Seeder — Bassila Commune

Bassila is a commune in the Donga department (Benin), with 4 arrondissements.
Seed all known villages/quartiers grouped by arrondissement.

**Arrondissement de Bassila**
- Bassila (chef-lieu)
- Alédjo-Attakora
- Barei
- Béssakourou
- Gbégourou
- Kounouhou
- Tchétou
- Worogui

**Arrondissement de Manigri**
- Manigri
- Bétékoukou
- Gbassi
- Kikélé
- Kpakpaza
- Ode
- Sème

**Arrondissement de Pénéssoulou**
- Pénéssoulou
- Kolokondé
- Kokobou

**Arrondissement de Wawa**
- Wawa
- Manta

Admin can add, edit, toggle-active, or delete villages after seeding via the custom Livewire admin panel.

---

## 3. Models

### `Village` model

- `$fillable`: `name`, `arrondissement`, `is_active`, `sort_order`
- `$casts`: `is_active` → boolean
- Scope `active()`: `where('is_active', true)`

### `Profile` model — additions

- Add `gender`, `whatsapp`, `village_id` to `$fillable`
- Add `village()` BelongsTo relation → `Village`

---

## 4. Admin Panel — ManageVillages

`app/Livewire/Admin/ManageVillages.php` following the `ManageCategories` pattern:

- **List**: table of all villages, grouped by arrondissement, showing name / arrondissement / active status
- **Create**: inline form — name (required), arrondissement (optional), sort_order
- **Edit**: inline edit of name, arrondissement, sort_order
- **Toggle active**: one-click toggle of `is_active`
- **Delete**: with soft guard (warn if village has profiles attached — count shown)

Admin route and nav link added alongside existing admin pages.

---

## 5. Profile Form — CreateProfile

### Step 1 — Identité

Add `gender` radio after the first/last name row:

```
○ Homme   ○ Femme
```

Optional field (nullable). No step-validation requirement.

### Step 2 — Localisation & Contact

**Village**: dropdown after the city field, populated from `Village::active()->orderBy('arrondissement')->orderBy('sort_order')->orderBy('name')`. Grouped by arrondissement using `<optgroup>`. Optional.

**WhatsApp**: new field in the contact section alongside the existing `phone` field. Label: "WhatsApp". Optional.

### Livewire component changes (`CreateProfile`)

- New public properties: `public string $gender = ''`, `public string $whatsapp = ''`, `public ?int $village_id = null`
- Step validation: no required constraint on any of the three new fields
- Full validation rules: `gender` nullable/in:M,F, `whatsapp` nullable/string/max:30, `village_id` nullable/exists:villages,id
- `save()`: pass new fields to `Profile::create()`
- `render()`: pass `$villages` collection to the view

### EditProfile

Same fields added to the edit form (Step 1 for gender, Step 2 for village + whatsapp), pre-filled from the existing profile.

---

## 6. Testing

- Migration runs cleanly (`php artisan migrate`)
- Seeder runs: `php artisan db:seed --class=VillageSeeder`
- Create profile form: gender, whatsapp, village save correctly
- Admin: create / edit / toggle / delete village works
- Profiles with a deleted village get `village_id = null` (nullOnDelete)

---

## Out of scope

- Village search/filter on the public directory (future)
- Village as a required field
- Displaying village on the public profile card (can be added separately)
