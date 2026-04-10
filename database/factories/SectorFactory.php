<?php

namespace Database\Factories;

use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

class SectorFactory extends Factory
{
    protected $model = Sector::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Agriculture', 'Commerce', 'Santé', 'Éducation', 'IT & Technologie',
                'Industrie', 'Finance & Banque', 'BTP & Immobilier', 'Transports & Logistique',
                'Médias & Communication', 'Administration publique', 'Autre',
            ]),
        ];
    }
}
