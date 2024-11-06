<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

// Thông tin kết nối tới MySQL database cho người dùng
$userServername = "localhost";
$userUsername = "root";
$userPassword = "H&ptiot2024";
$userDbname = "user";

// Thông tin kết nối tới MySQL database cho cảm biến
$sensorServername = "localhost";
$sensorUsername = "root";
$sensorPassword = "H&ptiot2024";
$sensorDbname = "sensor";

// Tạo kết nối tới database người dùng
$connUser = new mysqli($userServername, $userUsername, $userPassword, $userDbname);

// Tạo kết nối tới database cảm biến
$connSensor = new mysqli($sensorServername, $sensorUsername, $sensorPassword, $sensorDbname);

// Kiểm tra kết nối
if ($connUser->connect_error) {
    die("User database connection failed: " . $connUser->connect_error);
}
if ($connSensor->connect_error) {
    die("Sensor database connection failed: " . $connSensor->connect_error);
}

// Xử lý đăng ký người dùng
if (isset($_POST['register'])) {
    if (isset($_POST['reg_username']) && isset($_POST['reg_password'])) {
        $reg_username = $connUser->real_escape_string($_POST['reg_username']);
        $reg_password = password_hash($connUser->real_escape_string($_POST['reg_password']), PASSWORD_BCRYPT);

        $stmt = $connUser->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $reg_username, $reg_password);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Registration successful. <a href='#login'>Login here</a></div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='alert alert-warning'>Username and password are required for registration.</div>";
    }
}

// Xử lý đăng nhập người dùng
if (isset($_POST['login'])) {
    if (isset($_POST['login_username']) && isset($_POST['login_password'])) {
        $login_username = $connUser->real_escape_string($_POST['login_username']);
        $login_password = $connUser->real_escape_string($_POST['login_password']);

        $stmt = $connUser->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $login_username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashed_password);
            $stmt->fetch();
            if (password_verify($login_password, $hashed_password)) {
                $_SESSION['username'] = $login_username;
                header("Location: index.php");
                exit();
            } else {
                echo "<div class='alert alert-danger'>Invalid password</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Username not found</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='alert alert-warning'>Username and password are required for login.</div>";
    }
}

// Xử lý đăng xuất người dùng
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
if (isset($_POST['Next'])) {
    session_destroy();
    header("Location: XENON.php");
    exit();
}

// Xử lý thêm dữ liệu cảm biến
// Handle POST request to insert data into the Xenon_Sensor_Data table
// Xử lý POST request - lưu dữ liệu cảm biến vào cơ sở dữ liệu
// Xử lý POST request - lưu dữ liệu cảm biến vào cơ sở dữ liệu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['temperature1']) && isset($_POST['humidityAir1']) && isset($_POST['temperature2']) && isset($_POST['humidityAir2']) && isset($_POST['temperature3']) && isset($_POST['humidityAir3']) && isset($_POST['currentValue']) && isset($_POST['lon']) && isset($_POST['lat']) && isset($_POST['oprtime']) && isset($_POST['powerValue']) && isset($_POST['lightToggleCount']) && isset($_POST['opr_flag']) && isset($_POST['time'])) {

    // Nhận dữ liệu từ ESP thông qua POST
    $temperature1 = $_POST['temperature1'];
    $humidityAir1 = $_POST['humidityAir1'];
    $temperature2 = $_POST['temperature2'];
    $humidityAir2 = $_POST['humidityAir2'];
    $temperature3 = $_POST['temperature3'];
    $humidityAir3 = $_POST['humidityAir3'];
    $currentValue = $_POST['currentValue'];
    $lon = $_POST['lon'];
    $lat = $_POST['lat'];
    $oprtime = $_POST['oprtime'];
    $powerValue = $_POST['powerValue'];
    $lightToggleCount = $_POST['lightToggleCount'];
    $opr_flag = $_POST['opr_flag'];
    $time = $_POST['time'];

    // Chuẩn bị câu truy vấn SQL bao gồm các cột mới
    $stmt = $connSensor->prepare("INSERT INTO Xenon_Sensor_Data (nhiet_do1, do_am1, nhiet_do2, do_am2, nhiet_do3, do_am3, lon, lat, currentValue, powerValue, lightToggleCount, opr_flag, opr_time, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Liên kết các giá trị với các tham số trong câu truy vấn
    // Đảm bảo kiểu dữ liệu đúng: 'd' cho số thực và 's' cho chuỗi
    $stmt->bind_param("ddddddddddssss", $temperature1, $humidityAir1, $temperature2, $humidityAir2, $temperature3, $humidityAir3, $lon, $lat, $currentValue, $powerValue, $lightToggleCount, $opr_flag, $oprtime, $time);

    // Thực thi câu lệnh và kiểm tra thành công
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>New record created successfully</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    // Đóng câu lệnh chuẩn bị
    $stmt->close();
}


