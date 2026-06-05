@extends('layouts.app')

@section('title', __('messages.manage_staff_title'))

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-200">
        <div class="p-6 bg-slate-800 text-white flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-white">{{ __('messages.manage_staff_title') }}</h2>
                <p class="text-slate-400 text-sm">{{ __('messages.manage_staff_subtitle') }}</p>
            </div>
            <div class="bg-emerald-500 px-4 py-2 rounded-lg font-bold">
                {{ __('messages.total_staff_count', ['count' => $users->count()]) }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-slate-500 font-semibold uppercase text-xs">{{ __('messages.th_employee') }}</th>
                        <th class="px-6 py-4 text-slate-500 font-semibold uppercase text-xs">{{ __('messages.th_position_dept') }}</th>
                        <th class="px-6 py-4 text-slate-500 font-semibold uppercase text-xs text-center">{{ __('messages.th_role') }}</th>
                        <th class="px-6 py-4 text-slate-500 font-semibold uppercase text-xs text-center">{{ __('messages.th_action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($users as $user)
                    <tr class="hover:bg-gray-50/80 transition group">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 font-bold mr-3 overflow-hidden">
                                    @if($user->profile_image)
                                        <img src="{{ asset('storage/' . $user->profile_image) }}" class="w-full h-full object-cover">
                                    @else
                                        {{ mb_substr($user->name, 0, 1) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800">{{ $user->name }} {{ $user->last_name }}</div>
                                    <div class="text-xs text-gray-400">ID: {{ $user->username }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-slate-700 font-medium">{{ $user->position }}</div>
                            <div class="text-xs text-slate-400">{{ $user->department ?? '-' }} ({{ $user->branch ?? __('messages.default_head_office') }})</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $user->role == 'admin' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center space-x-3">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete_staff') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection