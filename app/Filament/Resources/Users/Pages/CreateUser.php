<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Ensure 'name' is set when creating a user. If the form submitted a
     * 'pharmacy_name' (used for client roles) but 'name' is empty, copy the
     * pharmacy_name into name so the DB insert doesn't fail when name is
     * required and has no default.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If name is empty and pharmacy_name is provided, use it as name
        if ((empty($data['name']) || is_null($data['name'])) && ! empty($data['pharmacy_name'])) {
            $data['name'] = $data['pharmacy_name'];
        }

        return $data;
    }
}
