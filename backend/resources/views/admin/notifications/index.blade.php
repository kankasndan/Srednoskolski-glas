@extends('layouts.master')

@section('title', 'Нотификации')

@section('content')
    <x-admin.page-header title="Нотификации" subtitle="Сите известувања за модерација и мислења." />

    <x-admin.flash />

    @if ($notifications->isEmpty())
        <x-admin.empty title="Нема нотификации." description="Кога ќе има нови пријави, жалби или мислења, ќе се појават тука."
            icon="fa-bell" />
    @else
        <div class="space-y-2">
            @foreach ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = is_null($notification->read_at);
                @endphp
                <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                    @csrf
                    <button type="submit"
                        class="flex w-full flex-col rounded-xl border bg-white px-4 py-3 text-left shadow-sm hover:border-my-purple/40 {{ $isUnread ? 'border-my-purple/25' : 'border-gray-200' }}">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-semibold text-gray-800">{{ $data['title'] ?? 'Нотификација' }}</span>
                            <span class="text-xs text-gray-400">{{ $notification->created_at?->diffForHumans() }}</span>
                        </div>
                        <span class="mt-1 text-sm text-gray-600">{{ $data['message'] ?? '' }}</span>
                    </button>
                </form>
            @endforeach
        </div>

        <x-admin.pagination :paginator="$notifications" />
    @endif
@endsection