// API: Lấy dữ liệu sensor gần nhất (latest)
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['latest'])) {
    header('Content-Type: application/json');

    // Truy vấn SQL để lấy dữ liệu gần nhất với các cột mới
    $stmt = $connSensor->prepare("SELECT nhiet_do1, do_am1, nhiet_do2, do_am2, nhiet_do3, do_am3, lon, lat, currentValue, powerValue, lightToggleCount, opr_flag, opr_time, created_at FROM Xenon_Sensor_Data ORDER BY created_at DESC LIMIT 1");

    $stmt->execute();
    $result_latest = $stmt->get_result();

    // Kiểm tra nếu có kết quả và trả về dưới dạng JSON
    if ($result_latest->num_rows > 0) {
        $latest_data = $result_latest->fetch_assoc();
        echo json_encode($latest_data);
    } else {
        echo json_encode(["error" => "No data found"]);
    }
    $stmt->close();
    exit();
}

// API: Lấy toàn bộ dữ liệu sensor (all_data)
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['all_data'])) {
    header('Content-Type: application/json');

    // Truy vấn SQL để lấy toàn bộ dữ liệu với các cột mới
    $stmt = $connSensor->prepare("SELECT id, nhiet_do1, do_am1, nhiet_do2, do_am2, nhiet_do3, do_am3, lon, lat, currentValue, powerValue, lightToggleCount, opr_flag, opr_time, created_at FROM Xenon_Sensor_Data ORDER BY created_at DESC");

    $stmt->execute();
    $result = $stmt->get_result();

    // Lấy tất cả dữ liệu và trả về dưới dạng JSON
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
    $stmt->close();
    exit();
}




// Đóng kết nối
$connUser->close();
$connSensor->close();

// Kiểm tra xem người dùng đã đăng nhập chưa
$isLoggedIn = isset($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="Backend/styles.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <img src="Backend/XENON.jpg" alt="">
        <?php if (!$isLoggedIn): ?>
            <div class="card">
                <div class="card-header">
                    Register
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="mb-3">
                            <input type="text" name="reg_username" class="form-control" placeholder="Username" required>
                        </div>
                        <div class="mb-3">
                            <input type="password" name="reg_password" class="form-control" placeholder="Password" required>
                        </div>
                        <button type="submit" name="register" class="btn btn-primary">Register</button>
                    </form>
                    <hr>
                    <h5 id="login">Login</h5>
                    <form method="post" action="">
                        <div class="mb-3">
                            <input type="text" name="login_username" class="form-control" placeholder="Username" required>
                        </div>
                        <div class="mb-3">
                            <input type="password" name="login_password" class="form-control" placeholder="Password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary">Login</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    Sensor Dashboard
                </div>
                <div class="sensor-data-container">
                    <div class="sensor-data">
                        <p class="label">Temperature1</p>
                        <p class="value" id="temperature1">Loading...</p>
                        <p class="unit">°C</p>
                    </div>
                    <div class="sensor-data">
                        <p class="label">Humidity1</p>
                        <p class="value" id="humidityAir1">Loading...</p>
                        <p class="unit">%</p>
                    </div>
                    <div class="sensor-data">
                        <p class="label">Temperature2</p>
                        <p class="value" id="temperature2">Loading...</p>
                        <p class="unit">°C</p>
                    </div>
                    <div class="sensor-data">
                        <p class="label">Humidity2</p>
                        <p class="value" id="humidityAir2">Loading...</p>
                        <p class="unit">%</p>
                    </div>
                    <div class="sensor-data">
                        <p class="label">Temperature3</p>
                        <p class="value" id="temperature3">Loading...</p>
                        <p class="unit">°C</p>
                    </div>
                    <div class="sensor-data">
                        <p class="label">Humidity3</p>
                        <p class="value" id="humidityAir3">Loading...</p>
                        <p class="unit">%</p>
                    </div>
                </div>
                <p class="timestamp">Last updated: <span id="timestamp">Loading...</span></p>
            </div>

            <form method="post" action="" class="mt-3">
                <button type="submit" name="logout" class="btn btn-danger">Logout</button>
                <button type="submit" name="Next" class="btn btn-danger">XENON</button>
            </form>


            <div class="card mt-4">
                <div class="card-header">
                    All Sensor Data
                </div>
                <div class="card-body">
                    <table class="table table-bordered" id="sensor-data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Temperature1 (°C)</th>
                                <th>Humidity1 (%)</th>
                                <th>Temperature2 (°C)</th>
                                <th>Humidity2 (%)</th>
                                <th>Temperature3 (°C)</th>
                                <th>Humidity3 (%)</th>
                                <th>Longitude</th>
                                <th>Latitude</th>
                                <th>Current Value</th>
                                <th>TimeFlag</th> <!-- Thêm cột mới cho TimeFlag -->
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dữ liệu sẽ được thêm vào đây qua JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="Backend/script.js"></script>
</body>

</html>