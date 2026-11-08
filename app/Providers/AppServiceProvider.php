<?php

namespace App\Providers;

use App\Services\Ai\AiException;
use App\Services\Ai\AiWriter;
use App\Services\Ai\AnthropicWriter;
use App\Services\Ai\OpenAiWriter;
use App\Support\RuntimeConfig;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AiWriter::class, fn ($app) => match (config('ai.provider')) {
            'openai' => $app->make(OpenAiWriter::class),
            'anthropic' => $app->make(AnthropicWriter::class),
            default => throw new AiException('Unknown AI provider "'.config('ai.provider').'". Choose OpenAI or Anthropic in Admin → Integrations.', 503),
        });
    }

    public function boot(): void
    {
        // Mail and AI settings saved in the admin override .env.
        RuntimeConfig::apply();
        // Long-running queue workers pick up admin changes before every job.
        Event::listen(JobProcessing::class, fn () => RuntimeConfig::apply());

        ResetPassword::createUrlUsing(fn ($user, string $token) => route('admin.password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]));

        RateLimiter::for('contact', fn (Request $r) => Limit::perHour(config('site.limits.contact_per_hour'))->by('contact:'.$r->ip()));
        RateLimiter::for('subscribe', fn (Request $r) => Limit::perHour(config('site.limits.subscribe_per_hour'))->by('subscribe:'.$r->ip()));
        RateLimiter::for('events', fn (Request $r) => Limit::perMinute(config('site.limits.events_per_minute'))->by('events:'.$r->ip()));
        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(config('site.limits.login_per_minute'))
            ->by('login:'.mb_strtolower((string) $r->input('email')).'|'.$r->ip()));
        RateLimiter::for('two-factor', fn (Request $r) => Limit::perMinute(5)->by('two-factor:'.$r->session()->get('login.id').'|'.$r->ip()));
        RateLimiter::for('forgot-password', fn (Request $r) => Limit::perHour(5)->by('forgot-password:'.$r->ip()));
        RateLimiter::for('reset-password', fn (Request $r) => Limit::perHour(10)->by('reset-password:'.$r->ip()));
        RateLimiter::for('ai', fn (Request $r) => Limit::perHour(config('ai.requests_per_hour'))->by('ai:'.($r->user()?->id ?: $r->ip())));
        RateLimiter::for('mail-test', fn (Request $r) => Limit::perMinute(3)->by('mail-test:'.$r->user()?->id));
    }
}
