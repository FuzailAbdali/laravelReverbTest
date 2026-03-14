<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Live Stream</title>
</head>
<body>
  <h1>Live Match Stream</h1>

  <p>YouTube embed:</p>
  <iframe
    width="960"
    height="540"
    src="https://www.youtube.com/embed/{{ $youtubeVideoId }}"
    title="YouTube live stream"
    frameborder="0"
    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
    referrerpolicy="strict-origin-when-cross-origin"
    allowfullscreen>
  </iframe>

  <p>Or direct HLS from MediaMTX:</p>
  <code>http://YOUR_SERVER:8888/camera/front_gate/index.m3u8</code>
</body>
</html>
