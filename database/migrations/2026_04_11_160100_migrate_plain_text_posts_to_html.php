<?php

use App\Services\BlogContentSanitizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        // Backup existing rows as JSON (rollback safety)
        $backup = DB::table('blog_posts')->get()->toArray();
        $backupPath = 'backups/blog_posts_' . now()->format('Y-m-d_His') . '.json';
        Storage::disk('local')->put(
            $backupPath,
            json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        );

        $sanitizer = app(BlogContentSanitizer::class);

        DB::table('blog_posts')->orderBy('id')->get()->each(function ($post) use ($sanitizer) {
            $plain = (string) ($post->content ?? '');
            if ($plain === '') {
                return;
            }

            // If already contains block-level HTML tags, skip — already migrated.
            if (preg_match('/<(p|h[1-6]|ul|ol|blockquote|pre|figure)[\s>]/i', $plain)) {
                return;
            }

            // Split on blank lines → paragraphs; escape inner text; keep line
            // breaks via <br>; then sanitize through the blog profile.
            $paragraphs = preg_split('/\n\s*\n/', trim($plain)) ?: [];
            $html = collect($paragraphs)
                ->filter(fn ($p) => trim($p) !== '')
                ->map(fn ($p) => '<p>' . nl2br(e(trim($p)), false) . '</p>')
                ->implode("\n");

            $clean = $sanitizer->clean($html);

            DB::table('blog_posts')
                ->where('id', $post->id)
                ->update(['content' => $clean]);
        });
    }

    public function down(): void
    {
        $files = Storage::disk('local')->files('backups');
        $latest = collect($files)
            ->filter(fn ($f) => str_starts_with(basename($f), 'blog_posts_'))
            ->sortDesc()
            ->first();

        if (! $latest) {
            throw new \RuntimeException('No blog_posts backup available for rollback.');
        }

        $data = json_decode(Storage::disk('local')->get($latest), true);

        foreach ($data as $row) {
            DB::table('blog_posts')
                ->where('id', $row['id'])
                ->update(['content' => $row['content']]);
        }
    }
};
