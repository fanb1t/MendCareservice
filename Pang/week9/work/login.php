<?php
require 'connectDB.php';

// จัดการการ Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signin'])) {
    $user = trim($_POST['user$user']);
    $password = trim($_POST['password']);

    if (!empty($user) && !empty($password)) {
        $sql = "SELECT STU_ID, username FROM STUDENT WHERE username = ? AND password = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $user, $password);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                session_start();
                $_SESSION['stu_id'] = $row['STU_ID'];
                $_SESSION['user'] = $row['username'];
                header("Location: search.php");
                exit();
            } else {
                echo "<script>alert('ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');</script>";
            }
            $stmt->close();
        }
    } else {
        echo "<script>alert('กรุณากรอกข้อมูลให้ครบ');</script>";
    }
}

// จัดการการ Sign Up
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $username = trim($_POST['user$user']);
    $password = trim($_POST['password']);
    $stu_id = trim($_POST['phone']);
    $stu_name = trim($_POST['user']);
    
    if (!empty($username) && !empty($password) && !empty($stu_id) && !empty($stu_name)) {
        $sql = "INSERT INTO STUDENT (STU_ID, STU_NAME, username, password) VALUES (?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssss", $stu_id, $stu_name, $username, $password);
            
            if ($stmt->execute()) {
                echo "<script>alert('สมัครสมาชิกเรียบร้อยแล้ว!');</script>";
            } else {
                echo "<script>alert('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง');</script>";
            }
            $stmt->close();
        }
    } else {
        echo "<script>alert('กรุณากรอกข้อมูลให้ครบทุกช่อง');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration form</title>
    <link rel="stylesheet" href="style.css" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/b791b9cfac.js" crossorigin="anonymous"></script>
</head>

<body>
    <div id="container">
        <div class="form_container sign_in">
            <form action="" method="POST">
                <h1 class="grey">Log in</h1>
                <div class="rs_icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-google"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-github"></i></a>
                </div>
                <h6 class="grey">Or enter your login details</h6>
                <div class="user$user icon">
                    <input type="text" id="user$user_in" name="user$user" placeholder="Username" required/>
                    <i class="fa fa-envelope fa-sm"></i>
                </div>
                <div class="password icon">
                    <input type="password" id="password_up" name="password" placeholder="Password" required/>
                    <i class="fas fa-key"></i>
                    <i class="show fas fa-eye-slash" onclick="myFunction()"></i>
                </div>
                <div class="remember grey">
                    <input type="checkbox" id="remember" name="remember"/>
                    <h6>Remember me</h6>
                </div>
                <div class="submit">
                    <button id="btnin" name="signin" type="submit" class="overlay_button">Log In</button>
                </div>
            </form>
        </div>

        <div class="form_container sign_up">
            <form action="" method="POST">
                <h1 class="grey">Create an Account</h1>
                <div class="rs_icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-google"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-github"></i></a>
                </div>
                <h6 class="grey">Or enter your personal details</h6>
                <div class="user icon">
                    <input type="text" id="user" name="user" placeholder="Full Name" required/>
                    <i class="fa fa-user fa-sm"></i>
                </div>
                <div class="user$user icon">
                    <input type="text" id="user$user_up" name="user$user" placeholder="Username" required/>
                    <i class="fa fa-envelope fa-sm"></i>
                </div>
                <div class="phone icon">
                    <input type="text" id="phone" name="phone" placeholder="Student ID" required/>
                    <i class="fa fa-id-card fa-sm"></i>
                </div>
                <div class="password icon">
                    <input type="password" id="password" name="password" placeholder="Create password" required/>
                    <i class="fas fa-key"></i>
                    <i class="show fas fa-eye-slash" onclick="myFunction()"></i>
                </div>
                <div class="password icon">
                    <input type="password" id="cpassword" name="cpassword" placeholder="Repeat password" required/>
                    <i class="fas fa-key"></i>
                    <i class="show fas fa-eye-slash" onclick="myFunction2()"></i>
                </div>
                <div class="submit">
                    <button id="btnin" name="signup" type="submit" class="overlay_button">Sign up</button>
                </div>
            </form>
        </div>

        <div class="overlay_container">
            <div class="overlay">
                <div class="overlay_panel overlay_left">
                    <h1>Welcome Student!</h1>
                    <p>A student management system that efficiently displays personal information</p>
                    <p> </p><br>
                    <button id="signIn" type="button" class="overlay_button">Log In</button>
                </div>
                <div class="overlay_panel overlay_right">
                    <h1>Welcome Register!</h1>
                    <p>Create an account to access all features and manage your student information easily.</p>
                    <p> </p><br>
                    <button id="signUp" type="button" class="overlay_button">Sign Up</button>
                    <button id="signInMobile" type="button" class="overlay_button">Log In</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const signInButtonMobile = document.getElementById('signInMobile');
        const container = document.getElementById('container');

        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });

        signInButtonMobile.addEventListener('click', () => {
            container.classList.add("mobile");
        });

        function myFunction() {
            var x = document.getElementById("password_up");
            var y = document.getElementById("password");

            if (x.type === "password") {
                x.type = "text";
            } else {
                x.type = "password";
            }
            if (y.type === "password") {
                y.type = "text";
            } else {
                y.type = "password";
            }
        }

        function myFunction2() {
            var z = document.getElementById("cpassword");
            if (z.type === "password") {
                z.type = "text";
            } else {
                z.type = "password";
            }
        }
    </script>
</body>
</html>
