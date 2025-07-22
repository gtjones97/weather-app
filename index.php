<?php
date_default_timezone_set('America/New_York');

$data = json_decode(file_get_contents('data.json'), true);

// Open-Meteo API call for daily precipitation probability, sunrise, and sunset
$lat = "32.84";
$lon = "-83.63";
// Add daily=sunrise,sunset,precipitation_probability_max to the API call
$rainfall_api = "https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lon&daily=precipitation_probability_max,sunrise,sunset&hourly=precipitation_probability,precipitation&timezone=America/New_York";
$weather_response = file_get_contents($rainfall_api);
$weather_data = json_decode($weather_response, true);
$daily_rain_chance = $weather_data['daily']['precipitation_probability_max'][0] ?? 'N/A';

// Parse sunrise and sunset for today
$sunrise_time = null;
$sunset_time = null;
if (isset($weather_data['daily']['sunrise'][0])) {
    $sunrise_time = date('g:i A', strtotime($weather_data['daily']['sunrise'][0]));
}
if (isset($weather_data['daily']['sunset'][0])) {
    $sunset_time = date('g:i A', strtotime($weather_data['daily']['sunset'][0]));
}

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
        if ($precip > 0.1 || $prob > 15) {
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

// Fetch current weather alerts from National Weather Service
$alerts_api = "https://api.weather.gov/alerts/active?point=$lat,$lon";
$alerts_response = @file_get_contents($alerts_api);
$alert_event = null;
$alert_headline = null;
if ($alerts_response !== false) {
    $alerts_data = json_decode($alerts_response, true);
    if (!empty($alerts_data['features']) && is_array($alerts_data['features'])) {
        $first_alert = $alerts_data['features'][0]['properties'] ?? null;
        if ($first_alert) {
            $alert_event = $first_alert['event'] ?? null;
            $alert_headline = $first_alert['headline'] ?? null;
        }
    }
}
if (!$alert_event && !$alert_headline) {
    $alert_event = "No active alerts.";
    $alert_headline = "";
}

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
      font-size: 2rem;
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

    .alert-card {
      border: 2px solid #cc2222;
      color: #cc2222;
      max-width: 300px;
      background: #fff0f0;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(204, 34, 34, 0.2);
      padding: 1.5rem 2rem;
      margin: 0.5rem;
      text-align: center;
    }

    .alert-label {
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    .alert-headline {
      font-weight: 600;
      margin-top: 0.5rem;
      font-size: 1rem;
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
      <div class="value">
        <?php if ($sunrise_time): ?>
          🌅 <?= $sunrise_time ?><br>
        <?php endif; ?>
        <?php if ($sunset_time): ?>
          🌇 <?= $sunset_time ?>
        <?php endif; ?>
      </div>
      <div class="label"></div>
    </div>
  </div>

  <div class="grid">
    <div class="card" style="min-width: 516px;">
    <div class="label" style="margin-bottom: 0.5em;">Today's Expected Rain Hours</div>
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

    <div class="<?= $alert_event !== 'No active alerts.' ? 'alert-card' : 'card' ?>">
    <div class="alert-label">🚨 Weather Alerts</div>
      <div class="alert-headline"><?= htmlspecialchars($alert_event) ?></div>
      <?php if ($alert_headline): ?>
        <div><?= htmlspecialchars($alert_headline) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="timestamp">
    Last updated: <?= date("F j, g:i A", strtotime($data['timestamp'])) ?>
  </div>
</body>
</html>