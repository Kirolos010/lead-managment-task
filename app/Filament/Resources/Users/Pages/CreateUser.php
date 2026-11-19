<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Role;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = auth()->user();

        if ($user->isSalesManager()) {
            $data['role_id'] = Role::where('slug', 'sales_agent')->value('id');
            $data['team_id'] = $user->team_id;
        }

        return static::getModel()::create($data);
    }
}
