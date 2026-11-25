<?php

use App\Jobs\SyncOrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Suppliers\JustAnotherPanelCatalogImporter;
use App\Services\Suppliers\TextVerifiedCatalogImporter;
use App\Support\ServiceCatalogue;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('suppliers:import-justanotherpanel {--brands=* : Brands to import (instagram,tiktok,facebook,youtube,telegram,x-twitter,snapchat,whatsapp)} {--types=Default : Comma-separated JustAnotherPanel service types} {--markup=10 : Retail markup percent over JustAnotherPanel rate} {--usd-xaf-rate=597.85 : USD to XAF rate} {--status=active : Product status} {--limit=0 : Max products to import (0 = all)} {--dry-run : Report without writing}', function () {
    $result = app(JustAnotherPanelCatalogImporter::class)->import([
        'brands' => $this->option('brands'),
        'types' => $this->option('types'),
        'markup' => (float) $this->option('markup'),
        'usd_xaf_rate' => (float) $this->option('usd-xaf-rate'),
        'status' => $this->option('status'),
        'limit' => (int) $this->option('limit'),
        'dry_run' => (bool) $this->option('dry-run'),
    ]);

    $this->info(($result['dry_run'] ? 'Dry run complete' : 'Import complete').': '.$result['imported'].' products, '.$result['matched'].' matched of '.$result['catalog_count'].' catalog services.');
    foreach ($result['matched_by_brand'] as $brand => $count) {
        $this->line(str_pad($brand, 12).$count);
    }

    if ($result['dry_run']) {
        $this->warn('No products were written.');
    }
})->purpose('Import JustAnotherPanel SMM services as XAF products');

Artisan::command('suppliers:sync-justanotherpanel {--dry-run : Report without writing}', function () {
    $result = app(JustAnotherPanelCatalogImporter::class)->sync([
        'dry_run' => (bool) $this->option('dry-run'),
    ]);

    if (isset($result['error'])) {
        $this->error('JustAnotherPanel catalog is unavailable; no products were changed.');
        return;
    }

    $this->info('JAP catalog has '.$result['catalog_count'].' services.');
    $this->line('Tracked products: '.$result['products'].', missing from JAP: '.$result['missing'].', candidates to archive: '.$result['candidates'].'.');

    if ($result['dry_run']) {
        $this->warn('Dry run complete. No products were archived.');
    } else {
        $this->info('Archived '.$result['archived'].' products removed from JustAnotherPanel.');
    }
})->purpose('Archive JustAnotherPanel products removed from the supplier catalog');

Artisan::command('catalogue:sync-smm-caps {--dry-run : Report without writing}', function () {
    $allowedIds = collect();
    foreach (ServiceCatalogue::brands() as $slug => $brand) {
        $allowedIds = $allowedIds->merge(ServiceCatalogue::catalogueRows($slug)->pluck('product.id'));
    }
    $allowedIds = $allowedIds->unique()->values();

    $activeQuery = fn () => Product::active()->where('type', 'smm_service');
    $candidateQuery = $allowedIds->isEmpty()
        ? $activeQuery()
        : $activeQuery()->whereNotIn('id', $allowedIds->all());
    $candidateIds = $candidateQuery->pluck('id');
    $activeCount = $activeQuery()->count();
    $dryRun = (bool) $this->option('dry-run');

    if (! $dryRun && $candidateIds->isNotEmpty()) {
        Product::query()->whereIn('id', $candidateIds)->update(['is_active' => false, 'status' => 'archived']);
    }

    $this->info('Active SMM products before sync: '.$activeCount.'.');
    $this->line('Verified catalogue products allowed: '.$allowedIds->count().', candidates to archive: '.$candidateIds->count().'.');

    if ($dryRun) {
        $this->warn('Dry run complete. No products were archived.');
    } else {
        $this->info('Archived '.$candidateIds->count().' products outside the verified SMM catalogue.');
        $this->info('Remaining active SMM products: '.Product::active()->where('type', 'smm_service')->count().'.');
    }
})->purpose('Archive active SMM products outside the verified brand/group price catalogue');

Artisan::command('orders:sync-supplier-status {--limit=200 : Maximum open orders to queue}', function () {
    $limit = max(1, (int) $this->option('limit'));

    $ids = Order::query()
        ->whereNotNull('supplier_reference')
        ->where('supplier_reference', '!=', '')
        ->whereNotIn('status', [
            Order::STATUS_COMPLETED,
            Order::STATUS_FAILED,
            Order::STATUS_CANCELLED,
            Order::STATUS_REFUNDED,
        ])
        ->latest('updated_at')
        ->limit($limit)
        ->pluck('id');

    foreach ($ids as $orderId) {
        SyncOrderStatus::dispatch($orderId);
    }

    $this->info('Queued supplier status sync for '.$ids->count().' orders.');
})->purpose('Queue supplier status checks for open orders');

