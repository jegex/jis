<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\EmailService;
use App\Services\InvoicePdfGenerator;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->weight('font-bold'),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->placeholder(fn ($state) => $state ?? 'Guest'),

                TextColumn::make('total')
                    ->money(fn ($record) => $record->currency_code ?? 'IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('preorder_released_at')
                    ->label('Product Sent')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-')
                    ->color(fn ($state) => $state ? 'success' : 'warning'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatus::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('preview_invoice')
                        ->label('Preview Invoice')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->color('gray')
                        ->visible(fn (Order $record): bool => $record->status === OrderStatus::Paid)
                        ->modalHeading(fn (Order $record): string => "Invoice — {$record->order_number}")
                        ->modalWidth(Width::SevenExtraLarge)
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel(__('Close'))
                        ->modalContent(fn (Order $record): View => view('filament.orders.invoice-preview', [
                            'previewUrl' => route('invoices.download', [
                                'invoice' => app(InvoicePdfGenerator::class)->generate($record),
                                'inline' => 1,
                            ]),
                        ])),

                    Action::make('download_invoice')
                        ->label('Download Invoice')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('gray')
                        ->visible(fn (Order $record): bool => $record->status === OrderStatus::Paid)
                        ->action(function (Order $record): StreamedResponse {
                            $media = self::ensureInvoiceMedia($record);

                            return response()->streamDownload(
                                fn (): string => (string) Storage::disk($media->disk)->get($media->getPathRelativeToRoot()),
                                $media->file_name,
                                ['Content-Type' => 'application/pdf'],
                            );
                        }),

                    Action::make('resend_confirmation')
                        ->label('Resend Confirmation')
                        ->icon('heroicon-o-arrow-path')
                        ->color('gray')
                        ->visible(fn (Order $record): bool => $record->status === OrderStatus::Paid)
                        ->requiresConfirmation()
                        ->action(function (Order $record): void {
                            app(EmailService::class)->sendOrderConfirmation($record);

                            Notification::make()
                                ->title('Confirmation email sent')
                                ->body("Order confirmation for {$record->order_number} has been resent with the invoice attached.")
                                ->success()
                                ->send();
                        }),

                    Action::make('send_preorder_product')
                        ->label('Send Product')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->visible(fn (Order $record): bool => $record->status === OrderStatus::Paid
                            && $record->preorder_released_at === null
                            && $record->items->first()?->product?->isPreorder())
                        ->requiresConfirmation()
                        ->tooltip('Send the preorder download link to the customer via email.')
                        ->action(function (Order $record): void {
                            $record->update(['preorder_released_at' => now()]);

                            app(EmailService::class)->sendPreorderRelease($record);

                            Notification::make()
                                ->title('Product sent')
                                ->body("Download link for {$record->order_number} has been sent to the customer.")
                                ->success()
                                ->send();
                        }),

                    EditAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private static function ensureInvoiceMedia(Order $record): Media
    {
        $generator = app(InvoicePdfGenerator::class);
        $generator->generate($record);

        /** @var Media $media */
        $media = $generator->storedPdf($record);

        return $media;
    }
}
