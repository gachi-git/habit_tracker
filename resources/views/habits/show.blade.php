<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $habit->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('habit-records.index', $habit) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    記録を見る
                </a>
                <a href="{{ route('habits.edit', $habit) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    編集
                </a>
                <a href="{{ route('habits.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    戻る
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- 習慣情報 -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">習慣情報</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-600">習慣名</label>
                                    <p class="text-gray-900">{{ $habit->name }}</p>
                                </div>
                                
                                @if($habit->description)
                                <div>
                                    <label class="text-sm font-medium text-gray-600">説明</label>
                                    <p class="text-gray-900">{{ $habit->description }}</p>
                                </div>
                                @endif
                                
                                @if($habit->category)
                                <div>
                                    <label class="text-sm font-medium text-gray-600">カテゴリ</label>
                                    <div class="flex items-center space-x-2">
                                        @if($habit->category->color)
                                            <span class="inline-block w-4 h-4 rounded-full" style="background-color: {{ $habit->category->color }};"></span>
                                        @endif
                                        <span class="text-gray-900">{{ $habit->category->name }}</span>
                                    </div>
                                </div>
                                @endif
                                
                                <div>
                                    <label class="text-sm font-medium text-gray-600">目標頻度</label>
                                    <p class="text-gray-900">{{ $habit->target_frequency }}回 / {{ $habit->target_unit === 'daily' ? '日' : ($habit->target_unit === 'weekly' ? '週' : '月') }}</p>
                                </div>
                                
                                <div>
                                    <label class="text-sm font-medium text-gray-600">状態</label>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $habit->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $habit->is_active ? 'アクティブ' : '非アクティブ' }}
                                    </span>
                                </div>
                                
                                <div>
                                    <label class="text-sm font-medium text-gray-600">作成日</label>
                                    <p class="text-gray-900">{{ $habit->created_at->format('Y年n月j日') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- 統計情報 -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">統計情報</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-center">
                                    <p class="text-gray-600">統計機能は今後実装予定です</p>
                                    <div class="mt-4 space-y-2 text-sm">
                                        <p>・継続日数</p>
                                        <p>・達成率</p>
                                        <p>・最近の記録</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- アクション -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <form action="{{ route('habits.destroy', $habit) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('本当に削除しますか？この操作は取り消せません。')">
                                    習慣を削除
                                </button>
                            </form>
                            
                            <div class="text-sm text-gray-600">
                                最終更新: {{ $habit->updated_at->format('Y年n月j日 H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
