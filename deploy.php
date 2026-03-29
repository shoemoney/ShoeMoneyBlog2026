<?php

namespace Deployer;

require 'recipe/laravel.php';

// ============================================
// shoemoney.com - Deployer Config
//
// Deploy:   vendor/bin/dep deploy
// Rollback: vendor/bin/dep rollback
// Status:   vendor/bin/dep releases
// ============================================

set('application', 'shoemoney.com');
set('repository', 'https://github.com/shoemoney/ShoeMoneyBlog2026.git');
set('branch', 'main');
set('keep_releases', 5);
set('ssh_multiplexing', true);
set('composer_options', '--verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader');
set('bin/php', '/usr/bin/php8.4');

// ============================================
// Host Configuration
// ============================================
host('production')
    ->set('hostname', '100.49.4.12')
    ->set('remote_user', 'shoemoney')
    ->set('identity_file', '~/.ssh/smx.pem')
    ->set('deploy_path', '/var/www/shoemoney.com')
    ->set('labels', ['stage' => 'production']);

// ============================================
// Custom Tasks
// ============================================

desc('Build frontend assets');
task('deploy:build', function () {
    if (test('[ -f {{release_path}}/package.json ]')) {
        cd('{{release_path}}');
        run('npm ci');
        run('npm run build');
    }
});

desc('Clear all Laravel caches');
task('deploy:cache:clear', function () {
    within('{{release_path}}', function () {
        run('{{bin/php}} artisan config:clear');
        run('{{bin/php}} artisan cache:clear');
        run('{{bin/php}} artisan route:clear');
        run('{{bin/php}} artisan view:clear');
        run('{{bin/php}} artisan event:clear');
    });
});

desc('Rebuild optimized caches');
task('deploy:cache:warm', function () {
    within('{{release_path}}', function () {
        run('{{bin/php}} artisan config:cache');
        run('{{bin/php}} artisan route:cache');
        run('{{bin/php}} artisan view:cache');
        run('{{bin/php}} artisan event:cache');
    });
});

desc('Restart Octane via Supervisor');
task('octane:reload', function () {
    run('sudo supervisorctl restart laravel-octane');
});

desc('Restart queue workers');
task('queue:restart', function () {
    run('sudo supervisorctl restart laravel-worker:*');
});

// ============================================
// Deploy Pipeline Hooks
// ============================================
after('deploy:vendors', 'deploy:build');

// After symlink goes live: clear stale caches, rebuild optimized caches, then restart services
after('deploy:symlink', 'deploy:cache:clear');
after('deploy:cache:clear', 'deploy:cache:warm');
after('deploy:cache:warm', 'octane:reload');
after('octane:reload', 'queue:restart');

after('deploy:failed', 'deploy:unlock');
