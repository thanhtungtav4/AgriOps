<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Truy xuất {{ $trace['packing_lot_code'] }}</title>
    <style>
        :root {
            color-scheme: light;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #17201b;
            background: #f6f8f4;
        }

        body {
            margin: 0;
        }

        main {
            max-width: 760px;
            margin: 0 auto;
            padding: 28px 18px 48px;
        }

        header {
            padding: 28px 0 20px;
            border-bottom: 1px solid #dfe7dc;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 30px;
            line-height: 1.15;
            letter-spacing: 0;
        }

        h2 {
            margin: 28px 0 12px;
            font-size: 18px;
            letter-spacing: 0;
        }

        .status {
            display: inline-flex;
            margin-top: 10px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #d8efe0;
            color: #14532d;
            font-size: 14px;
            font-weight: 700;
        }

        dl {
            display: grid;
            grid-template-columns: minmax(120px, 180px) 1fr;
            gap: 10px 16px;
            margin: 18px 0 0;
        }

        dt {
            color: #5a6b60;
        }

        dd {
            margin: 0;
            font-weight: 650;
        }

        details {
            margin-top: 12px;
            padding: 16px;
            border: 1px solid #dfe7dc;
            border-radius: 8px;
            background: #fff;
        }

        summary {
            cursor: pointer;
            font-weight: 750;
        }

        .muted {
            color: #5a6b60;
        }

        @media (max-width: 560px) {
            h1 {
                font-size: 24px;
            }

            dl {
                grid-template-columns: 1fr;
                gap: 4px;
            }
        }
    </style>
</head>
<body>
<main>
    <header>
        <p class="muted">AgriOps Traceability</p>
        <h1>{{ implode(', ', $trace['crop_names']) ?: 'Nông sản' }}</h1>
        <div class="status">
            {{ $trace['safety_status'] === 'safety_controls_compliant' ? 'Tuân thủ kiểm soát an toàn' : 'Đang kiểm tra cách ly' }}
        </div>
    </header>

    <section>
        <h2>Lô đóng gói</h2>
        <dl>
            <dt>Mã lô</dt>
            <dd>{{ $trace['packing_lot_code'] }}</dd>
            <dt>Ngày đóng gói</dt>
            <dd>{{ $trace['packed_at'] }}</dd>
            <dt>Sản lượng</dt>
            <dd>{{ $trace['total_output_quantity'] }} {{ $trace['unit'] }}</dd>
            <dt>Trạng thái</dt>
            <dd>{{ $trace['status'] }}</dd>
        </dl>
    </section>

    <section>
        <h2>Nguồn sản xuất</h2>
        @foreach ($trace['sources'] as $source)
            <details open>
                <summary>{{ $source['farm_name'] }} · {{ $source['crop_name'] }}</summary>
                <dl>
                    <dt>Mã farm</dt>
                    <dd>{{ $source['farm_code'] }}</dd>
                    <dt>Mã lứa</dt>
                    <dd>{{ $source['planting_batch_code'] ?: 'Chưa cập nhật' }}</dd>
                    <dt>Ngày trồng</dt>
                    <dd>{{ $source['planted_at'] ?: 'Chưa cập nhật' }}</dd>
                    <dt>Ngày thu hoạch</dt>
                    <dd>{{ $source['harvest_date'] }}</dd>
                    <dt>Sản lượng nguồn</dt>
                    <dd>{{ $source['quantity'] }} {{ $source['unit'] }}</dd>
                    <dt>Sơ chế</dt>
                    <dd>{{ $source['processing_steps_count'] }} bước ghi nhận</dd>
                </dl>
            </details>
        @endforeach
    </section>

    <section>
        <h2>Chứng nhận</h2>
        @forelse ($trace['farms'] as $farm)
            <details>
                <summary>{{ $farm['name'] }} ({{ $farm['code'] }})</summary>
                <p>{{ $farm['certification'] ? json_encode($farm['certification'], JSON_UNESCAPED_UNICODE) : 'Chưa cập nhật chứng nhận.' }}</p>
            </details>
        @empty
            <p class="muted">Chưa cập nhật chứng nhận.</p>
        @endforelse
    </section>
</main>
</body>
</html>
