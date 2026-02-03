<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ChangeLanguage
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next)
  {
    $locale = null;
    if ($request->session()->has('currentLocaleCode')) {
      $locale = $request->session()->get('currentLocaleCode');
    }

    if (empty($locale)) {
      try {
        $languageCode = Language::query()->where('is_default', '=', 1)
          ->pluck('code')
          ->first();
        App::setLocale($languageCode ?: config('app.fallback_locale', 'en'));
      } catch (\Throwable $e) {
        App::setLocale(config('app.fallback_locale', 'en'));
      }
    } else {
      App::setLocale($locale);
    }

    return $next($request);
  }
}
