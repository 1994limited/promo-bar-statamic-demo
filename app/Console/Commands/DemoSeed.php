<?php

namespace App\Console\Commands;

use DuncanMcClean\Cargo\Facades\Discount;
use Illuminate\Console\Command;
use NineteenNinetyFour\PromoBar\Bar;
use NineteenNinetyFour\PromoBar\PromoBars;
use Statamic\Facades\Addon;

/**
 * The demo's promo bars, each showing something the addon does, and a
 * Cargo free shipping discount for the free delivery bar. Run it again
 * to put them back as they were.
 */
class DemoSeed extends Command
{
    protected $signature = 'demo:seed';

    protected $description = 'Make the demo shop\'s promo bars (local only)';

    public function handle(PromoBars $bars): int
    {
        if (! app()->environment('local')) {
            $this->components->error('demo:seed replaces every promo bar, so it only runs on local sites.');

            return self::FAILURE;
        }

        foreach ($bars->all() as $bar) {
            $bars->delete($bar);
        }

        $shipping = Discount::query()->where('title', 'Free delivery over £40')->first() ?? Discount::make();
        $shipping->title('Free delivery over £40')->type('free_shipping')->data(['minimum_order_value' => 4000])->save();

        $text = fn (string ...$parts) => collect($parts)->map(fn ($part) => str_starts_with($part, '**')
            ? ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => trim($part, '*')]
            : ['type' => 'text', 'text' => $part])->all();

        $seed = [
            [
                'title' => 'Autumn sale',
                'message' => $text('**Autumn sale:**', ' 20% off every bag of beans'),
                'code_source' => 'text',
                'code' => 'AUTUMN20',
                'countdown' => true,
                'ends_at' => now()->addDays(4)->setTime(23, 59)->format('Y-m-d H:i'),
                'cta_label' => 'Shop the sale',
                'cta_url' => '/shop',
                'style' => 'solid',
            ],
            [
                'title' => 'Free delivery',
                'message' => $text('Free UK delivery on orders over :threshold'),
                'free_delivery' => true,
                'free_delivery_source' => 'discount',
                'free_delivery_discount' => $shipping->id(),
                'pages' => 'only',
                'collections' => ['products'],
                'urls' => "/shop\n/basket",
                'style' => 'subtle',
            ],
            [
                'title' => 'Members early access',
                'message' => $text('**Members:**', ' the winter blend is yours a week early'),
                'audience' => 'signed_in',
                'groups' => ['members'],
                'cta_label' => 'Have a look',
                'cta_url' => '/shop/house-blend',
                'style' => 'dark',
            ],
            [
                'title' => 'Newsletter',
                'message' => $text('New roasts every Tuesday. Sign in to hear first.'),
                'audience' => 'guests',
                'cta_label' => 'Sign in',
                'cta_url' => '/sign-in',
                'style' => 'minimal',
                'dismiss_days' => 30,
            ],
            [
                'title' => 'Black Friday',
                'message' => $text('**Black Friday:**', ' half price subscriptions, today only'),
                'starts_at' => now()->year.'-11-27 00:00',
                'ends_at' => now()->year.'-11-28 00:00',
                'code_source' => 'text',
                'code' => 'BLACKFRIDAY',
                'style' => 'gradient',
            ],
        ];

        foreach ($seed as $data) {
            $bars->save(Bar::make($data));
            $this->components->twoColumnDetail($data['title'], $data['style']);
        }

        Addon::get('1994/promo-bar-statamic')->settings()->set(['display' => 'stack'])->save();

        return self::SUCCESS;
    }
}
