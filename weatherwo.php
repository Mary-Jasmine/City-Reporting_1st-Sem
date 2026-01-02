<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather in San Pablo City</title>

    <style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

    body.container {
    background: 
        linear-gradient(rgba(0, 0, 0, 0.727), rgba(0, 0, 0, 0.705)), 
        url('gunao.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    filter: blur();
}

.attr {
    position: absolute;
    bottom: 10px;
    left: 10px;
    background: rgba(0,0,0,0.5);
    color: #fff;
    padding: 6px 10px;
    border-radius: 12px;
    font-size: 14px;
    z-index: 10;
}
.attr a {
    color: #1dd1a1;
    text-decoration: none;
}
.attr a:hover {
    text-decoration: underline;
}

.container {
    background: #ffffffff;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    width: 100%;
    max-width: 1000px;
    transform: scale(0.85);       
    transform-origin: top center;
}

.weather-details {
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    gap: 8px;
    padding-top: 10px;
}
.weather-details.active {
    display: flex;
}


.weather-details .grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 8px;
}

.temperature {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.temperature .temperatureTxt {
    font-size: 28px;    
    display: flex;
    align-items: center;
    gap: 4px;
}
.temperature img {
    width: 100px;         
    height: auto;
}
#weather-condition-name {
    font-size: 16px;      
    margin-top: 4px;
}

.info {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 5px;
}
.info li {
    display: flex;
    align-items: center;
    gap: 6px;
}
.info li img {
    width: 40px;
    height: 40px;
}


.daily-cards {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    overflow-x: auto;
    gap: 8px;
    padding: 10px;
    max-width: 100%;
}
.daily-cards::-webkit-scrollbar {
    height: 6px;
}
.daily-cards::-webkit-scrollbar-thumb {
    background: #b64848ff;
    border-radius: 75px;
}
.daily-cards .card {
    width: 130px;        
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 6px;
    border-radius: 8px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
    background: #fafafa;
}
.daily-cards .card img {
    width: 70px;
    height: 70px;       
}
.daily-cards .card p {
    margin: 2px 0;
    font-size: 12px;     
}
.daily-cards .card .temps {
    width: 100%;
    display: flex;
    justify-content: space-between;
    font-size: 12px;   
}


.errTxt {
    color: #dc3545;
    font-size: 16px;
    font-weight: 500;
    text-align: center;
}

@media screen and (max-width: 768px) {
    .container {
        transform: scale(0.75);
    }
    .temperature img {
        width: 80px;
        height: auto;
    }
    .temperature .temperatureTxt {
        font-size: 24px;
    }
    .daily-cards .card {
        width: 110px;
    }
    .daily-cards .card img {
        width: 60px;
        height: 60px;
    }
}

@media screen and (max-width: 480px) {
    .container {
        transform: scale(0.7);
        padding: 10px;
    }
    .temperature img {
        width: 60px;
    }
    .temperature .temperatureTxt {
        font-size: 20px;
    }
    .daily-cards {
        gap: 5px;
        padding: 5px;
    }
    .daily-cards .card {
        width: 90px;
        padding: 4px;
    }
    .daily-cards .card img {
        width: 50px;
        height: 50px;
    }
    .info li img {
        width: 32px;
        height: 32px;
    }
    .errTxt {
        font-size: 14px;
    }



}

    </style>
</head>
<body class="weather-page">

<div class="container">

    <p class="errTxt" id="errTxt"></p>
    
    <div class="weather-details" id="weather-details">
        <h2>Weather In <span id="location">San Pablo City</span></h2>
        <div class="grid">
            
            <div class="temperature">
                <img src="https://arsentech.github.io/source-codes/weather-assets/clear.svg" alt="weather-icon" width="150" height="150" id="weather-condition-icon" />
                <p class="temperatureTxt">
                    <span id="temperature">0</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M16.5 5c1.55 0 3 .47 4.19 1.28l-1.16 2.89A4.47 4.47 0 0 0 16.5 8C14 8 12 10 12 12.5s2 4.5 4.5 4.5c1.03 0 1.97-.34 2.73-.92l1.14 2.85A7.47 7.47 0 0 1 16.5 20A7.5 7.5 0 0 1 9 12.5A7.5 7.5 0 0 1 16.5 5M6 3a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m0 2a1 1 0 0 0-1 1a1 1 0 0 0 1 1a1 1 0 0 0 1-1a1 1 0 0 0-1-1"/>
                    </svg>
                </p>
                <p id="weather-condition-name">Clear</p>
            </div>
            
            <ul class="info">
                <li><img src="https://arsentech.github.io/source-codes/weather-assets/humidity.svg" alt="humidity" width="64" height="64"><span id="humidity">0</span>%</li>
                <li><img src="https://arsentech.github.io/source-codes/weather-assets/wind.svg" alt="wind-speed" width="64" height="64"><span id="wind-speed">0</span>km/h</li>
            </ul>
        </div>
        
        <h2>Daily Forecast</h2>
        <div class="daily-cards" id="daily-forecast"></div>
    </div>
