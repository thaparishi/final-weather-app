const search_btn = document.querySelector("input");
const form = document.querySelector("form");
const name_of_city = document.querySelector(".city-name");
const weathers_datas = document.querySelector(".weather-datas");
const section = document.querySelector(".main-section");
const container = document.querySelector(".container");
const full_date = document.querySelector('.full_date')
const current_time = document.querySelector('.current_time')
const icon = document.querySelector('.fa-solid ')
const apiKey = "e6b3a54913bff6377a8e88fc919a3150"

function unixToTime(data) {
  const timezoneOffset = data.timezone; // replace with the timezone offset from the OpenWeather API response
  const unixTimestamp = data.dt; // replace with the Unix timestamp from the OpenWeather API response

  // Create a new Date object using the Unix timestamp and timezone offset
  const date = new Date((unixTimestamp + timezoneOffset) * 1000);

  // Format the date and time string using Intl.DateTimeFormat
  const formattedDateTime = new Intl.DateTimeFormat('en-US', {
    timeStyle: 'medium',
    timeZone: 'UTC' // set to 'UTC' to display the time in the location's timezone
  }).format(date);

  current_time.innerHTML = "Time: " + formattedDateTime
}

function unixToFullDate(data) {
  const timezoneOffset = data.timezone;
  const unixTimestamp = data.dt;

  const date = new Date((unixTimestamp + timezoneOffset) * 1000);

  const formattedDate = new Intl.DateTimeFormat('en-US', {
    dateStyle: 'medium',
    timeZone: 'UTC'
  }).format(date);

  full_date.innerHTML = "Date: " + formattedDate
}

async function showDefaultWeather() {
  const storedData = JSON.parse(localStorage.getItem("current"));

  if (navigator.onLine) {
    if (storedData) {
      console.log("Default data has been retrieved from local storage successfully");
      mappedDatas(storedData);
      unixToTime(storedData);
      unixToFullDate(storedData);
    } else {
      const response = await fetch(
        `https://api.openweathermap.org/data/2.5/weather?q=Belfast&appid=${apiKey}&units=metric`
      );
      const data = await response.json();

      if (data.cod === "404") {
        weathers_datas.innerHTML = `<p>${data.message}</p>`;
        return;
      }

      localStorage.setItem("current", JSON.stringify(data));
      mappedDatas(data);
      unixToTime(data);
      unixToFullDate(data);
    }
  } else {
    if (storedData) {
      console.log("Default data has been retrieved from local storage successfully");
      mappedDatas(storedData);
      unixToTime(storedData);
      unixToFullDate(storedData);
    }
  }
}

showDefaultWeather();


///////////////////////////show weather data after searching//////////////

let arr = JSON.parse(localStorage.getItem("searched")) || [];

async function showWeather(cityName) {
  if (navigator.onLine) {
    // User is online, check if data is already in local storage
    const storedData = JSON.parse(localStorage.getItem("searched"));
    if (storedData && storedData.length > 0) {
      let cityData = null;
      for (let i = 0; i < storedData.length; i++) {
        if (storedData[i].name.toLowerCase() === cityName.toLowerCase()) {
          console.log(" data fetched from local storage")
          cityData = storedData[i];
          break;
        }
      }
      if (cityData !== null) {
        // Data found in local storage, use it
        mappedDatas(cityData);
        unixToTime(cityData);
        unixToFullDate(cityData);
        return;
      }
    }

    // Data not found in local storage, make an API call
    const response = await fetch(
      `https://api.openweathermap.org/data/2.5/weather?q=${cityName}&appid=${apiKey}&units=metric`
    );
    const data = await response.json();


    if (data.cod !== "404") {
      arr.push(data);
      localStorage.setItem("searched", JSON.stringify(arr));
    }

    if (data.cod === "404") {
      weathers_datas.innerHTML = `<p>${data.message}</p>`;
      return;
    }

    mappedDatas(data);
    unixToTime(data);
    unixToFullDate(data);
    console.log("data fetched from api")
  } else {
    // User is offline, check if data is already in local storage
    const storedData = JSON.parse(localStorage.getItem("searched"));
    if (storedData) {
      console.log("Searched data fetched from local storage")
    }
    if (storedData && storedData.length > 0) {
      let cityData = null;
      for (let i = 0; i < storedData.length; i++) {
        if (storedData[i].name.toLowerCase() === cityName.toLowerCase()) {
          cityData = storedData[i];
          break;
        }
      }
      if (cityData !== null) {
        // Data found in local storage, use it
        mappedDatas(cityData);
        unixToTime(cityData);
        unixToFullDate(cityData);
      } else {
        weathers_datas.innerHTML = `<p>Sorry data for  ${cityName} has not been stored in local host yet!</p>`;
      }
    }
  }
}


function mappedDatas(datas) {
  name_of_city.innerHTML = `City : ${datas.name}`;
  weathers_datas.innerHTML = `
  <div class = "main-temprature">
                  <h1>${datas.main.temp}°</h1>
                  <img src = "http://openweathermap.org/img/w/${datas.weather[0].icon}.png">
              </div>
              <div class = "row">
                  <div class = "column">
                  <p class = "paragraph">Pressure</p>
                  <span>${datas.main.pressure} hPa</span>
                  </div>

                  <div class = "column">
                      <p class = "paragraph">WindSpeed</p>
                      <span>${datas.wind.speed} m/s</span>
                  </div>

                  <div class = "column">
                      <p class = "paragraph">Humidity</p>
                      <span>${datas.main.humidity}%</span>
                  </div>

                  <div class = "column">
                      <p class = "paragraph">Weather Condition </p>
                      <span>${datas.weather[0].description}</span>
                  </div>
  `;
}


icon.addEventListener('click', (e) => {
  e.preventDefault()
  showWeather(search_btn.value);
  console.log("hello");
})

form.addEventListener('submit', () => {
  showWeather(search_btn.value);
})
