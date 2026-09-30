<?php

namespace App\Filament\Pages;

use App\Models\Tax;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaxSetting extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-percent-badge';
    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Tax';
    protected static ?string $title = 'Tax';
    protected string $view = 'filament.pages.tax-setting';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = Tax::firstOrFail();
        $this->form->fill($setting->toArray());
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('Tax Setting')
                    ->schema([
                        TextInput::make('percentage')
                            ->numeric()
                            ->autofocus()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                    ])
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        Tax::find(1)->update($this->form->getState());

        Notification::make()
            ->title('Tax changed')
            ->success()
            ->send();
    }
}
