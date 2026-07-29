<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class Helpers
{
    public static function slugifyDomainForURL(string $domain): string
    {
        return $domain ? Str::slug($domain) : Str::random(8);
    }
}
