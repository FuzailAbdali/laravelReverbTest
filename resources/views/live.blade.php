<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Live Stream + Realtime Score</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; }
        .grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 16px; }
        video, iframe { width: 100%; border-radius: 8px; }
        .score { font-size: 2rem; font-weight: 700; margin: 10px 0; }
        input { width: 100%; margin: 8px 0; padding: 8px; }
        button { padding: 10px 14px; }
    </style>
</head>
<body>
<h1>Live Streaming Control Room</h1>
<div class="grid">
    <div class="card">
        <h2>MediaMTX HLS Playback</h2>
        <video id="hls-video" controls autoplay muted></video>
        <p>Default source: <code>http://localhost:8888/cam1/index.m3u8</code></p>

        <h2>YouTube Embedded Live (optional)</h2>
        <iframe
            src="https://www.youtube.com/embed/live_stream?channel=YOUR_CHANNEL_ID"
            title="YouTube Live"
            frameborder="0"
            allow="autoplay; encrypted-media; picture-in-picture"
            allowfullscreen>
        </iframe>
    </div>

    <div class="card">
        <h2>Realtime Score</h2>
        <div id="score" class="score">0 - 0</div>
        <div>Match: <span id="match-id">1</span></div>

        <h3>Add Camera Input</h3>
        <input id="camera-url" placeholder="rtsp://user:pass@ip:554/stream1" />
        <button id="save-camera">Save Camera URL</button>
        <p id="camera-status">No camera URL saved yet.</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
    const video = document.getElementById('hls-video');
    const hlsUrl = 'http://localhost:8888/cam1/index.m3u8';

    if (window.Hls && Hls.isSupported()) {
        const hls = new Hls();
        hls.loadSource(hlsUrl);
        hls.attachMedia(video);
    } else {
        video.src = hlsUrl;
    }

    // Placeholder listener: your compiled Echo JS should update this in real app.
    window.updateScoreUI = ({ home_score, away_score }) => {
        document.getElementById('score').innerText = `${home_score} - ${away_score}`;
    };

    document.getElementById('save-camera').addEventListener('click', () => {
        const value = document.getElementById('camera-url').value;
        localStorage.setItem('camera_input_url', value);
        document.getElementById('camera-status').innerText = `Saved camera URL: ${value}`;
    });

    const current = localStorage.getItem('camera_input_url');
    if (current) {
        document.getElementById('camera-url').value = current;
        document.getElementById('camera-status').innerText = `Saved camera URL: ${current}`;
    }
</script>
</body>
</html>
