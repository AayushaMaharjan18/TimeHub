<?php

namespace App\Filament\Resources\FooterSettings\Pages;

use App\Filament\Resources\FooterSettings\FooterSettingResource;
use App\Models\FooterSetting;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFooterSettings extends ListRecords
{
    protected static string $resource = FooterSettingResource::class;

    /**
     * Site settings is a single record: open it straight away instead of
     * showing a one-row table.
     */
    public function mount(): void
    {
        $settings = FooterSetting::where('is_active', true)->first() ?? FooterSetting::first();

        $this->redirect($settings
            ? FooterSettingResource::getUrl('edit', ['record' => $settings])
            : FooterSettingResource::getUrl('create'));
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
