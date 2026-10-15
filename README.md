# Harbour Coffee: a Promo Bar demo

A small, made-up coffee shop built with [Statamic](https://statamic.com), [Cargo](https://builtwithcargo.dev) and [Promo Bar](https://github.com/1994limited/promo-bar-statamic). Every screenshot in the Promo Bar docs comes from it. Run it to try Promo Bar before you build with it, or copy its templates.

![Harbour Coffee's shop, with a sale bar, free delivery progress and a newsletter bar](https://raw.githubusercontent.com/1994limited/promo-bar-statamic/main/docs/images/shop.png)

It has five bars:

- **Autumn sale**: a countdown to the end of the sale, a code with a Copy button, and a button to the shop. Solid style.
- **Free delivery**: how far the basket is from free delivery, from a Cargo free shipping discount (£40), on the shop, product and basket pages only. Subtle.
- **Members early access**: only for signed-in customers in the Members group. Dark.
- **Newsletter**: only for guests, and back 30 days after it's dismissed. Minimal.
- **Black Friday**: scheduled for 27 November, so it isn't on the site (or in its HTML) until then. Gradient.

## Running it

You need PHP 8.3+ and Composer.

```bash
git clone https://github.com/1994limited/promo-bar-statamic-demo.git
cd promo-bar-statamic-demo
composer install
cp .env.example .env
php artisan key:generate
php artisan demo:users
php artisan demo:seed
php artisan serve
```

Open http://localhost:8000.

- `demo:users` makes `admin@example.com`, for the Control Panel at `/cp`, and `ada@example.com`, a customer in the Members group. Both use the password `password`, so it only runs with `APP_ENV=local`.
- `demo:seed` makes the five bars and the free shipping discount. Run it again to put them back as they were (it replaces every bar).
- Running Statamic Pro, Cargo and Promo Bar locally needs no licences. The Control Panel shows a trial notice.

### With static caching

Promo Bar is built for statically cached sites. To see it working on cached pages, set this in `.env`:

```
STATAMIC_STATIC_CACHING_STRATEGY=half
```

Sign in as Ada to see the members' bar replace the newsletter on the same cached pages, and add something to the basket to see the free delivery progress fetched for you.

## Where to look

| File | Shows |
| --- | --- |
| `resources/views/layout.antlers.html` | `{{ promo_bar:assets }}` in `<head>` and `{{ promo_bar }}` at the top of the page |
| `app/Console/Commands/DemoSeed.php` | Each bar's settings, made with Promo Bar's PHP API |
| `resources/addons/promo-bar-statamic.yaml` | The Promo Bar settings: bars stacked |
| `resources/views/sign-in.antlers.html` | Signing in, for the members' bar |
| `public/css/site.css` | The shop's own styles. The bars use Promo Bar's presets. |
| `scripts/screenshots.mjs` | How the docs' screenshots are taken (Playwright with your own Chrome) |

`routes/web.php` has two routes for taking screenshots (signing in as a demo user, a ready-made basket). They only exist when `APP_ENV=local`.

## Licence

The demo's own code, templates and styles are [MIT licensed](LICENSE.md): copy whatever helps. Promo Bar itself is commercial software, free to try locally.
