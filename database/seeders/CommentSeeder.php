<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Models\WordPress\WpComment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommentSeeder extends Seeder
{
    /**
     * Fix invalid datetime values (e.g., DST transitions like 2007-03-11 02:00:00).
     * MySQL strict mode rejects these, so we detect and fix DST gap times.
     *
     * DST "spring forward" gaps in US Central time (common WordPress installs):
     * - 2007-03-11 02:00:00 - 02:59:59 doesn't exist (became 03:00 instantly)
     * - This pattern repeats on second Sunday of March each year
     */
    private function fixDatetime(?string $datetime): ?string
    {
        if (!$datetime) {
            return now()->format('Y-m-d H:i:s');
        }

        // Check if this is a DST gap time (2am hour on DST transition days)
        // Common DST transition dates for US Central Time:
        $dstGapDates = [
            '2007-03-11', '2008-03-09', '2009-03-08', '2010-03-14',
            '2011-03-13', '2012-03-11', '2013-03-10', '2014-03-09',
            '2015-03-08', '2016-03-13', '2017-03-12', '2018-03-11',
            '2019-03-10', '2020-03-08', '2021-03-14', '2022-03-13',
            '2023-03-12', '2024-03-10', '2025-03-09', '2026-03-08',
        ];

        $date = substr($datetime, 0, 10);
        $hour = (int)substr($datetime, 11, 2);

        // If it's 2am hour on a DST transition date, shift to 3am
        if (in_array($date, $dstGapDates) && $hour === 2) {
            return $date . ' 03' . substr($datetime, 13);
        }

        return $datetime;
    }

    public function run(): void
    {
        $this->command->info('Migrating WordPress comments (this may take several minutes)...');

        // Build lookup maps
        $postMap = Post::pluck('id', 'wordpress_id')->toArray();
        $userMap = User::whereNotNull('wordpress_id')
            ->pluck('id', 'wordpress_id')
            ->toArray();

        // Pre-fetch all WordPress comment IDs for parent mapping
        // This is needed because we'll insert in chunks and need to map parent_id
        $wpCommentIdToLaravelId = [];

        $total = WpComment::approved()->count();
        $bar = $this->command->getOutput()->createProgressBar($total);
        $bar->start();

        // PASS 1: Insert all comments (parent_id = null initially)
        // We can't set parent_id during insert because child might process before parent
        WpComment::approved()
            ->orderBy('comment_ID', 'asc')
            ->chunk(1000, function ($wpComments) use ($postMap, $userMap, &$wpCommentIdToLaravelId, $bar) {
                $commentsData = [];

                foreach ($wpComments as $wpComment) {
                    $laravelPostId = $postMap[$wpComment->comment_post_ID] ?? null;

                    if (!$laravelPostId) {
                        // Comment for unpublished/non-existent post, skip
                        $bar->advance();
                        continue;
                    }

                    // Map WordPress user_id to Laravel user_id (0 = guest commenter)
                    $userId = null;
                    if ($wpComment->user_id > 0) {
                        $userId = $userMap[$wpComment->user_id] ?? null;
                    }

                    $commentDate = $this->fixDatetime($wpComment->comment_date);

                    $commentsData[] = [
                        'wordpress_id' => $wpComment->comment_ID,
                        'post_id' => $laravelPostId,
                        'user_id' => $userId,
                        'parent_id' => null, // Set in pass 2
                        'author_name' => $wpComment->comment_author ?: 'Anonymous',
                        'author_email' => $wpComment->comment_author_email ?: '',
                        'author_url' => $wpComment->comment_author_url ?: null,
                        'author_ip' => $wpComment->comment_author_IP ?: null,
                        'content' => $wpComment->comment_content,
                        'status' => 'approved',
                        'created_at' => $commentDate,
                        'updated_at' => $commentDate,
                    ];
                }

                if (!empty($commentsData)) {
                    Comment::upsert(
                        $commentsData,
                        ['wordpress_id'],
                        ['post_id', 'user_id', 'author_name', 'author_email', 'author_url', 'author_ip', 'content', 'status', 'updated_at']
                    );
                }

                $bar->advance(count($commentsData));
            });

        $bar->finish();
        $this->command->newLine();

        // PASS 2: Update parent_id for threaded comments
        // Use efficient SQL join instead of loading all IDs into memory
        $this->command->info('Updating parent references for comment threading...');

        // Get WordPress database name
        $wpDatabase = config('database.connections.wordpress.database');
        $wpPrefix = config('database.connections.wordpress.prefix');

        // Create temp table with parent mappings for efficiency
        DB::statement("
            CREATE TEMPORARY TABLE IF NOT EXISTS parent_mapping AS
            SELECT
                c.id as comment_id,
                p.id as parent_id
            FROM comments c
            JOIN {$wpDatabase}.{$wpPrefix}comments wc ON c.wordpress_id = wc.comment_ID
            JOIN comments p ON p.wordpress_id = wc.comment_parent
            WHERE wc.comment_parent > 0
        ");

        $mappingCount = DB::table('parent_mapping')->count();
        $this->command->info("  Found {$mappingCount} parent mappings");

        // Batch update using the temp table
        $updated = DB::update('
            UPDATE comments c
            JOIN parent_mapping pm ON c.id = pm.comment_id
            SET c.parent_id = pm.parent_id
        ');

        $this->command->info("  Updated {$updated} parent references");

        $totalMigrated = Comment::count();
        $rootComments = Comment::whereNull('parent_id')->count();
        $repliedComments = Comment::whereNotNull('parent_id')->count();

        $this->command->info("Migration complete:");
        $this->command->info("  Total comments: {$totalMigrated}");
        $this->command->info("  Root comments: {$rootComments}");
        $this->command->info("  Replies (threaded): {$repliedComments}");
    }
}
