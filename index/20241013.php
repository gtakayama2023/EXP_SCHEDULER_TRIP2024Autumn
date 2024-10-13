<?php
session_start();

// 設定データを階層構造にする
$settingsData = [
    'setting0' => [
        ['id' => 'setting0_1', 'label' => 'Setting0 - 1', 'duration' => 1, 'locked' => true],
        ['id' => 'setting0_2', 'label' => 'Setting0 - 2', 'duration' => 2, 'locked' => false],
        ['id' => 'setting0_3', 'label' => 'Setting0 - 3', 'duration' => 1, 'locked' => false]
    ],
    'setting1' => [
        ['id' => 'setting1_1', 'label' => 'Setting1 - 1', 'duration' => 2, 'locked' => false],
        ['id' => 'setting1_2', 'label' => 'Setting1 - 2', 'duration' => 3, 'locked' => false],
        ['id' => 'setting1_3', 'label' => 'Setting1 - 3', 'duration' => 1, 'locked' => false]
    ],
    'setting2' => [
        ['id' => 'setting2_1', 'label' => 'Setting2 - 1', 'duration' => 2, 'locked' => false],
        ['id' => 'setting2_2', 'label' => 'Setting2 - 2', 'duration' => 3, 'locked' => false],
        ['id' => 'setting2_3', 'label' => 'Setting2 - 3', 'duration' => 1, 'locked' => false]
    ],
];

// 保存されたデータを取得
$previousSettings = [];
if (file_exists('settings.json')) {
    $previousSettings = json_decode(file_get_contents('settings.json'), true);
}

// POSTリクエストがあった場合、設定を保存
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    // 現在の設定の順序と開始時刻をファイルに保存
    file_put_contents('settings.json', json_encode($data));
    // 成功レスポンスを返す
    echo json_encode(['status' => 'success', 'message' => '設定が保存されました。']);
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
		        background-color: #f9f9f9; /* 背景色の設定 */
		        margin: 0;
		        padding: 20px;
		    }
				h1 {
				    text-align: center; /* 中央揃え */
				    margin-bottom: 20px; /* 下の余白を設定 */
				    font-size: 2em; /* フォントサイズを調整（必要に応じて） */
				    color: #333; /* テキストカラー */
				}
				#settings {
				    width: 100%; /* 幅を100%に設定 */
				    max-width: 12000px; /* 最大幅を設定（必要に応じて調整） */
				    padding: 20px;
				    border: 1px solid #ccc;
				    border-radius: 10px; /* 角を丸くする */
				    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* 影の追加 */
				    background-color: #ffffff; /* コンテナの背景色 */
				    box-sizing: border-box; /* ボックスモデルを調整 */
				}
		
		    .setting {
		        padding: 15px;
		        margin-bottom: 10px;
		        background-color: #e0e0e0;
		        cursor: move;
		        border: 1px solid #000;
		        border-radius: 5px;
		        transition: background-color 0.3s; /* 背景色の変化をスムーズに */
		    }
		
		    .setting:hover {
		        background-color: #d1d1d1; /* ホバー時の背景色 */
		    }
		
		    .time-info {
		        font-size: 0.9em;
		        color: #333;
		    }
		
		    .highlight {
		        background-color: #ffe57f; /* ハイライト色の変更 */
		    }
		
		    .changed {
		        background-color: #ff5252; /* 変更の際の背景色 */
		    }
		
		    .folder {
		        background-color: #bbdefb; /* フォルダーの背景色 */
		        font-weight: bold;
		        padding: 15px;
		        border: 2px solid #000;
		        border-radius: 5px;
		        margin-bottom: 15px;
		        transition: box-shadow 0.3s; /* 影の変化をスムーズに */
		    }
		
		    .folder:hover {
		        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* ホバー時の影 */
		    }
		
		    .folder.dragging {
		        opacity: 0.5;
		        border: 2px dashed #000;
		    }
		
		    .highlight-above {
		        border-top: 2px dashed #f00;
		    }
		
		    .highlight-below {
		        border-bottom: 2px dashed #00f;
		    }
		
		    .sub-setting {
		        background-color: #c8e6c9; /* サブ設定の背景色 */
		        padding: 10px;
		        border-radius: 5px;
		    }
		
		    .start-time-input {
		        width: 200px;
		        margin-top: 5px;
		    }
		
		    .adjust-buttons button {
		        margin-right: 5px;
		    }
		
		    .toggle-button {
		        cursor: pointer;
		        color: #1976d2; /* トグルボタンの色 */
		        text-decoration: underline;
		    }
		
		    .hidden {
		        display: none;
		    }
		
		    .lock-button {
		        margin-left: 10px;
		        cursor: pointer;
		        color: #388e3c; /* ロックボタンの色 */
		        text-decoration: underline;
		    }
		
		    .increase-button {
		        background-color: #d32f2f;
		        color: white;
		        border: none;
		        padding: 5px 10px;
		        cursor: pointer;
		        margin-right: 5px;
		        border-radius: 5px; /* ボタンの角を丸くする */
		    }
		
		    .decrease-button {
		        background-color: #1976d2;
		        color: white;
		        border: none;
		        padding: 5px 10px;
		        cursor: pointer;
		        margin-right: 5px;
		        border-radius: 5px; /* ボタンの角を丸くする */
		    }
		</style>
