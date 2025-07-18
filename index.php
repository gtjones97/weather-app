<?php
$data = json_decode(file_get_contents('data.json'), true);
var_dump($data);

function getTempColor($temp_f) {
    if ($temp_f <= 32) return '#003366';
    if ($temp_f <= 50) return '#336699';
    if ($temp_f <= 65) return '#66aaff';
    if ($temp_f <= 75) return '#88cc88';
    if ($temp_f <= 85) return '#ffaa66';
    if ($temp_f <= 95) return '#ff7744';
    return '#cc2222';
}

$temp_color = getTempColor($data['temperature_f']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pi Weather Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      margin: 0;
      background: #f5f7fa;
      color: #222;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 2rem;
    }

    h1 {
      margin-bottom: 2rem;
      font-size: 2rem;
    }


    .card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      padding: 1.5rem 2rem;
      margin: 0.5rem;
      width: 100%;
      max-width: 300px;
      text-align: center;
    }

    .value {
      font-size: 2.5rem;
      font-weight: 600;
      margin: 0.5rem 0;
    }

    .label {
      font-size: 1rem;
      color: #666;
    }

    .timestamp {
      margin-top: 2rem;
      font-size: 0.9rem;
      color: #888;
    }

    @media (min-width: 700px) {
      .grid {
        display: flex;
        gap: 1rem;
        min-width: 900px;
      }
    }
  </style>
</head>
<body>
  <h1>Gabriel's Weather Station</h1>

  <div class="grid">
    <div class="card" style="background: <?= $temp_color ?>; color: #fff;">
      <div class="value"><?= $data['temperature_f'] ?>°F</div>
      <div class="label">Indoor Temperature</div>
    </div>

    <div class="card">
      <div class="value"><?= $data['humidity'] ?>%</div>
      <div class="label">Humidity</div>
    </div>

    <div class="card">
      <div class="value"><?= $data['pressure_hpa'] ?> hPa</div>
      <div class="label">Pressure</div>
    </div>
  </div>

  <div class="grid">
  <div class="card">
      <div class="value"><?= $data['temperatureOutside'] ?>°F</div>
      <div class="label">Outdoor Temperature</div>
    </div>

    <div class="card">
      <div class="value"><?= $data['pressure_hpa'] ?> hPa</div>
      <div class="label">Pressure3</div>
    </div>

    <div class="card">
      <div class="value"><?= $data['pressure_hpa'] ?> hPa</div>
      <div class="label">Pressure4</div>
    </div>
  </div>

  <div class="timestamp">
    Last updated: <?= date("F j, g:i A", strtotime($data['timestamp'])) ?>
  </div>
</body>
</html>
