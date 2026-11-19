<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use App\Services\LeadService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $user = auth()->user();
        $service = app(LeadService::class);
        $newAgentId = $data['assigned_to'] ?? null;
        unset($data['assigned_to']);
        $record = $service->updateLeadForUser($record, $user, $data);
        if ($newAgentId
            && $user->can('assign', $record)
            && (int) $newAgentId !== (int) $record->assigned_to
        ) {
            $result = $service->assignLeadsToUser([$record], (int) $newAgentId, $user);

            if ($result['assigned'] === 0) {
                Notification::make()
                    ->title('Agent is at full capacity or unavailable')
                    ->danger()
                    ->send();
            }
        }

        return $record->refresh();
    }
}
