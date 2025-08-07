<?php

namespace App\Http\Controllers;

use App\Models\Expense;
    use Illuminate\Http\Request;

/**
 * Controller for managing company expenses.
 */
class ExpenseController extends Controller
{
    public function index()
    {
        // List all expenses ordered by month and creation date
        $expenses = Expense::orderByDesc('month')->orderByDesc('created_at')->get();
        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        $types = $this->getTypes();
        $paymentTypes = $this->getPaymentTypes();
        return view('expenses.create', compact('types', 'paymentTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description'   => 'nullable|string|max:255',
            'type'          => 'required|in:' . implode(',', array_keys($this->getTypes())),
            'amount'        => 'required|numeric|min:0',
            'payment_type'  => 'required|in:' . implode(',', array_keys($this->getPaymentTypes())),
            'note'          => 'nullable|string',
            'month'         => 'required|string',
        ]);

        Expense::create($validated);
        return redirect()->route('expenses.index')->with('success', 'Trošak je uspešno dodat.');
    }

    public function edit(Expense $expense)
    {
        $types = $this->getTypes();
        $paymentTypes = $this->getPaymentTypes();
        return view('expenses.edit', compact('expense', 'types', 'paymentTypes'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'description'   => 'nullable|string|max:255',
            'type'          => 'required|in:' . implode(',', array_keys($this->getTypes())),
            'amount'        => 'required|numeric|min:0',
            'payment_type'  => 'required|in:' . implode(',', array_keys($this->getPaymentTypes())),
            'note'          => 'nullable|string',
            'month'         => 'required|string',
        ]);

        $expense->update($validated);
        return redirect()->route('expenses.index')->with('success', 'Trošak je uspešno ažuriran.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Trošak je uspešno obrisan.');
    }

    /**
     * Forma za unos više troškova istog meseca odjednom.
     */
    public function createMonthly()
    {
        $types = $this->getTypes();
        $paymentTypes = $this->getPaymentTypes();
        $defaultMonth = now()->format('Y-m');
        return view('expenses.monthly', compact('types', 'paymentTypes', 'defaultMonth'));
    }

    /**
     * Obrada liste mesečnih troškova.
     */
    public function storeMonthly(Request $request)
    {
        $request->validate([
            'month' => 'required|string',
            'items' => 'required|array',
        ]);

        $month        = $request->month;
        $types        = $this->getTypes();
        $paymentTypes = $this->getPaymentTypes();

        foreach ($request->items as $key => $item) {
            if (!array_key_exists($key, $types)) {
                continue;
            }
            $amount      = isset($item['amount']) ? floatval($item['amount']) : 0;
            $paymentType = $item['payment_type'] ?? null;
            $note        = $item['note'] ?? null;
            $description = $item['description'] ?? null;

            if ($amount > 0 && in_array($paymentType, array_keys($paymentTypes))) {
                Expense::create([
                    'description'   => $description,
                    'type'          => $key,          // npr. Šivenje
                    'amount'        => $amount,
                    'payment_type'  => $paymentType,  // KES ili FIRMA
                    'note'          => $note,
                    'month'         => $month,
                ]);
            }
        }

        return redirect()->route('expenses.index')->with('success', 'Mesečni troškovi su uspešno uneti.');
    }

    /**
     * Lista tipova troškova na srpskom.
     */
    protected function getTypes(): array
    {
        /*
         * List of expense categories in Serbian.  Keys and values are identical
         * so that stored data matches the user-facing label.
         */
        return [
            'Šivenje'     => 'Šivenje',
            'Reklama'     => 'Reklama',
            'Gorivo'      => 'Gorivo',
            'Nabavka'     => 'Nabavka',
            'Plate'       => 'Plate',
            'Greške'      => 'Greške',
            'Porezi'      => 'Porezi',
            'Op Troškovi' => 'Op Troškovi',
        ];
    }

    /**
     * Lista tipova plaćanja.
     */
    protected function getPaymentTypes(): array
    {
        return [
            'KES'   => 'Keš',
            'FIRMA' => 'Firma',
        ];
    }
}