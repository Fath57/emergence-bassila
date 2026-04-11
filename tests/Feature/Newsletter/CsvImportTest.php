<?php

use App\Actions\Newsletter\ImportSubscribersFromCsv;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('imports valid CSV rows, skips duplicates, and records invalid emails', function () {
    // Pre-existing subscriber (should be skipped)
    NewsletterSubscriber::create(['email' => 'existing@example.com', 'confirmed_at' => now()]);

    $csv = "email,first_name\n"
         . "new@example.com,Ibrahim\n"
         . "existing@example.com,\n"     // duplicate — should be skipped
         . "not-an-email,Test\n"         // invalid — should be error
         . "second@example.com,Fatima\n";

    Storage::fake('local');
    $path = sys_get_temp_dir() . '/test_import.csv';
    file_put_contents($path, $csv);

    $file   = new UploadedFile($path, 'subscribers.csv', 'text/csv', null, true);
    $action = new ImportSubscribersFromCsv();
    $action->run($file);

    expect($action->imported)->toHaveCount(2)
        ->and($action->skipped)->toHaveCount(1)
        ->and($action->errors)->toHaveCount(1);

    $imported = NewsletterSubscriber::where('email', 'new@example.com')->sole();
    expect($imported->first_name)->toBe('Ibrahim')
        ->and($imported->confirmed_at)->not->toBeNull()  // imported = pre-confirmed
        ->and($imported->source)->toBe('csv_import');

    NewsletterSubscriber::where('email', 'second@example.com')->sole();

    // Cleanup
    unlink($path);
});
