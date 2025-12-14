<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Получение информации о текущем пользователе
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Обновление профиля
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:8|confirmed',
            'password_confirmation' => 'required_with:password|string',
        ]);

        $updateData = [];

        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }

        if ($request->has('phone')) {
            $updateData['phone'] = $request->phone;
        }

        if ($request->has('email')) {
            $updateData['email'] = $request->email;
        }

        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Получение всех компаний
     */
    public function companies(Request $request)
    {
        $user = $request->user();
        
        $companies = $user->companies()->get();

        return response()->json([
            'companies' => $companies,
        ]);
    }

    /**
     * Получение всех опций
     *
     * - Получаем все опции из таблицы options
     * - Получаем значения опций пользователя
     * - Объединяем все опции с значениями пользователя
     */
    public function options(Request $request)
    {
        $user = $request->user();
        
        $allOptions = Option::all();
        
        $userOptions = $user->options()->get()->keyBy('id');
        
        $options = $allOptions->map(function ($option) use ($userOptions, $user) {
            $userOption = $userOptions->get($option->id);
            
            return [
                'id' => $option->id,
                'key' => $option->key,
                'name' => $option->name,
                'description' => $option->description,
                'value' => $userOption ? (bool) $userOption->pivot->value : false,
            ];
        });

        return response()->json([
            'options' => $options,
        ]);
    }

    /**
     * Обновление опции
     *
     * - Находим опцию по ID или ключу
     * - Обновляем или создаем связь
     * - Получаем обновленную опцию
     */
    public function updateOption(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'option_id' => 'required_without:key|integer|exists:options,id',
            'key' => 'required_without:option_id|string|exists:options,key',
            'value' => 'required|boolean',
        ]);

        if ($request->has('option_id')) {
            $option = Option::findOrFail($request->option_id);
        } else {
            $option = Option::where('key', $request->key)->firstOrFail();
        }

        $user->options()->syncWithoutDetaching([
            $option->id => ['value' => $request->value ? 1 : 0]
        ]);

        $updatedOption = $user->options()->where('options.id', $option->id)->first();

        return response()->json([
            'message' => 'Option updated successfully',
            'option' => $updatedOption,
        ]);
    }
}

