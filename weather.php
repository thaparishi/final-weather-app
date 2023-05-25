<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
 
table{
  box-shadow: 2px 2px 5px  rgb(51, 51, 51);
  text-align: left;
  border-collapse: collapse;
  width:100%;
  margin:auto;
  }
  
  thead{
   box-shadow: 2px 2px 5px  rgb(51, 51, 51);
  }
  
  th{
  padding:1rem 1rem;
  text-transform: uppercase;
  letter-spacing: 0.1rem;
  font-family: 'Roboto', sans-serif;
  }
  
  td{
   padding:1rem 1rem;
   font-family: 'Roboto', sans-serif;
  }

    </style>
</head>
<body>

<?php

$conn = mysqli_connect("localhost", "root", "", "prototype2");
$sql = "CREATE TABLE IF NOT EXISTS weather (
    id INT AUTO_INCREMENT,
    cityName VARCHAR(255),
    temperature DECIMAL(10,2),
    pressure DECIMAL(10,2),
    humidity DECIMAL(10,2),
    wind DECIMAL(10,2),
    description VARCHAR(255),
    full_date DATETIME,
    PRIMARY KEY (id)
  );";

  mysqli_query($conn, $sql);

function fetchWeatherData($city_name, $conn) {

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $user_input = $city_name;
        $url = "https://api.openweathermap.org/geo/1.0/direct?q=" . urlencode($user_input) . "&appid=e6b3a54913bff6377a8e88fc919a3150";
        $response =  @file_get_contents($url);

        $data = json_decode($response, true);

        if ($data) {
            $location = $data[0];
            $name = $location['name'];
            $lat = $location['lat'];
            $lon = $location['lon'];

            if (isset($lat) && isset($lon)) {
                $startTimestamp = strtotime('-6days');
                $endTimestamp = time();
                
                $historical_url = "https://history.openweathermap.org/data/2.5/history/city?lat={$lat}&lon={$lon}&type=hour&start={$startTimestamp}&end={$endTimestamp}&cnt=168&appid=e6b3a54913bff6377a8e88fc919a3150&units=metric";
                $historical_data = json_decode(file_get_contents($historical_url), true);

                $prev_date = null;
                $filtered_data = array();

                foreach ($historical_data['list'] as $data) {
                    $unix_timestamp = $data['dt'];
                    $current_date = date("Y-m-d", $unix_timestamp);

                    if ($current_date !== $prev_date) {
                        $filtered_data[] = array(
                            'temperature' => $data['main']['temp'],
                            'pressure' => $data['main']['pressure'],
                            'humidity' => $data['main']['humidity'],
                            'wind' => $data['wind']['speed'],
                            'description' => $data['weather'][0]['description'],
                            'current_time' => date("H:i:s", $unix_timestamp),
                            'full_date' => $current_date,
                            'cityName' => $name
                        );
                        $prev_date = $current_date;
                    }
                }

                foreach ($filtered_data as $data) {
                    $check_duplicate_sql = "SELECT * FROM weather WHERE full_date = '{$data['full_date']}' AND cityName = '{$data['cityName']}'";
                    $duplicate_result = mysqli_query($conn, $check_duplicate_sql);

                    if (mysqli_num_rows($duplicate_result) === 0) {
                        $temperature = $data['temperature'];
                        $pressure = $data['pressure'];
                        $humidity = $data['humidity'];
                        $wind = $data['wind'];
                        $description = $data['description'];
                        $full_date = $data['full_date'];
                        $cityName = $data['cityName'];

                        $sql = "REPLACE INTO weather (temperature, pressure, humidity, wind, `description`, full_date, cityName)
                                VALUES ('$temperature', '$pressure', '$humidity', '$wind', '$description', '$full_date', '$cityName')";
                        mysqli_query($conn, $sql);
                    } else {
                        continue;
                    }
                }
            }
        }

        if ($conn) {
            // Search for the city name in the database
            $getdata_sql = "SELECT * FROM weather WHERE cityName LIKE '%$city_name%' ORDER BY id DESC ";
            $req_all_data = $conn->query($getdata_sql); 
        
            // Check if the query was successful
            if ($req_all_data) {
        
                // If the query was successful, echo the table
                echo '<table>';
                echo '<thead>';
                echo '<tr>';
                echo '<th>ID</th>';
                echo '<th>City Name</th>';
                echo '<th>Temperature</th>';
                echo '<th>Pressure</th>';
                echo '<th>Humidity</th>';
                echo '<th>Wind</th>';
                echo '<th>Description</th>';
                echo '<th>Date</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
        
                while ($data = mysqli_fetch_assoc($req_all_data)) {
                    $city_name = $data['cityName'];

                    echo '<tr>';
                    echo '<td>' . $data['id'] . '</td>';
                    echo '<td>' . $city_name . '</td>';
                    echo '<td>' . $data['temperature'] . '</td>';
                    echo '<td>' . $data['pressure'] . '</td>';
                    echo '<td>' . $data['humidity'] . '</td>';
                    echo '<td>' . $data['wind'] . '</td>';
                    echo '<td>' . $data['description'] . '</td>';
                    echo '<td>' . $data['full_date'] . '</td>';
                    echo '</tr>';
                }
        
                echo '</tbody>';
                echo '</table>';
            } else {
                // If the query was not successful, echo an error message
                echo "Could not get data";
            }
        } else {
            // If the connection was not successful, echo an error message
            echo "Could not connect to the database";
        }
    }
    else{
        $getdata_sql = "SELECT * FROM weather WHERE cityName = 'Belfast' ORDER BY id DESC ";
    $req_all_data = $conn->query($getdata_sql); 
        
    // Check if the query was successful
    if ($req_all_data) {
        
        // If the query was successful, echo the table
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>City Name</th>';
        echo '<th>Temperature</th>';
        echo '<th>Pressure</th>';
        echo '<th>Humidity</th>';
        echo '<th>Wind</th>';
        echo '<th>Description</th>';
        echo '<th>Date</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        while ($data = mysqli_fetch_assoc($req_all_data)) {
            echo '<tr>';
            echo '<td>' . $data['id'] . '</td>';
            echo '<td>' . $data['cityName'] . '</td>';
            echo '<td>' . $data['temperature'] . '</td>';
            echo '<td>' . $data['pressure'] . '</td>';
            echo '<td>' . $data['humidity'] . '</td>';
            echo '<td>' . $data['wind'] . '</td>';
            echo '<td>' . $data['description'] . '</td>';
            echo '<td>' . $data['full_date'] . '</td>';
            echo '<tr>';

            
        }
         echo '</tbody>';
         echo '</table>';
    }
 }
 
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $city_name = $_POST['city_name'];
    fetchWeatherData($city_name, $conn);
} else{
    fetchWeatherData("Belfast", $conn);
}


?>
</body>
</html>