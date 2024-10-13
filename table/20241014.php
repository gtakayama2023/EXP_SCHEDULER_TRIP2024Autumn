<?php

// JSONファイルを読み込む関数
function load_settings($file_path) {
    $json_data = file_get_contents($file_path);
    return json_decode($json_data, true);
}

// 設定項目をHTMLテーブルに変換する関数
function settings_to_table($settings) {
    $table = '<table class="modern-table">';
    $table .= '<tr><th>Setting Label</th><th>Item Label</th><th>Start Time</th><th>Duration (hours)</th><th>Locked</th></tr>';

    $last_label = '';
    $is_alternate_color = false;  // 交互に色を変えるためのフラグ

    foreach ($settings as $setting_key => $setting_value) {
        $setting_label = $setting_value['label'];
        if ($setting_label !== $last_label) {
            $is_alternate_color = !$is_alternate_color;  // ラベルが変わったらフラグを切り替え
        }
        $last_label = $setting_label;

        // 色の選択：交互に薄赤(#ffe6e6)か白
        $row_color = $is_alternate_color ? 'style="background-color: #e6ffe6;"' : 'style="background-color: #ffffff;"';

        foreach ($setting_value['items'] as $item) {
            $table .= '<tr ' . $row_color . '>';
            $table .= '<td>' . htmlspecialchars($setting_label) . '</td>';
            $table .= '<td>' . htmlspecialchars($item['label']) . '</td>';
            $table .= '<td>' . htmlspecialchars($item['startTime']) . '</td>';
            $table .= '<td>' . htmlspecialchars($item['duration']) . '</td>';
            $table .= '<td>' . ($item['locked'] ? 'Yes' : 'No') . '</td>';
            $table .= '</tr>';
        }
    }

    $table .= '</table>';
    return $table;
}

// 使用例
$json_file_path = 'settings.json';  // JSONファイルのパス
$settings = load_settings($json_file_path);
echo settings_to_table($settings);

?>

<style>
    /* 全体レイアウト */
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f4f9;
        color: #333;
        margin: 0;
        padding: 20px;
        display: flex;
        justify-content: center;
        align-items: flex-start;  /* テーブルが上に位置するように調整 */
        min-height: 100vh;
    }

    /* テーブルデザイン */
    table.modern-table {
        width: 100%;  /* テーブルを画面幅いっぱいに */
        max-width: 2400px;  /* 最大幅を制限 */
        margin: 25px auto;
        border-collapse: collapse;
        font-size: 16px;
        text-align: left;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        background-color: #fff;
    }

    table.modern-table th, table.modern-table td {
        padding: 12px 20px;  /* パディングを広めに */
        border-bottom: 1px solid #ddd;
    }

    table.modern-table th {
        background-color: #009879;
        color: #ffffff;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    table.modern-table tr:hover {
        background-color: #f1f1f1;
    }

    table.modern-table td {
        color: #555;
    }

    /* レスポンシブデザイン */
    @media (max-width: 768px) {
        table.modern-table {
            font-size: 14px;  /* 小さい画面ではフォントサイズを調整 */
        }

        table.modern-table th, table.modern-table td {
            padding: 10px 15px;  /* パディングを少し減らす */
        }
    }

    @media (max-width: 480px) {
        table.modern-table {
            font-size: 12px;  /* より小さい画面用にフォントサイズをさらに調整 */
        }

        table.modern-table th, table.modern-table td {
            padding: 8px 10px;
        }
    }
</style>

