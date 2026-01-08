<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GuestImportController extends Controller
{
    public function create(Request $request)
    {
        return Inertia::render('Guests/Import', [
            'importResult' => $request->session()->get('import_result'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $path = $validated['file']->getRealPath();
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return back()->withErrors(['file' => 'Unable to read the uploaded file.']);
        }

        // Detect and skip UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            // Not a BOM, rewind to beginning
            rewind($handle);
        }
        // If it was a BOM, we've already consumed it, so continue normally

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return back()->withErrors(['file' => 'CSV file is empty.']);
        }

        $normalized = array_map(function ($value) {
            return strtolower(trim($value));
        }, $header);

        $required = ['name', 'phone', 'table', 'hall'];
        $index = [];
        foreach ($required as $field) {
            $pos = array_search($field, $normalized, true);
            if ($pos === false) {
                fclose($handle);
                $found = implode(', ', $normalized);
                return back()->withErrors([
                    'file' => "CSV headers must include: name, phone, table, hall. Found: $found",
                ]);
            }
            $index[$field] = $pos;
        }

        $imported = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                if ($row === [null]) {
                    continue;
                }

                $name = isset($row[$index['name']]) ? trim($row[$index['name']]) : '';
                $phone = isset($row[$index['phone']]) ? trim($row[$index['phone']]) : '';
                $table = isset($row[$index['table']]) ? trim($row[$index['table']]) : '';
                $hall = isset($row[$index['hall']]) ? trim($row[$index['hall']]) : '';

                if ($name === '') {
                    $skipped++;
                    continue;
                }

                Guest::create([
                    'name' => $name,
                    'phone' => $phone,
                    'table_name' => $table,
                    'hall' => $hall,
                    'qr_secret' => Str::random(32),
                ]);
                $imported++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            report($e);

            return back()->withErrors([
                'file' => 'Import failed. Please check the CSV format and try again.',
            ]);
        }

        fclose($handle);

        return redirect()
            ->route('guests.import')
            ->with('import_result', [
                'imported' => $imported,
                'skipped' => $skipped,
            ]);
    }
}
