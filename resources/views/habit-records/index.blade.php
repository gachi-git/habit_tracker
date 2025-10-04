<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $habit->name }} の記録
            </h2>
            <a href="{{ route('habits.show', $habit) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                習慣に戻る
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- 新しい記録追加フォーム -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">新しい記録を追加</h3>
                    
                    <form method="POST" action="{{ route('habit-records.store', $habit) }}" class="space-y-4">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="recorded_date" :value="__('実施日')" />
                                <x-text-input id="recorded_date" name="recorded_date" type="date" 
                                    class="mt-1 block w-full" value="{{ old('recorded_date', date('Y-m-d')) }}" required />
                                <x-input-error class="mt-2" :messages="$errors->get('recorded_date')" />
                            </div>
                            
                            <div>
                                <x-input-label for="duration_minutes" :value="__('実施時間（分）')" />
                                <x-text-input id="duration_minutes" name="duration_minutes" type="number" 
                                    class="mt-1 block w-full" value="{{ old('duration_minutes') }}" 
                                    min="1" max="1440" placeholder="例: 30" />
                                <x-input-error class="mt-2" :messages="$errors->get('duration_minutes')" />
                            </div>
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="completed" value="1" 
                                    class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" 
                                    {{ old('completed', true) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">完了しました</span>
                            </label>
                        </div>
                        
                        <div>
                            <x-input-label for="note" :value="__('メモ（任意）')" />
                            <textarea id="note" name="note" 
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
                                rows="2" placeholder="今日の習慣について一言...">{{ old('note') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('note')" />
                        </div>
                        
                        <div class="flex items-center justify-end">
                            <x-primary-button>
                                記録を追加
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 記録一覧 -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">記録履歴</h3>
                    
                    @if($records->count() > 0)
                        <div class="space-y-4">
                            @foreach($records as $record)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 {{ $record->completed ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2 mb-2">
                                                <span class="text-lg font-medium">
                                                    {{ $record->recorded_date->format('Y年m月d日') }}
                                                </span>
                                                @if($record->completed)
                                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">
                                                        完了
                                                    </span>
                                                @else
                                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-red-900 dark:text-red-300">
                                                        未完了
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            @if($record->duration_minutes)
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                                    実施時間: {{ $record->duration_minutes }}分
                                                </p>
                                            @endif
                                            
                                            @if($record->note)
                                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                                    {{ $record->note }}
                                                </p>
                                            @endif
                                        </div>
                                        
                                        <form method="POST" action="{{ route('habit-records.destroy', $record) }}" 
                                            onsubmit="return confirm('この記録を削除してもよろしいですか？');">
                                            @csrf
                                            @method('DELETE')
                                            <x-danger-button type="submit" class="text-xs">
                                                削除
                                            </x-danger-button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6">
                            {{ $records->links() }}
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                            まだ記録がありません。上のフォームから最初の記録を追加してみましょう！
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>