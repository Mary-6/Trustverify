<?php

namespace App\Support;

use App\Models\Product;
use App\Services\SettingsService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ServiceCatalogue
{
    public static function brands(): array
    {
        return [
            'instagram' => ['label' => 'Instagram', 'sort_order' => 1, 'logo' => 'instagram', 'color' => '#E1306C', 'gradient' => 'linear-gradient(135deg,#833AB4 0%,#E1306C 45%,#F56040 75%,#FCAF45 100%)'],
            'facebook' => ['label' => 'Facebook', 'sort_order' => 2, 'logo' => 'facebook', 'color' => '#1877F2', 'gradient' => 'linear-gradient(135deg,#1877F2 0%,#0A5BE0 100%)'],
            'youtube' => ['label' => 'YouTube', 'sort_order' => 3, 'logo' => 'youtube', 'color' => '#FF0000', 'gradient' => 'linear-gradient(135deg,#FF0000 0%,#FF4E45 100%)'],
            'tiktok' => ['label' => 'TikTok', 'sort_order' => 4, 'logo' => 'tiktok', 'color' => '#FE2C55', 'gradient' => 'linear-gradient(135deg,#010101 0%,#25F4EE 55%,#FE2C55 100%)'],
            'telegram' => ['label' => 'Telegram', 'sort_order' => 5, 'logo' => 'telegram', 'color' => '#229ED9', 'gradient' => 'linear-gradient(135deg,#229ED9 0%,#1E96D4 100%)'],
            'whatsapp' => ['label' => 'WhatsApp', 'sort_order' => 6, 'logo' => 'whatsapp', 'color' => '#25D366', 'gradient' => 'linear-gradient(135deg,#128C7E 0%,#25D366 100%)'],
            'x-twitter' => ['label' => 'X (formerly Twitter)', 'sort_order' => 7, 'logo' => 'x', 'color' => '#000000', 'gradient' => 'linear-gradient(135deg,#1F2937 0%,#000000 100%)'],
        ];
    }

    public static function groups(string $brand): array
    {
        return self::definitions()[$brand] ?? [];
    }

    public static function brandForProduct(Product $product): ?string
    {
        $override = self::overrideMapping($product);
        if ($override !== null) {
            return $override[0];
        }

        $platform = $product->meta['smm_platform'] ?? null;
        if (is_string($platform) && isset(self::brands()[$platform])) {
            return $platform;
        }

        $slug = $product->category?->slug;

        return is_string($slug) && isset(self::brands()[$slug]) ? $slug : null;
    }

    public static function groupForProduct(Product $product): ?string
    {
        $override = self::overrideMapping($product);
        if ($override !== null) {
            return $override[1];
        }

        $brand = self::brandForProduct($product);
        if (! $brand) {
            return null;
        }

        $name = Str::lower((string) $product->name);
        if (self::mentionsForeignBrand($name, $brand)) {
            return null;
        }

        foreach (self::matchFirstGroups($brand) as $group => $definition) {
            foreach ($definition['needles'] as $needle) {
                if (Str::contains($name, Str::lower($needle))) {
                    return $group;
                }
            }
        }

        foreach (self::groups($brand) as $group => $definition) {
            foreach ($definition['needles'] as $needle) {
                if (Str::contains($name, Str::lower($needle))) {
                    return $group;
                }
            }
        }

        return null;
    }

    public static function isPresentable(Product $product): bool
    {
        $name = Str::lower((string) $product->name);

        foreach (['prank', 'bots', 'high drop', 'spam', 'no refund', 'low quality', 'fake', 'crypto', 'nft', '[lq', '[mq'] as $bad) {
            if (Str::contains($name, $bad)) {
                return false;
            }
        }

        $identity = Str::lower($product->name.' '.(string) ($product->meta['supplier_service_name'] ?? ''));

        if (CountryFilter::isCountrySpecific($identity)) {
            return false;
        }

        return (float) $product->price > 0;
    }

    public static function withinReferenceCap(string $brand, string $group, Product $product): bool
    {
        $cap = self::groups($brand)[$group]['cap'] ?? null;

        return $cap === null || (float) $product->price <= (float) $cap;
    }

    public static function catalogueRows(string $brand): Collection
    {
        return Product::active()
            ->where('type', 'smm_service')
            ->whereHas('category', fn ($query) => $query->where('slug', $brand))
            ->with('category')
            ->get()
            ->map(function (Product $product) use ($brand) {
                if (self::brandForProduct($product) !== $brand) {
                    return null;
                }

                $group = self::groupForProduct($product);

                if ($group === null || ! self::isPresentable($product)) {
                    return null;
                }

                return ['product' => $product, 'group' => $group];
            })
            ->filter()
            ->values();
    }

    public static function summaries(string $brand): array
    {
        $rows = self::catalogueRows($brand);
        $summaries = [];

        foreach (self::groups($brand) as $slug => $definition) {
            $matches = self::curateRows($rows->where('group', $slug)->values(), $brand, $slug);
            $minPrice = $matches->isEmpty() ? null : (float) $matches->map(fn (Product $product) => (float) $product->price)->min();

            $summaries[$slug] = [
                'slug' => $slug,
                'label' => $definition['label'],
                'description' => $definition['description'],
                'count' => $matches->count(),
                'min_price' => $minPrice,
                'available' => $matches->isNotEmpty(),
            ];
        }

        return $summaries;
    }

    public static function productsForGroup(string $brand, string $group): Collection
    {
        $rows = self::catalogueRows($brand)->where('group', $group)->values();

        return self::curateRows($rows, $brand, $group);
    }

    private static function curateRows(Collection $rows, string $brand, string $group): Collection
    {
        $products = self::selectQualityPair($rows->map(fn ($row) => $row['product'])->values());
        $limit = self::displayLimit($brand, $group);

        return $products
            ->when($limit !== null, fn (Collection $items) => $items->take($limit))
            ->values();
    }

    private static function selectQualityPair(Collection $products): Collection
    {
        $products = $products->values();

        if ($products->isEmpty()) {
            return collect();
        }

        if ($products->count() === 1) {
            return $products;
        }

        $scored = $products->map(fn (Product $product) => [
            'product' => $product,
            'score' => self::qualityScore($product),
            'price' => (float) $product->price,
            'sort' => (int) ($product->sort_order ?? 999999),
            'id' => (int) $product->id,
        ])->values();

        $high = $scored->sort(function (array $a, array $b) {
            if ($a['score'] !== $b['score']) {
                return $b['score'] <=> $a['score'];
            }

            if ($a['price'] !== $b['price']) {
                return $b['price'] <=> $a['price'];
            }

            if ($a['sort'] !== $b['sort']) {
                return $a['sort'] <=> $b['sort'];
            }

            return $a['id'] <=> $b['id'];
        })->first();

        $low = $scored
            ->reject(fn (array $row) => $row['product']->id === $high['product']->id)
            ->sort(function (array $a, array $b) {
                if ($a['price'] !== $b['price']) {
                    return $a['price'] <=> $b['price'];
                }

                if ($a['score'] !== $b['score']) {
                    return $a['score'] <=> $b['score'];
                }

                if ($a['sort'] !== $b['sort']) {
                    return $a['sort'] <=> $b['sort'];
                }

                return $a['id'] <=> $b['id'];
            })
            ->first();

        $high['product']->setAttribute('smm_quality', 'high');
        $low['product']->setAttribute('smm_quality', 'low');

        return collect([$high['product'], $low['product']])->values();
    }

    private static function qualityScore(Product $product): int
    {
        $text = Str::lower($product->name.' '.(string) ($product->meta['supplier_service_name'] ?? ''));
        $score = 0;

        if (preg_match('/\b(hq|high quality|premium|real|genuine|organic)\b/', $text)) {
            $score += 6;
        }

        if (preg_match('/\b(no drop|zero drop|non drop|stable)\b/', $text)) {
            $score += 3;
        }

        if (($product->meta['refill'] ?? null) === true || preg_match('/refill:\s*(yes|lifetime|[0-9]+\s*d)/', $text)) {
            $score += 2;
        }

        if (preg_match('/\b(random|cheap|budget|economy|low cost)\b/', $text)) {
            $score -= 4;
        }

        if (preg_match('/\b(drop|drip|slow)\b/', $text) && ! preg_match('/\b(no drop|zero drop|non drop)\b/', $text)) {
            $score -= 3;
        }

        if ((($product->meta['refill'] ?? null) === false) || preg_match('/\b(refill:\s*no|no refill|non refill|without refill)\b/', $text)) {
            $score -= 2;
        }

        return $score;
    }

    public static function displayLimit(string $brand, string $group): ?int
    {
        $limits = app(SettingsService::class)->get('smm.display_limits', []);
        $value = is_array($limits) ? ($limits[$brand][$group] ?? null) : null;

        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }

    private static function overrideMapping(Product $product): ?array
    {
        $override = $product->meta['smm_group'] ?? null;
        if (! is_string($override) || trim($override) === '') {
            return null;
        }

        $value = trim($override);
        if (str_contains($value, ':')) {
            [$brand, $group] = explode(':', $value, 2);
            $brand = trim($brand);
            $group = trim($group);
        } else {
            $brand = (string) ($product->category?->slug ?? '');
            $group = $value;
        }

        if ($brand !== '' && isset(self::brands()[$brand]) && isset(self::groups($brand)[$group])) {
            return [$brand, $group];
        }

        return null;
    }

    private static function matchFirstGroups(string $brand): array
    {
        return collect(self::groups($brand))
            ->filter(fn ($definition) => ! empty($definition['match_first']))
            ->all();
    }

    private static function mentionsForeignBrand(string $name, string $brand): bool
    {
        $needles = [
            'instagram' => 'instagram',
            'facebook' => 'facebook',
            'youtube' => 'youtube',
            'tiktok' => 'tiktok',
            'telegram' => 'telegram',
            'whatsapp' => 'whatsapp',
            'x-twitter' => 'twitter',
        ];

        foreach ($needles as $other => $needle) {
            if ($other !== $brand && Str::contains($name, $needle)) {
                return true;
            }
        }

        return false;
    }

    private static function definitions(): array
    {
        return [
            'instagram' => [
                'followers' => ['label' => 'Followers', 'description' => 'Grow your Instagram profile with follower packages.', 'needles' => ['follower']],
                'likes' => ['label' => 'Likes', 'description' => 'Boost likes on Instagram posts.', 'needles' => ['like']],
                'reel_likes' => ['label' => 'Reel Likes', 'description' => 'Increase likes on Instagram reels.', 'match_first' => true, 'needles' => ['reel like', 'reels like', 'reel likes']],
                'views' => ['label' => 'Views', 'description' => 'Increase views on Instagram posts, reels and stories.', 'needles' => ['view', 'reel', 'impression', 'story']],
                'comments' => ['label' => 'Comments', 'description' => 'Add comments to Instagram posts and reels.', 'needles' => ['comment']],
                'shares' => ['label' => 'Shares', 'description' => 'Amplify reach with Instagram shares and reposts.', 'needles' => ['share', 'repost']],
            ],
            'facebook' => [
                'page_followers' => ['label' => 'Page Followers', 'description' => 'Grow followers on your Facebook page or profile.', 'needles' => ['page follower', 'profile follower', 'friend request', 'follower', 'fan']],
                'page_likes' => ['label' => 'Page Likes', 'description' => 'Increase likes on your Facebook page.', 'needles' => ['page like', 'fan']],
                'post_likes' => ['label' => 'Post Likes', 'description' => 'Boost likes on Facebook posts and photos.', 'needles' => ['post like', 'photo like', 'like']],
                'post_comments' => ['label' => 'Post Comments', 'description' => 'Add comments to Facebook posts.', 'needles' => ['comment']],
                'post_shares' => ['label' => 'Post Shares', 'description' => 'Increase shares on Facebook posts.', 'needles' => ['share']],
                'video_views' => ['label' => 'Video Views', 'description' => 'Increase views on Facebook videos and reels.', 'needles' => ['video view', 'videos view', 'reel view', 'reels view', 'watch']],
            ],
            'youtube' => [
                'subscribers' => ['label' => 'Subscribers', 'description' => 'Grow your YouTube channel with subscribers.', 'needles' => ['subscriber', 'subscription']],
                'video_views' => ['label' => 'Video Views', 'description' => 'Increase views on YouTube videos.', 'needles' => ['view', 'play']],
                'likes' => ['label' => 'Likes', 'description' => 'Boost likes on YouTube videos.', 'needles' => ['like']],
                'comments' => ['label' => 'Comments', 'description' => 'Add comments to YouTube videos.', 'needles' => ['comment']],
                'shares' => ['label' => 'Shares', 'description' => 'Increase shares on YouTube videos.', 'needles' => ['share']],
                'watch_time' => ['label' => 'Watch Time', 'description' => 'Add watch time hours to YouTube videos.', 'match_first' => true, 'needles' => ['watch time', 'watchtime', 'watch hour', 'hours watch', 'watch hrs']],
            ],
            'tiktok' => [
                'followers' => ['label' => 'Followers', 'description' => 'Grow your TikTok profile with followers.', 'needles' => ['follower']],
                'likes' => ['label' => 'Likes', 'description' => 'Boost likes on TikTok videos and livestreams.', 'needles' => ['like']],
                'video_views' => ['label' => 'Video Views', 'description' => 'Increase views on TikTok videos.', 'needles' => ['view', 'play']],
                'comments' => ['label' => 'Comments', 'description' => 'Add comments to TikTok videos.', 'needles' => ['comment']],
                'shares' => ['label' => 'Shares', 'description' => 'Increase shares on TikTok videos.', 'needles' => ['share']],
            ],
            'telegram' => [
                'channel_members' => ['label' => 'Channel Members', 'description' => 'Grow members on your Telegram channel.', 'needles' => ['channel', 'subscriber']],
                'group_members' => ['label' => 'Group Members', 'description' => 'Grow members in your Telegram group.', 'needles' => ['group', 'member', 'invite']],
                'post_views' => ['label' => 'Post Views', 'description' => 'Increase views on Telegram posts.', 'needles' => ['post view', 'view']],
                'reactions' => ['label' => 'Reactions', 'description' => 'Add reactions to Telegram posts.', 'needles' => ['reaction', 'emoji']],
            ],
            'whatsapp' => [
                'channel_followers' => ['label' => 'Channel Followers', 'description' => 'Grow followers on your WhatsApp channel.', 'needles' => ['channel follower', 'channel member', 'follower']],
                'group_members' => ['label' => 'Group Members', 'description' => 'Grow members in your WhatsApp group.', 'needles' => ['group member', 'group', 'member', 'invite']],
                'channel_views' => ['label' => 'Channel Views', 'description' => 'Increase views on WhatsApp channel posts.', 'needles' => ['channel view', 'post view', 'view']],
                'reactions' => ['label' => 'Reactions', 'description' => 'Add reactions to WhatsApp channel posts.', 'needles' => ['reaction', 'emoji']],
            ],
            'x-twitter' => [
                'followers' => ['label' => 'Followers', 'description' => 'Grow followers on X.', 'needles' => ['follower', 'subscriber']],
                'likes' => ['label' => 'Likes', 'description' => 'Boost likes on X posts.', 'needles' => ['like']],
                'views' => ['label' => 'Views', 'description' => 'Increase views on X posts.', 'needles' => ['view', 'impression']],
                'retweets' => ['label' => 'Reposts (Retweets)', 'description' => 'Increase reposts and retweets on X.', 'needles' => ['retweet', 'repost']],
                'comments' => ['label' => 'Comments (Replies)', 'description' => 'Add comments and replies on X posts.', 'needles' => ['comment', 'reply']],
            ],
        ];
    }
}
