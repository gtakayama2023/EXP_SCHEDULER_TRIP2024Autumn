<?php
session_start();

// 設定ファイルのパス
$settingsFile = 'settings.json';

// 初期設定データ
$initialSettingsData = [
    'setting0' => [
        'label' => 'BigRIPS tuning',
        'items' => [
            ['id' => 'setting0_1', 'label' => 'Setting0 - 1', 'duration' => 1, 'locked' => true],
            ['id' => 'setting0_2', 'label' => 'Setting0 - 2', 'duration' => 2, 'locked' => false],
            ['id' => 'setting0_3', 'label' => 'Setting0 - 3', 'duration' => 1, 'locked' => false]
        ]
    ],
    'setting1' => [
        'label' => 'フォルダ1のラベル',
        'items' => [
            ['id' => 'setting1_1', 'label' => 'Setting1 - 1', 'duration' => 2, 'locked' => false],
            ['id' => 'setting1_2', 'label' => 'Setting1 - 2', 'duration' => 3, 'locked' => false],
            ['id' => 'setting1_3', 'label' => 'Setting1 - 3', 'duration' => 1, 'locked' => false]
        ]
    ],
    'setting2' => [
        'label' => 'フォルダ2のラベル',
        'items' => [
            ['id' => 'setting2_1', 'label' => 'Setting2 - 1', 'duration' => 2, 'locked' => false],
            ['id' => 'setting2_2', 'label' => 'Setting2 - 2', 'duration' => 3, 'locked' => false],
            ['id' => 'setting2_3', 'label' => 'Setting2 - 3', 'duration' => 1, 'locked' => false]
        ]
    ]
];

// 保存されたデータを取得
//$settingsData = $initialSettingsData;
//if (file_exists($settingsFile)) {
//    $fileContents = file_get_contents($settingsFile);
//    if (!empty($fileContents)) {
//        $savedSettings = json_decode($fileContents, true);
//        if (json_last_error() === JSON_ERROR_NONE && is_array($savedSettings)) {
//            foreach ($savedSettings as $folderKey => $folderData) {
//                if (isset($folderData['items']) && is_array($folderData['items'])) {
//                    foreach ($folderData['items'] as $item) {
//                        if (isset($item['id'])) {
//                            $folder = explode('_', $item['id'])[0];
//                            $itemIndex = array_search($item['id'], array_column($settingsData[$folder]['items'], 'id'));
//                            if ($itemIndex !== false) {
//                                $settingsData[$folder]['items'][$itemIndex] = array_merge(
//                                    $settingsData[$folder]['items'][$itemIndex],
//                                    $item
//                                );
//                            }
//                        }
//                    }
//                }
//            }
//        } else {
//            error_log("Invalid JSON in settings file");
//        }
//    } else {
//        error_log("Empty settings file");
//    }
//} else {
//    error_log("Settings file not found");
//}

// 設定データの初期化
$settingsData = [];

// 設定ファイルの存在確認
if (file_exists($settingsFile)) {
    $fileContents = file_get_contents($settingsFile);
    if (!empty($fileContents)) {
        $savedSettings = json_decode($fileContents, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($savedSettings)) {
            // ファイルから設定データを読み取る
            $settingsData = $savedSettings; // ここで初期設定データを上書きする
        } else {
            error_log("Invalid JSON in settings file");
        }
    } else {
        error_log("Empty settings file");
    }
} else {
    error_log("Settings file not found");
}

