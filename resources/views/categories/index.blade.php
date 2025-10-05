<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('カテゴリ管理') }}
            </h2>
            <a href="{{ route('categories.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                新しいカテゴリを追加
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($categories->count() > 0)
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach($categories as $category)
                                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-center mb-3">
                                        <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $category->color }}"></div>
                                        <h3 class="font-semibold text-lg text-gray-900">{{ $category->name }}</h3>
                                    </div>
                                    
                                    <div class="text-sm text-gray-600 mb-4">
                                        {{ $category->habits_count }}個の習慣
                                    </div>
                                    
                                    <div class="flex justify-between items-center">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('categories.show', $category) }}" class="text-blue-600 hover:text-blue-900 text-sm">詳細</a>
                                            <a href="{{ route('categories.edit', $category) }}" class="text-green-600 hover:text-green-900 text-sm">編集</a>
                                        </div>
                                        
                                        @if($category->habits_count === 0)
                                            <form method="POST" action="{{ route('categories.destroy', $category) }}" class="inline" onsubmit="return confirm('このカテゴリを削除してもよろしいですか？')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm">削除</button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 text-sm">習慣あり</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-500 mb-4">
                                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">カテゴリがありません</h3>
                            <p class="text-gray-600 mb-4">習慣を分類するためのカテゴリを作成しましょう。</p>
                            <a href="{{ route('categories.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                最初のカテゴリを作成
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>