<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class Hall
{
    /**
     * 'scene:lobby'      -> route('frontend.hall.lobby')   ('scene:exterior' is the site start = frontend.home)
     * 'route:jobs.index' -> route('frontend.jobs.index')   (the plain name is tried too)
     * '#' if the route does not exist yet
     */
    public static function url(string $target): string
    {
        [$type, $value] = array_pad(explode(':', $target, 2), 2, null);

        return match ($type) {
            'scene' => $value === 'exterior'
                ? route('frontend.home')
                : (Route::has('frontend.hall.'.$value) ? route('frontend.hall.'.$value) : '#'),
            'route' => Route::has('frontend.'.$value) ? route('frontend.'.$value)
                : (Route::has($value) ? route($value) : '#'),
            default => '#',
        };
    }

    /** true when the target page is built (so the UI can show "soon" for the rest) */
    public static function ready(string $target): bool
    {
        return self::url($target) !== '#';
    }

    public static function active(string $target): bool
    {
        $url = self::url($target);

        return $url !== '#' && request()->url() === $url;
    }
}
