<?php

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    $this->author = User::factory()->create(['email_verified_at' => now()]);
    $this->author->assignRole('member');
});

/**
 * Run the content-migration migration file in isolation against the current
 * state (tests start with RefreshDatabase, so the migration has already run
 * once on an empty table). We re-run its up() directly against fresh fixtures.
 */
function runContentMigration(): void
{
    $migration = require database_path(
        'migrations/2026_04_11_160100_migrate_plain_text_posts_to_html.php',
    );
    $migration->up();
}

it('wraps plain text paragraphs in p tags', function () {
    $post = BlogPost::create([
        'user_id' => $this->author->id,
        'title'   => 'Plain',
        'slug'    => 'plain',
        'content' => "Premier paragraphe.\n\nSecond paragraphe.",
        'status'  => 'draft',
    ]);

    runContentMigration();

    $fresh = DB::table('blog_posts')->where('id', $post->id)->value('content');
    expect($fresh)
        ->toContain('<p>Premier paragraphe.</p>')
        ->toContain('<p>Second paragraphe.</p>');
});

it('preserves french accents during migration', function () {
    $post = BlogPost::create([
        'user_id' => $this->author->id,
        'title'   => 'Accents',
        'slug'    => 'accents',
        'content' => "Émergence à Bassila — des idées novatrices.",
        'status'  => 'draft',
    ]);

    runContentMigration();

    $fresh = DB::table('blog_posts')->where('id', $post->id)->value('content');
    expect($fresh)
        ->toContain('Émergence')
        ->toContain('à Bassila')
        ->toContain('idées');
});

it('skips posts that already contain html block tags', function () {
    $existing = '<p>Déjà</p><h2>En HTML</h2>';
    $post = BlogPost::create([
        'user_id' => $this->author->id,
        'title'   => 'HTMLed',
        'slug'    => 'htmled',
        'content' => $existing,
        'status'  => 'draft',
    ]);

    runContentMigration();

    $fresh = DB::table('blog_posts')->where('id', $post->id)->value('content');
    expect($fresh)->toBe($existing);
});
