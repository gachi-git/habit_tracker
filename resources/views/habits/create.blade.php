<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('新しい習慣を追加') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('habits.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('習慣名')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" :value="__('説明（任意）')" />
                            <textarea id="description" name="description" rows="4" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="category_id" :value="__('カテゴリ（任意）')" />
                            <select id="category_id" name="category_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">カテゴリを選択</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="target_frequency" :value="__('目標頻度')" />
                            <x-text-input id="target_frequency" class="block mt-1 w-full" type="number" name="target_frequency" :value="old('target_frequency', 1)" required min="1" />
                            <x-input-error :messages="$errors->get('target_frequency')" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="target_unit" :value="__('頻度の単位')" />
                            <select id="target_unit" name="target_unit" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                <option value="daily" {{ old('target_unit') == 'daily' ? 'selected' : '' }}>毎日</option>
                                <option value="weekly" {{ old('target_unit', 'weekly') == 'weekly' ? 'selected' : '' }}>毎週</option>
                                <option value="monthly" {{ old('target_unit') == 'monthly' ? 'selected' : '' }}>毎月</option>
                            </select>
                            <x-input-error :messages="$errors->get('target_unit')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('habits.index') }}" class="text-gray-600 hover:text-gray-900">
                                キャンセル
                            </a>
                            <x-primary-button>
                                {{ __('習慣を作成') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
