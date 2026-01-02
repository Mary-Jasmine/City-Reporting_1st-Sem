<style>
    * {
  margin: 50px;
  padding: 0;
  box-sizing: border-box;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}
body {
  position: relative;
  min-height: 50px;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 15px;
}
.attr {
  position: absolute;
  bottom: 15px;
  left: 15px;
  background: rgba(0, 0, 0, 0.5);
  color: #fff;
  padding: 10px;
  border-radius: 15px;
  font-size: 18px;
}
.attr a {
  color: #1dd1a1;
  text-decoration: none;
}
.attr a:hover {
  text-decoration: underline;
}
.container {
  background: #fafafa;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 5px 25px rgba(0, 0, 0, 0.13);
  width: 100%;
  max-width: 512px;
}
.container .search-box {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  gap: 5px;
  margin: 10px 0;
  width: 100%;
}
.container .search-box :where(input, button) {
  flex-grow: 1;
  flex-shrink: 0;
  border-radius: 10px;
  padding: 0.5em;
  transition: 0.3s;
}
.container .search-box input {
  font-size: 16px;
  border: 1px solid #bbb;
  outline: none;
  transition: 0.3s;
}
.container .search-box input:focus {
  border-color: #046c17ff;
  box-shadow: 0 0 5px #0c6b01ff;
}
.container .search-box button {
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #046c17ff;
  background: #046c17a8;
  cursor: pointer;
}
.container .search-box button:hover {
  background: #017556;
  border-color: #017556;
  color: #fff;
}
.container .weather-details {
  display: none;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  gap: 5px;
}
.container .weather-details.active {
  display: flex;
}
.container .weather-details .grid {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 10px;
}
.container .weather-details .temperature {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
}
.container .weather-details .temperature .temperatureTxt {
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 40px;
}
.container .weather-details .info {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  width: 100%;
}
.container .weather-details .info li {
  display: flex;
  align-items: center;
  gap: 10px;
}
.container .daily-cards {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  max-width: 512px;
  overflow-x: auto;
  padding: 15px;
  gap: 10px;
}
.container .daily-cards::-webkit-scrollbar {
  height: 10px;
}
.container .daily-cards::-webkit-scrollbar-thumb {
  background: #bbb;
  border-radius: 75px;
}
.container .daily-cards .card {
  width: 180px;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  box-shadow: 0 5px 10px rgba(0, 0, 0, 0.5);
  padding: 10px;
  border-radius: 10px;
  background: #fafafa;
}
.container .daily-cards .card p {
  margin: 3px 0;
}
.container .daily-cards .card .temp {
  display: flex;
  align-items: center;
}
.container .daily-cards .card .temp svg {
  width: 16px;
  height: 16px;
}
.container .daily-cards .card .temps {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}
.errTxt {
  color: #dc3545;
  font-size: 20px;
  font-weight: 500;
}
@media screen and (max-width: 500px) {
  .container .daily-cards {
    max-width: 275px;
  }
  .attr {
    width: 100%;
    bottom: 0;
    left: 0;
    border-radius: 0;
    text-align: center;
  }
}
@media screen and (max-width: 400px) {
  .container .weather-details .grid {
    width: 100%;
    grid-template-columns: auto;
  }
  .container .weather-details .info {
    width: 100%;
    flex-direction: row;
    justify-content: space-between;
  }
  .container .weather-details .info li {
    gap: 5px;
  }
}

</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="weather-page"></body>

<div class="container">
  <h1>Weather</h1>
  <form class="search-box" id="search-box">
    <input type="text" name="location" placeholder="Search..." id="location-input">
    <button type="submit" aria-label="Search"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
        <path fill="currentColor" d="m19.6 21l-6.3-6.3q-.75.6-1.725.95T9.5 16q-2.725 0-4.612-1.888T3 9.5t1.888-4.612T9.5 3t4.613 1.888T16 9.5q0 1.1-.35 2.075T14.7 13.3l6.3 6.3zM9.5 14q1.875 0 3.188-1.312T14 9.5t-1.312-3.187T9.5 5T6.313 6.313T5 9.5t1.313 3.188T9.5 14" />
      </svg></button>
  </form>
  <p class="errTxt" id="errTxt"></p>
  <div class="weather-details" id="weather-details">
    <h2>Weather In <span id="location">Location Name</span></h2>
    <div class="grid">
      <div class="temperature">
        <img src="https://arsentech.github.io/source-codes/weather-assets/clear.svg" alt="weather-icon" width="150" height="150" id="weather-condition-icon" />
        <p class="temperatureTxt"><span id="temperature">0</span><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
            <path fill="currentColor" d="M16.5 5c1.55 0 3 .47 4.19 1.28l-1.16 2.89A4.47 4.47 0 0 0 16.5 8C14 8 12 10 12 12.5s2 4.5 4.5 4.5c1.03 0 1.97-.34 2.73-.92l1.14 2.85A7.47 7.47 0 0 1 16.5 20A7.5 7.5 0 0 1 9 12.5A7.5 7.5 0 0 1 16.5 5M6 3a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m0 2a1 1 0 0 0-1 1a1 1 0 0 0 1 1a1 1 0 0 0 1-1a1 1 0 0 0-1-1" />
          </svg></p>
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
<p class="attr">Image by <a href="https://pixabay.com/users/elg21-3764790/?utm_source=link-attribution&utm_medium=referral&utm_campaign=image&utm_content=5534319">Enrique</a> from <a href="https://pixabay.com//?utm_source=link-attribution&utm_medium=referral&utm_campaign=image&utm_content=5534319">Pixabay</a></p>


