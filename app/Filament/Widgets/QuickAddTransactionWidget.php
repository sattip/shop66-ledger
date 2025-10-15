<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class QuickAddTransactionWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.quick-add-transaction-widget';

    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';

    protected $listeners = ['store-changed' => '$refresh'];

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'transaction_date' => Carbon::now()->format('Y-m-d'),
            'type' => 'income',
            'category_id' => null,
            'amount' => null,
            'description' => '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(6)
                    ->schema([
                        Forms\Components\DatePicker::make('transaction_date')
                            ->label('Ημερομηνία')
                            ->required()
                            ->default(Carbon::now())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        Forms\Components\ToggleButtons::make('type')
                            ->label('Τύπος')
                            ->options([
                                'income' => 'Έσοδο',
                                'expense' => 'Έξοδο',
                            ])
                            ->icons([
                                'income' => 'heroicon-o-arrow-trending-up',
                                'expense' => 'heroicon-o-arrow-trending-down',
                            ])
                            ->colors([
                                'income' => 'success',
                                'expense' => 'danger',
                            ])
                            ->inline()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('category_id', null))
                            ->columnSpan(1),

                        Forms\Components\Select::make('category_id')
                            ->label('Κατηγορία')
                            ->options(function (callable $get) {
                                $type = $get('type');
                                if (! $type) {
                                    return [];
                                }

                                return Category::where('type', $type)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('amount')
                            ->label('Ποσό')
                            ->numeric()
                            ->required()
                            ->prefix('€')
                            ->step(0.01)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('description')
                            ->label('Περιγραφή')
                            ->placeholder('Προαιρετικά...')
                            ->columnSpan(1),
                    ]),
            ])
            ->statePath('data');
    }

    public function addTransaction(): void
    {
        $data = $this->form->getState();

        $storeId = app(\App\Support\StoreContext::class)->get();

        if (! $storeId) {
            Notification::make()
                ->title('Σφάλμα')
                ->body('Παρακαλώ επιλέξτε κατάστημα.')
                ->danger()
                ->send();

            return;
        }

        // Create transaction
        Transaction::create([
            'store_id' => $storeId,
            'type' => $data['type'],
            'category_id' => $data['category_id'],
            'transaction_date' => $data['transaction_date'],
            'total' => $data['amount'],
            'description' => $data['description'] ?? null,
            'status' => 'posted', // Auto-post quick transactions
        ]);

        Notification::make()
            ->title('Επιτυχής Καταχώρηση')
            ->body('Η συναλλαγή καταχωρήθηκε επιτυχώς.')
            ->success()
            ->send();

        // Reset form
        $this->form->fill([
            'transaction_date' => Carbon::now()->format('Y-m-d'),
            'type' => 'income',
            'category_id' => null,
            'amount' => null,
            'description' => '',
        ]);

        // Refresh all widgets
        $this->dispatch('transaction-added');
    }
}
