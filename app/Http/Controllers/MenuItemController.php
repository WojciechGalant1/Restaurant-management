<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Dish;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', MenuItem::class);
        $menuItems = MenuItem::with('dish')->get();
        return view('menu-items.index', compact('menuItems'));
    }

    public function create()
    {
        $this->authorize('create', MenuItem::class);
        $dishes = Dish::all();
        return view('menu-items.create', compact('dishes'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', MenuItem::class);
        $validated = $request->validate([
            'dish_id' => 'required|exists:dishes,id',
            'price' => 'required|numeric',
            'is_available' => 'boolean',
        ]);

        MenuItem::create($validated);
        return redirect()->route('menu-items.index')->with('success', 'Menu item added successfully.');
    }

    public function edit(MenuItem $menuItem)
    {
        $this->authorize('update', $menuItem);
        $dishes = Dish::all();
        return view('menu-items.edit', compact('menuItem', 'dishes'));
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $this->authorize('update', $menuItem);
        $validated = $request->validate([
            'price' => 'numeric',
            'is_available' => 'boolean',
        ]);

        $menuItem->update($validated);
        return redirect()->route('menu-items.index')->with('success', 'Menu item updated successfully.');
    }

    public function destroy(MenuItem $menuItem)
    {
        $this->authorize('delete', $menuItem);
        $menuItem->delete();
        return redirect()->route('menu-items.index')->with('success', 'Menu item removed successfully.');
    }
}
