<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk EXIF/Metadata Extractor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/exif-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/exifreader@4.20.0/dist/exif-reader.min.js"></script>
    <script src="/assets/js/lib/mediainfo/mediainfo.min.js"></script>
    <style>
        .drop-zone {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
            cursor: pointer;
        }
        .drop-zone.dragover {
            border-color: #0d6efd;
            background-color: #e9ecef;
        }
        .drop-zone:hover {
            border-color: #6c757d;
        }
        #logArea {
            font-family: monospace;
            font-size: 0.8rem;
            max-height: 150px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-lg">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-images"></i> Bulk EXIF/Metadata Extractor</h5>
                <span class="badge bg-secondary">v1.0</span>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    大量のファイル（数千枚規模）を一括処理するためのツールです。<br>
                    フォルダごとドラッグ＆ドロップするか、ボタンから選択してください。<br>
                    <small>※ 処理はすべてブラウザ内で行われ、サーバーへのアップロードは発生しません。</small>
                    <hr>
                    <small class="d-block mt-2 text-dark">
                        <strong><i class="bi bi-apple"></i> Macユーザーの方へ:</strong><br>
                        「写真」アプリのライブラリ（Photos Library）は直接選択できません。<br>
                        アプリのメニューから <strong>[ファイル] &gt; [書き出す] &gt; [未編集のオリジナルを書き出す]</strong> を行い、書き出したフォルダを選択してください。
                    </small>
                </div>

                <!-- Initialization Status -->
                <div id="initStatus" class="alert alert-warning">
                    <div class="spinner-border spinner-border-sm" role="status"></div>
                    ライブラリを読み込み中...
                </div>

                <!-- Input Area -->
                <div id="inputArea" class="d-none">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="drop-zone" id="dropZone">
                                <i class="bi bi-cloud-upload display-4 text-secondary"></i>
                                <p class="mt-2 mb-0">ここにフォルダまたはファイルを<br>ドラッグ＆ドロップ</p>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex flex-column justify-content-center gap-2">
                            <label class="btn btn-outline-primary btn-lg w-100">
                                <i class="bi bi-folder-plus"></i> フォルダを選択
                                <input type="file" id="folderInput" class="d-none" webkitdirectory directory multiple>
                            </label>
                            <label class="btn btn-outline-secondary btn-lg w-100">
                                <i class="bi bi-file-earmark-plus"></i> ファイルを選択
                                <input type="file" id="fileInput" class="d-none" multiple accept="image/*,video/*">
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Progress Area -->
                <div id="progressArea" class="d-none">
                    <h6 class="border-bottom pb-2 mb-3">処理状況</h6>
                    <div class="progress mb-2" style="height: 25px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small mb-3">
                        <span id="progressStats">待機中...</span>
                        <span id="timeStats">--:--</span>
                    </div>
                    
                    <div class="d-flex gap-2 mb-4">
                        <button id="pauseBtn" class="btn btn-warning flex-grow-1">
                            <i class="bi bi-pause-fill"></i> 一時停止
                        </button>
                        <button id="resumeBtn" class="btn btn-primary flex-grow-1 d-none">
                            <i class="bi bi-play-fill"></i> 再開
                        </button>
                        <button id="stopBtn" class="btn btn-danger">
                            <i class="bi bi-stop-fill"></i> 中止
                        </button>
                    </div>
                </div>

                <!-- Result Area -->
                <div id="resultArea" class="d-none">
                    <div class="card bg-light border-success mb-3">
                        <div class="card-body text-center">
                            <h3 class="text-success"><i class="bi bi-check-circle-fill"></i> 完了</h3>
                            <p class="mb-3">すべての処理が完了しました。</p>
                            <div class="d-flex justify-content-center gap-3">
                                <button id="downloadBtn" class="btn btn-success btn-lg">
                                    <i class="bi bi-download"></i> JSONをダウンロード (<span id="resultSize">0 KB</span>)
                                </button>
                                <button onclick="location.reload()" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> リセット
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Log Area -->
                <div class="card">
                    <div class="card-header bg-light py-1 px-2 d-flex justify-content-between align-items-center">
                        <small class="text-muted">Log</small>
                        <button class="btn btn-link btn-sm p-0 text-decoration-none" onclick="document.getElementById('logArea').innerHTML=''">Clear</button>
                    </div>
                    <div class="card-body p-2 bg-dark text-light" id="logArea"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuration
        const CHUNK_SIZE = 5; // Reduced from 10 to keep UI responsive
        const CHUNK_DELAY = 20; // Increased delay to 20ms to yield UI more often

        // State
        let mediaInfoInstance = null;
        let fileQueue = [];
        let processedResults = {};
        let isProcessing = false;
        let isPaused = false;
        let processedCount = 0;
        let totalFiles = 0;
        let startTime = 0;

        // Logger
        function log(msg, type = 'info') {
            const logArea = document.getElementById('logArea');
            const div = document.createElement('div');
            const time = new Date().toLocaleTimeString();
            div.innerHTML = `<span class="text-muted">[${time}]</span> <span class="${type === 'error' ? 'text-danger' : 'text-light'}">${msg}</span>`;
            logArea.appendChild(div);
            logArea.scrollTop = logArea.scrollHeight;
        }

        // Initialization
        window.addEventListener('load', function() {
            log('Initializing system...');
            
            if (typeof MediaInfo === 'undefined') {
                document.getElementById('initStatus').className = 'alert alert-danger';
                document.getElementById('initStatus').innerHTML = '<i class="bi bi-x-circle"></i> MediaInfo library failed to load.';
                log('MediaInfo library missing', 'error');
                return;
            }

            // Determine factory
            let MediaInfoFactory = MediaInfo;
            if (typeof MediaInfo !== 'function') {
                if (MediaInfo.default && typeof MediaInfo.default === 'function') {
                    MediaInfoFactory = MediaInfo.default;
                } else if (MediaInfo.MediaInfo && typeof MediaInfo.MediaInfo === 'function') {
                    MediaInfoFactory = MediaInfo.MediaInfo;
                }
            }

            MediaInfoFactory({
                format: 'object',
                locateFile: (path, prefix) => `/assets/js/lib/mediainfo/${path}`
            }).then((mediainfo) => {
                mediaInfoInstance = mediainfo;
                log('MediaInfo initialized.');
                document.getElementById('initStatus').classList.add('d-none');
                document.getElementById('inputArea').classList.remove('d-none');
                setupEventListeners();
            }).catch((error) => {
                log('Initialization Error: ' + error.message, 'error');
                document.getElementById('initStatus').className = 'alert alert-danger';
                document.getElementById('initStatus').textContent = 'Error: ' + error.message;
            });
        });

        function setupEventListeners() {
            const dropZone = document.getElementById('dropZone');
            const folderInput = document.getElementById('folderInput');
            const fileInput = document.getElementById('fileInput');

            // Drag & Drop
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.classList.add('dragover');
            });
            dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.classList.remove('dragover');
                handleFiles(e.dataTransfer.files);
            });

            // Inputs
            folderInput.addEventListener('change', (e) => handleFiles(e.target.files));
            fileInput.addEventListener('change', (e) => handleFiles(e.target.files));

            // Controls
            document.getElementById('pauseBtn').addEventListener('click', () => {
                isPaused = true;
                document.getElementById('pauseBtn').classList.add('d-none');
                document.getElementById('resumeBtn').classList.remove('d-none');
                log('Paused.');
            });
            document.getElementById('resumeBtn').addEventListener('click', () => {
                isPaused = false;
                document.getElementById('pauseBtn').classList.remove('d-none');
                document.getElementById('resumeBtn').classList.add('d-none');
                log('Resuming...');
                processQueue();
            });
            document.getElementById('stopBtn').addEventListener('click', () => {
                if(confirm('処理を中止しますか？')) {
                    fileQueue = [];
                    finishProcessing();
                }
            });
        }

        async function handleFiles(fileList) {
            if (!fileList.length) return;

            // Show loading state immediately
            document.getElementById('inputArea').classList.add('d-none');
            document.getElementById('progressArea').classList.remove('d-none');
            document.getElementById('progressStats').textContent = 'ファイルをスキャン中...';
            log(`Scanning ${fileList.length} files...`);

            // Allow UI to update
            await new Promise(r => setTimeout(r, 50));

            const supportedTypes = ['image/jpeg', 'image/png', 'image/heic', 'video/mp4', 'video/quicktime'];
            const files = [];
            const BATCH_SIZE = 500; // Scan in batches
            
            // Convert FileList to Array once to avoid issues with repeated access or slicing
            const allFiles = Array.from(fileList);

            // Process filtering in chunks to avoid freezing
            for (let i = 0; i < allFiles.length; i += BATCH_SIZE) {
                const chunk = allFiles.slice(i, i + BATCH_SIZE);
                const validChunk = chunk.filter(f => 
                    supportedTypes.some(type => f.type === type) || 
                    /\.(jpg|jpeg|png|heic|mov|mp4)$/i.test(f.name)
                );
                files.push(...validChunk);
                
                // Update UI occasionally
                const currentCount = Math.min(i + chunk.length, allFiles.length);
                document.getElementById('progressStats').textContent = `スキャン中... (${currentCount} / ${allFiles.length})`;
                await new Promise(r => setTimeout(r, 0)); // Yield to main thread
            }

            if (files.length === 0) {
                alert('対応ファイルが見つかりませんでした。');
                document.getElementById('progressArea').classList.add('d-none');
                document.getElementById('inputArea').classList.remove('d-none');
                return;
            }

            // Add to queue
            fileQueue = files;
            totalFiles = files.length;
            processedCount = 0;
            processedResults = {};
            startTime = Date.now();

            log(`Queued ${totalFiles} files.`);

            // Start processing
            isProcessing = true;
            isPaused = false;
            processQueue();
        }

        async function processQueue() {
            if (!isProcessing || isPaused) return;

            if (fileQueue.length === 0) {
                finishProcessing();
                return;
            }

            // Take a chunk
            const chunk = fileQueue.splice(0, CHUNK_SIZE);
            
            // Process chunk sequentially to avoid MediaInfo concurrency issues
            for (const file of chunk) {
                if (!isProcessing || isPaused) {
                    // Put back remaining files if stopped/paused
                    fileQueue.unshift(file, ...chunk.slice(chunk.indexOf(file) + 1));
                    return;
                }
                await processSingleFile(file);
            }

            // Update UI
            updateProgress();

            // Schedule next chunk
            setTimeout(processQueue, CHUNK_DELAY);
        }

        async function processSingleFile(file) {
            try {
                let metadata;
                // Use MediaInfo for Video only
                if (file.type.startsWith('video/') || /\.(mov|mp4)$/i.test(file.name)) {
                    metadata = await getMediaMetadata(file);
                } 
                // Use ExifReader for Images (HEIC, JPG, PNG)
                else if (file.type.startsWith('image/') || /\.(jpg|jpeg|png|heic)$/i.test(file.name)) {
                    metadata = await getExifReaderData(file);
                } else {
                    metadata = { error: 'Unsupported type' };
                }
                
                // Use relative path if available (webkitRelativePath), else name
                const key = file.webkitRelativePath || file.name;
                processedResults[key] = metadata;
                processedCount++;
            } catch (err) {
                console.error(err);
                const key = file.webkitRelativePath || file.name;
                processedResults[key] = { error: err.message };
                processedCount++;
                log(`Error processing ${file.name}: ${err.message}`, 'error');
            }
        }

        function updateProgress() {
            const percent = Math.round((processedCount / totalFiles) * 100);
            const progressBar = document.getElementById('progressBar');
            progressBar.style.width = percent + '%';
            progressBar.textContent = percent + '%';

            // Stats
            const elapsed = (Date.now() - startTime) / 1000;
            const speed = processedCount / elapsed; // files per sec
            const remaining = (totalFiles - processedCount) / speed;
            
            document.getElementById('progressStats').textContent = 
                `${processedCount} / ${totalFiles} 完了 (${Math.round(speed)} files/s)`;
            
            document.getElementById('timeStats').textContent = 
                `残り: ${formatTime(remaining)}`;
        }

        function finishProcessing() {
            isProcessing = false;
            document.getElementById('progressArea').classList.add('d-none');
            document.getElementById('resultArea').classList.remove('d-none');
            
            const jsonStr = JSON.stringify(processedResults, null, 2);
            const blob = new Blob([jsonStr], { type: 'application/json' });
            const sizeKB = Math.round(blob.size / 1024);
            document.getElementById('resultSize').textContent = sizeKB + ' KB';

            const url = URL.createObjectURL(blob);
            const btn = document.getElementById('downloadBtn');
            btn.onclick = () => {
                const a = document.createElement('a');
                a.href = url;
                a.download = `metadata_bulk_${new Date().toISOString().slice(0,19).replace(/[-:]/g,'')}.json`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            };
            log('Processing finished.');
        }

        // --- Helpers ---

        function formatTime(seconds) {
            if (!isFinite(seconds)) return '--:--';
            const m = Math.floor(seconds / 60);
            const s = Math.floor(seconds % 60);
            return `${m}:${s.toString().padStart(2, '0')}`;
        }

        function convertDMSToDD(dms, ref) {
            if (!dms || dms.length < 3) return null;
            let dd = dms[0] + dms[1]/60 + dms[2]/3600;
            if (ref === "S" || ref === "W") {
                dd = dd * -1;
            }
            return dd;
        }

        function getExifData(file) {
            return new Promise((resolve) => {
                EXIF.getData(file, function() {
                    const allTags = EXIF.getAllTags(this);
                    const cleanTags = {};
                    for (const key in allTags) {
                        if (key === 'MakerNote' || key === 'UserComment') continue;
                        let value = allTags[key];
                        if (value instanceof Number) value = value.valueOf();
                        cleanTags[key] = value;
                    }
                    if (cleanTags.GPSLatitude && cleanTags.GPSLatitudeRef && cleanTags.GPSLongitude && cleanTags.GPSLongitudeRef) {
                        cleanTags.GPSLatitudeDecimal = convertDMSToDD(cleanTags.GPSLatitude, cleanTags.GPSLatitudeRef);
                        cleanTags.GPSLongitudeDecimal = convertDMSToDD(cleanTags.GPSLongitude, cleanTags.GPSLongitudeRef);
                    }
                    resolve(cleanTags);
                });
            });
        }

        function getExifReaderData(file) {
            return new Promise((resolve, reject) => {
                ExifReader.load(file).then(function (tags) {
                    const cleanTags = {};
                    
                    // Iterate through ALL tags
                    for (const key in tags) {
                        // Skip definitely useless/binary tags
                        if (['Thumbnail', 'PrintIM'].includes(key)) continue;
                        
                        const tag = tags[key];
                        if (!tag) continue;

                        // Special handling for MakerNote
                        if (key === 'MakerNote') {
                            // If description says "Binary data" or similar, skip it
                            if (tag.description && (
                                tag.description.includes('Binary data') || 
                                tag.description.includes('undefined') ||
                                tag.description.length > 1000 // Too long text is also suspicious/heavy
                            )) {
                                continue;
                            }
                            // If value is a large array, skip it
                            if (Array.isArray(tag.value) && tag.value.length > 100) {
                                continue;
                            }
                            // If it seems okay, let it fall through to be added
                        }

                        // ExifReader tags usually have a 'description' property which is the formatted value.
                        if (tag.description !== undefined) {
                            cleanTags[key] = tag.description;
                        } else if (tag.value !== undefined) {
                            // Fallback: use raw value if it's simple
                            if (typeof tag.value !== 'object' && typeof tag.value !== 'function') {
                                cleanTags[key] = tag.value;
                            } else if (Array.isArray(tag.value) && tag.value.length < 100) {
                                // Allow small arrays (e.g. components configuration), skip large binary dumps
                                cleanTags[key] = tag.value;
                            }
                        }
                    }

                    // --- Custom Calculations (Keep these for convenience) ---

                    // GPS Decimal Calculation
                    if (tags['GPSLatitude'] && tags['GPSLongitude']) {
                        // Latitude / Longitude
                        let lat = parseFloat(tags['GPSLatitude'].description);
                        let lon = parseFloat(tags['GPSLongitude'].description);
                        
                        if (tags['GPSLatitudeRef']) {
                            const ref = tags['GPSLatitudeRef'].value[0];
                            if (ref === 'S') lat = -lat;
                        }
                        if (tags['GPSLongitudeRef']) {
                            const ref = tags['GPSLongitudeRef'].value[0];
                            if (ref === 'W') lon = -lon;
                        }
                        
                        cleanTags.GPSLatitudeDecimal = lat;
                        cleanTags.GPSLongitudeDecimal = lon;

                        // Altitude
                        if (tags['GPSAltitude']) {
                            let alt = parseFloat(tags['GPSAltitude'].description);
                            // Adjust for Ref (0 = Above Sea Level, 1 = Below Sea Level)
                            if (tags['GPSAltitudeRef']) {
                                const ref = parseInt(tags['GPSAltitudeRef'].value);
                                if (ref === 1) alt = -alt;
                            }
                            cleanTags.GPSAltitude = alt;
                        }

                        // Accuracy (HPositioningError)
                        if (tags['GPSHPositioningError']) {
                            cleanTags.GPSHPositioningError = parseFloat(tags['GPSHPositioningError'].description);
                        }
                    }

                    resolve(cleanTags);
                }).catch(function (error) {
                    reject(error);
                });
            });
        }

        function getMediaMetadata(file) {
            return new Promise((resolve, reject) => {
                if (!mediaInfoInstance) {
                    reject(new Error("MediaInfo not initialized"));
                    return;
                }
                const getSize = () => file.size;
                const readChunk = (chunkSize, offset) =>
                    new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = (event) => {
                            if (event.target.error) reject(event.target.error);
                            resolve(new Uint8Array(event.target.result));
                        };
                        reader.readAsArrayBuffer(file.slice(offset, offset + chunkSize));
                    });

                mediaInfoInstance.analyzeData(getSize, readChunk)
                    .then(resolve)
                    .catch(reject);
            });
        }
    </script>
</body>
</html>
