<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use Illuminate\Http\Request;

class DishController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Dish::class);
        $dishes = Dish::latest()->paginate(20);
        return view('dishes.index', compact('dishes'));
    }

    public function create()
    {
        $this->authorize('create', Dish::class);
        return view('dishes.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Dish::class);
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'category' => 'required|in:starter,main,dessert,drink,side',
        ]);

        Dish::create($validated);
        return redirect()->route('dishes.index')->with('success', 'Dish created successfully.');
    }

    public function edit(Dish $dish)
    {
        $this->authorize('update', $dish);
        return view('dishes.edit', compact('dish'));
    }

    public function update(Request $request, Dish $dish)
    {
        $this->authorize('update', $dish);
        $validated = $request->validate([
            'name' => 'string',
            'description' => 'nullable|string',
            'category' => 'in:starter,main,dessert,drink,side',
        ]);

        $dish->update($validated);
        return redirect()->route('dishes.index')->with('success', 'Dish updated successfully.');
    }

    public function destroy(Dish $dish)
    {
        $this->authorize('delete', $dish);
        $dish->delete();
        return redirect()->route('dishes.index')->with('success', 'Dish deleted successfully.');
    }
}
