<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accounting;
use Illuminate\Http\Request;

class AccountingController extends Controller
{
    public function index(Request $request)
    {
        $query = Accounting::with('user:id,first_name,last_name');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $records = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($records);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'      => 'required|exists:users,id',
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'payment_date' => 'nullable|date',
            'status'       => 'required|in:Pagato,In attesa',
        ]);

        $record = Accounting::create($validated);
        $record->load('user:id,first_name,last_name');

        return response()->json($record, 201);
    }

    public function update(Request $request, $id)
    {
        $record = Accounting::findOrFail($id);

        $validated = $request->validate([
            'user_id'      => 'sometimes|required|exists:users,id',
            'description'  => 'sometimes|required|string|max:255',
            'amount'       => 'sometimes|required|numeric|min:0',
            'payment_date' => 'nullable|date',
            'status'       => 'sometimes|required|in:Pagato,In attesa',
        ]);

        $record->update($validated);
        $record->load('user:id,first_name,last_name');

        return response()->json($record);
    }
}
