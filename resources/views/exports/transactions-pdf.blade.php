<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Αναφορά Συναλλαγών</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            margin: 20px 0;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
        }
        .summary-value.income { color: #22c55e; }
        .summary-value.expense { color: #ef4444; }
        .summary-value.balance { color: #3b82f6; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #333;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .income { color: #22c55e; font-weight: bold; }
        .expense { color: #ef4444; font-weight: bold; }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Αναφορά Συναλλαγών</h1>
        <p>
            Περίοδος: {{ \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') }}
        </p>
        <p>Ημερομηνία Εκτύπωσης: {{ now()->locale('el')->translatedFormat('d M Y, H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Συνολικά Έσοδα</div>
                <div class="summary-value income">€{{ number_format($summary['income'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Συνολικά Έξοδα</div>
                <div class="summary-value expense">€{{ number_format($summary['expense'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Καθαρό Αποτέλεσμα</div>
                <div class="summary-value balance">€{{ number_format($summary['balance'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Περιθώριο Κέρδους</div>
                <div class="summary-value">{{ number_format($summary['profit_margin'], 1) }}%</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Ημερομηνία</th>
                <th>Κατάστημα</th>
                <th>Τύπος</th>
                <th>Κατηγορία</th>
                <th style="text-align: right;">Ποσό</th>
                <th>Περιγραφή</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                    <td>{{ $transaction->store->name }}</td>
                    <td class="{{ $transaction->type }}">
                        {{ $transaction->type === 'income' ? 'Έσοδο' : 'Έξοδο' }}
                    </td>
                    <td>{{ $transaction->category->name }}</td>
                    <td style="text-align: right;" class="{{ $transaction->type }}">
                        €{{ number_format($transaction->total, 2) }}
                    </td>
                    <td>{{ $transaction->description ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Σύνολο συναλλαγών: {{ $transactions->count() }}</p>
        <p>MoneyBoard - Σύστημα Οικονομικής Διαχείρισης</p>
    </div>
</body>
</html>
