<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ServiceCatalogue
{
    public static function brands(): array
    {
        return [
            'instagram' => ['label' => 'Instagram', 'sort_order' => 1],
            'tiktok' => ['label' => 'TikTok', 'sort_order' => 2],
            'facebook' => ['label' => 'Facebook', 'sort_order' => 3],
            'youtube' => ['label' => 'YouTube', 'sort_order' => 4],
            'telegram' => ['label' => 'Telegram', 'sort_order' => 5],
            'whatsapp' => ['label' => 'WhatsApp', 'sort_order' => 6],
        ];
    }

    public static function groups(string $brand): array
    {
        return self::definitions()[$brand] ?? [];
    }

    public static function groupForProduct(Product $product): ?string
    {
        $brand = $product->category?->slug;
        if (! $brand || ! isset(self::brands()[$brand])) {
            return null;
        }

        $name = Str::lower((string) $product->name);
        if (self::mentionsForeignBrand($name, $brand)) {
            return null;
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
            $matches = $rows->where('group', $slug)->sortBy(fn ($row) => (float) $row['product']->price)->values();
            $summaries[$slug] = [
                'slug' => $slug,
                'label' => $definition['label'],
                'description' => $definition['description'],
                'count' => $matches->count(),
                'min_price' => $matches->isEmpty() ? null : (float) $matches->first()['product']->price,
                'available' => $matches->isNotEmpty(),
            ];
        }

        return $summaries;
    }

    public static function productsForGroup(string $brand, string $group): Collection
    {
        return self::catalogueRows($brand)
            ->where('group', $group)
            ->sortBy(fn ($row) => (float) $row['product']->price)
            ->pluck('product')
            ->values();
    }

    private static function mentionsForeignBrand(string $name, string $brand): bool
    {
        $needles = [
            'instagram' => 'instagram',
            'tiktok' => 'tiktok',
            'facebook' => 'facebook',
            'youtube' => 'youtube',
            'telegram' => 'telegram',
            'whatsapp' => 'whatsapp',
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
                'followers' => ['label' => 'Followers', 'description' => 'Grow your Instagram profile with follower packages.', 'cap' => 2400, 'needles' => ['follower']],
                'comments' => ['label' => 'Custom Comments', 'description' => 'Add comments to posts and reels.', 'cap' => 2400, 'needles' => ['comment']],
                'story_views' => ['label' => 'Story Views', 'description' => 'Increase views on your Instagram stories.', 'cap' => 168, 'needles' => ['story']],
                'likes' => ['label' => 'Post Likes', 'description' => 'Boost likes on posts and reels.', 'cap' => 320, 'needles' => ['like']],
                'views' => ['label' => 'Video / Reel Views', 'description' => 'Increase views on videos and reels.', 'cap' => 48, 'needles' => ['view', 'reel', 'impression']],
                'reposts_shares' => ['label' => 'Reposts & Shares', 'description' => 'Amplify reach with reposts and shares.', 'cap' => 531.2, 'needles' => ['repost', 'share']],
                'channel_members' => ['label' => 'Channel Members', 'description' => 'Add members to your Instagram channel.', 'cap' => 800, 'needles' => ['channel', 'member']],
            ],
            'tiktok' => [
                'followers' => ['label' => 'Followers', 'description' => 'Choose average or high-quality TikTok followers.', 'cap' => 4000, 'needles' => ['follower']],
                'saves' => ['label' => 'Video Saves', 'description' => 'Increase saves on your TikTok videos.', 'cap' => 8, 'needles' => ['save']],
                'shares' => ['label' => 'Video Shares', 'description' => 'Boost shares on your TikTok content.', 'cap' => 160, 'needles' => ['share']],
                'livestream_likes' => ['label' => 'Livestream Likes', 'description' => 'Send likes during your TikTok livestreams.', 'cap' => 160, 'needles' => ['live', 'livestream']],
                'comments' => ['label' => 'Custom Comments', 'description' => 'Add custom or random comments to videos.', 'cap' => 3200, 'needles' => ['comment']],
                'comment_likes' => ['label' => 'Comment Likes', 'description' => 'Like comments on TikTok videos.', 'cap' => 400, 'needles' => ['comment like']],
                'views' => ['label' => 'Video Views', 'description' => 'Increase views on your TikTok videos.', 'cap' => 48, 'needles' => ['view', 'play']],
                'likes' => ['label' => 'Video Likes', 'description' => 'Boost likes on your TikTok videos.', 'cap' => 360, 'needles' => ['like']],
            ],
            'facebook' => [
                'page_followers' => ['label' => 'Page Followers', 'description' => 'Grow followers on your Facebook page.', 'cap' => 2400, 'needles' => ['page follower', 'page fan']],
                'profile_followers' => ['label' => 'Profile Followers', 'description' => 'Increase followers on your Facebook profile.', 'cap' => 1600, 'needles' => ['profile follower', 'friend request']],
                'page_reviews' => ['label' => 'Page Reviews', 'description' => 'Add reviews and ratings to your Facebook page.', 'cap' => 44800, 'needles' => ['review', 'rating']],
                'reactions' => ['label' => 'Emoji Post Reactions', 'description' => 'Add emoji reactions to your Facebook posts.', 'cap' => 336, 'needles' => ['emoji', 'reaction', '[love', '[care', '[haha', '[wow', '[sad']],
                'shares' => ['label' => 'Post Shares', 'description' => 'Increase shares on your Facebook posts.', 'cap' => 800, 'needles' => ['share']],
                'group_members' => ['label' => 'Group Members', 'description' => 'Grow members in your Facebook group.', 'cap' => 1440, 'needles' => ['group member', 'group']],
                'video_views' => ['label' => 'Video / Reel Views', 'description' => 'Increase views on Facebook videos and reels.', 'cap' => 72, 'needles' => ['video view', 'reel view', 'view']],
                'comments' => ['label' => 'Custom Comments', 'description' => 'Add custom comments to Facebook posts.', 'cap' => 3200, 'needles' => ['comment']],
                'post_likes' => ['label' => 'Post Likes', 'description' => 'Boost likes on your Facebook posts.', 'cap' => 320, 'needles' => ['post like', 'page like', 'photo like', 'like']],
            ],
            'whatsapp' => [
                'channel_followers' => ['label' => 'Channel Followers', 'description' => 'Grow followers on your WhatsApp channel.', 'cap' => 4200, 'needles' => ['channel member', 'follower', 'member']],
                'reactions' => ['label' => 'Channel Emoji Reactions', 'description' => 'Add emoji reactions to WhatsApp channel posts.', 'cap' => 1700, 'needles' => ['reaction', 'emoji']],
                'poll_votes' => ['label' => 'Poll Votes', 'description' => 'Boost votes on WhatsApp channel polls.', 'cap' => 2400, 'needles' => ['poll', 'vote']],
            ],
            'telegram' => [
                'members' => ['label' => 'Group / Channel Members', 'description' => 'Choose average or high-quality Telegram members.', 'cap' => 2400, 'needles' => ['member', 'subscriber', 'follower']],
                'future_post_views' => ['label' => 'Future Post Views', 'description' => 'Views for upcoming Telegram posts.', 'cap' => 480, 'needles' => ['future post view', 'future view', 'future views']],
                'post_views' => ['label' => 'Post Views', 'description' => 'Increase views on a specific Telegram post.', 'cap' => 24, 'needles' => ['post view', 'view']],
                'future_post_reactions' => ['label' => 'Future Post Reactions', 'description' => 'Reactions for upcoming Telegram posts.', 'cap' => 2560, 'needles' => ['future post reaction', 'future reaction', 'future reactions']],
                'reactions' => ['label' => 'Post Reactions', 'description' => 'Add reactions to a specific Telegram post.', 'cap' => 16, 'needles' => ['reaction', 'emoji']],
            ],
            'youtube' => [
                'subscribers' => ['label' => 'Subscribers', 'description' => 'Choose average or high-quality YouTube subscribers.', 'cap' => 32000, 'needles' => ['subscriber']],
                'views' => ['label' => 'Video Views', 'description' => 'Increase views on your YouTube videos.', 'cap' => 1120, 'needles' => ['view', 'watch', 'play']],
                'likes' => ['label' => 'Video Likes', 'description' => 'Boost likes on your YouTube videos.', 'cap' => 4800, 'needles' => ['like']],
                'comments' => ['label' => 'Custom Comments', 'description' => 'Add custom comments to YouTube videos.', 'cap' => 8000, 'needles' => ['comment']],
            ],
        ];
    }
}
