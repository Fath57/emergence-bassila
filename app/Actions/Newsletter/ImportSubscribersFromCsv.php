<?php

namespace App\Actions\Newsletter;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\UploadedFile;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportSubscribersFromCsv
{
    public array $imported = [];
    public array $skipped  = [];
    public array $errors   = [];

    public function run(UploadedFile $file): void
    {
        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setHeaderOffset(0);

        $records = Statement::create()->process($csv);

        foreach ($records as $i => $row) {
            $email = strtolower(trim($row['email'] ?? ''));

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = "Ligne " . ($i + 2) . " : email invalide « {$email} »";
                continue;
            }

            $firstName = trim($row['first_name'] ?? $row['prenom'] ?? $row['prénom'] ?? '');

            $existing = NewsletterSubscriber::where('email', $email)->first();

            if ($existing) {
                $this->skipped[] = $email;
                continue;
            }

            NewsletterSubscriber::create([
                'email'        => $email,
                'first_name'   => $firstName ?: null,
                'confirmed_at' => now(),   // imported = already confirmed (RGPD consent at source)
                'source'       => 'csv_import',
            ]);

            $this->imported[] = $email;
        }
    }
}
