<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('カテゴリ作成') }}
            </h2>
            <a href="{{ route('categories.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                戻る
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('categories.store') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('カテゴリ名')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus autocomplete="name" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="color" :value="__('カテゴリカラー')" />
                            <div class="mt-1 flex items-center space-x-3">
                                <input id="color" name="color" type="color" value="{{ old('color', '#3B82F6') }}" class="h-10 w-20 rounded border border-gray-300 cursor-pointer" required />
                                <span class="text-sm text-gray-600">習慣の色分けに使用されます</span>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('color')" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('categories.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-4">
                                キャンセル
                            </a>
                            <x-primary-button>
                                {{ __('作成') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>