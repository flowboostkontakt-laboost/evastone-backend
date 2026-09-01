<?php

declare(strict_types=1);

namespace App\Domain\Leads\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Wpis globalnego rejestru sprzeciwów (dok. 07 §6). Adresy trzymamy jako
 * hash — dopasowanie hash-do-hash, zero surowych maili w rejestrze.
 */
class SuppressionEntry extends Model
{
    protected $table = 'suppression_list';

    protected $fillable = ['email_hash', 'reason', 'channel', 'note'];

    /** sha256(lower(trim(email)) + pepper) — ten sam pepper co ip_hash (RODO §15). */
    public static function hashEmail(string $email): string
    {
        return hash('sha256', mb_strtolower(trim($email)).config('evastone.ip_pepper'));
    }

    /** Twarda blokada — czy adres jest w rejestrze sprzeciwów (dowolny powód). */
    public static function isSuppressed(string $email): bool
    {
        return static::where('email_hash', static::hashEmail($email))->exists();
    }

    /** Dodaje adres do rejestru (idempotentnie po email_hash). */
    public static function suppress(string $email, string $reason, ?string $channel = 'newsletter', ?string $note = null): void
    {
        static::updateOrCreate(
            ['email_hash' => static::hashEmail($email)],
            ['reason' => $reason, 'channel' => $channel, 'note' => $note],
        );
    }

    /** Bulk-import listy adresów (np. S-CD z płyty) — zwraca liczbę dodanych. */
    public static function suppressMany(iterable $emails, string $reason, ?string $note = null): int
    {
        $now = now();
        $rows = [];
        foreach ($emails as $email) {
            $email = trim((string) $email);
            if ($email === '') {
                continue;
            }
            $rows[static::hashEmail($email)] = [
                'email_hash' => static::hashEmail($email),
                'reason' => $reason,
                'channel' => 'all',
                'note' => $note,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if ($rows === []) {
            return 0;
        }

        // upsert po unikalnym email_hash — idempotentne przy wielokrotnym imporcie
        DB::table('suppression_list')->upsert(array_values($rows), ['email_hash'], ['reason', 'note', 'updated_at']);

        return count($rows);
    }
}
