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
            <!-- カテゴリフィルタ -->
            @if($categories->count() > 0 || $activeHabits->whereNull('category_id')->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <div class="flex items-center space-x-2 overflow-x-auto">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">フィルタ:</span>
                        <a href="{{ route('dashboard') }}" 
                           class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border transition-colors {{ !$categoryFilter || $categoryFilter === 'all' ? 'bg-blue-100 text-blue-800 border-blue-200' : 'bg-gray-100 text-gray-800 border-gray-200 hover:bg-gray-200' }}">
                            すべて ({{ $totalHabits }})
                        </a>
                        @if($activeHabits->whereNull('category_id')->count() > 0)
                            <a href="{{ route('dashboard', ['category' => 'none']) }}" 
                               class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border transition-colors {{ $categoryFilter === 'none' ? 'bg-gray-600 text-white border-gray-600' : 'bg-gray-100 text-gray-800 border-gray-200 hover:bg-gray-200' }}">
                                カテゴリなし ({{ $activeHabits->whereNull('category_id')->count() }})
                            </a>
                        @endif
                        @foreach($categories as $category)
                            @if($category->habits_count > 0)
                                <a href="{{ route('dashboard', ['category' => $category->id]) }}" 
                                   class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border transition-colors text-white {{ $categoryFilter == $category->id ? 'ring-2 ring-offset-2 ring-gray-500' : 'hover:ring-1 hover:ring-offset-1 hover:ring-gray-400' }}"
                                   style="background-color: {{ $category->color }}; border-color: {{ $category->color }};">
                                    {{ $category->name }} ({{ $category->habits_count }})
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- フィルタ説明 -->
            @if($categoryFilter && $categoryFilter !== 'all')
            <div class="bg-white border border-blue-200 rounded-lg p-3 shadow-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm text-gray-900 font-medium">
                        @if($categoryFilter === 'none')
                            カテゴリなしの習慣のみ表示中
                        @else
                            {{ $categories->where('id', $categoryFilter)->first()?->name }}カテゴリの習慣のみ表示中
                        @endif
                        （{{ $activeHabits->count() }}個）
                    </span>
                </div>
            </div>
            @endif

            <!-- 統計カード -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="text-3xl font-bold text-blue-600">{{ $categoryFilter && $categoryFilter !== 'all' ? $activeHabits->count() : $totalHabits }}</div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-600">
                                    {{ $categoryFilter && $categoryFilter !== 'all' ? '表示中習慣数' : '総習慣数' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $categoryFilter && $categoryFilter !== 'all' ? 'フィルタ適用中' : '登録済み' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="text-3xl font-bold text-green-600">{{ $activeHabits->count() }}</div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-600">アクティブ</div>
                                <div class="text-xs text-gray-500">実行中</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="text-3xl font-bold text-purple-600">{{ collect($todayRecords)->filter()->where('completed', true)->count() }}</div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-600">今日の完了</div>
                                <div class="text-xs text-gray-500">{{ $activeHabits->count() }}個中</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="text-3xl font-bold text-orange-600">{{ $activeStreakCount }}</div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-600">アクティブストリーク数</div>
                                <div class="text-xs text-gray-500">継続中の習慣</div>
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
                            <div class="border rounded-lg p-4 {{ isset($todayRecords[$habit->id]) && $todayRecords[$habit->id]->completed ? 'bg-green-50 border-green-200' : 'bg-gray-50' }}">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-semibold text-gray-900">{{ $habit->name }}</h4>
                                    <div class="flex items-center space-x-1">
                                        @if(isset($todayRecords[$habit->id]))
                                            @if($todayRecords[$habit->id]->completed)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    ✓ 完了
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    記録済
                                                </span>
                                            @endif
                                        @endif
                                        @if($habit->category)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $habit->category->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mb-1">{{ $habit->target_frequency }}回 / {{ $habit->target_unit === 'daily' ? '日' : ($habit->target_unit === 'weekly' ? '週' : '月') }}</p>
                                
                                <div class="flex items-center space-x-4 text-xs text-gray-500 mb-3">
                                    @php
                                        $streakUnit = match($habit->target_unit) {
                                            'daily' => '日',
                                            'weekly' => '週',
                                            'monthly' => '月',
                                            default => '日'
                                        };
                                        $primaryRate = $habit->getPrimaryCompletionRate();
                                    @endphp
                                    <span>🔥 {{ $habit->getCurrentStreak() }}{{ $streakUnit }}連続</span>
                                    <span>📊 {{ number_format($primaryRate['rate'], 0) }}% ({{ $primaryRate['label'] }})</span>
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('habits.show', $habit) }}" class="text-blue-600 hover:text-blue-900 text-sm">詳細</a>
                                        <a href="{{ route('habit-records.index', $habit) }}" class="text-purple-600 hover:text-purple-900 text-sm">記録</a>
                                    </div>
                                    
                                    @if(!isset($todayRecords[$habit->id]))
                                        <form method="POST" action="{{ route('habit-records.store', $habit) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="recorded_date" value="{{ date('Y-m-d') }}">
                                            <input type="hidden" name="completed" value="1">
                                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white text-xs font-medium py-1 px-2 rounded">
                                                今日完了
                                            </button>
                                        </form>
                                    @endif
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

            <!-- グラフセクション（テスト用） -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">進捗トレンド</h3>
                    <div class="relative" style="height: 300px;">
                        <canvas id="progressChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Chart.js 実データグラフ
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('progressChart').getContext('2d');
            const chartData = @json($chartData);
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: '完了した習慣数',
                        data: chartData.data,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: Math.max(chartData.totalHabits, 5),
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: '過去7日間の習慣完了数'
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
