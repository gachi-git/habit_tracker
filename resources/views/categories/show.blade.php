<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $category->color }}"></div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $category->name }}
                </h2>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('categories.edit', $category) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    編集
                </a>
                <a href="{{ route('categories.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    戻る
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- カテゴリ情報 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ $habits->count() }}</div>
                            <div class="text-sm font-medium text-gray-600">習慣数</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">{{ $habits->where('is_active', true)->count() }}</div>
                            <div class="text-sm font-medium text-gray-600">アクティブ</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600">{{ $habits->sum('records_count') }}</div>
                            <div class="text-sm font-medium text-gray-600">総記録数</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- このカテゴリの習慣一覧 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">このカテゴリの習慣</h3>
                        <a href="{{ route('habits.create', ['category' => $category->id]) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            習慣を追加
                        </a>
                    </div>
                    
                    @if($habits->count() > 0)
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            @foreach($habits as $habit)
                                <div class="border rounded-lg p-4 {{ $habit->is_active ? 'bg-white' : 'bg-gray-50' }}">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-semibold text-gray-900">{{ $habit->name }}</h4>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $habit->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $habit->is_active ? 'アクティブ' : '非アクティブ' }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-600 mb-2">{{ $habit->target_frequency }}回 / {{ $habit->target_unit === 'daily' ? '日' : ($habit->target_unit === 'weekly' ? '週' : '月') }}</p>
                                    
                                    <div class="text-xs text-gray-500 mb-3">
                                        {{ $habit->records_count }}回記録済み
                                    </div>
                                    
                                    <div class="flex justify-between items-center">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('habits.show', $habit) }}" class="text-blue-600 hover:text-blue-900 text-sm">詳細</a>
                                            <a href="{{ route('habit-records.index', $habit) }}" class="text-purple-600 hover:text-purple-900 text-sm">記録</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-500 mb-4">
                                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">このカテゴリに習慣がありません</h3>
                            <p class="text-gray-600 mb-4">このカテゴリの最初の習慣を作成しましょう。</p>
                            <a href="{{ route('habits.create', ['category' => $category->id]) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                習慣を作成
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>