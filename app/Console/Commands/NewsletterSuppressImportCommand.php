<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Leads\Models\SuppressionEntry;
use Illuminate\Console\Command;

/**
 * Dok. 07 §1 — zasilenie globalnego rejestru sprzeciwów listą adresów,
 * które NIGDY nie mogą trafić do wysyłki. Domyślny powód `s_cd` (zbiór z
 * płyty, >7000 kontaktów). Plik: jeden adres w wierszu (CSV/txt).
 *
 * Adresy są hashowane przy zapisie — surowych maili rejestr nie trzyma.
 */
class NewsletterSuppressImportCommand extends Command
{
    protected $signature = 'newsletter:suppress-import
        {file : Ścieżka do pliku (jeden adres w wierszu)}
        {--reason=s_cd : Powód: s_cd|s_own_reject|objection|complaint|bounce}
        {--note= : Opcjonalna notatka}';

    protected $description = 'Import adresów do rejestru sprzeciwów (twarda blokada wysyłki)';

    public function handle(): int
    {
        $file = (string) $this->argument('file');
        if (! is_file($file)) {
            $this->error("Plik nie istnieje: {$file}");

            return self::FAILURE;
        }

        $lines = preg_split('/\r\n|\r|\n/', (string) file_get_contents($file), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        // odetnij ewentualny nagłówek CSV
        $emails = array_filter($lines, fn ($l) => str_contains($l, '@'));

        $count = SuppressionEntry::suppressMany($emails, (string) $this->option('reason'), $this->option('note'));

        $this->info("Zablokowano {$count} adresów (reason={$this->option('reason')}). Rejestr trzyma tylko hashe.");

        return self::SUCCESS;
    }
}
