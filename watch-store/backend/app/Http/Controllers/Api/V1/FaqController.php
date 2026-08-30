<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FaqCategory;
use App\Models\FaqItem;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = FaqCategory::query()
            ->orderBy('sort_order')
            ->with(['items' => fn ($q) => $q->published()->orderBy('sort_order')])
            ->get()
            ->filter(fn (FaqCategory $c) => $c->items->isNotEmpty())
            ->values();

        $uncategorized = FaqItem::published()
            ->whereNull('faq_category_id')
            ->orderBy('sort_order')
            ->get();

        $data = $categories->map(fn (FaqCategory $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'items' => $c->items->map(fn (FaqItem $i) => [
                'id' => $i->id,
                'question' => $i->question,
                'answer' => $i->answer,
            ]),
        ])->values();

        if ($uncategorized->isNotEmpty()) {
            $data->push([
                'id' => null,
                'name' => 'General',
                'items' => $uncategorized->map(fn (FaqItem $i) => [
                    'id' => $i->id,
                    'question' => $i->question,
                    'answer' => $i->answer,
                ]),
            ]);
        }

        return response()->json(['success' => true, 'data' => $data]);
    }
}
