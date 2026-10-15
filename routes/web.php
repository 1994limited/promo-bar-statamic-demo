<?php

use Illuminate\Support\Facades\Route;

// Route::statamic('example', 'example-view', [
//    'title' => 'Example'
// ]);

// Demo only, for screenshots. Local sites only.
if (app()->environment('local')) {
    // Sign in as a demo user, then go on: /demo-login?as=ada@example.com&to=/shop
    Route::get('demo-login', function (\Illuminate\Http\Request $request) {
        $user = \Statamic\Facades\User::findByEmail($request->query('as', 'admin@example.com')) ?? abort(404);
        \Illuminate\Support\Facades\Auth::login($user);
        event(new \Illuminate\Auth\Events\Login('web', $user, false));
        $to = (string) $request->query('to', '/cp');
        $to = str_starts_with($to, '/') && ! str_starts_with($to, '//') ? $to : '/cp';

        // Snooze Statamic's trial licensing notice in this browser, so it isn't in the screenshots.
        return response('<script>localStorage.setItem("statamic.snooze_license_banner", Date.now() + 864e5); location.replace('.json_encode($to).');</script>');
    })->middleware('web');

    // A basket with two bags of coffee in it, for the free delivery bar.
    Route::get('demo-basket', function (\Illuminate\Http\Request $request) {
        $cart = \DuncanMcClean\Cargo\Facades\Cart::make();
        $cart->lineItems()->create(['product' => 'product-ethiopia-guji', 'quantity' => 1]);
        $cart->lineItems()->create(['product' => 'product-house-blend', 'quantity' => 1]);
        $cart->save();
        \DuncanMcClean\Cargo\Facades\Cart::setCurrent($cart);
        $to = (string) $request->query('to', '/shop');

        return redirect(str_starts_with($to, '/') && ! str_starts_with($to, '//') ? $to : '/shop');
    })->middleware('web');
}
