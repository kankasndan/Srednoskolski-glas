@extends('layouts.master')

@section('title', 'Жалби')

@section('content')
    <x-admin.page-header title="Жалби на забрани" subtitle="Прегледај и реши жалби поднесени од банирани членови.">
        <span class="inline-flex items-center rounded-full bg-my-purple/10 px-3 py-1 text-sm font-medium text-my-purple">
            {{ $tab === 'history' ? 'Решени: '.$resolvedAppeals->total() : 'Во тек: '.$appeals->total() }}
        </span>
    </x-admin.page-header>

    <div class="mb-6 flex gap-1 border-b border-gray-200">
        <a href="{{ route('appeal.index', ['tab' => 'queue']) }}"
            class="px-4 py-2.5 text-sm font-medium border-b-2 {{ $tab === 'queue' ? 'border-my-purple text-my-purple' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Жалби
        </a>
        <a href="{{ route('appeal.index', ['tab' => 'history']) }}"
            class="px-4 py-2.5 text-sm font-medium border-b-2 {{ $tab === 'history' ? 'border-my-purple text-my-purple' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Историја
        </a>
    </div>

    <x-admin.flash />

    @if ($tab === 'queue')
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-xs tracking-wide text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-3 text-left">Член</th>
                        <th class="px-6 py-3 text-left">Причина за забрана</th>
                        <th class="px-6 py-3 text-left">Тип на забрана</th>
                        <th class="px-6 py-3 text-left">Поднесена</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($appeals as $appeal)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('appeal.show', $appeal) }}" class="flex items-center gap-3">
                                    <x-admin.avatar :user="$appeal->user" size="sm" />
                                    <span class="font-medium text-gray-800 hover:text-my-purple">{{ $appeal->user->username }}</span>
                                </a>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $appeal->sanction->reason }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium {{ match ($appeal->sanction->type) {
                                        'warning' => 'bg-my-yellow/30 text-gray-800',
                                        'permanent_ban' => 'bg-red-100 text-red-700',
                                        '7-day' => 'bg-green-100 text-green-600',
                                        default => 'bg-gray-100 text-gray-600',
                                    } }}">
                                    {{ match ($appeal->sanction->type) {
                                        'warning' => 'Предупредување',
                                        'permanent_ban' => 'Трајна забрана',
                                        '7-day' => '7-дневна забрана',
                                        'custom' => 'Прилагодена',
                                        default => $appeal->sanction->type,
                                    } }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $appeal->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-400">Нема совпаѓачки жалби.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.pagination :paginator="$appeals" />
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-xs tracking-wide text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-3 text-left">Член</th>
                        <th class="px-6 py-3 text-left">Причина за забрана</th>
                        <th class="px-6 py-3 text-left">Одлука</th>
                        <th class="px-6 py-3 text-left">Решено од</th>
                        <th class="px-6 py-3 text-left">Решено на</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($resolvedAppeals as $resolvedAppeal)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('appeal.show', $resolvedAppeal) }}" class="flex items-center gap-3">
                                    <x-admin.avatar :user="$resolvedAppeal->user" size="sm" />
                                    <span class="font-medium text-gray-800 hover:text-my-purple">{{ $resolvedAppeal->user->username ?? 'Непознато' }}</span>
                                </a>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $resolvedAppeal->sanction->reason ?? '—' }}</td>
                            <td class="px-6 py-4">
                                @if ($resolvedAppeal->status === 'accepted')
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Прифатена
                                        и отстранета санкција</span>
                                @else
                                    <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Одбиена</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $resolvedAppeal->admin?->username ?? 'Систем' }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $resolvedAppeal->resolved_at?->format('d.m.Y') ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">Нема решени жалби.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.pagination :paginator="$resolvedAppeals" />
    @endif
@endsection