// POSTリクエストがあった場合、設定を保存
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() === JSON_ERROR_NONE) {
        file_put_contents($settingsFile, json_encode($data, JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'success', 'message' => '設定が保存されました。']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '無効なJSONデータです。']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TRIP-S3CAN 2024秋 測定メニュー</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4; /* 背景色を少し薄く */
            margin: 0;
            padding: 20px;
            color: #444; /* テキストカラーをやや濃く */
        }
        
        h1 {
            text-align: center; /* 中央揃え */
            margin-bottom: 20px; /* 下の余白を設定 */
            font-size: 2.5em; /* フォントサイズを大きく */
            color: #2c3e50; /* ダークブルー */
        }
        
        #settings {
            width: 100%; /* 幅を100%に設定 */
            max-width: 1200px; /* 最大幅を設定 */
            padding: 20px;
            border: none; /* 枠線を削除 */
            border-radius: 10px; /* 角を丸くする */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); /* 影を強調 */
            background-color: #ffffff; /* コンテナの背景色 */
            box-sizing: border-box; /* ボックスモデルを調整 */
            margin: auto; /* コンテナを中央揃え */
        }

        .setting {
            padding: 20px; /* パディングを増やしてスペースを確保 */
            margin-bottom: 15px; /* 下の余白を増加 */
            background-color: #e9ecef; /* グレーに近い薄い色 */
            cursor: move;
            border-radius: 5px; /* 角を丸くする */
            transition: background-color 0.3s, box-shadow 0.3s; /* 背景色と影の変化をスムーズに */
        }

        .setting:hover {
            background-color: #ff6f61; /* ホバー時の背景色を明るいオレンジ */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); /* ホバー時の影 */
        }

        .time-info {
            font-size: 0.9em;
            color: #555; /* 色を少し薄く */
        }

        .highlight {
            background-color: #ffe57f; /* ハイライト色 */
        }

        .changed {
            background-color: #ff5252; /* 変更の際の背景色 */
        }

        #folder-controls {
            margin-bottom: 20px;
            text-align: center; /* ボタンを中央揃え */
        }
        
        #folder-controls button {
            margin: 0 10px; /* ボタン間の余白を均等に */
            padding: 10px 15px;
            background-color: #007bff; /* 青色 */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s; /* 背景色の変化をスムーズに */
        }
        
        #folder-controls button:hover {
            background-color: #0056b3; /* ホバー時の色 */
        }

        .folder {
            background-color: #bbdefb; /* フォルダーの背景色 */
            font-weight: bold;
            padding: 20px; /* パディングを増加 */
            border: 2px solid #007bff; /* 青いボーダー */
            border-radius: 5px;
            margin-bottom: 15px;
            transition: box-shadow 0.3s; /* 影の変化をスムーズに */
        }

        .folder:hover {
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* ホバー時の影 */
        }

        .sub-setting {
            background-color: #c8e6c9; /* サブ設定の背景色 */
            padding: 15px; /* パディングを調整 */
            border-radius: 5px;
            margin-top: 10px; /* 上部の余白を追加 */
        }

        .start-time-input {
            width: 220px; /* 入力フィールドの幅を調整 */
            margin-top: 5px;
            padding: 5px; /* 入力フィールドのパディング */
            border: 1px solid #ccc; /* 薄いボーダー */
            border-radius: 5px; /* 角を丸くする */
        }

        .adjust-buttons button {
            margin-right: 5px; /* 右側の余白を設定 */
        }

        .toggle-button {
            cursor: pointer;
            color: #007bff; /* トグルボタンの色 */
            text-decoration: underline;
        }

        .hidden {
            display: none;
        }

        .lock-button, .increase-button, .decrease-button, .skip-button {
            padding: 5px 10px;
            cursor: pointer;
            border: none;
            border-radius: 5px; /* ボタンの角を丸くする */
            transition: background-color 0.3s; /* 背景色の変化をスムーズに */
        }

        .lock-button {
            background-color: #388e3c; /* 緑色 */
            color: white;
        }

        .lock-button:hover {
            background-color: #2e7d32; /* ホバー時の色 */
        }

        .increase-button {
            background-color: #d32f2f; /* 赤色 */
            color: white;
        }

        .increase-button:hover {
            background-color: #c62828; /* ホバー時の色 */
        }

        .decrease-button {
            background-color: #1976d2; /* 青色 */
            color: white;
        }

        .decrease-button:hover {
            background-color: #1565c0; /* ホバー時の色 */
        }

        .skip-button {
            background-color: #4e4e4e; /* グレー */
            color: white;
        }

        .skip-button:hover {
            background-color: #3d3d3d; /* ホバー時の色 */
        }
    </style>
