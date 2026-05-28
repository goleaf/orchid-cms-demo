<?php

namespace App\Actions\Security;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditPrivateFileDownloadAction
{
    public function handle(
        User $actor,
        string $disk,
        string $path,
        ?Model $auditable = null,
        ?Request $request = null,
    ): void {
        app(RecordAuditLogAction::class)->handle(
            'private_file.downloaded',
            $actor,
            $auditable,
            [],
            [],
            [
                'disk' => $disk,
                'path_hash' => hash('sha256', $path),
                'filename' => Str::limit(basename($path), 120, ''),
            ],
            $request,
            'private_file_download',
        );
    }
}
