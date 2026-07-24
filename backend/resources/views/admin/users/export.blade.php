<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .section { margin-top: 20px; }
        .section-title { font-size: 13px; font-weight: bold; text-transform: uppercase; color: #666; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 4px 0; vertical-align: top; }
        .label { color: #888; width: 160px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; }
        .badge-red { background: #fee2e2; color: #b91c1c; }
        .badge-green { background: #dcfce7; color: #15803d; }
    </style>
</head>
<body>
    <h1>{{ $user->username ?? 'No username' }}</h1>
    <p>{{ $user->email }}</p>

    <div class="section">
        <div class="section-title">Account Details</div>
        <table>
            <tr><td class="label">Sign-in method</td><td>{{ $user->provider ?? '—' }}</td></tr>
            <tr><td class="label">Joined</td><td>{{ $user->created_at?->format('M d, Y') }}</td></tr>
            <tr><td class="label">Onboarding completed</td><td>{{ $user->onboarding_completed_at ? 'Yes' : 'No' }}</td></tr>
        </table>
    </div>

    @if ($user->studentData)
        <div class="section">
            <div class="section-title">Student Info</div>
            <table>
                <tr><td class="label">School</td><td>{{ $user->studentData->school->name ?? '—' }}</td></tr>
                <tr><td class="label">City</td><td>{{ $user->studentData->school->city->name ?? '—' }}</td></tr>
                <tr><td class="label">Vocation</td><td>{{ $user->studentData->vocation->name ?? '—' }}</td></tr>
                <tr><td class="label">Grade</td><td>{{ $user->studentData->grade }}</td></tr>
            </table>
        </div>
    @endif

    <div class="section">
        <div class="section-title">Feed Topics</div>
        <p>
            @forelse ($user->topics as $topic)
                {{ $topic->name }}{{ ! $loop->last ? ', ' : '' }}
            @empty
                No topics selected
            @endforelse
        </p>
    </div>

    <div class="section">
        <div class="section-title">Sanction History</div>
        @forelse ($user->sanctions as $sanction)
            <table>
                <tr>
                    <td class="label">{{ ucfirst(str_replace('_', ' ', $sanction->type)) }}</td>
                    <td>
                        <span class="badge {{ $sanction->expires_at === null || $sanction->expires_at->isFuture() ? 'badge-red' : 'badge-green' }}">
                            {{ $sanction->expires_at === null || $sanction->expires_at->isFuture() ? 'Active' : 'Inactive' }}
                        </span>
                        — {{ $sanction->reason }}
                    </td>
                </tr>
            </table>
        @empty
            <p>No sanctions on record.</p>
        @endforelse
    </div>
</body>
</html>