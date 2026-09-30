<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use OpenSpout\Reader\XLSX\Reader;
use Throwable;

/**
 * One-off Excel (.xlsx) import screen.
 *
 * Reads the uploaded workbook with OpenSpout, hands the raw rows to
 * processRows() for business logic/validation, then writes the prepared
 * records inside a transaction via persist().
 *
 * No queue, no database tables and no permanent file storage: the upload
 * is kept as a Livewire temporary file only for the duration of the request.
 */
class ImportItems extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Import Items';

    protected static ?string $title = 'Import Items';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.import-items';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('Import items from Excel')
                    ->description('Upload an .xlsx file. The first row must contain the column headers.')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Excel file (.xlsx)')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            // One-off import: never store the file on a disk.
                            ->storeFiles(false)
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        $state = $this->form->getState();
        $file = $state['file'] ?? null;

        if (! $file instanceof TemporaryUploadedFile) {
            Notification::make()
                ->title('Please upload an .xlsx file.')
                ->danger()
                ->send();

            return;
        }

        try {
            [$headers, $rows] = $this->readSpreadsheet($file->getRealPath());
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Could not read the Excel file.')
                ->body($exception->getMessage())
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        if ($headers === []) {
            Notification::make()
                ->title('The Excel file appears to be empty.')
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        [$records, $errors] = $this->processRows($headers, $rows);

        if ($errors !== []) {
            $this->reportErrors($errors);

            return;
        }

        DB::transaction(function () use ($records): void {
            $this->persist($records);
        });

        Notification::make()
            ->title(count($records).' row(s) imported.')
            ->success()
            ->send();

        $this->form->fill();
    }

    /**
     * Read the first worksheet into a header row and a list of value rows.
     *
     * @return array{0: array<int, string>, 1: array<int, array<int, mixed>>}
     */
    private function readSpreadsheet(string $path): array
    {
        $reader = new Reader;

        $reader->open($path);

        $headers = [];
        $rows = [];
        $rowIndex = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rowIndex++;
                $values = $row->toArray();

                if ($rowIndex === 1) {
                    $headers = array_map(fn (mixed $value): string => trim((string) $value), $values);

                    continue;
                }

                // Skip fully blank rows.
                if (count(array_filter($values, fn (mixed $value): bool => filled($value))) === 0) {
                    continue;
                }

                $rows[] = $values;
            }

            // Only the first worksheet is imported.
            break;
        }

        $reader->close();

        return [$headers, $rows];
    }

    /*
    |--------------------------------------------------------------------------
    | PROCESS THE SPREADSHEET HERE (after reading, before inserting)
    |--------------------------------------------------------------------------
    | Everything above only reads the workbook. This is the single place where
    | you turn the raw sheet into database-ready rows:
    |
    |   • map header names to your own attributes,
    |   • resolve master data (category/color/size/storage),
    |   • compute derived prices with App\Utilities\TaxService,
    |   • validate each row and collect human-readable error messages.
    |
    | Return [$records, $errors]:
    |   $records — array of rows to hand to persist(); any shape you choose.
    |   $errors  — array of messages; when non-empty the import is aborted
    |              and every message is shown in a Filament notification.
    |
    | The example below just keys each row by its header and performs no
    | business logic. Replace it with your rules.
    */
    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, string>}
     */
    private function processRows(array $headers, array $rows): array
    {
        $records = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $record = [];

            foreach ($headers as $columnIndex => $header) {
                if (blank($header)) {
                    continue;
                }

                $record[$header] = $row[$columnIndex] ?? null;
            }

            // ►►► YOUR PER-ROW TRANSFORMATION / VALIDATION GOES HERE ◄◄◄
            // e.g. validate required columns, resolve category/color/size,
            // calculate price_tax and price_round via TaxService.
            // $errors[] = 'Row ' . ($index + 2) . ': ...';

            $records[] = $record;
        }

        return [$records, $errors];
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT THE PREPARED ROWS HERE
    |--------------------------------------------------------------------------
    | Called inside a DB transaction once processRows() returned no errors.
    | Add your Eloquent inserts here, for example:
    |
    |   $item = Item::create([
    |       'name'        => $record['Name'],
    |       'category_id' => $category->id,
    |       // ...
    |   ]);
    |
    |   ItemUnits::create([
    |       'item_id'    => $item->id,
    |       'storage_id' => $storage->id,
    |       'number'     => $number,
    |       'code'       => $category->code . $size->code . $color->code . str_pad($number, 4, '0', STR_PAD_LEFT),
    |       'status'     => 'available',
    |   ]);
    */
    /**
     * @param  array<int, array<string, mixed>>  $records
     */
    private function persist(array $records): void
    {
        foreach ($records as $record) {
            // ►►► YOUR INSERTS GO HERE ◄◄◄
        }
    }

    /**
     * @param  array<int, string>  $errors
     */
    private function reportErrors(array $errors): void
    {
        $items = collect($errors)
            ->map(fn (string $error): string => '<li>'.e($error).'</li>')
            ->implode('');

        Notification::make()
            ->title('Import failed — nothing was imported')
            ->body(new HtmlString('<ul class="list-disc ps-4">'.$items.'</ul>'))
            ->danger()
            ->persistent()
            ->send();
    }
}
