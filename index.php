<?php
$data = json_decode(file_get_contents('data.json'), true);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Pi Weather Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: sans-serif;
      text-align: center;
      padding: 2rem;
      background: #eef1f5;
      color: #222;
    }
    .reading {
      font-size: 2em;
      margin: 1rem 0;
    }
    .label {
      font-weight: bold;
      color: #444;
    }
    .timestamp {
      margin-top: 2rem;
      color: #666;
      font-size: 0.9em;
    }
  </style>
</head>
<body>
  <h1>🌦️ Pi Weather Station</h1>

  <div class="reading"><span class="label">Temperature:</span> <?= $data['temperature_c'] ?> °C</div>
  <div class="reading"><span class="label">Humidity:</span> <?= $data['humidity'] ?> %</div>
  <div class="reading"><span class="label">Pressure:</span> <?= $data['pressure_hpa'] ?> hPa</div>
  <div class="timestamp">Last updated: <?= date("F j, g:i A", strtotime($data['timestamp'])) ?></div>
</body>
</html>