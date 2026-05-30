<?php

use App\Actions\Security\PruneOldLoginAttemptsAction;
use App\Actions\Security\PruneOldUserSecuritySessionsAction;
use App\Rules\LoginAttemptRetentionDaysRule;
use App\Rules\UserSecuritySessionRetentionDaysRule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('security:prune-login-attempts {--days=90}', function (): int {
    $validator = Validator::make(
        ['days' => $this->option('days')],
        ['days' => [new LoginAttemptRetentionDaysRule]],
    );

    if ($validator->fails()) {
        $this->error($validator->errors()->first('days'));

        return Command::FAILURE;
    }

    $deleted = app(PruneOldLoginAttemptsAction::class)->handle((int) $this->option('days'));
    $this->info(tkey('security.login_attempts.messages.pruned')." {$deleted}");

    return Command::SUCCESS;
})->purpose('Prune old local login attempt records');

Artisan::command('security:prune-sessions {--days=90}', function (): int {
    $validator = Validator::make(
        ['days' => $this->option('days')],
        ['days' => [new UserSecuritySessionRetentionDaysRule]],
    );

    if ($validator->fails()) {
        $this->error($validator->errors()->first('days'));

        return Command::FAILURE;
    }

    $deleted = app(PruneOldUserSecuritySessionsAction::class)->handle((int) $this->option('days'));
    $this->info(tkey('security.sessions.messages.pruned')." {$deleted}");

    return Command::SUCCESS;
})->purpose('Prune old logged-out or revoked security sessions');
