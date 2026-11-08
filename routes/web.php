<?php

use App\Content\ContentTypes;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Site;
use Illuminate\Support\Facades\Route;

/*
| Public site
*/
Route::get('/', Site\HomeController::class)->name('home');
Route::get('/work', [Site\WorkController::class, 'index'])->name('work.index');
Route::get('/work/{slug}', [Site\WorkController::class, 'show'])->name('work.show');
Route::get('/about', [Site\PageController::class, 'about'])->name('about');
Route::get('/experience', [Site\PageController::class, 'experience'])->name('experience');
Route::get('/ventures', [Site\PageController::class, 'ventures'])->name('ventures');
Route::get('/products', [Site\PageController::class, 'products'])->name('products');
Route::get('/expertise', [Site\PageController::class, 'expertise'])->name('expertise');
Route::get('/media', [Site\PageController::class, 'media'])->name('media');
Route::get('/contact', [Site\PageController::class, 'contact'])->name('contact.page');
Route::get('/blog', [Site\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [Site\BlogController::class, 'show'])->name('blog.show');

Route::post('/contact', Site\ContactController::class)->middleware('throttle:contact')->name('contact');
Route::post('/subscribe', [Site\SubscribeController::class, 'store'])->middleware('throttle:subscribe')->name('subscribe');
Route::get('/subscribe/confirm/{token}', [Site\SubscribeController::class, 'confirm'])->name('subscribe.confirm');
Route::match(['get', 'post'], '/unsubscribe/{token}', [Site\SubscribeController::class, 'unsubscribe'])->name('subscribe.unsubscribe');

Route::post('/events', Site\EventController::class)->middleware('throttle:events')->name('events');
Route::get('/whatsapp', Site\WhatsappController::class)->middleware('throttle:events')->name('whatsapp');
Route::get('/cv', Site\CvController::class)->middleware('throttle:events')->name('cv');
Route::get('/sitemap.xml', [Site\FeedController::class, 'sitemap'])->name('sitemap');
Route::get('/feed.xml', [Site\FeedController::class, 'rss'])->name('feed');
Route::get('/robots.txt', [Site\FeedController::class, 'robots'])->name('robots');

/*
| Admin
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [Admin\AuthController::class, 'create'])->name('login');
        Route::post('/login', [Admin\AuthController::class, 'store'])->middleware('throttle:login');
        Route::get('/two-factor-challenge', [Admin\AuthController::class, 'challenge'])->name('two-factor.challenge');
        Route::post('/two-factor-challenge', [Admin\AuthController::class, 'verify'])->middleware('throttle:two-factor');
        Route::get('/forgot-password', [Admin\PasswordResetController::class, 'create'])->name('password.request');
        Route::post('/forgot-password', [Admin\PasswordResetController::class, 'send'])->middleware('throttle:forgot-password')->name('password.email');
        Route::get('/reset-password/{token}', [Admin\PasswordResetController::class, 'edit'])->name('password.reset');
        Route::post('/reset-password', [Admin\PasswordResetController::class, 'update'])->middleware('throttle:reset-password')->name('password.update');
    });

    Route::middleware(['auth', 'auth.session', 'admin'])->group(function () {
        Route::post('/logout', [Admin\AuthController::class, 'destroy'])->name('logout');

        Route::get('/', Admin\OverviewController::class)->name('dashboard');
        Route::patch('/availability', [Admin\SettingsController::class, 'availability'])->name('availability');

        Route::get('/messages', [Admin\MessageController::class, 'index'])->name('messages.index');
        Route::post('/messages/{message}/reply', [Admin\MessageController::class, 'reply'])->name('messages.reply');
        Route::patch('/messages/{message}/archive', [Admin\MessageController::class, 'archive'])->name('messages.archive');
        Route::patch('/messages/{message}/restore', [Admin\MessageController::class, 'restore'])->name('messages.restore');
        Route::delete('/messages/{message}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy');

        Route::get('/subscribers', [Admin\SubscriberController::class, 'index'])->name('subscribers.index');
        Route::get('/subscribers/export', [Admin\SubscriberController::class, 'export'])->name('subscribers.export');
        Route::delete('/subscribers/{subscriber}', [Admin\SubscriberController::class, 'destroy'])->name('subscribers.destroy');

        // The same screens for every content type; the type reaches the controller as a route default.
        foreach (array_keys(ContentTypes::TYPES) as $type) {
            foreach ([
                ['get', '', 'index'],
                ['get', '/create', 'create'],
                ['post', '', 'store'],
                ['patch', '/reorder', 'reorder'],
                ['get', '/{id}/edit', 'edit'],
                ['put', '/{id}', 'update'],
                ['patch', '/{id}/visibility', 'visibility'],
                ['delete', '/{id}', 'destroy'],
            ] as [$verb, $uri, $action]) {
                Route::$verb($type.$uri, [Admin\ContentController::class, $action])
                    ->whereNumber('id')
                    ->defaults('type', $type)
                    ->name("{$type}.{$action}");
            }
        }

        Route::get('/homepage', [Admin\ListController::class, 'index'])->name('lists.index');
        Route::put('/homepage/{list}', [Admin\ListController::class, 'update'])->name('lists.update');

        Route::get('/settings', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::get('/pages', [Admin\PagesController::class, 'edit'])->name('pages.edit');
        Route::put('/pages', [Admin\PagesController::class, 'update'])->name('pages.update');

        Route::get('/integrations', [Admin\IntegrationsController::class, 'edit'])->name('integrations.edit');
        Route::put('/integrations/ai', [Admin\IntegrationsController::class, 'updateAi'])->name('integrations.ai');
        Route::post('/integrations/ai/test', [Admin\IntegrationsController::class, 'testAi'])->middleware('throttle:ai')->name('integrations.ai.test');
        Route::put('/integrations/mail', [Admin\IntegrationsController::class, 'updateMail'])->name('integrations.mail');
        Route::post('/integrations/mail/test', [Admin\IntegrationsController::class, 'testMail'])->middleware('throttle:mail-test')->name('integrations.mail.test');

        Route::get('/account', [Admin\AccountController::class, 'edit'])->name('account.edit');
        Route::put('/account/profile', [Admin\AccountController::class, 'updateProfile'])->name('account.profile');
        Route::put('/account/password', [Admin\AccountController::class, 'updatePassword'])->name('account.password');
        Route::post('/account/two-factor', [Admin\TwoFactorController::class, 'store'])->name('two-factor.store');
        Route::post('/account/two-factor/confirm', [Admin\TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
        Route::post('/account/two-factor/recovery-codes', [Admin\TwoFactorController::class, 'regenerate'])->name('two-factor.recovery');
        Route::delete('/account/two-factor', [Admin\TwoFactorController::class, 'destroy'])->name('two-factor.destroy');

        // JSON endpoints used from inside the editors.
        Route::post('/media', [Admin\MediaController::class, 'store'])->name('media.store');
        Route::post('/cv', [Admin\MediaController::class, 'cv'])->name('cv.store');
        Route::middleware('throttle:ai')->prefix('ai')->name('ai.')->group(function () {
            Route::post('/draft', [Admin\AiController::class, 'draft'])->name('draft');
            Route::post('/rewrite', [Admin\AiController::class, 'rewrite'])->name('rewrite');
            Route::post('/seo', [Admin\AiController::class, 'seo'])->name('seo');
        });
    });
});