<script>
    const weather_codes = {
  0: {
    name: "Clear Sky",
    icons: {
      day: "clear.svg",
      night: "clear-night.svg"
    }
  },
  1: {
    name: "Mainly Clear",
    icons: {
      day: "clear.svg",
      night: "clear-night.svg"
    }
  },
  2: {
    name: "Partly Cloudy",
    icons: {
      day: "partly-cloudy.svg",
      night: "partly-cloudy-night.svg"
    }
  },
  3: {
    name: "Overcast",
    icons: {
      day: "overcast.svg",
      night: "overcast.svg"
    }
  },
  45: {
    name: "Fog",
    icons: {
      day: "fog.svg",
      night: "fog-night.svg"
    }
  },
  48: {
    name: "Rime Fog",
    icons: {
      day: "rime-fog.svg",
      night: "rime-fog.svg"
    }
  },
  51: {
    name: "Light Drizzle",
    icons: {
      day: "light-drizzle.svg",
      night: "light-drizzle.svg"
    }
  },
  53: {
    name: "Moderate Drizzle",
    icons: {
      day: "drizzle.svg",
      night: "drizzle.svg"
    }
  },
  55: {
    name: "Heavy Drizzle",
    icons: {
      day: "heavy-drizzle.svg",
      night: "heavy-drizzle.svg"
    }
  },
  56: {
    name: "Light Freezing Drizzle",
    icons: {
      day: "drizzle.svg",
      night: "drizzle.svg"
    }
  },
  57: {
    name: "Dense Freezing Drizzle",
    icons: {
      day: "heavy-drizzle.svg",
      night: "heavy-drizzle.svg"
    }
  },
  61: {
    name: "Slight Rain",
    icons: {
      day: "slight-rain.svg",
      night: "slight-rain-night.svg"
    }
  },
  63: {
    name: "Moderate Rain",
    icons: {
      day: "rain.svg",
      night: "rain.svg"
    }
  },
  65: {
    name: "Heavy Rain",
    icons: {
      day: "heavy-rain.svg",
      night: "heavy-rain.svg"
    }
  },
  66: {
    name: "Light Freezing Rain",
    icons: {
      day: "rain.svg",
      night: "rain.svg"
    }
  },
  67: {
    name: "Heavy Freezing Rain",
    icons: {
      day: "heavy-rain.svg",
      night: "heavy-rain.svg"
    }
  },
  71: {
    name: "Slight snowfall",
    icons: {
      day: "light-snow.svg",
      night: "light-snow-night.svg"
    }
  },
  73: {
    name: "Moderate snowfall",
    icons: {
      day: "snow.svg",
      night: "snow.svg"
    }
  },
  75: {
    name: "Heavy snowfall",
    icons: {
      day: "heavy-snow.svg",
      night: "heavy-snow.svg"
    }
  },
  77: {
    name: "Snow Grains",
    icons: {
      day: "snow-grains.svg",
      night: "snow-grains.svg"
    }
  },
  80: {
    name: "Slight Rain Showers",
    icons: {
      day: "slight-rain-showers.svg",
      night: "slight-rain-showers-night.svg"
    }
  },
  81: {
    name: "Moderate Rain Showers",
    icons: {
      day: "rain-showers.svg",
      night: "rain-showers.svg"
    }
  },
  82: {
    name: "Violent Rain Showers",
    icons: {
      day: "heavy-rain-showers.svg",
      night: "heavy-rain-showers.svg"
    }
  },
  85: {
    name: "Light Snow Showers",
    icons: {
      day: "light-snow-showers.svg",
      night: "light-snow-showers.svg"
    }
  },
  86: {
    name: "Heavy Snow Showers",
    icons: {
      day: "heavy-snow-showers.svg",
      night: "heavy-snow-showers.svg"
    }
  },
  95: {
    name: "Thunderstorm",
    icons: {
      day: "thunderstorm.svg",
      night: "thunderstorm.svg"
    }
  },
  96: {
    name: "Slight Hailstorm",
    icons: {
      day: "hail.svg",
      night: "hail.svg"
    }
  },
  99: {
    name: "Heavy Hailstorm",
    icons: {
      day: "heavy-hail.svg",
      night: "heavy-hail.svg"
    }
  }
};
const searchBox = document.getElementById("search-box");
const weatherDetailsElem = document.getElementById("weather-details");
const locationTxt = document.getElementById("location");
const weatherCondIcon = document.getElementById("weather-condition-icon");
const weatherCondName = document.getElementById("weather-condition-name");
const temperatureTxt = document.getElementById("temperature");
const humidityTxt = document.getElementById("humidity");
const windSpeedTxt = document.getElementById("wind-speed");
const locationInput = document.getElementById("location-input");
const dailyForecastElems = document.getElementById("daily-forecast");
const errTxt = document.getElementById("errTxt");
async function getLocation(location) {
  const res = await fetch(
    `https://geocoding-api.open-meteo.com/v1/search?name=${location}&count=1&language=en&format=json`
  );
  const data = await res.json();
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
    `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,relative_humidity_2m,is_day,weather_code,wind_speed_10m&daily=weather_code,temperature_2m_max,temperature_2m_min`
  );
  const data = await res.json();
  return {
    name,
    current: data.current,
    daily: data.daily
  };
}
searchBox.addEventListener("submit", async (e) => {
  e.preventDefault();
  weatherDetailsElem.classList.remove("active");
  dailyForecastElems.innerHTML = "";
  if (locationInput.value.trim() === "") {
    errTxt.textContent = "Please Enter a Location To Get Weather Details";
  } else {
    errTxt.textContent = "";
    try {
      const weather = await getWeather(locationInput.value);
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
      temperatureTxt.textContent = temperature_2m;
      humidityTxt.textContent = relative_humidity_2m;
      windSpeedTxt.textContent = wind_speed_10m;
      weatherCondName.textContent = weatherCondition.name;
      weatherCondIcon.src = imgSrc;
      for (let i = 0; i < 7; i++) {
        const weatherCond = weather_codes[daily_weather_code[i]];
        const temperatureMax = temperature_2m_max[i];
        const temperatureMin = temperature_2m_min[i];
        const timestamp = time[i];
        const elem = document.createElement("div");
        elem.className = "card";
        elem.innerHTML = `<img src="https://arsentech.github.io/source-codes/weather-assets/${weatherCond.icons.day}" alt="weather-icon" width="100" height="100"/>
                    <div class="temps">
                         <p class="temp" title="Maximum Temperature">${temperatureMax}<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M16.5 5c1.55 0 3 .47 4.19 1.28l-1.16 2.89A4.47 4.47 0 0 0 16.5 8C14 8 12 10 12 12.5s2 4.5 4.5 4.5c1.03 0 1.97-.34 2.73-.92l1.14 2.85A7.47 7.47 0 0 1 16.5 20A7.5 7.5 0 0 1 9 12.5A7.5 7.5 0 0 1 16.5 5M6 3a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m0 2a1 1 0 0 0-1 1a1 1 0 0 0 1 1a1 1 0 0 0 1-1a1 1 0 0 0-1-1"/></svg></p>
                         <p class="temp" title="Minimum Temperature">${temperatureMin}<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M16.5 5c1.55 0 3 .47 4.19 1.28l-1.16 2.89A4.47 4.47 0 0 0 16.5 8C14 8 12 10 12 12.5s2 4.5 4.5 4.5c1.03 0 1.97-.34 2.73-.92l1.14 2.85A7.47 7.47 0 0 1 16.5 20A7.5 7.5 0 0 1 9 12.5A7.5 7.5 0 0 1 16.5 5M6 3a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m0 2a1 1 0 0 0-1 1a1 1 0 0 0 1 1a1 1 0 0 0 1-1a1 1 0 0 0-1-1"/></svg></p>
                    </div>
                    <p class="date">${timestamp}</p>`;
        dailyForecastElems.appendChild(elem);
      }
      weatherDetailsElem.classList.add("active");
    } catch {
      errTxt.textContent = "Location Not Found";
    }
  }
});
</script>
</body>
</html>