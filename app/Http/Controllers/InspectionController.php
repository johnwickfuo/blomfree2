<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInspectionRequest;
use App\Mail\InspectionConfirmedCustomer;
use App\Mail\InspectionRequestedAdmin;
use App\Models\Animal;
use App\Models\Inspection;
use App\Models\Land;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class InspectionController extends Controller
{
    private const WEEKDAY_NUMBERS = [
        'Sun' => 0, 'Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6,
    ];

    public function createForLand(Land $land): Response
    {
        return $this->renderCreateForm('land', $land, [
            'title' => $land->title,
            'subtitle' => $land->city_or_lga.', '.$land->state,
            'meta' => $land->location_address,
            'image' => $land->cover_url,
            'back_url' => route('lands.show', $land),
            'kind' => 'site',
        ]);
    }

    public function createForAnimal(Animal $animal): Response
    {
        abort_unless($animal->supports_inspection, 404);

        return $this->renderCreateForm('animal', $animal, [
            'title' => $animal->name,
            'subtitle' => $animal->breed,
            'meta' => $animal->origin,
            'image' => $animal->cover_url,
            'back_url' => route('kennel-farm.show', $animal),
            'kind' => 'animal',
        ]);
    }

    public function store(StoreInspectionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $class = Inspection::INSPECTABLE_TYPES[$data['inspectable_type']];
        $inspectable = $class::findOrFail($data['inspectable_id']);

        $inspection = $inspectable->inspections()->create([
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'preferred_date' => $data['preferred_date'],
            'preferred_time_slot' => $data['preferred_time_slot'],
            'alternate_date' => $data['alternate_date'] ?? null,
            'party_size' => $data['party_size'],
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
        ]);

        $inspection->setRelation('inspectable', $inspectable);

        $adminEmail = Setting::get('admin_notification_email', 'admin@blomfree.com');
        Mail::to($adminEmail)->send(new InspectionRequestedAdmin($inspection));
        Mail::to($inspection->customer_email)->send(new InspectionConfirmedCustomer($inspection));

        return redirect()->route('inspections.show', $inspection);
    }

    public function show(Inspection $inspection): Response
    {
        $inspection->load('inspectable.media');

        $inspectable = $inspection->inspectable;
        $approved = $inspection->isApproved();

        return Inertia::render('Inspections/Show', [
            'inspection' => [
                'reference' => $inspection->reference,
                'status' => $inspection->status,
                'customer_name' => $inspection->customer_name,
                'customer_email' => $inspection->customer_email,
                'customer_phone' => $inspection->customer_phone,
                'preferred_date' => $inspection->preferred_date?->toDateString(),
                'preferred_time_slot' => $inspection->preferred_time_slot,
                'alternate_date' => $inspection->alternate_date?->toDateString(),
                'party_size' => $inspection->party_size,
                'notes' => $inspection->notes,
                'created_at' => $inspection->created_at?->toIso8601String(),
                // Meeting details stay hidden until the request is approved.
                'meeting_address' => $approved ? $inspection->meeting_address : null,
                'meeting_instructions' => $approved ? $inspection->meeting_instructions : null,
            ],
            'property' => $inspectable ? [
                'type' => class_basename($inspectable),
                'title' => $inspectable->title,
                'location' => match (true) {
                    $inspectable instanceof Land => $inspectable->location_address,
                    $inspectable instanceof Animal => $inspectable->breed,
                    default => null,
                },
                'cover_url' => $inspectable->cover_url ?? null,
                'url' => match (true) {
                    $inspectable instanceof Land => route('lands.show', $inspectable),
                    $inspectable instanceof Animal => route('kennel-farm.show', $inspectable),
                    default => null,
                },
            ] : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $subject
     */
    private function renderCreateForm(string $type, Model $subject, array $subjectDisplay): Response
    {
        return Inertia::render('Inspections/Create', [
            'subject' => array_merge(
                ['type' => $type, 'id' => $subject->getKey()],
                $subjectDisplay,
            ),
            'timeSlots' => Setting::list('inspection_time_slots', '9am-11am,11am-1pm,1pm-3pm,3pm-5pm'),
            'disabledWeekDays' => $this->disabledWeekDays(),
        ]);
    }

    /**
     * Weekday numbers (0 = Sunday) that should be disabled in the date picker.
     *
     * @return list<int>
     */
    private function disabledWeekDays(): array
    {
        $allowed = Setting::list('inspection_days', 'Mon,Tue,Wed,Thu,Fri,Sat');

        $disabled = [];
        foreach (self::WEEKDAY_NUMBERS as $name => $number) {
            if (! in_array($name, $allowed, true)) {
                $disabled[] = $number;
            }
        }

        return $disabled;
    }
}
