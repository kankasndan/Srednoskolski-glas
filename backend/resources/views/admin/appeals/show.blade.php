@extends('layouts.master')

@section('title', 'Appeal Details')

@section('content')
    @php
        $sanction = $appeal->sanction;
        $report = $sanction?->report;
        $reportable = $report?->reportable;
        $appealUser = $appeal->user;
        $school = $appealUser?->studentData?->school;
        $statusBadgeClasses = match ($appeal->status) {
            'pending' => 'bg-yellow-100 text-yellow-700',
            'rejected' => 'bg-red-100 text-red-700',
            'accepted' => 'bg-green-100 text-green-600',
            default => 'bg-gray-100 text-gray-600',
        };
        $reportableType = $reportable ? class_basename($reportable) : null;
    @endphp

    <div class="p-6">

        <div class="max-w-4xl mx-auto">

            <!-- Back link -->
            <a href="{{ route('appeal.index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-6">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to appeals
            </a>

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <img src="{{ $appealUser?->imageUrl }}" alt="{{ $appealUser?->username }}"
                        class="w-14 h-14 rounded-full object-cover bg-gray-100">
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">{{ $appealUser?->username }}</h1>
                        <p class="text-sm text-gray-500">
                            School: {{ $school?->name ?? 'No school linked' }}
                        </p>
                    </div>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded-full capitalize {{ $statusBadgeClasses }}">
                    {{ $appeal->status }} 
                </span>
            </div>

            <!-- Ban Info Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-4">Sanction Details</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Ban Type</p>
                        <p class="text-sm font-medium text-gray-800">{{ $sanction?->type ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Reason</p>
                        <p class="text-sm font-medium text-gray-800">{{ $sanction?->reason ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Issued On</p>
                        <p class="text-sm font-medium text-gray-800">
                            {{ $sanction?->created_at?->format('M d, Y · H:i') ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Issued By</p>
                        <p class="text-sm font-medium text-gray-800">
                            {{ $sanction?->issuedBy?->username ?? 'Unknown' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Expires</p>
                        <p class="text-sm font-medium text-gray-800">
                            {{ $sanction?->expires_at ? ($sanction->expires_at->isPast() ? 'Expired' : $sanction->expires_at->diffForHumans()) : 'Permanent' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Resolved On</p>
                        <p class="text-sm font-medium text-gray-800">
                            {{ $appeal->resolved_at?->format('M d, Y · H:i') ?? 'Not resolved yet' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Appeal Message -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-3">Member's Appeal</h2>
                <div class="flex items-start gap-3">
                    <img src="{{ $appealUser?->imageUrl }}" alt="{{ $appealUser?->username }}"
                        class="size-10 rounded-full object-cover bg-gray-100">
                    <div class="bg-gray-50 rounded-lg p-4 flex-1">
                        <p class="text-sm text-gray-700">
                            {{ $appeal->explanation }}
                        </p>
                        <p class="text-xs text-gray-400 mt-2">Submitted {{ $appeal->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>

            <!-- Reported Content Preview -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-3">Reported Content</h2>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                    @if ($report)
                        <p class="text-xs text-gray-500 mb-1">
                            {{ $reportableType ? $reportableType.' on' : 'Reported item' }}
                            @if ($reportableType === 'Comment')
                                on thread "{{ $reportable?->thread?->title ?? 'Unknown thread' }}"
                            @elseif ($reportableType === 'Thread')
                                "{{ $reportable?->title ?? 'Unknown thread' }}"
                            @elseif ($reportableType === 'User')
                                "{{ $reportable?->username ?? 'Unknown user' }}"
                            @endif
                        </p>
                        <p class="text-sm text-gray-700">
                            {{ $reportableType === 'Comment' ? $reportable?->content : ($reportableType === 'Thread' ? $reportable?->description : $report->reason) }}
                        </p>
                    @else
                        <p class="text-sm text-gray-500">No linked report was found for this sanction.</p>
                    @endif
                </div>
            </div>

            <!-- Decision Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-3">Resolve Appeal</h2>

                <div class="mb-4">
                    <label class="text-xs font-medium text-gray-500 uppercase mb-1 block">Moderator Note (optional)</label>
                    <textarea rows="3" placeholder="Add an internal note about your decision..."
                        class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <form action="{{ route('appeal.reject', ['appeal' => $appeal->id]) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="px-5 py-2 rounded-lg text-sm font-medium bg-red-600 text-white hover:bg-red-700">
                            Reject Appeal
                        </button>
                    </form>
                    <form action="{{ route('appeal.accept', ['appeal' => $appeal->id]) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="px-5 py-2 rounded-lg text-sm font-medium bg-green-600 text-white hover:bg-green-700">
                            Accept &amp; Unban
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>
@endsection
