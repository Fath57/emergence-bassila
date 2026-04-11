<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Idempotent with value preservation: on re-run, existing rows keep
     * their `value` (so admin-edited settings are never reverted by
     * reseeding) while metadata (label, description, group, sort_order,
     * type) is always brought back into sync with the seeder definition.
     */
    public function run(): void
    {
        $seed = [
            [
                'key'         => 'site.registration_open',
                'type'        => 'bool',
                'value'       => '1',
                'group'       => 'site',
                'sort_order'  => 1,
                'label'       => 'Inscriptions ouvertes',
                'description' => "Si désactivé, /inscription redirige vers /connexion avec un message.",
            ],
            [
                'key'         => 'site.maintenance_mode',
                'type'        => 'bool',
                'value'       => '0',
                'group'       => 'site',
                'sort_order'  => 2,
                'label'       => 'Mode maintenance',
                'description' => "Bloque tout le site public. Les admins gardent l'accès.",
            ],
            [
                'key'         => 'site.maintenance_message',
                'type'        => 'string',
                'value'       => 'Le site est temporairement indisponible pour maintenance. Nous revenons très vite.',
                'group'       => 'site',
                'sort_order'  => 3,
                'label'       => 'Message de maintenance',
                'description' => "Affiché sur la page de blocage.",
            ],
            [
                'key'         => 'blog.public_creation',
                'type'        => 'bool',
                'value'       => '1',
                'group'       => 'blog',
                'sort_order'  => 1,
                'label'       => "Création d'articles par les membres",
                'description' => "Si désactivé, seuls les admins peuvent publier.",
            ],
            [
                'key'         => 'blog.require_moderation',
                'type'        => 'bool',
                'value'       => '0',
                'group'       => 'blog',
                'sort_order'  => 2,
                'label'       => 'Modération avant publication',
                'description' => "Les articles créés par des membres restent en brouillon tant qu'un admin n'a pas publié.",
            ],
            [
                'key'         => 'comments.enabled',
                'type'        => 'bool',
                'value'       => '1',
                'group'       => 'comments',
                'sort_order'  => 1,
                'label'       => 'Commentaires activés',
                'description' => "Accepter de nouveaux commentaires sur les articles.",
            ],
            [
                'key'         => 'comments.require_moderation',
                'type'        => 'bool',
                'value'       => '1',
                'group'       => 'comments',
                'sort_order'  => 2,
                'label'       => 'Modération préalable des commentaires',
                'description' => "Les commentaires restent invisibles tant qu'un admin ne les a pas approuvés.",
            ],
        ];

        foreach ($seed as $row) {
            $setting = Setting::firstOrNew(['key' => $row['key']]);

            if (! $setting->exists) {
                $setting->fill($row)->save();
                continue;
            }

            $setting->fill([
                'type'        => $row['type'],
                'group'       => $row['group'],
                'label'       => $row['label'],
                'description' => $row['description'],
                'sort_order'  => $row['sort_order'],
            ])->save();
        }
    }
}
