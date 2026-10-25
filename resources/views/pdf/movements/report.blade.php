<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: dejavusans; font-size: 8pt; direction: rtl; }
        h1 { font-size: 16pt; color: #047857; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #e2e8f0; font-weight: bold; }
        th, td { border: 1px solid #cbd5e1; padding: 5px; text-align: right; }
        .muted { color: #475569; }
    </style>
</head>
<body>
    @php
        $typeLabels = ['sale' => 'بيع', 'purchase' => 'شراء', 'purchase_return' => 'مرتجع شراء', 'sale_return' => 'مرتجع بيع'];
        $statusLabels = ['confirmed' => 'مؤكدة', 'draft' => 'مسودة', 'cancelled' => 'ملغاة'];
    @endphp
    <h1>تقرير الفواتير والحركات</h1>
    <p class="muted">عدد الحركات: {{ $movements->count() }} · تاريخ التقرير: {{ now()->format('Y-m-d') }}</p>
    <table>
        <thead>
            <tr>
                <th>رقم الحركة</th>
                <th>رقم المستند</th>
                <th>النوع</th>
                <th>التاريخ</th>
                <th>الطرف</th>
                <th>الإجمالي الصافي</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movements as $movement)
                <tr>
                    <td>#{{ $movement->number }}</td>
                    <td>{{ $movement->document_no ?: '—' }}</td>
                    <td>{{ $typeLabels[$movement->type] ?? $movement->type }}</td>
                    <td>{{ $movement->date?->format('Y-m-d') ?? '—' }}</td>
                    <td>{{ $movement->party?->name ?? 'نقدي مباشر' }}</td>
                    <td>{{ number_format((float) $movement->total, 2) }} {{ $movement->currency ?: $currency }}</td>
                    <td>{{ $statusLabels[$movement->status] ?? $movement->status }}</td>
                </tr>
            @empty
                <tr><td colspan="7">لا توجد فواتير مطابقة للفلاتر.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
