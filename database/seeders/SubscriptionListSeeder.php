<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use LaravelPlus\Subscribe\Models\SubscriptionList;

final class SubscriptionListSeeder extends Seeder
{
    public function run(): void
    {
        if (!class_exists(SubscriptionList::class)) {
            return;
        }

        $lists = [
            [
                'name' => 'Newsletter',
                'slug' => 'newsletter',
                'description' => 'Our main newsletter with updates and news',
                'is_public' => true,
                'is_default' => true,
                'double_opt_in' => true,
                'welcome_email_enabled' => true,
                'welcome_email_subject' => 'Welcome to our Newsletter!',
                'welcome_email_content' => "Thank you for subscribing to our newsletter!\n\nYou'll receive the latest updates, news, and exclusive content directly in your inbox.",
            ],
            [
                'name' => 'Product Updates',
                'slug' => 'product-updates',
                'description' => 'Get notified about new features and improvements',
                'is_public' => true,
                'is_default' => false,
                'double_opt_in' => true,
            ],
            [
                'name' => 'Promotions',
                'slug' => 'promotions',
                'description' => 'Special offers and discounts',
                'is_public' => true,
                'is_default' => false,
                'double_opt_in' => false,
            ],
            [
                'name' => 'Beta Testers',
                'slug' => 'beta-testers',
                'description' => 'Early access to new features',
                'is_public' => false,
                'is_default' => false,
                'double_opt_in' => true,
                'welcome_email_enabled' => true,
                'welcome_email_subject' => 'Welcome to the Beta Program!',
                'welcome_email_content' => "You're now part of our exclusive beta testing program.\n\nYou'll get early access to new features before anyone else.",
            ],
        ];

        foreach ($lists as $listData) {
            SubscriptionList::query()->updateOrCreate(
                ['slug' => $listData['slug']],
                $listData,
            );
        }
    }
}
