<?php
$data = json_decode(file_get_contents('data.json'), true);
var_dump($data);

// Open-Meteo API call for daily precipitation probability
$lat = "32.84";
$lon = "-83.63";
// Open-Meteo API call for daily and hourly precipitation probability and precipitation
$rainfall_api = "https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lon&daily=precipitation_probability_max&hourly=precipitation_probability,precipitation&timezone=auto";
$weather_response = file_get_contents($rainfall_api);
$weather_data = json_decode($weather_response, true);
$daily_rain_chance = $weather_data['daily']['precipitation_probability_max'][0] ?? 'N/A';

// Parse hourly rain forecast for today
$hourly_times = $weather_data['hourly']['time'] ?? [];
$hourly_precip = $weather_data['hourly']['precipitation'] ?? [];
$hourly_prob = $weather_data['hourly']['precipitation_probability'] ?? [];
$today_date = date('Y-m-d');
$rain_forecast_hours = [];
for ($i = 0; $i < count($hourly_times); $i++) {
    $time = $hourly_times[$i];
    if (strpos($time, $today_date) === 0) {
        $precip = $hourly_precip[$i] ?? 0;
        $prob = $hourly_prob[$i] ?? 0;
        if ($precip > 0.1 || $prob > 40) {
            // Format hour as h AM/PM
            $hour_fmt = date('g A', strtotime($time));
            $rain_forecast_hours[] = [
                'time' => $hour_fmt,
                'precip' => $precip,
                'prob' => $prob
            ];
        }
    }
}

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
      <div class="value"><?= $daily_rain_chance ?>%</div>
      <div class="label">Max Rain Chance for Today</div>
    </div>

    <div class="card">
      <div class="value"><?= $data['pressure_hpa'] ?> hPa</div>
      <div class="label">Pressure4</div>
    </div>
  </div>

  <div class="grid">
    <div class="card">
      <div class="label" style="font-weight: bold; margin-bottom: 0.5em;">Today's Rain Forecast by Hour</div>
      <?php if (count($rain_forecast_hours) > 0): ?>
        <ul style="list-style:none; padding:0; margin:0;">
          <?php foreach ($rain_forecast_hours as $rf): ?>
            <li>
              <span style="font-weight:600;"><?= htmlspecialchars($rf['time']) ?></span>:
              <?= number_format($rf['prob']) ?>% chance,
              <?= number_format($rf['precip'], 2) ?> mm
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <div>No significant rain expected today.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="timestamp">
    Last updated: <?= date("F j, g:i A", strtotime($data['timestamp'])) ?>
  </div>
</body>
</html>