</head>
<body>
    <h1>TRIP-S3CAN 2024秋 測定メニュー</h1>
    <div id="settings"></div>
    <button id="save-button">設定を保存</button>

    <script>
    const settingsData = <?php echo json_encode($settingsData); ?>;
    const savedSettings = <?php echo json_encode($previousSettings); ?>;
    const settingsContainer = document.getElementById('settings');
    const labels = {};
    const durations = {};
    const originalDurations = {};
    const lockedStartTimes = {};

    function createSettings() {
        settingsContainer.innerHTML = '';
        const folderSettings = {};

        savedSettings.forEach(savedSetting => {
            const [folder, settingId] = savedSetting.id.split('_');
            if (!folderSettings[folder]) {
                folderSettings[folder] = [];
            }
            folderSettings[folder].push(savedSetting);
        });

        for (const folder in folderSettings) {
            const folderDiv = createFolderElement(folder);
            const settingsDiv = document.createElement('div');
            settingsDiv.className = 'settings-group';

            folderSettings[folder].forEach(savedSetting => {
                const settingData = settingsData[folder].find(s => s.id === savedSetting.id);
                if (settingData) {
                    const settingDiv = createSettingElement(settingData, savedSetting);
                    settingsDiv.appendChild(settingDiv);
                }
            });

            folderDiv.appendChild(settingsDiv);
            settingsContainer.appendChild(folderDiv);
        }
    }

    function createFolderElement(folder) {
        const folderDiv = document.createElement('div');
        folderDiv.className = 'folder';
        folderDiv.innerText = folder;

        const toggleButton = document.createElement('span');
        toggleButton.className = 'toggle-button';
        toggleButton.innerText = ' 折畳';
        toggleButton.onclick = () => {
            const settingsGroup = folderDiv.querySelector('.settings-group');
            settingsGroup.classList.toggle('hidden');
            toggleButton.innerText = settingsGroup.classList.contains('hidden') ? ' 展開' : ' 折畳';
        };

        folderDiv.appendChild(toggleButton);
        return folderDiv;
    }

    function createSettingElement(settingData, savedSetting) {
        const { id, label, duration } = settingData;
        durations[id] = savedSetting.duration || duration;
        originalDurations[id] = duration;
        lockedStartTimes[id] = savedSetting.locked || false;

        const settingDiv = document.createElement('div');
        settingDiv.className = 'setting sub-setting';
        settingDiv.id = id;
        settingDiv.draggable = true;

        settingDiv.innerHTML = `
		        <div class="label">${label}</div>  <!-- labelを表示するための要素を追加 -->
            <div class="time-info">開始時刻: <span class="start-time">${savedSetting.startTime}</span> 所要時間: <span class="duration">${durations[id]}</span>h</div>
            <div class="adjust-buttons">
		            <span>所要時間: </span>
								<button class="increase-button" onclick="adjustDuration('${id}', 3.0)">x 3</button>
								<button class="increase-button" onclick="adjustDuration('${id}', 2.0)">x 2</button>
								<button class="increase-button" onclick="adjustDuration('${id}', 1.33)">x 4/3</button>
								<button class="decrease-button" onclick="adjustDuration('${id}', 0.75)">x 3/4</button>
								<button class="decrease-button" onclick="adjustDuration('${id}', 0.5)">x 1/2</button>
								<button class="decrease-button" onclick="adjustDuration('${id}', 0.333)">x 1/3</button>
                <button class="decrease-button" onclick="adjustDuration('${id}', 0)">0 にする</button>
                <button onclick="resetToOriginal('${id}')">所要時間を元に戻す</button>
                <span class="lock-button" onclick="toggleLock('${id}', this)">${lockedStartTimes[id] ? '開始時刻の固定解除' : '開始時刻を固定'}</span>
            </div>
						<label for="start-time" class="start-time-label">開始時刻を選択:</label>
						<input type="datetime-local" id="start-time" class="start-time-input" value="${savedSetting.startTime}" step="60" />
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
        } else {
            currentDuration = Math.max(0, currentDuration * factor);
        }

        durationElement.textContent = currentDuration.toFixed(2);
        durations[id] = currentDuration;
        highlightChangedSettings();
        updateStartTimes();
    }

    function resetToOriginal(id) {
        const durationElement = document.querySelector(`#${id} .duration`);
        durationElement.textContent = originalDurations[id].toFixed(2);
        durations[id] = originalDurations[id];
        highlightChangedSettings();
        updateStartTimes();
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
        });
    }

    document.getElementById('save-button').addEventListener('click', saveSettings);

    function saveSettings() {
        const settings = Array.from(document.querySelectorAll('.setting')).map(setting => {
            return {
                id: setting.id,
                label: setting.querySelector('.label').innerText,
                startTime: setting.querySelector('.start-time-input').value,
                duration: parseFloat(setting.querySelector('.duration').textContent),
                locked: lockedStartTimes[setting.id] || false
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
