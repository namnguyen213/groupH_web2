<?php
//Chức năng đăng nhập bằng username hoặc email
function login()
{
    global $conn, $username, $errors;
    // gọi hàm escape đã được định nghĩa
    $username = escape($_POST['username']);
    $password = escape($_POST['password']);
    // make sure form is filled properly
    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($password)) {
        array_push($errors, "Password is required");
    }
    // attempt login if no errors on form
    if (count($errors) == 0) {
        //ứng dụng hàm md5() mã hóa password
        $password = md5($password);
        $query = "SELECT * FROM users WHERE username='$username' AND password='$password' LIMIT 1";
        $query2 = "SELECT * FROM users WHERE username='$username' AND password='$password'";
        $results = mysqli_query($conn, $query);
        $results2 = mysqli_query($conn, $query2);
        $row = mysqli_fetch_array($results2);
        if (mysqli_num_rows($results) == 1) { // user found
            // check if user is admin or user
            $logged_in_user = mysqli_fetch_assoc($results);
            if ($logged_in_user['user_type'] == 'admin') {
                $_SESSION['user'] = $logged_in_user;
                $_SESSION['success']  = "You are now logged in by Admin";
                if (isset($_POST['remember'])) {
                    //thiết lập cookie username và password
                    setcookie("user", $row['username'], time() + (86400 * 30));
                    setcookie("pass", $row['password'], time() + (86400 * 30));
                }
                header('location: home.php');
            } else {
                $_SESSION['user'] = $logged_in_user;
                $_SESSION['success']  = "You are now logged in";
                if (isset($_POST['remember'])) {
                    //thiết lập cookie username và password
                    setcookie("user", $row['username'], time() + (86400 * 30));
                    setcookie("pass", $row['password'], time() + (86400 * 30));
                }
                header('location: index.php');
            }
        } else {
            array_push($errors, "Wrong username/password combination");
        }
    }
}

//Kiểm tra trùng username
if (mysqli_num_rows(mysqli_query($conn, "SELECT username FROM users WHERE username='$username'")) != 0) {
    array_push($errors, "Username đã tồn tại. Vui lòng nhập Username khác");
}

//Kiểm tra trùng email 
elseif (mysqli_num_rows(mysqli_query($conn, "SELECT email FROM users WHERE email='$email'"))) {
    array_push($errors, "Email đã tồn tại. Vui lòng nhập Email khác");
}
