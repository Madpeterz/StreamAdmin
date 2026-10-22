<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\SlConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Change history shown on the audit log page. Entries are attributed to the signed in staff
 * member's avatar, or to the system owner for changes made without one (console, in-world API).
 */
class AuditLogger
{
    public function log(string $store, ?string $sourceId, string $valueName, ?string $oldValue = null, ?string $newValue = null): void
    {
        AuditLog::create([
            // clipped to the 2.0.x column sizes so a log entry can never fail the change it records
            'store' => mb_substr($store, 0, 12),
            'source_id' => $sourceId === null ? null : mb_substr($sourceId, 0, 8),
            'value_name' => $valueName,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'unixtime' => time(),
            'avatar_id' => Auth::user()?->avatar_id ?? SlConfig::find(1)?->owner_avatar_id,
        ]);
    }

    /** One entry per attribute changed by the last save of $model. */
    public function logChanges(string $store, ?string $sourceId, Model $model): void
    {
        $previous = $model->getPrevious();
        foreach ($model->getChanges() as $field => $newValue) {
            if ($model->hasCast($field, ['bool', 'boolean'])) {
                $this->log($store, $sourceId, $field, $this->yesNo($previous[$field] ?? null), $this->yesNo($newValue));

                continue;
            }
            $this->log($store, $sourceId, $field, $this->scalar($previous[$field] ?? null), $this->scalar($newValue));
        }
    }

    private function scalar(mixed $value): ?string
    {
        return $value === null ? null : (string) $value;
    }

    /** switches are logged as Yes/No (2.0.x logged 1/0, which reads the same as a count) */
    private function yesNo(mixed $value): ?string
    {
        return $value === null ? null : ((bool) $value ? 'Yes' : 'No');
    }
}
