<?php

namespace App\Filament\Resources\ShippingDistricts\Pages;

use App\Filament\Resources\ShippingDistricts\ShippingDistrictResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageShippingDistricts extends ManageRecords
{
    protected static string $resource = ShippingDistrictResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
