<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Statamic\Facades\User;

class AuthorizeConfigure
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = User::current();

        abort_unless($user && ($user->isSuper() || $user->hasPermission('configure forms')), 403);

        return $next($request);
    }
}
