<?php

return [

  /*
  |--------------------------------------------------------------------------
  | Third Party Services
  |--------------------------------------------------------------------------
  |
  | This file is for storing the credentials for third party services such
  | as Mailgun, Postmark, AWS and more. This file provides the de facto
  | location for this type of information, allowing packages to have
  | a conventional file to locate the various service credentials.
  |
  */

  'mailgun' => [
    'domain' => env('MAILGUN_DOMAIN'),
    'secret' => env('MAILGUN_SECRET'),
    'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
  ],

  'postmark' => [
    'token' => env('POSTMARK_TOKEN'),
  ],

  'ses' => [
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
  ],

  'paytm-wallet' => [
    'env' => env('PAYTM_ENVIRONMENT'),
    'merchant_id' => env('PAYTM_MERCHANT_ID'),
    'merchant_key' => env('PAYTM_MERCHANT_KEY'),
    'merchant_website' => env('PAYTM_MERCHANT_WEBSITE'),
    'channel' => env('PAYTM_CHANNEL'),
    'industry_type' => env('PAYTM_INDUSTRY_TYPE'),
  ],

  'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
  ],

  'facebook' => [
    'client_id' => env('FACEBOOK_APP_ID'),
    'client_secret' => env('FACEBOOK_APP_SECRET'),
    'redirect' => env('FACEBOOK_REDIRECT_URI', rtrim(env('APP_URL'), '/') . '/auth/social/callback/facebook'),
  ],

  'linkedin' => [
    'client_id' => env('LINKEDIN_CLIENT_ID'),
    'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
    'redirect' => env('LINKEDIN_REDIRECT_URI', rtrim(env('APP_URL'), '/') . '/auth/social/callback/linkedin'),
  ],

  'tiktok' => [
    'client_key' => env('TIKTOK_CLIENT_KEY'),
    'client_secret' => env('TIKTOK_CLIENT_SECRET'),
    'redirect' => env('TIKTOK_REDIRECT_URI', rtrim(env('APP_URL'), '/') . '/auth/social/callback/tiktok'),
  ],

  'x' => [
    'client_id' => env('TWITTER_CLIENT_ID'),
    'client_secret' => env('TWITTER_CLIENT_SECRET'),
    'redirect' => env('TWITTER_REDIRECT_URI', rtrim(env('APP_URL'), '/') . '/auth/social/callback/twitter'),
  ],

];
