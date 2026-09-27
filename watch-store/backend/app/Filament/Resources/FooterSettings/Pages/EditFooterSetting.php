<?php

namespace App\Filament\Resources\FooterSettings\Pages;

use App\Filament\Resources\FooterSettings\FooterSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditFooterSetting extends EditRecord
{
    protected static string $resource = FooterSettingResource::class;

    protected function getHeaderActions(): array
    {
        // No delete: the storefront reads this single settings record on every page.
        return [];
    }
}