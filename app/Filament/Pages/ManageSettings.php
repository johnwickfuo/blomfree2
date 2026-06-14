<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-settings';

    protected static ?string $title = 'Site Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            // Branding
            'site_logo' => self::pathToFileUploadState(Setting::get('site_logo_path')),
            'ceo_image' => self::pathToFileUploadState(Setting::get('ceo_image_path')),

            // Notifications
            'admin_notification_email' => Setting::get('admin_notification_email'),
            'affiliate_admin_notification_email' => Setting::get('affiliate_admin_notification_email'),
            'installment_admin_notification_email' => Setting::get('installment_admin_notification_email'),
            'pickup_address' => Setting::get('pickup_address'),

            // Inspections
            'inspection_days' => Setting::list('inspection_days'),
            'inspection_time_slots' => Setting::list('inspection_time_slots'),

            // Affiliate
            'affiliate_auto_approve_signup' => (bool) Setting::get('affiliate_auto_approve_signup'),
            'affiliate_commission_hold_days' => (int) Setting::get('affiliate_commission_hold_days', '7'),
            'affiliate_commission_hold_fallback_days' => (int) Setting::get('affiliate_commission_hold_fallback_days', '30'),
            'affiliate_minimum_withdrawal' => (int) Setting::get('affiliate_minimum_withdrawal', '5000'),
            'affiliate_withdrawal_fee' => (int) Setting::get('affiliate_withdrawal_fee', '100'),

            // Installments
            'installment_signup_enabled' => (bool) Setting::get('installment_signup_enabled'),
            'installment_forfeiture_percentage' => (int) Setting::get('installment_forfeiture_percentage', '10'),
            'installment_land_resale_wait_days' => (int) Setting::get('installment_land_resale_wait_days', '90'),
            'installment_terms_version' => Setting::get('installment_terms_version', 'v1.0'),
            'installment_due_reminder_days_before' => (int) Setting::get('installment_due_reminder_days_before', '3'),
            'installment_overdue_warning_days' => Setting::get('installment_overdue_warning_days', '30,14,7,3,1'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Tabs::make('Settings')
                    ->tabs([
                        Tabs\Tab::make('Branding')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Site logo')
                                    ->description('Shown in the header on every page. PNG or SVG with transparent background works best.')
                                    ->schema([
                                        FileUpload::make('site_logo')
                                            ->label('Logo image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('branding')
                                            ->visibility('public')
                                            ->maxSize(2048)
                                            ->imagePreviewHeight('120')
                                            ->helperText('Recommended: 240 x 60 px. Max 2 MB.'),
                                    ]),
                                Section::make('CEO photo')
                                    ->description('Used on the home page hero and the About page. A clean portrait works best.')
                                    ->schema([
                                        FileUpload::make('ceo_image')
                                            ->label('CEO portrait')
                                            ->image()
                                            ->disk('public')
                                            ->directory('branding')
                                            ->visibility('public')
                                            ->maxSize(4096)
                                            ->imagePreviewHeight('200')
                                            ->helperText('Recommended: portrait 4:5, at least 800 x 1000 px. Max 4 MB.'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Notifications')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                Section::make('Email addresses')
                                    ->description('Where admin alerts are delivered. Leave the program-specific fields blank to use the general admin email.')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('admin_notification_email')
                                            ->label('General admin email')
                                            ->email()
                                            ->required()
                                            ->helperText('Orders, inquiries, low-stock digests.'),
                                        TextInput::make('affiliate_admin_notification_email')
                                            ->label('Affiliate program email')
                                            ->email()
                                            ->helperText('New affiliate signups, withdrawal requests.'),
                                        TextInput::make('installment_admin_notification_email')
                                            ->label('Installment program email')
                                            ->email()
                                            ->helperText('New installment requests, defaults, refund requests.'),
                                    ]),
                                Section::make('Pickup address')
                                    ->description('Shown to customers who choose self-pickup at checkout.')
                                    ->schema([
                                        Textarea::make('pickup_address')
                                            ->label('Office / pickup address')
                                            ->rows(4)
                                            ->required(),
                                    ]),
                            ]),

                        Tabs\Tab::make('Inspections')
                            ->icon('heroicon-o-calendar-days')
                            ->schema([
                                Section::make('Booking windows')
                                    ->description('Days and time slots customers can pick when booking a land or animal inspection.')
                                    ->columns(2)
                                    ->schema([
                                        TagsInput::make('inspection_days')
                                            ->label('Available days')
                                            ->placeholder('Mon, Tue, …')
                                            ->helperText('Press Enter after each day.'),
                                        TagsInput::make('inspection_time_slots')
                                            ->label('Time slots')
                                            ->placeholder('9am-11am')
                                            ->helperText('Press Enter after each slot.'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Affiliate program')
                            ->icon('heroicon-o-user-group')
                            ->schema([
                                Section::make('Signups & commissions')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('affiliate_auto_approve_signup')
                                            ->label('Auto-approve new affiliate signups')
                                            ->helperText('Off means new affiliates wait for manual approval.')
                                            ->columnSpanFull(),
                                        TextInput::make('affiliate_commission_hold_days')
                                            ->label('Commission hold (days after delivery)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->required()
                                            ->suffix('days')
                                            ->helperText('How long after delivery before a commission becomes available to withdraw.'),
                                        TextInput::make('affiliate_commission_hold_fallback_days')
                                            ->label('Fallback hold (days after order)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->required()
                                            ->suffix('days')
                                            ->helperText('Used when a delivery date is not recorded.'),
                                    ]),
                                Section::make('Withdrawals')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('affiliate_minimum_withdrawal')
                                            ->label('Minimum withdrawal amount')
                                            ->numeric()
                                            ->minValue(0)
                                            ->required()
                                            ->prefix('₦'),
                                        TextInput::make('affiliate_withdrawal_fee')
                                            ->label('Withdrawal fee')
                                            ->numeric()
                                            ->minValue(0)
                                            ->required()
                                            ->prefix('₦')
                                            ->helperText('Deducted from each payout.'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Installments')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Section::make('Program controls')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('installment_signup_enabled')
                                            ->label('Accept new installment plans')
                                            ->helperText('Off means the customer-facing installment signup is hidden.')
                                            ->columnSpanFull(),
                                        TextInput::make('installment_forfeiture_percentage')
                                            ->label('Forfeiture on default')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->required()
                                            ->suffix('%')
                                            ->helperText('Kept from refund when a plan defaults or is cancelled.'),
                                        TextInput::make('installment_land_resale_wait_days')
                                            ->label('Land resale wait after default')
                                            ->numeric()
                                            ->minValue(0)
                                            ->required()
                                            ->suffix('days')
                                            ->helperText('How long a defaulted plot stays off the market.'),
                                        TextInput::make('installment_terms_version')
                                            ->label('Terms & conditions version')
                                            ->required()
                                            ->helperText('Bumping this forces customers to re-accept the latest terms.'),
                                    ]),
                                Section::make('Reminders')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('installment_due_reminder_days_before')
                                            ->label('Send "due soon" reminder (days before)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->required()
                                            ->suffix('days'),
                                        TextInput::make('installment_overdue_warning_days')
                                            ->label('Overdue warning schedule')
                                            ->required()
                                            ->placeholder('30,14,7,3,1')
                                            ->helperText('Comma-separated list of days past due when a warning is sent.'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Branding — store the disk path (or null when cleared).
        Setting::set('site_logo_path', self::fileUploadStateToPath($data['site_logo'] ?? null) ?? '');
        Setting::set('ceo_image_path', self::fileUploadStateToPath($data['ceo_image'] ?? null) ?? '');

        // Notifications
        Setting::set('admin_notification_email', (string) ($data['admin_notification_email'] ?? ''));
        Setting::set('affiliate_admin_notification_email', (string) ($data['affiliate_admin_notification_email'] ?? ''));
        Setting::set('installment_admin_notification_email', (string) ($data['installment_admin_notification_email'] ?? ''));
        Setting::set('pickup_address', (string) ($data['pickup_address'] ?? ''));

        // Inspections — TagsInput returns an array, store as CSV.
        Setting::set('inspection_days', implode(',', $data['inspection_days'] ?? []));
        Setting::set('inspection_time_slots', implode(',', $data['inspection_time_slots'] ?? []));

        // Affiliate
        Setting::set('affiliate_auto_approve_signup', ($data['affiliate_auto_approve_signup'] ?? false) ? '1' : '0');
        Setting::set('affiliate_commission_hold_days', (string) (int) ($data['affiliate_commission_hold_days'] ?? 7));
        Setting::set('affiliate_commission_hold_fallback_days', (string) (int) ($data['affiliate_commission_hold_fallback_days'] ?? 30));
        Setting::set('affiliate_minimum_withdrawal', (string) (int) ($data['affiliate_minimum_withdrawal'] ?? 5000));
        Setting::set('affiliate_withdrawal_fee', (string) (int) ($data['affiliate_withdrawal_fee'] ?? 100));

        // Installments
        Setting::set('installment_signup_enabled', ($data['installment_signup_enabled'] ?? false) ? '1' : '0');
        Setting::set('installment_forfeiture_percentage', (string) (int) ($data['installment_forfeiture_percentage'] ?? 10));
        Setting::set('installment_land_resale_wait_days', (string) (int) ($data['installment_land_resale_wait_days'] ?? 90));
        Setting::set('installment_terms_version', (string) ($data['installment_terms_version'] ?? 'v1.0'));
        Setting::set('installment_due_reminder_days_before', (string) (int) ($data['installment_due_reminder_days_before'] ?? 3));
        Setting::set('installment_overdue_warning_days', (string) ($data['installment_overdue_warning_days'] ?? '30,14,7,3,1'));

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    /**
     * Convert a stored disk path into the array shape Filament's FileUpload
     * component expects ([uuid => path]).
     */
    private static function pathToFileUploadState(?string $path): array
    {
        if (blank($path)) {
            return [];
        }

        return [(string) Str::uuid() => $path];
    }

    /**
     * Pull a single stored path back out of the FileUpload component's
     * array-shaped state.
     */
    private static function fileUploadStateToPath(mixed $state): ?string
    {
        if (is_string($state) && filled($state)) {
            return $state;
        }

        if (is_array($state)) {
            $first = collect($state)->first();

            return is_string($first) && filled($first) ? $first : null;
        }

        return null;
    }
}
