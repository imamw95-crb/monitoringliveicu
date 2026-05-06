<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="1800">
    <title>FLV Stream Monitor - Auto Audio</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #0a0a0a;
            overflow: hidden;
            height: 100vh;
            width: 100vw;
        }
        
        .monitor-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100%;
        }
        
        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 8px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
            z-index: 10;
            border-bottom: 1px solid #333;
        }
        
        .header h1 {
            font-size: 1.2rem;
            font-weight: 500;
        }
        
        .status-info {
            display: flex;
            gap: 15px;
            font-size: 0.8rem;
        }
        
        .online-count { 
            background: #00ff8844; 
            padding: 4px 12px; 
            border-radius: 20px;
            color: #00ff88;
        }
        .offline-count { 
            background: #ff444444; 
            padding: 4px 12px; 
            border-radius: 20px;
            color: #ff8888;
        }
        .blank-count {
            background: #66666644;
            padding: 4px 12px;
            border-radius: 20px;
            color: #aaa;
        }
        
        .refresh-info {
            background: #333;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            color: #ffaa44;
        }
        
        .audio-badge {
            background: #00ff8844;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            color: #00ff88;
            animation: pulse 1.5s infinite;
        }
        
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            flex: 1;
            gap: 2px;
            background: #1a1a1a;
            padding: 2px;
            min-height: 0;
        }
        
        .monitor {
            background: #000;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .monitor:hover {
            filter: brightness(1.05);
        }
        
        .monitor video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #000;
            pointer-events: none;
        }
        
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.8), transparent);
            color: white;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 5;
            pointer-events: none;
        }
        
        .monitor-number {
            font-weight: bold;
            background: rgba(0,0,0,0.7);
            padding: 5px 14px;
            border-radius: 25px;
            font-size: 1rem;
            letter-spacing: 1px;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        
        .badge-live {
            background: #ff0000;
            color: white;
            animation: blink 1s infinite;
        }
        
        .badge-offline {
            background: #555;
            color: #ccc;
        }
        
        .badge-blank {
            background: #2c3e50;
            color: #95a5a6;
        }
        
        .audio-indicator {
            position: absolute;
            bottom: 15px;
            left: 15px;
            background: #00ff8844;
            color: #00ff88;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 10px;
            z-index: 6;
            pointer-events: none;
            backdrop-filter: blur(4px);
            font-weight: bold;
            animation: pulse 1.5s infinite;
        }
        
        .audio-indicator::before {
            content: "🔊 ";
        }
        
        .fullscreen-icon {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: rgba(0,0,0,0.6);
            color: white;
            padding: 8px 12px;
            border-radius: 30px;
            font-size: 12px;
            z-index: 6;
            pointer-events: none;
            backdrop-filter: blur(4px);
        }
        
        .offline-placeholder, .blank-placeholder {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 2;
            pointer-events: none;
        }
        
        .offline-placeholder {
            background: linear-gradient(135deg, #1a0a0a, #2a1515);
        }
        
        .blank-placeholder {
            background: linear-gradient(135deg, #0a0a1a, #111122);
        }
        
        .placeholder-icon {
            font-size: 55px;
            margin-bottom: 12px;
            opacity: 0.7;
        }
        
        .placeholder-text {
            font-size: 1rem;
            font-weight: 500;
            opacity: 0.8;
        }
        
        .placeholder-sub {
            font-size: 0.7rem;
            opacity: 0.5;
            margin-top: 6px;
        }
        
        .footer {
            background: #111;
            color: #666;
            text-align: center;
            padding: 5px;
            font-size: 10px;
            flex-shrink: 0;
        }
        
        .countdown {
            color: #ffaa44;
            font-weight: bold;
        }
        
        /* MODAL FULLSCREEN */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #000;
            z-index: 1000;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #000;
        }
        
        .modal .close-btn {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 40px;
            cursor: pointer;
            z-index: 1001;
            background: rgba(0,0,0,0.5);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .modal .close-btn:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.1);
        }
        
        .modal .modal-title {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            background: rgba(0,0,0,0.6);
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: bold;
            z-index: 1001;
            backdrop-filter: blur(4px);
        }
        
        .modal .status-indicator {
            position: absolute;
            top: 20px;
            right: 100px;
            background: #ff0000;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            animation: blink 1s infinite;
            z-index: 1001;
        }
        
        .modal .audio-info {
            position: absolute;
            bottom: 30px;
            left: 30px;
            background: #00ff8844;
            color: #00ff88;
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 14px;
            z-index: 1001;
            backdrop-filter: blur(4px);
            font-weight: bold;
            animation: pulse 1.5s infinite;
        }
        
        .volume-slider-container {
            position: absolute;
            bottom: 30px;
            right: 30px;
            background: rgba(0,0,0,0.7);
            padding: 10px 15px;
            border-radius: 30px;
            z-index: 1001;
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .volume-slider-container span {
            color: white;
            font-size: 12px;
        }
        
        .volume-slider {
            width: 100px;
            cursor: pointer;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; background: #ff0000; }
            50% { opacity: 0.7; background: #cc0000; }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }
        
        @media (max-width: 768px) {
            .monitor-number { font-size: 0.75rem; padding: 3px 10px; }
            .status-badge { font-size: 0.6rem; padding: 3px 8px; }
            .placeholder-icon { font-size: 35px; }
            .placeholder-text { font-size: 0.75rem; }
            .header h1 { font-size: 0.9rem; }
            .status-info { gap: 8px; }
            .online-count, .offline-count, .blank-count, .audio-badge { padding: 2px 8px; font-size: 0.7rem; }
            .fullscreen-icon { font-size: 10px; padding: 4px 8px; bottom: 8px; right: 8px; }
            .audio-indicator { font-size: 8px; bottom: 8px; left: 8px; }
            .modal .close-btn { font-size: 30px; width: 40px; height: 40px; top: 10px; right: 15px; }
            .modal .modal-title { font-size: 0.8rem; top: 10px; left: 10px; }
            .modal .audio-info { font-size: 10px; padding: 5px 12px; bottom: 15px; left: 15px; }
            .volume-slider-container { bottom: 15px; right: 15px; padding: 5px 10px; }
            .volume-slider { width: 60px; }
        }
    </style>
</head>
<body>
<div class="monitor-container">
    <div class="header">
        <h1>📺 FLV STREAM MONITOR 🔊 AUTO AUDIO</h1>
        <div class="status-info" id="statusInfo">
            <span class="online-count" id="onlineCount">🟢 LIVE: --</span>
            <span class="offline-count" id="offlineCount">🔴 OFFLINE: --</span>
            <span class="blank-count" id="blankCount">⚫ BLANK: --</span>
            <span class="audio-badge" id="audioBadge">🔊 AUDIO ACTIVE</span>
            <span class="refresh-info" id="refreshInfo">⏱️ Refresh: 30 menit</span>
        </div>
    </div>
    
    <div class="grid" id="monitorGrid">
        <?php
        // KONFIGURASI MONITOR 1,2,3,4 (FLV WITH AUTO AUDIO)
        $monitors = [
            1 => ['url' => 'http://192.168.0.25/0.flv', 'name' => 'MONITOR 1'],
            2 => ['url' => 'http://192.168.0.25/4.flv', 'name' => 'MONITOR 2'],
            3 => ['url' => 'http://192.168.0.25/8.flv', 'name' => 'MONITOR 3'],
            4 => ['url' => '', 'name' => 'MONITOR 4']
        ];
        
        // FUNGSI CEK STATUS STREAM
        function cekStatusStream($url) {
            if (empty($url)) return ['status' => 'blank', 'code' => 0];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return ['status' => ($httpCode == 200) ? 'live' : 'offline', 'code' => $httpCode];
        }
        
        // LOOP MONITOR
        foreach ($monitors as $num => $monitor):
            $url = $monitor['url'];
            $name = $monitor['name'];
            $result = cekStatusStream($url);
            $isBlank = empty($url);
            $isLive = ($result['status'] == 'live');
            
            if ($isBlank) {
                $badgeClass = 'badge-blank';
                $badgeText = 'BLANK';
            } elseif ($isLive) {
                $badgeClass = 'badge-live';
                $badgeText = '● LIVE';
            } else {
                $badgeClass = 'badge-offline';
                $badgeText = 'OFFLINE';
            }
            
            $dataUrl = htmlspecialchars($url);
        ?>
        <div class="monitor" data-id="<?= $num ?>" data-url="<?= $dataUrl ?>" data-status="<?= $isBlank ? 'blank' : ($isLive ? 'live' : 'offline') ?>" data-name="<?= htmlspecialchars($name) ?>">
            <div class="overlay">
                <span class="monitor-number">📹 <?= htmlspecialchars($name) ?></span>
                <span class="status-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
            </div>
            
            <?php if ($isBlank): ?>
                <div class="blank-placeholder">
                    <div class="placeholder-icon">⚫</div>
                    <div class="placeholder-text">MONITOR <?= $num ?></div>
                    <div class="placeholder-sub">SLOT KOSONG</div>
                </div>
                
            <?php elseif ($isLive): ?>
                <!-- FLV Player dengan AUTO AUDIO (unmuted) -->
                <video id="flv_<?= $num ?>" class="flv-player" autoplay playsinline loop>
                    <source src="<?= htmlspecialchars($url) ?>" type="video/x-flv">
                </video>
                <div class="audio-indicator" id="audio_ind_<?= $num ?>">AUDIO ON</div>
                <div class="fullscreen-icon">
                    🔍 Klik untuk full view
                </div>
                
            <?php else: ?>
                <div class="offline-placeholder">
                    <div class="placeholder-icon">📡❌</div>
                    <div class="placeholder-text">STREAM OFFLINE</div>
                    <div class="placeholder-sub">HTTP <?= $result['code'] ?></div>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="footer">
        🔄 Auto refresh halaman: <span class="countdown" id="countdown">30:00</span> menit | 
        Last update: <?= date('H:i:s') ?> | 
        🔊 AUDIO PLAY OTOMATIS | 💡 Klik video untuk fullscreen | FLV Stream
    </div>
</div>

<!-- MODAL FULLSCREEN -->
<div id="fullscreenModal" class="modal" onclick="closeModal()">
    <div class="modal-title" id="modalTitle">MONITOR 1</div>
    <div class="status-indicator" id="modalIndicator">● LIVE</div>
    <div class="close-btn" onclick="event.stopPropagation(); closeModal()">✕</div>
    <video id="modalVideo" class="flv-player" autoplay playsinline loop></video>
    <div class="audio-info" id="modalAudioInfo">🔊 AUDIO ACTIVE</div>
    <div class="volume-slider-container" onclick="event.stopPropagation()">
        <span>🔊</span>
        <input type="range" id="modalVolumeSlider" class="volume-slider" min="0" max="100" value="80" onchange="event.stopPropagation(); setModalVolume(this.value)">
    </div>
</div>

<!-- FLV.JS untuk play FLV -->
<script src="https://cdn.jsdelivr.net/npm/flv.js@latest"></script>

<script>
    // Data stream untuk setiap monitor
    let flvPlayers = {};
    let modalFlvPlayer = null;
    let streams = {};
    
    // UPDATE COUNTER
    function updateCounters() {
        const monitors = document.querySelectorAll('.monitor');
        let live = 0, offline = 0, blank = 0;
        
        monitors.forEach(monitor => {
            const status = monitor.getAttribute('data-status');
            if (status === 'live') live++;
            else if (status === 'offline') offline++;
            else if (status === 'blank') blank++;
        });
        
        document.getElementById('onlineCount').innerHTML = `🟢 LIVE: ${live}`;
        document.getElementById('offlineCount').innerHTML = `🔴 OFFLINE: ${offline}`;
        document.getElementById('blankCount').innerHTML = `⚫ BLANK: ${blank}`;
    }
    
    // FORCE PLAY AUDIO (work around browser autoplay policy)
    function forcePlayAudio() {
        const allVideos = document.querySelectorAll('.monitor video');
        allVideos.forEach(video => {
            // Pastikan tidak muted
            video.muted = false;
            video.volume = 0.8;
            
            // Force play
            const playPromise = video.play();
            if (playPromise !== undefined) {
                playPromise.catch(e => {
                    console.log('Play error, retrying:', e);
                    // Retry after user interaction
                    document.body.addEventListener('click', function retry() {
                        video.play().catch(() => {});
                        document.body.removeEventListener('click', retry);
                    }, { once: true });
                });
            }
        });
    }
    
    // INIT FLV PLAYER UNTUK GRID (DENGAN AUTO AUDIO)
    function initFlvPlayers() {
        const videos = document.querySelectorAll('.monitor video');
        
        videos.forEach((video, idx) => {
            const parent = video.closest('.monitor');
            const monitorId = parent.getAttribute('data-id');
            const src = parent.getAttribute('data-url');
            
            if (!src || src === '') return;
            
            // AUTO AUDIO: tidak muted, volume 80%
            video.muted = false;
            video.playsInline = true;
            video.autoplay = true;
            video.loop = true;
            video.volume = 0.8;
            
            // Coba pakai flv.js untuk FLV
            if (flvjs && flvjs.isSupported()) {
                try {
                    if (flvPlayers[monitorId]) {
                        flvPlayers[monitorId].destroy();
                    }
                    
                    const flvPlayer = flvjs.createPlayer({
                        type: 'flv',
                        url: src,
                        isLive: true,
                        cors: true,
                        enableWorker: true,
                        enableStashBuffer: false,
                        autoCleanupSourceBuffer: true
                    });
                    
                    flvPlayer.attachMediaElement(video);
                    flvPlayer.load();
                    
                    // Play dengan audio
                    setTimeout(() => {
                        flvPlayer.play().catch(e => {
                            console.log('FLV play error:', e);
                            // Fallback: coba native
                            video.src = src;
                            video.play().catch(() => {});
                        });
                    }, 100);
                    
                    flvPlayers[monitorId] = flvPlayer;
                    streams[monitorId] = src;
                    
                    flvPlayer.on(flvjs.Events.ERROR, (err) => {
                        console.error('FLV Error:', err);
                        video.src = src;
                        video.play().catch(e => console.log('Fallback play:', e));
                    });
                    
                } catch(e) {
                    console.log('FLV init error, fallback to native:', e);
                    video.src = src;
                    video.play().catch(e => console.log('Native play:', e));
                }
            } else {
                // Native fallback
                video.src = src;
                video.play().catch(e => console.log('Native play error:', e));
            }
        });
        
        // Force audio after a short delay
        setTimeout(forcePlayAudio, 500);
    }
    
    // FULLSCREEN MODAL UNTUK FLV (DENGAN AUTO AUDIO)
    function openFullscreen(monitorElement) {
        const monitorId = monitorElement.getAttribute('data-id');
        const monitorName = monitorElement.getAttribute('data-name');
        const monitorStatus = monitorElement.getAttribute('data-status');
        const monitorUrl = monitorElement.getAttribute('data-url');
        
        const modal = document.getElementById('fullscreenModal');
        const modalVideo = document.getElementById('modalVideo');
        const modalTitle = document.getElementById('modalTitle');
        const modalIndicator = document.getElementById('modalIndicator');
        const modalAudioInfo = document.getElementById('modalAudioInfo');
        
        modalTitle.textContent = `📹 ${monitorName}`;
        
        if (monitorStatus === 'live' && monitorUrl) {
            modalIndicator.textContent = '● LIVE';
            modalIndicator.style.display = 'block';
            modalIndicator.style.background = '#ff0000';
            modalIndicator.style.animation = 'blink 1s infinite';
            
            modalAudioInfo.style.display = 'block';
            
            // Hentikan player sebelumnya
            if (modalFlvPlayer) {
                modalFlvPlayer.destroy();
                modalFlvPlayer = null;
            }
            
            // Set volume slider ke default
            const volumeSlider = document.getElementById('modalVolumeSlider');
            volumeSlider.value = 80;
            
            // AUTO AUDIO: tidak muted
            modalVideo.muted = false;
            modalVideo.playsInline = true;
            modalVideo.autoplay = true;
            modalVideo.loop = true;
            modalVideo.volume = 0.8;
            
            // Gunakan flv.js untuk modal
            if (flvjs && flvjs.isSupported()) {
                try {
                    modalFlvPlayer = flvjs.createPlayer({
                        type: 'flv',
                        url: monitorUrl,
                        isLive: true,
                        cors: true,
                        enableWorker: true,
                        enableStashBuffer: false,
                        autoCleanupSourceBuffer: true
                    });
                    
                    modalFlvPlayer.attachMediaElement(modalVideo);
                    modalFlvPlayer.load();
                    
                    setTimeout(() => {
                        modalFlvPlayer.play().catch(e => {
                            console.log('Modal FLV play error:', e);
                            modalVideo.src = monitorUrl;
                            modalVideo.play().catch(() => {});
                        });
                    }, 100);
                    
                    modalFlvPlayer.on(flvjs.Events.ERROR, (err) => {
                        console.error('Modal FLV Error:', err);
                        modalVideo.src = monitorUrl;
                        modalVideo.play().catch(() => {});
                    });
                    
                } catch(e) {
                    console.log('Modal FLV init error:', e);
                    modalVideo.src = monitorUrl;
                    modalVideo.play().catch(() => {});
                }
            } else {
                modalVideo.src = monitorUrl;
                modalVideo.play().catch(() => {});
            }
            
        } else {
            modalIndicator.textContent = monitorStatus === 'blank' ? '● BLANK' : '● OFFLINE';
            modalIndicator.style.background = '#555';
            modalIndicator.style.animation = 'none';
            modalAudioInfo.style.display = 'none';
            modalVideo.style.display = 'none';
        }
        
        modal.classList.add('active');
    }
    
    function closeModal() {
        const modal = document.getElementById('fullscreenModal');
        const modalVideo = document.getElementById('modalVideo');
        
        modal.classList.remove('active');
        
        if (modalFlvPlayer) {
            modalFlvPlayer.destroy();
            modalFlvPlayer = null;
        }
        modalVideo.src = '';
        modalVideo.style.display = 'block';
        modalVideo.style.display = '';
    }
    
    // SET MODAL VOLUME
    function setModalVolume(value) {
        const modalVideo = document.getElementById('modalVideo');
        if (modalVideo) {
            modalVideo.volume = value / 100;
        }
    }
    
    // BIND CLICK EVENT
    function bindClickEvents() {
        const monitors = document.querySelectorAll('.monitor');
        monitors.forEach(monitor => {
            const status = monitor.getAttribute('data-status');
            if (status === 'live') {
                monitor.style.cursor = 'pointer';
                monitor.addEventListener('click', (e) => {
                    e.stopPropagation();
                    openFullscreen(monitor);
                });
            } else {
                monitor.style.cursor = 'default';
            }
        });
    }
    
    // COUNTDOWN 30 MENIT
    let timeLeft = 1800;
    const countdownElement = document.getElementById('countdown');
    
    function updateCountdown() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        countdownElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeLeft <= 0) {
            location.reload();
        }
        timeLeft--;
    }
    
    // INTERAKSI USER UNTUK MEMASTIKAN AUDIO (workaround browser policy)
    function enableAudioOnInteraction() {
        const allVideos = document.querySelectorAll('.monitor video');
        allVideos.forEach(video => {
            if (video.muted) {
                video.muted = false;
            }
            if (video.paused) {
                video.play().catch(() => {});
            }
        });
        
        // Modal video juga
        const modalVideo = document.getElementById('modalVideo');
        if (modalVideo && modalVideo.muted) {
            modalVideo.muted = false;
            modalVideo.play().catch(() => {});
        }
    }
    
    // TUTUP MODAL DENGAN ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
    
    // User interaction untuk enable audio (browser policy)
    document.body.addEventListener('click', enableAudioOnInteraction);
    document.body.addEventListener('touchstart', enableAudioOnInteraction);
    
    // JALANKAN SEMUA
    setTimeout(() => {
        initFlvPlayers();
        bindClickEvents();
        updateCounters();
        updateCountdown();
    }, 100);
    
    setInterval(updateCounters, 5000);
    setInterval(updateCountdown, 1000);
</script>
</body>
</html>