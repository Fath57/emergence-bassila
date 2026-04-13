<?php

use App\Models\Village;

it('village seeder creates bassila villages', function () {
    $this->seed(\Database\Seeders\VillageSeeder::class);

    expect(Village::count())->toBeGreaterThan(15);
    expect(Village::where('name', 'Bassila')->exists())->toBeTrue();
    expect(Village::where('arrondissement', 'Manigri')->count())->toBeGreaterThan(3);
});