</div>


<script>
    const weather_codes = {
        0: { name: "Clear Sky", icons: { day: "clear.svg", night: "clear-night.svg" } },
        1: { name: "Mainly Clear", icons: { day: "clear.svg", night: "clear-night.svg" } },
        2: { name: "Partly Cloudy", icons: { day: "partly-cloudy.svg", night: "partly-cloudy-night.svg" } },
        3: { name: "Overcast", icons: { day: "overcast.svg", night: "overcast.svg" } },
        45: { name: "Fog", icons: { day: "fog.svg", night: "fog-night.svg" } },
        48: { name: "Rime Fog", icons: { day: "rime-fog.svg", night: "rime-fog.svg" } },
        51: { name: "Light Drizzle", icons: { day: "light-drizzle.svg", night: "light-drizzle.svg" } },
        53: { name: "Moderate Drizzle", icons: { day: "drizzle.svg", night: "drizzle.svg" } },
        55: { name: "Heavy Drizzle", icons: { day: "heavy-drizzle.svg", night: "heavy-drizzle.svg" } },
        56: { name: "Light Freezing Drizzle", icons: { day: "drizzle.svg", night: "drizzle.svg" } },
        57: { name: "Dense Freezing Drizzle", icons: { day: "heavy-drizzle.svg", night: "heavy-drizzle.svg" } },
        61: { name: "Slight Rain", icons: { day: "slight-rain.svg", night: "slight-rain-night.svg" } },
        63: { name: "Moderate Rain", icons: { day: "rain.svg", night: "rain.svg" } },
        65: { name: "Heavy Rain", icons: { day: "heavy-rain.svg", night: "heavy-rain.svg" } },
        66: { name: "Light Freezing Rain", icons: { day: "rain.svg", night: "rain.svg" } },
        67: { name: "Heavy Freezing Rain", icons: { day: "heavy-rain.svg", night: "heavy-rain.svg" } },
        71: { name: "Slight snowfall", icons: { day: "light-snow.svg", night: "light-snow-night.svg" } },
        73: { name: "Moderate snowfall", icons: { day: "snow.svg", night: "snow.svg" } },
        75: { name: "Heavy snowfall", icons: { day: "heavy-snow.svg", night: "heavy-snow.svg" } },
        77: { name: "Snow Grains", icons: { day: "snow-grains.svg", night: "snow-grains.svg" } },
        80: { name: "Slight Rain Showers", icons: { day: "slight-rain-showers.svg", night: "slight-rain-showers-night.svg" } },
        81: { name: "Moderate Rain Showers", icons: { day: "rain-showers.svg", night: "rain-showers.svg" } },
        82: { name: "Violent Rain Showers", icons: { day: "heavy-rain-showers.svg", night: "heavy-rain-showers.svg" } },
        85: { name: "Light Snow Showers", icons: { day: "light-snow-showers.svg", night: "light-snow-showers.svg" } },
        86: { name: "Heavy Snow Showers", icons: { day: "heavy-snow-showers.svg", night: "heavy-snow-showers.svg" } },
        95: { name: "Thunderstorm", icons: { day: "thunderstorm.svg", night: "thunderstorm.svg" } },
        96: { name: "Slight Hailstorm", icons: { day: "hail.svg", night: "hail.svg" } },
        99: { name: "Heavy Hailstorm", icons: { day: "heavy-hail.svg", night: "heavy-hail.svg" } }
    };
    
    const weatherDetailsElem = document.getElementById("weather-details");
    const locationTxt = document.getElementById("location");
    const weatherCondIcon = document.getElementById("weather-condition-icon");
    const weatherCondName = document.getElementById("weather-condition-name");
    const temperatureTxt = document.getElementById("temperature");
    const humidityTxt = document.getElementById("humidity");
    const windSpeedTxt = document.getElementById("wind-speed");
    const dailyForecastElems = document.getElementById("daily-forecast");
    const errTxt = document.getElementById("errTxt");

    const FIXED_LOCATION = "San Pablo City, Philippines"; 

    async function getLocation(location) {
        const res = await fetch(
            `https://geocoding-api.open-meteo.com/v1/search?name=${location}&count=1&language=en&format=json`
        );
        const data = await res.json();
        if (!data.results || data.results.length === 0) {
            throw new Error("Location not found");
        }
        const result = data.results[0];
        return {
            name: result.name || "",
            lat: result.latitude,
            lon: result.longitude
        };
    }
    
    async function getWeather(location) {
        const { lat, lon, name } = await getLocation(location);
        const res = await fetch(
            `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,relative_humidity_2m,is_day,weather_code,wind_speed_10m&daily=weather_code,temperature_2m_max,temperature_2m_min&timezone=Asia%2FSingapore`
        );
        const data = await res.json();
        return {
            name,
            current: data.current,
            daily: data.daily
        };
    }

    async function displayWeather(location) {
        weatherDetailsElem.classList.remove("active");
        dailyForecastElems.innerHTML = "";
        errTxt.textContent = "";

        try {
            const weather = await getWeather(location);
            const {
                temperature_2m,
                relative_humidity_2m,
                is_day,
                weather_code,
                wind_speed_10m
            } = weather.current;
            const {
                weather_code: daily_weather_code,
                temperature_2m_max,
                temperature_2m_min,
                time
            } = weather.daily;

            const weatherCondition = weather_codes[weather_code];
            const imgSrc = `https://arsentech.github.io/source-codes/weather-assets/${
                is_day ? weatherCondition.icons.day : weatherCondition.icons.night
            }`;

            locationTxt.textContent = weather.name;
            temperatureTxt.textContent = Math.round(temperature_2m);
            humidityTxt.textContent = relative_humidity_2m;
            windSpeedTxt.textContent = wind_speed_10m;
            weatherCondName.textContent = weatherCondition.name;
            weatherCondIcon.src = imgSrc;

            for (let i = 0; i < 7; i++) {
                const weatherCond = weather_codes[daily_weather_code[i]];
                const temperatureMax = Math.round(temperature_2m_max[i]);
                const temperatureMin = Math.round(temperature_2m_min[i]);
                const dateOptions = { weekday: 'short', month: 'short', day: 'numeric' };
                const timestamp = new Date(time[i]).toLocaleDateString('en-US', dateOptions);

                const elem = document.createElement("div");
                elem.className = "card";
                elem.innerHTML = `
                    <p>${weatherCond.name}</p>
                    <div class="temps">
                        <p class="temp" title="Maximum Temperature">${temperatureMax}<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M16.5 5c1.55 0 3 .47 4.19 1.28l-1.16 2.89A4.47 4.47 0 0 0 16.5 8C14 8 12 10 12 12.5s2 4.5 4.5 4.5c1.03 0 1.97-.34 2.73-.92l1.14 2.85A7.47 7.47 0 0 1 16.5 20A7.5 7.5 0 0 1 9 12.5A7.5 7.5 0 0 1 16.5 5M6 3a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m0 2a1 1 0 0 0-1 1a1 1 0 0 0 1 1a1 1 0 0 0 1-1a1 1 0 0 0-1-1"/></svg></p>
                        <p class="temp" title="Minimum Temperature">${temperatureMin}<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M16.5 5c1.55 0 3 .47 4.19 1.28l-1.16 2.89A4.47 4.47 0 0 0 16.5 8C14 8 12 10 12 12.5s2 4.5 4.5 4.5c1.03 0 1.97-.34 2.73-.92l1.14 2.85A7.47 7.47 0 0 1 16.5 20A7.5 7.5 0 0 1 9 12.5A7.5 7.5 0 0 1 16.5 5M6 3a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m0 2a1 1 0 0 0-1 1a1 1 0 0 0 1 1a1 1 0 0 0 1-1a1 1 0 0 0-1-1"/></svg></p>
                    </div>
                    <p class="date">${timestamp}</p>`;
                dailyForecastElems.appendChild(elem);
            }
            weatherDetailsElem.classList.add("active");
        } catch (error) {
            console.error(error);
            errTxt.textContent = "Could not fetch weather data for San Pablo City.";
        }
    }
    window.onload = () => {
        displayWeather(FIXED_LOCATION);
    };

</script>
</body>
</html>