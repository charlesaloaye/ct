<?php

namespace App\Http\Resources\Admin;

use App\Support\Totp;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Two-factor status. While setting up, includes the secret and otpauth link;
 * right after confirming, includes the recovery codes (shown once).
 *
 * @mixin \App\Models\User
 */
class TwoFactorResource extends JsonResource
{
    public function __construct($resource, private readonly ?array $recoveryCodes = null)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $pending = $this->two_factor_secret !== null && $this->two_factor_confirmed_at === null;

        return [
            'enabled' => $this->hasTwoFactorEnabled(),
            'pending' => $pending,
            'confirmedAt' => $this->two_factor_confirmed_at?->toIso8601String(),
            'recoveryCodesLeft' => count($this->two_factor_recovery_codes ?? []),
            $this->mergeWhen($pending, fn () => [
                'secret' => $this->two_factor_secret,
                'otpauthUrl' => $uri = Totp::uri($this->two_factor_secret, $this->email, parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.name')),
                'qrCode' => self::qr($uri),
            ]),
            $this->mergeWhen($this->recoveryCodes !== null, fn () => ['recoveryCodes' => $this->recoveryCodes]),
        ];
    }

    /** The otpauth link as a QR code (SVG data URI) for the authenticator app to scan. */
    private static function qr(string $uri): string
    {
        $svg = (new Writer(new ImageRenderer(new RendererStyle(192, 1), new SvgImageBackEnd())))->writeString($uri);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
