<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\TransactionItem;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
 
        $data['is_split'] = (bool) ($data['is_split'] ?? false); 

        if (!$data['is_split'] && isset($data['_single_category_id'])) {
            session(['_single_category_id' => $data['_single_category_id']]);
        }
        unset($data['_single_category_id']);

        if ($data['type'] === 'transfer') {
            $data['destination_amount'] = ($data['amount'] ?? 0) - ($data['admin_fee'] ?? 0);
        }

        return $data;
    }
 
    protected function afterCreate(): void
    {
        $record = $this->getRecord();
 
        // If not split but has a single category, create one item
        if (!$record->is_split) {
            $categoryId = session('_single_category_id');
            if ($categoryId) {
                TransactionItem::create([
                    'transaction_id' => $record->id,
                    'category_id' => $categoryId,
                    'amount' => $record->amount,
                    'description' => $record->description,
                ]);
                session()->forget('_single_category_id');
            }
        }
    }
}