</head>
<body>
    <h1>TRIP-S3CAN 2024秋 測定メニュー</h1>
    <div id="folder-controls">
        <button id="expand-all">全て展開</button>
        <button id="collapse-all">全て折りたたむ</button>
    </div>
    <div id="settings"></div>

    <script>
    const settingsData = <?php echo json_encode($settingsData); ?>;
    const settingsContainer = document.getElementById('settings');
    const labels = {};
    const durations = {};
    const originalDurations = {};
    const lockedStartTimes = {};

		function expandAllFolders() {
		    document.querySelectorAll('.folder .settings-group').forEach(group => {
		        group.classList.remove('hidden');
		    });
		    document.querySelectorAll('.folder .toggle-button').forEach(button => {
		        button.innerText = ' 折畳';
		    });
		}
		
		function collapseAllFolders() {
		    document.querySelectorAll('.folder .settings-group').forEach(group => {
		        group.classList.add('hidden');
		    });
		    document.querySelectorAll('.folder .toggle-button').forEach(button => {
		        button.innerText = ' 展開';
		    });
		}
		
		document.getElementById('expand-all').addEventListener('click', expandAllFolders);
		document.getElementById('collapse-all').addEventListener('click', collapseAllFolders);

		function createSettings() {
		    settingsContainer.innerHTML = '';
		    const folderSettings = {};
		
		    // settingsDataの構造に基づいてフォルダと設定を作成
		    for (const folder in settingsData) {
		        folderSettings[folder] = [];
		        const folderData = settingsData[folder];
		        const folderDiv = createFolderElement(folder, folderData.label);
		        const settingsDiv = document.createElement('div');
		        settingsDiv.className = 'settings-group';
		
		        folderData.items.forEach(item => {
		            const settingDiv = createSettingElement(item);
		            settingsDiv.appendChild(settingDiv);
		            folderSettings[folder].push(item);
		        });
		
		        folderDiv.appendChild(settingsDiv);
		        settingsContainer.appendChild(folderDiv);
		    }
		}

		function createFolderElement(folder, label) {
		    const folderDiv = document.createElement('div');
		    folderDiv.className = 'folder';
		    folderDiv.innerHTML = `
		        <span class="folder-label">${label}</span>
		        <span class="toggle-button">折畳</span>
		    `;
		
		    const toggleButton = folderDiv.querySelector('.toggle-button');
		    toggleButton.onclick = () => {
		        const settingsGroup = folderDiv.querySelector('.settings-group');
		        settingsGroup.classList.toggle('hidden');
		        toggleButton.innerText = settingsGroup.classList.contains('hidden') ? ' 展開' : ' 折畳';
		    };
		
		    return folderDiv;
		}

		function createSettingElement(settingData) {
		    const { id, label, duration, locked, startTime } = settingData;
		    durations[id] = duration;
		    originalDurations[id] = duration;
		    lockedStartTimes[id] = locked || false;
		
		    const settingDiv = document.createElement('div');
		    settingDiv.className = 'setting sub-setting';
		    settingDiv.id = id;
		    settingDiv.draggable = true;
		
		    settingDiv.innerHTML = `
		        <div class="label">${label}</div>

		        <div class="url-info">
		            <span>関連リンク: </span>
		            <a href="https://www.google.com" target="_blank">www.google.com</a> <!-- URLをリンクとして表示 -->
		        </div>

		        <label for="start-time" class="start-time-label">開始時刻を選択:</label>
		        <input type="datetime-local" id="start-time" class="start-time-input" value="${startTime || ''}" step="60" />
		            <span class="lock-button" onclick="toggleLock('${id}', this)">${locked ? '開始時刻の固定解除' : '開始時刻を固定'}</span>
		        <div class="time-info">開始時刻: <span class="start-time">${startTime || ''}</span> 所要時間: <span class="duration">${duration}</span>h</div>
		        <div class="adjust-buttons">
		            <span>所要時間: </span>
		            <button class="increase-button" onclick="adjustDuration('${id}', 0.25001)">+ 15 min</button>
		            <button class="decrease-button" onclick="adjustDuration('${id}', -0.25001)">- 15 min</button>
		            <button class="skip-button" onclick="adjustDuration('${id}', 0)">Skip this setting</button>
		            <button onclick="resetToOriginal('${id}')">所要時間を元に戻す</button>
		        </div>
		    `;
		
		    return settingDiv;
		}

		function setupDragAndDrop() {
		    const folders = document.querySelectorAll('.folder');
		    let draggedFolder = null;
		
		    // フォルダーのドラッグイベント
		    folders.forEach(folder => {
		        const settingsGroup = folder.querySelector('.settings-group');

						folder.addEventListener('mousedown', function() {
						    this.setAttribute('draggable', true);
						});
						folder.addEventListener('mouseup', function() {
						    this.setAttribute('draggable', false);
						});
		
		        folder.addEventListener('dragstart', function () {
		            draggedFolder = folder;
		            setTimeout(() => folder.style.display = 'none', 0);
		        });
		
		        folder.addEventListener('dragend', function () {
		            setTimeout(() => {
		                folder.style.display = 'block';
		                draggedFolder = null;
		                clearHighlights();
		                updateStartTimes();
		            }, 0);
		        });
		
		        folder.addEventListener('dragover', e => e.preventDefault());
		        folder.addEventListener('dragenter', function(e) {
		            e.preventDefault();
		            this.classList.add('highlight');
		        });
		
		        folder.addEventListener('dragleave', function () {
		            this.classList.remove('highlight');
		        });
		
		        folder.addEventListener('drop', function (e) {
		            e.preventDefault();
		            if (draggedFolder !== this) {
		                const parent = this.parentNode;
		                parent.insertBefore(draggedFolder, this);
		                clearHighlights();
		                updateStartTimes();
		            }
		        });
		    });
		
		    // 各設定のドラッグイベント
		    const settings = document.querySelectorAll('.setting');
		    settings.forEach(setting => {
		        const input = setting.querySelector('.start-time-input');
		
		        setting.addEventListener('dragstart', function () {
		            draggedFolder = null; // フォルダーを掴んでいる場合はクリア
		            setTimeout(() => setting.style.display = 'none', 0);
		        });
		
		        setting.addEventListener('dragend', function () {
		            setTimeout(() => {
		                setting.style.display = 'block';
		                clearHighlights();
		                updateStartTimes();
		            }, 0);
		        });
		
		        setting.addEventListener('dragover', e => e.preventDefault());
		        setting.addEventListener('dragenter', function(e) {
		            e.preventDefault();
		            this.classList.add('highlight');
		        });
		
		        setting.addEventListener('dragleave', function () {
		            this.classList.remove('highlight');
		        });
		
		        setting.addEventListener('drop', function (e) {
		            e.preventDefault();
		            if (draggedFolder) {
		                return; // フォルダーがドラッグされている場合は何もしない
		            }
		
		            if (draggedItem !== this) {
		                const parent = this.parentNode;
		                const children = Array.from(parent.children);
		                const fromIndex = children.indexOf(draggedItem);
		                const toIndex = children.indexOf(this);
		                if (fromIndex < toIndex) {
		                    parent.insertBefore(draggedItem, this.nextSibling);
		                } else {
		                    parent.insertBefore(draggedItem, this);
		                }
		                clearHighlights();
		                updateStartTimes();
		            }
		        });
		
		        input.addEventListener('change', updateStartTimes);
		    });
		}

		function adjustDuration(id, factor) {
		    const durationElement = document.querySelector(`#${id} .duration`);
		    let currentDuration = parseFloat(durationElement.textContent);
		
		    if (factor === 0) {
		        currentDuration = 0;
				} else if (factor === 0.25001) {
						currentDuration = Math.max(0, currentDuration + factor);
				} else if (factor === -0.25001) {
						currentDuration = Math.max(0, currentDuration + factor);
		    } else {
		        currentDuration = Math.max(0, currentDuration * factor);
		    }
		
		    durationElement.textContent = currentDuration.toFixed(2);
		    durations[id] = currentDuration;
		    highlightChangedSettings();
		    updateStartTimes();
		    
		    // 色を変更する処理を追加
		    updateSettingBackgroundColor(id, currentDuration);
		}

		function resetToOriginal(id) {
		    const durationElement = document.querySelector(`#${id} .duration`);
		    durationElement.textContent = originalDurations[id].toFixed(2);
		    durations[id] = originalDurations[id];
		    highlightChangedSettings();
		    updateStartTimes();
		    
		    // 背景色を更新
		    updateSettingBackgroundColor(id, originalDurations[id]);
		}

    function toggleLock(id, button) {
        lockedStartTimes[id] = !lockedStartTimes[id];
        button.textContent = lockedStartTimes[id] ? '開始時刻の固定解除' : '開始時刻の固定';
        updateStartTimes();
    }

    function updateStartTimes() {
        const settings = Array.from(document.querySelectorAll('.setting'));
        let previousEndTime = null;

        settings.forEach((setting, index) => {
            const id = setting.id;
            const durationElement = setting.querySelector('.duration');
            const duration = parseFloat(durationElement.textContent);
            const startTimeInput = setting.querySelector('.start-time-input');
            const startTimeSpan = setting.querySelector('.start-time');

            if (lockedStartTimes[id]) {
                const startTime = new Date(startTimeInput.value);
                startTimeSpan.textContent = formatDateTime(startTime);
                previousEndTime = new Date(startTime.getTime() + duration * 3600000);
            } else {
                if (previousEndTime) {
                    startTimeInput.value = formatDateTime(previousEndTime);
                    startTimeSpan.textContent = formatDateTime(previousEndTime);
                    previousEndTime = new Date(previousEndTime.getTime() + duration * 3600000);
                } else {
                    const now = new Date();
                    startTimeInput.value = formatDateTime(now);
                    startTimeSpan.textContent = formatDateTime(now);
                    previousEndTime = new Date(now.getTime() + duration * 3600000);
                }
            }

            let endTimeSpan = setting.querySelector('.end-time');
            if (!endTimeSpan) {
                endTimeSpan = document.createElement('span');
                endTimeSpan.className = 'end-time';
                setting.querySelector('.time-info').appendChild(endTimeSpan);
            }
            endTimeSpan.textContent = ` - ${formatDateTime(previousEndTime)}`;
        });
    }

    function formatDateTime(date) {
        const localDate = new Date(date.getTime() - (date.getTimezoneOffset() * 60000));
        return localDate.toISOString().slice(0, 16).replace('T', ' ');
    }

    function clearHighlights() {
        document.querySelectorAll('.highlight').forEach(el => el.classList.remove('highlight'));
    }

		function highlightChangedSettings() {
		    document.querySelectorAll('.setting').forEach(setting => {
		        const id = setting.id;
		        const durationElement = setting.querySelector('.duration');
		        const currentDuration = parseFloat(durationElement.textContent);
		        
		        if (currentDuration !== originalDurations[id]) {
		            setting.classList.add('changed');
		        } else {
		            setting.classList.remove('changed');
		        }
		
		        // 所要時間が0の場合の色変更
		        updateSettingBackgroundColor(id, currentDuration);
		    });
		}
		
		// 背景色を更新する関数を作成
		function updateSettingBackgroundColor(id, duration) {
		    const settingElement = document.getElementById(id);
		    if (duration === 0) {
		        settingElement.style.backgroundColor = '#d3d3d3'; // 灰色
		    } else {
		        settingElement.style.backgroundColor = ''; // 元の背景色に戻す
		    }
		}

		function saveSettings() {
		    const settings = {};
		    document.querySelectorAll('.folder').forEach(folder => {
		        const folderId = folder.querySelector('.settings-group').firstChild.id.split('_')[0];
		        const labelElement = folder.querySelector('.folder-label'); // ラベルを持つ要素を特定
		        settings[folderId] = {
		            label: labelElement ? labelElement.textContent.trim() : '', // ラベルが取得できたか確認
		            items: Array.from(folder.querySelectorAll('.setting')).map(setting => {
		                return {
		                    id: setting.id,
		                    label: setting.querySelector('.label').innerText,
		                    startTime: setting.querySelector('.start-time-input').value,
		                    duration: parseFloat(setting.querySelector('.duration').textContent),
		                    locked: lockedStartTimes[setting.id] || false
		                };
		            })
		        };
		    });
		
		    fetch('', {
		        method: 'POST',
		        headers: {
		            'Content-Type': 'application/json',
		        },
		        body: JSON.stringify(settings),
		    })
		    .then(response => response.json())
		    .then(data => {
		        if (data.status === 'success') {
		            alert('設定が保存されました。');
		        } else {
		            alert('設定の保存に失敗しました。');
		        }
		    })
		    .catch((error) => {
		        console.error('Error:', error);
		        alert('エラーが発生しました。');
		    });
		}


    // 初期化
    createSettings();
    setupDragAndDrop();
    updateStartTimes();
    </script>
</body>
</html>
