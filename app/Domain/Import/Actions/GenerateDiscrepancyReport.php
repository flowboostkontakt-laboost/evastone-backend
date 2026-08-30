<?php

declare(strict_types=1);

namespace App\Domain\Import\Actions;

use App\Domain\Import\Models\ImportProductRaw;
use Illuminate\Support\Facades\Storage;

/**
 * §4.5 — raport rozbieżności CSV, generowany przy KAŻDYM przebiegu,
 * do wysyłki do klientki od razu, nie na końcu migracji.
 *
 * Format: model_no; locale; brakujące_pole; wartość_z_ComUp; wartość_z_Comarch; status
 */
class GenerateDiscrepancyReport
{
    public function handle(string $batchId): string
    {
        $lines = ['model_no;locale;brakujące_pole;wartość_z_ComUp;wartość_z_Comarch;status'];

        $rows = ImportProductRaw::where('batch_id', $batchId)
            ->orWhere(fn ($q) => $q->whereIn('state', ['rejected', 'normalized', 'imported']))
            ->get();

        foreach ($rows as $row) {
            $modelNo = $row->normalized['model_no']
                ?? $row->payload['model_no']
                ?? $row->external_id;

            foreach ($row->errors['blockers'] ?? [] as $issue) {
                [$field, $locale] = $this->splitIssue($issue);
                $lines[] = implode(';', [
                    $modelNo, $locale, $this->csv($field), '', '',
                    $row->state === 'rejected' ? 'odrzucony' : 'do_uzupełnienia',
                ]);
            }

            foreach ($row->errors['warnings'] ?? [] as $issue) {
                [$field, $locale] = $this->splitIssue($issue);
                $lines[] = implode(';', [$modelNo, $locale, $this->csv($field), '', '', 'ostrzeżenie']);
            }
        }

        $path = "reports/import-{$batchId}.csv";
        Storage::disk('local')->put($path, "\u{FEFF}".implode("\n", $lines));

        return Storage::disk('local')->path($path);
    }

    private function splitIssue(string $issue): array
    {
        if (preg_match('/^(.*): (de|en|pl)$/', $issue, $m)) {
            return [$m[1], $m[2]];
        }

        return [$issue, ''];
    }

    private function csv(string $value): string
    {
        return str_replace(';', ',', $value);
    }
}
