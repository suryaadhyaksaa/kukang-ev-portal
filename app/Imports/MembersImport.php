<?php

namespace App\Imports;

use App\Models\Division;
use App\Models\Position;
use App\Services\MemberService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MembersImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    public int $importedCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function __construct(private readonly MemberService $memberService) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $payload = [
                'nim' => trim((string) ($row['nim'] ?? '')),
                'full_name' => trim((string) ($row['nama_lengkap'] ?? '')),
                'email' => strtolower(trim((string) ($row['email'] ?? ''))),
                'phone' => trim((string) ($row['nomor_hp'] ?? '')) ?: null,
                'cohort_year' => (int) ($row['angkatan'] ?? now()->year),
                'joined_at' => ! empty($row['tanggal_bergabung']) ? (string) $row['tanggal_bergabung'] : now()->toDateString(),
            ];

            $validator = Validator::make($payload, [
                'nim' => ['required', 'string', 'max:20', 'unique:members,nim'],
                'full_name' => ['required', 'string', 'max:150'],
                'email' => ['required', 'email', 'max:150', 'unique:users,email', 'unique:members,email'],
                'cohort_year' => ['required', 'integer', 'min:2015', 'max:'.(now()->year + 1)],
            ]);

            if ($validator->fails()) {
                $this->errors[] = "Baris {$rowNumber} ({$payload['nim']}): ".implode(', ', $validator->errors()->all());
                continue;
            }

            $division = ! empty($row['kode_divisi'])
                ? Division::where('code', strtoupper(trim((string) $row['kode_divisi'])))->first()
                : null;

            $position = ($division && ! empty($row['jabatan']))
                ? Position::where('division_id', $division->id)
                    ->where('name', trim((string) $row['jabatan']))
                    ->first()
                : null;

            $this->memberService->createMember([
                ...$payload,
                'division_id' => $division?->id,
                'position_id' => $position?->id,
                'status' => 'active',
                'role' => 'anggota',
            ]);

            $this->importedCount++;
        }
    }
}
