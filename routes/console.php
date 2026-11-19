<?php

use App\Models\Product;
use App\Services\Suppliers\JustAnotherPanelCatalogImporter;
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
