<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Admin Dashboard Report</title>
    <style>
        @page {
            margin: 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 0;
            color: #111827;
        }

        .subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .stats {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .stats td {
            width: 25%;
            border: 1px solid #e5e7eb;
            padding: 10px;
            text-align: center;
        }

        .stats .label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
        }

        .stats .value {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }

        h2 {
            font-size: 14px;
            margin-top: 25px;
            margin-bottom: 8px;
            color: #111827;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table.data th {
            background: #f3f4f6;
            text-align: left;
            padding: 6px 8px;
            font-size: 10px;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }

        table.data td {
            padding: 6px 8px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 11px;
        }

        table.data td.right,
        table.data th.right {
            text-align: right;
        }

        .two-col {
            width: 100%;
        }

        .two-col td {
            width: 50%;
            vertical-align: top;
            padding: 0 8px 0 0;
        }

        .footer {
            margin-top: 30px;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>

<body>

    <h1>Srednoskolski Glas — Admin Dashboard Report</h1>
    <p class="subtitle">Generated on {{ now()->format('d.m.Y H:i') }}</p>

    {{-- Stat Cards --}}
    <table class="stats">
        <tr>
            <td>
                <div class="label">Total Users</div>
                <div class="value">{{ number_format($totalUsers) }}</div>
            </td>
            <td>
                <div class="label">Active Users</div>
                <div class="value">{{ number_format($activeUsers) }}</div>
            </td>
            <td>
                <div class="label">New Registrations (30d)</div>
                <div class="value">{{ number_format($newRegistrations30d) }}</div>
            </td>
        </tr>
    </table>

    {{-- Top Forums --}}
    <h2>Top Forums</h2>
    <table class="data">
        <thead>
            <tr>
                <th>#</th>
                <th>Forum</th>
                <th class="right">Activity Score</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($topForums as $forum)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $forum->name }}</td>
                    <td class="right">{{ $forum->activity_score }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Users by City / School side by side --}}
    <table class="two-col">
        <tr>
            <td>
                <h2>Users by City</h2>
                <table class="data">
                    <thead>
                        <tr>
                            <th>City</th>
                            <th class="right">Users</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usersByCity as $row)
                            <tr>
                                <td>{{ $row->name }}</td>
                                <td class="right">{{ number_format($row->student_data_count) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
            <td>
                <h2>Users by School</h2>
                <table class="data">
                    <thead>
                        <tr>
                            <th>School</th>
                            <th class="right">Users</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usersBySchool as $row)
                            <tr>
                                <td>{{ $row->name }}</td>
                                <td class="right">{{ number_format($row->student_data_count) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer">
        Srednoskolski Glas Admin Panel — Confidential internal report
    </div>

</body>

</html>
