<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return response()->json(['data' => $addresses]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $data = $validator->validated();
        $data['ward'] = $data['ward'] ?? '';
        // The first address a customer saves becomes their default.
        $data['is_default'] = ($data['is_default'] ?? false) || ! $user->addresses()->exists();

        $address = DB::transaction(function () use ($user, $data) {
            if ($data['is_default']) {
                $user->addresses()->update(['is_default' => false]);
            }

            return $user->addresses()->create($data);
        });

        return response()->json(['data' => $address], 201);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['ward'] = $data['ward'] ?? '';

        DB::transaction(function () use ($request, $address, $data) {
            if (! empty($data['is_default'])) {
                $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }
            $address->update($data);
        });

        return response()->json(['data' => $address->fresh()]);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        // Keep one default address when possible.
        if ($wasDefault) {
            $request->user()->addresses()->latest()->first()?->update(['is_default' => true]);
        }

        return response()->json(['message' => 'Address deleted']);
    }

    private function rules(): array
    {
        return [
            'label' => 'nullable|string|max:50',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'district' => 'required|string|max:255',
            'municipality' => 'required|string|max:255',
            'ward' => 'nullable|string|max:50',
            'street' => 'required|string|max:255',
            'is_default' => 'boolean',
        ];
    }
}
