<?php

/*
| The article editor's writing assistant. Every value here can also be set in
| Admin → Integrations; the admin values win over these .env defaults.
*/
return [
    // Which provider writes: "openai" or "anthropic".
    'provider' => env('AI_PROVIDER', 'openai'),

    // Output token budget per request (reasoning/thinking counts towards it).
    'max_tokens' => (int) env('AI_MAX_TOKENS', 16000),

    'requests_per_hour' => (int) env('AI_REQUESTS_PER_HOUR', 20),

    // The house voice prompt. Empty uses the built-in one (App\Services\Ai\Prompts::VOICE).
    'voice' => env('AI_VOICE'),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        // Required. No default on purpose: pick the model in the admin or set OPENAI_MODEL.
        'model' => env('OPENAI_MODEL'),
        // Optional reasoning effort for reasoning models: minimal|low|medium|high. Empty = model default.
        'reasoning_effort' => env('OPENAI_REASONING_EFFORT'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'organization' => env('OPENAI_ORGANIZATION'),
        'timeout' => (int) env('OPENAI_TIMEOUT', 300),
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        // Required when this provider is used, e.g. ANTHROPIC_MODEL=claude-opus-5-5.
        'model' => env('ANTHROPIC_MODEL'),
        // Optional effort: low|medium|high|xhigh|max. Empty uses the model default.
        'effort' => env('ANTHROPIC_EFFORT'),
        // Server-side refusal fallback ("default" lets the API pick). Empty to turn off.
        'fallbacks' => env('ANTHROPIC_FALLBACKS', 'default'),
        'base_url' => env('ANTHROPIC_BASE_URL'),
        'timeout' => (int) env('ANTHROPIC_TIMEOUT', 300),
    ],
];
