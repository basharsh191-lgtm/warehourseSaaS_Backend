<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: dejavusans; font-size: 10pt; direction: rtl; }
        h1 { font-size: 18pt; color: #047857; }
        h2 { font-size: 12pt; margin-top: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #e2e8f0; font-weight: bold; }
        th, td { border: 1px solid #cbd5e1; padding: 7px; text-align: right; }
        .summary td { border: 0; }
        .muted { color: #475569; }
    </style>
</head>
<body>
    @php
        $typeLabels = ['sale' => 'بيع', 'purchase' => 'شراء', 'purchase_return' => 'مرتجع شراء', 'sale_return' => 'مرتجع بيع'];
        $statusLabels = ['confirmed' => 'مؤكدة', 'draft' => 'مسودة', 'cancelled' => 'ملغاة'];
    @endphp
    <h1>فاتورة رقم #{{ $movement->number }}</h1>
    <p class="muted">{{ $typeLabels[$movement->type] ?? $movement->type }} · {{ $movement->date?->format('Y-m-d') ?? '—' }}</p>
    <table>
        <tbody>
            <tr><th>الطرف</th><td>{{ $movement->party?->name ?? 'نقدي مباشر' }}</td><th>الحالة</th><td>{{ $statusLabels[$movement->status] ?? $movement->status }}</td></tr>
            <tr><th>رقم المستند</th><td colspan="3">{{ $movement->document_no ?: '—' }}</td></tr>
        </tbody>
    </table>

    <h2>مواد الفاتورة</h2>
    <table>
        <thead>
            <tr><th>المادة</th><th>الكمية</th><th>سعر الوحدة</th><th>الإجمالي</th></tr>
        </thead>
        <tbody>
            @foreach ($movement->items as $item)
                <tr>
                    <td>{{ $item->product?->name ?? 'مادة محذوفة' }}</td>
                    <td>{{ number_format((float) $item->quantity, 3) }}</td>
                    <td>{{ number_format((float) $item->price_unit, 2) }}</td>
                    <td>{{ number_format((float) $item->total_line, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>ملخص الفاتورة</h2>
    <table class="summary">
        <tbody>
            <tr><td>الإجمالي الفرعي</td><td>{{ number_format($movement->items->sum(fn ($item) => (float) $item->total_line), 2) }}</td></tr>
            <tr><td>الخصم</td><td>{{ number_format((float) $movement->discount, 2) }}</td></tr>
            <tr><th>الصافي المستحق</th><th>{{ number_format((float) $movement->total, 2) }}</th></tr>
        </tbody>
    </table>
</body>
</html>
