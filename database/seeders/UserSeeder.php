<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WordPress\WpUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * WordPress capability to Laravel role mapping.
     * WordPress stores roles in usermeta as serialized array.
     */
    private function mapWordPressRole(string $capabilities): string
    {
        // WordPress stores roles as serialized array: a:1:{s:13:"administrator";b:1;}
        if (str_contains($capabilities, 'administrator')) {
            return User::ROLE_ADMINISTRATOR;
        }
        if (str_contains($capabilities, 'editor')) {
            return User::ROLE_EDITOR;
        }
        return User::ROLE_AUTHOR;
    }

    public function run(): void
    {
        $this->command->info('Migrating WordPress users...');

        // Get wp2_ prefix capabilities meta key
        $capabilitiesKey = 'wp2_capabilities';

        $wpUsers = WpUser::all();

        foreach ($wpUsers as $wpUser) {
            // Get user capabilities from usermeta
            $capsMeta = DB::connection('wordpress')
                ->table('usermeta')
                ->where('user_id', $wpUser->ID)
                ->where('meta_key', $capabilitiesKey)
                ->value('meta_value');

            $role = $this->mapWordPressRole($capsMeta ?? '');

            User::updateOrCreate(
                ['wordpress_id' => $wpUser->ID],
                [
                    'name' => $wpUser->display_name,
                    'author_name' => $wpUser->user_nicename,
                    'email' => $wpUser->user_email,
                    'password' => Str::random(60), // Placeholder - user handles password migration manually
                    'role' => $role,
                    'created_at' => $wpUser->user_registered,
                    'updated_at' => now(),
                ]
            );

            $this->command->info("  Migrated: {$wpUser->display_name} ({$role})");
        }

        $this->command->info("Migrated {$wpUsers->count()} users.");
    }
}
