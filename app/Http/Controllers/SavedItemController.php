<?php

namespace App\Http\Controllers;

use App\Models\SavedItem;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SavedItemController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'saveable_type' => 'required|string',
            'saveable_id' => 'required|integer',
            'options' => 'nullable|array',
        ]);

        $morphType = Relation::getMorphedModel($request->saveable_type)
            ? $request->saveable_type
            : $request->saveable_type;

        SavedItem::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'saveable_type' => $morphType,
                'saveable_id' => $request->saveable_id,
            ],
            ['options' => $request->options],
        );

        return back()->with('success', 'Item saved for later.');
    }

    public function destroy(SavedItem $savedItem): RedirectResponse
    {
        $this->authorize('delete', $savedItem);
        $savedItem->delete();

        return back()->with('success', 'Item removed from saved list.');
    }
}
