<?php

namespace App\Filament\Pages;

use App\Models\WorkOrder;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Filament\Notifications\Notification;

class InstallationsCalendar extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Pregled montaža';
    protected static ?string $title           = 'Pregled montaža';
    protected static ?string $slug            = 'installations-calendar'; // /admin/installations-calendar
    protected static ?int    $navigationSort  = 12;
    protected static string  $view            = 'filament.pages.installations-calendar';

    /** Monday of current week (Y-m-d) */
    public string $weekStart;

    /** Currently opened order in modal */
    public ?WorkOrder $modalOrder = null;

    public function mount(): void
    {
        $today = Carbon::now();
        $this->weekStart = $today->startOfWeek(Carbon::MONDAY)->toDateString();
    }

    /** Go one week back */
    public function prevWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)
            ->subDays(7)
            ->startOfWeek(Carbon::MONDAY)
            ->toDateString();
    }

    /** Go one week forward */
    public function nextWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)
            ->addDays(7)
            ->startOfWeek(Carbon::MONDAY)
            ->toDateString();
    }

    /** Get current week start/end Carbon instances */
    public function getWeekRange(): array
    {
        $start = Carbon::parse($this->weekStart)->startOfWeek(Carbon::MONDAY)->startOfDay();
        $end   = (clone $start)->endOfWeek(Carbon::SUNDAY)->endOfDay();
        return [$start, $end];
    }

    /** List of 7 days (Mon..Sun) as Carbon instances */
    public function getWeekDays(): array
    {
        [$start,] = $this->getWeekRange();
        return array_map(
            fn (int $i) => (clone $start)->addDays($i),
            range(0, 6)
        );
    }

    /** Map Y-m-d => WorkOrder[] for current week */
    public function getEvents(): array
    {
        [$start, $end] = $this->getWeekRange();

        $orders = WorkOrder::query()
            ->select(['id', 'code', 'customer_name', 'status', 'scheduled_at', 'type', 'phone'])
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$start, $end])
            ->orderBy('scheduled_at')
            ->get();

        $byDay = [];
        foreach ($orders as $o) {
            $key = Carbon::parse($o->scheduled_at)->toDateString();
            $byDay[$key][] = $o;
        }
        return $byDay;
    }

    /** Count orders missing a date (new/in_progress) */
    public function getMissingCount(): int
    {
        return WorkOrder::query()
            ->whereNull('scheduled_at')
            ->whereIn('status', ['new', 'in_progress'])
            ->count();
    }

    /** CSS class by status (used in Blade) */
    public static function statusColor(string $status): string
    {
        return match ($status) {
            'new'         => 'status-new',
            'in_progress' => 'status-inprogress',
            'done'        => 'status-done',
            'cancelled'   => 'status-cancelled',
            default       => 'status-done',
        };
    }

    /** Click card -> open modal with full expand-row content */
    public function openOrder(int $workOrderId): void
    {
        $order = WorkOrder::with([
            'positions',
            'positions.metraza.product',
            'positions.garnisna.product',
            'positions.roloZebra.product',
            'positions.plise.product',
        ])->find($workOrderId);

        if (! $order) {
            $this->modalOrder = null;
            $this->dispatch('close-modal', id: 'work-order-modal');
            return;
        }

        $this->modalOrder = $order;
        $this->dispatch('open-modal', id: 'work-order-modal');
    }

    /** Close modal */
    public function closeModal(): void
    {
        $this->dispatch('close-modal', id: 'work-order-modal');
        $this->modalOrder = null;
    }

    /**
     * Drag & drop handler: move order to a new day.
     * @param int $workOrderId  The order being moved
     * @param string $targetDate YYYY-MM-DD (day cell you dropped onto)
     */
    public function moveOrder(int $workOrderId, string $targetDate): void
    {
        $order = WorkOrder::find($workOrderId);

        if (! $order) {
            Notification::make()
                ->title('Greška')
                ->body('Nalog nije pronađen.')
                ->danger()
                ->send();

            return;
        }

        // Preserve original time if exists; otherwise set to 09:00.
        $old = $order->scheduled_at ? Carbon::parse($order->scheduled_at) : null;
        $hour = $old?->hour ?? 9;
        $min  = $old?->minute ?? 0;
        $sec  = $old?->second ?? 0;

        $newDateTime = Carbon::parse($targetDate)->setTime($hour, $min, $sec);

        $order->scheduled_at = $newDateTime;
        $order->save();

        // Optional: if modal is open for this order, refresh it
        if ($this->modalOrder && $this->modalOrder->id === $order->id) {
            $this->modalOrder->refresh()->load([
                'positions',
                'positions.metraza.product',
                'positions.garnisna.product',
                'positions.roloZebra.product',
                'positions.plise.product',
            ]);
        }

        // Success toast
        Notification::make()
            ->title('Datum promenjen')
            ->body("Nalog #{$order->code} je premešten na " . $newDateTime->format('d.m.Y') . '.')
            ->success()
            ->send();

        // Re-render component so columns recalc events
        $this->dispatch('$refresh');
    }
}