Artisan::command('suppliers:sync-textverified-products {--markup=10 : Retail markup percent} {--usd-xaf-rate=597.85 : USD to XAF rate} {--status=active : Product status} {--dry-run : Report without writing}', function () {
    $result = app(TextVerifiedCatalogImporter::class)->sync([
        'markup' => (float) $this->option('markup'),
        'usd_xaf_rate' => (float) $this->option('usd-xaf-rate'),
        'status' => $this->option('status'),
        'dry_run' => (bool) $this->option('dry-run'),
    ]);

    if (isset($result['error'])) {
        $this->warn('TextVerified sync skipped: '.$result['error']);

        return 1;
    }

    $this->info(($result['dry_run'] ? 'Dry run complete' : 'Sync complete').': '.$result['imported'].' TextVerified products of '.$result['catalog_count'].' matched '.$result['matched'].'.');
    if ($result['dry_run']) {
        $this->warn('No products were written.');
    }
})->purpose('Sync verified TextVerified US SMS verification products');

Artisan::command('admin:create {--email= : Admin email address} {--name= : Admin display name} {--role=super_admin : Role to assign: admin or super_admin} {--password= : Password (prefer the hidden prompt)} {--force : Update the user if the email already exists}', function () {
    $email = strtolower(trim((string) $this->option('email')));
    $name = trim((string) $this->option('name')) ?: 'TrustVerifySMS Admin';
    $role = (string) $this->option('role');
    $password = (string) $this->option('password');

    if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('A valid --email is required.');

        return 1;
    }

    if (! in_array($role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true)) {
        $this->error('Role must be admin or super_admin.');

        return 1;
    }

    if ($password === '') {
        $password = (string) $this->secret('Admin password');
    }

    if (strlen($password) < 8) {
        $this->error('Password must be at least 8 characters.');

        return 1;
    }

    if (User::where('email', $email)->exists() && ! $this->option('force')) {
        $this->error('A user with that email already exists. Re-run with --force to update it.');

        return 1;
    }

    $user = User::updateOrCreate(
        ['email' => $email],
        [
            'name' => $name,
            'password' => $password,
            'role' => $role,
            'status' => 'active',
        ]
    );

    $this->info('Admin ready: '.$user->email.' ('.$user->role.').');

    return 0;
})->purpose('Create or update an admin user');

Artisan::command('site:bootstrap {--skip-migrate : Skip database migrations}', function () {
    if (! $this->option('skip-migrate')) {
        Artisan::call('migrate', ['--force' => true]);
        $this->info('Migrations complete.');
    }

    $activeSmm = Product::active()->where('type', 'smm_service')->count();
    $jap = app(JustAnotherPanelCatalogImporter::class);

    if ($activeSmm === 0) {
        $this->info('No active SMM products found; importing JustAnotherPanel catalogue.');
        $import = $jap->import([
            'brands' => ['instagram', 'tiktok', 'facebook', 'youtube', 'telegram', 'x-twitter', 'whatsapp'],
            'types' => 'Default',
            'markup' => 10,
            'usd_xaf_rate' => 597.85,
            'status' => 'active',
        ]);
        $this->info('JustAnotherPanel import: '.$import['imported'].' products.');
    } else {
        $this->info('Active SMM products already exist ('.$activeSmm.'); skipping JustAnotherPanel import.');
    }

    $textVerified = app(TextVerifiedCatalogImporter::class)->sync([
        'markup' => 10,
        'usd_xaf_rate' => 597.85,
        'status' => 'active',
    ]);
    if (isset($textVerified['error'])) {
        $this->warn('TextVerified sync skipped: '.$textVerified['error']);
    } else {
        $this->info('TextVerified sync: '.$textVerified['imported'].' products.');
    }

    $sync = $jap->sync([]);
    if (isset($sync['error'])) {
        $this->warn('JustAnotherPanel catalog is unavailable; sync skipped.');
    } else {
        $this->info('JustAnotherPanel sync archived '.$sync['archived'].' products.');
    }

    Artisan::call('catalogue:sync-smm-caps');
    $this->info('Site bootstrap complete.');

    return 0;
})->purpose('Prepare the live site: migrate, import missing catalogue products and sync suppliers');
