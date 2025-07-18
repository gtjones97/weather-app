import time
import board
import busio
import adafruit_bme280
import json
import requests

from datetime import datetime

# Set up I2C communication on Pi GPIO pins
i2c = busio.I2C(board.SCL, board.SDA)

# Initialize the BME280 sensor over I2C
bme280 = adafruit_bme280.Adafruit_BME280_I2C(i2c)

# Read sensor data
temperature = round(bme280.temperature, 1)              # °C
temperature_f = round((temperature * 9 / 5) + 32, 1)     # °F
humidity = round(bme280.humidity, 1)                    # %
pressure = round(bme280.pressure, 1)                    # hPa

# Get outside temperature

location = 'Macon'
WttrUrl = f'https://wttr.in/{location}?format=3'

temperatureOutside = requests.get(WttrUrl)

# Package the data
data = {
    "temperature_c": temperature,
    "temperature_f": temperature_f,
    "humidity": humidity,
    "pressure_hpa": pressure,
    "temperatureOutside": temperatureOutside,
    "timestamp": datetime.now().isoformat()
}

# Write to JSON file
try:
    with open("/var/www/weather/data.json", "w") as f:
        json.dump(data, f)
    print("Data written to /var/www/weather/data.json")
except Exception as e:
    print(f"Error writing data: {e}")
