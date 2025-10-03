<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('ダッシュボード') }}
            </h2>
            <a href="{{ route('habits.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                新しい習慣を追加
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- 統計カード -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="text-3xl font-bold text-blue-600">{{ $totalHabits }}</div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-600">総習慣数</div>
                                <div class="text-xs text-gray-500">登録済みの習慣</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="text-3xl font-bold text-green-600">{{ $activeHabits->count() }}</div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-600">アクティブな習慣</div>
                                <div class="text-xs text-gray-500">現在取り組み中</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="text-3xl font-bold text-purple-600">0</div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-600">今日の完了</div>
                                <div class="text-xs text-gray-500">今後実装予定</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- アクティブな習慣 -->
            @if($activeHabits->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">アクティブな習慣</h3>
                        <a href="{{ route('habits.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">すべて見る</a>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($activeHabits as $habit)
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-semibold text-gray-900">{{ $habit->name }}</h4>
                                    @if($habit->category)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $habit->category->name }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 mb-2">{{ $habit->target_frequency }}回 / {{ $habit->target_unit === 'daily' ? '日' : ($habit->target_unit === 'weekly' ? '週' : '月') }}</p>
                                <div class="flex space-x-2">
                                    <a href="{{ route('habits.show', $habit) }}" class="text-blue-600 hover:text-blue-900 text-sm">詳細</a>
                                    <button class="text-green-600 hover:text-green-900 text-sm">記録する</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- 最近の習慣 -->
            @if($recentHabits->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">最近作成した習慣</h3>
                    </div>
                    <div class="space-y-3">
                        @foreach($recentHabits as $habit)
                            <div class="flex items-center justify-between p-3 border rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ $habit->name }}</h4>
                                        <p class="text-sm text-gray-600">{{ $habit->created_at->format('Y年n月j日') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if($habit->category)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $habit->category->name }}
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $habit->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $habit->is_active ? 'アクティブ' : '非アクティブ' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- 習慣がない場合 -->
            @if($totalHabits === 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center">
                    <div class="text-gray-500 mb-4">
                        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">習慣を始めましょう</h3>
                    <p class="text-gray-600 mb-4">まだ習慣が登録されていません。最初の習慣を作成して、継続的な成長を始めましょう。</p>
                    <a href="{{ route('habits.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        最初の習慣を作成
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
