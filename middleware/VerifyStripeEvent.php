<?php namespace StudioAzura\Stripe\Middleware;

use App;
use Response;
use StudioAzura\Stripe\Models\Settings;

class VerifyStripeEvent
{
    public function handle($request, $next)
    {
        # Stripe-Signature is required
        if (!$request->hasHeader('Stripe-Signature')) {
            return $this->refused();
        }

        $mode = App::environment('live') ? 'live' : 'test';
        $timestamp = 0;
        $v1 = [];

        foreach(explode(',', $request->header('Stripe-Signature')) as $entry) {
            [$key, $value] = explode('=', $entry);
            if ($key === 'v1') {
                $v1[] = $value;
            } elseif ($key === 't') {
                $timestamp = $value;
            }
        }

        $body = $request->getContent();
        $secretKey = Settings::get('whsec_' . $mode);
        $payload = sprintf("%s.%s", $timestamp, $body);

        foreach ($v1 as $signed_payload) {
            $test_signed_payload = hash_hmac('sha256', $payload, $secretKey);
            if ($test_signed_payload === $signed_payload) {
                # found valid signature
                return $next($request);
            }
        }

        # could not find a valid signature
        return $this->refused();
    }

    public function refused()
    {
        return Response::make('request-refused', 403);
    }
}

