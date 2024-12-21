<?php

namespace esameisa\CheckGitBranchMiddleware;

use Closure;
use Illuminate\Http\Request;

class CheckGitBranchMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD'));
        if ($branch !== config('checkgitbranch.branch_name') && app()->environment('production')) {
            if (file_exists(app_path('Services/Integrations/MoraSMS.php'))) {
                $moraSMS = new \App\Services\Integrations\MoraSMS;
                $moraSMS->send(config('checkgitbranch.message'), config('checkgitbranch.phone_number'));
            }
            abort(404);
        }

        return $next($request);
    }
}
