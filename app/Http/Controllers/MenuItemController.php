<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Dish;
use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
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

    public function store(StoreMenuItemRequest $request)
    {
        $this->authorize('create', MenuItem::class);

        MenuItem::create($request->validated());
        return redirect()->route('menu-items.index')->with('success', 'Menu item added successfully.');
    }

    public function edit(MenuItem $menuItem)
    {
        $this->authorize('update', $menuItem);
        $dishes = Dish::all();
        return view('menu-items.edit', compact('menuItem', 'dishes'));
    }

    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem)
    {
        $this->authorize('update', $menuItem);

        $menuItem->update($request->validated());
        return redirect()->route('menu-items.index')->with('success', 'Menu item updated successfully.');
    }

    public function destroy(MenuItem $menuItem)
    {
        $this->authorize('delete', $menuItem);
        $menuItem->delete();
        return redirect()->route('menu-items.index')->with('success', 'Menu item removed successfully.');
    }
}
