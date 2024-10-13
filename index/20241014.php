<?php
session_start();

// settings.json からデータを読み込む
function readSettingsData($filePath) {
    if (!file_exists($filePath)) {
        throw new Exception("Settings file not found.");
    }

    // settings.json ファイルの内容を読み込む
    $jsonData = file_get_contents($filePath);
    $settingsData = json_decode($jsonData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error parsing JSON: " . json_last_error_msg());
    }

    return $settingsData;
}

try {
    // settings.json のパスを指定
    $filePath = './settings.json';
    
    // 設定データを読み込み
    $settingsData = readSettingsData($filePath);
} catch (Exception $e) {
    // エラーメッセージを表示
    echo "Error: " . $e->getMessage();
    exit;
}

// settingsData を JavaScript に渡す
?>
<script>
    const settingsData = <?php echo json_encode($settingsData); ?>;
</script>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>設定のドラッグアンドドロップと折りたたみ</title>
    <style>
        #settings {
            width: 1000px;
            padding: 10px;
            border: 1px solid #ccc;
        }
        .setting {
            padding: 10px;
            margin-bottom: 5px;
            background-color: lightgray;
            cursor: move;
				    border: 1px solid #000; /* 枠線の設定 */
				    border-radius: 5px; /* 角を丸くするオプション */
        }
        .time-info {
            font-size: 0.9em;
            color: #333;
        }
        .highlight {
            background-color: yellow;
        }
        .changed {
            background-color: red;
        }
				.folder {
				    background-color: lightblue;
				    font-weight: bold;
				    padding: 10px; /* 内側の余白 */
				    border: 2px solid #000; /* 枠線の設定 */
				    border-radius: 5px; /* 角を丸くするオプション */
				    margin-bottom: 10px; /* フォルダー間の隙間 */
				}
				.folder.dragging {
				    opacity: 0.5; /* ドラッグ中の透明度を変更 */
				    border: 2px dashed #000; /* ダッシュボーダーを追加 */
				}
				.setting {
				    margin-bottom: 10px; /* フォルダー間の隙間を増やす */
				}
				.highlight-above {
				    border-top: 2px dashed #f00; /* 上にドロップする場合のハイライト */
				}
				.highlight-below {
				    border-bottom: 2px dashed #00f; /* 下にドロップする場合のハイライト */
				}
        .sub-setting {
            background-color: lightgreen;
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
            color: blue;
            text-decoration: underline;
        }
        .hidden {
            display: none;
        }
        .lock-button {
            margin-left: 10px;
            cursor: pointer;
            color: green;
            text-decoration: underline;
        }
				.increase-button {
				    background-color: red; /* 増加ボタンの背景色 */
				    color: white; /* 増加ボタンのテキスト色 */
				    border: none; /* 枠線を無しにする */
				    padding: 5px 10px; /* 内側の余白 */
				    cursor: pointer; /* カーソルをポインターにする */
				    margin-right: 5px; /* ボタン間のスペース */
				}
				
				.decrease-button {
				    background-color: blue; /* 減少ボタンの背景色 */
				    color: white; /* 減少ボタンのテキスト色 */
				    border: none; /* 枠線を無しにする */
				    padding: 5px 10px; /* 内側の余白 */
				    cursor: pointer; /* カーソルをポインターにする */
				    margin-right: 5px; /* ボタン間のスペース */
				}
    </style>
</head>
<body>
    <h2>設定をドラッグして並べ替える（折りたたみ可能）</h2>
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
                <button onclick="resetToOriginal('${id}')">original</button>
                <span class="lock-button" onclick="toggleLock('${id}', this)">${lockedStartTimes[id] ? '解除' : '固定'}</span>
            </div>
            <input type="datetime-local" class="start-time-input" value="${savedSetting.startTime}" step="60" />
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
        button.textContent = lockedStartTimes[id] ? '解除' : '固定';
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
