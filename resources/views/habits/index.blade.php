<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('習慣管理') }}
            </h2>
            <a href="{{ route('habits.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                新しい習慣を追加
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($habits->count() > 0)
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            @foreach($habits as $habit)
                                <div class="bg-gray-50 rounded-lg p-6 border">
                                    <div class="flex justify-between items-start mb-4">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $habit->name }}</h3>
                                        @if($habit->category)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $habit->category->name }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($habit->description)
                                        <p class="text-gray-600 mb-4">{{ Str::limit($habit->description, 100) }}</p>
                                    @endif
                                    
                                    <div class="text-sm text-gray-500 mb-4">
                                        <p>目標: {{ $habit->target_frequency }}回 / {{ $habit->target_unit === 'daily' ? '日' : ($habit->target_unit === 'weekly' ? '週' : '月') }}</p>
                                        <p class="mt-1">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $habit->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $habit->is_active ? 'アクティブ' : '非アクティブ' }}
                                            </span>
                                        </p>
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        <a href="{{ route('habits.show', $habit) }}" class="text-blue-600 hover:text-blue-900 text-sm">詳細</a>
                                        <a href="{{ route('habits.edit', $habit) }}" class="text-yellow-600 hover:text-yellow-900 text-sm">編集</a>
                                        <form action="{{ route('habits.destroy', $habit) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm" onclick="return confirm('本当に削除しますか？')">削除</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500 text-lg mb-4">まだ習慣が登録されていません。</p>
                            <a href="{{ route('habits.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                最初の習慣を追加する
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
