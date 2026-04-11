<?php

namespace App\Support;

/**
 * Maps a business sector (Profile.sector) to the best matching
 * Skill.category for default-expanded skill picker accordions.
 *
 * This is intentionally a static lookup rather than a DB column,
 * because the mapping is a UX heuristic (one-to-one best-guess)
 * and lives next to the skill taxonomy definition.
 */
final class SectorSkillCategoryMap
{
    /** @var array<string, string> */
    private const MAP = [
        'IT & Technologie'         => 'Technologies & Informatique',
        'Santé'                    => 'Santé & Médecine',
        'Éducation'                => 'Éducation & Recherche',
        'Finance & Banque'         => 'Finance & Comptabilité',
        'Commerce'                 => 'Gestion & Entrepreneuriat',
        'Médias & Communication'   => 'Marketing & Communication',
        'BTP & Immobilier'         => 'Ingénierie & BTP',
        'Industrie'                => 'Ingénierie & BTP',
        'Agriculture'              => 'Agriculture & Environnement',
        'Transports & Logistique'  => 'Métiers & Services',
        'Administration publique'  => 'Droit & Juridique',
    ];

    public static function for(?string $sectorName): ?string
    {
        if ($sectorName === null) {
            return null;
        }

        return self::MAP[$sectorName] ?? null;
    }
}
