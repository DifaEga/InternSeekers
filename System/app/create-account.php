<?php
session_start();
date_default_timezone_set('Asia/Kuching');
require 'D:\XAMPP\htdocs\Internseekers\System\mail1\vendor\phpmailer\phpmailer\src/PHPMailer.php';
require 'D:\XAMPP\htdocs\Internseekers\System\mail1\vendor\phpmailer\phpmailer\src/SMTP.php';

if (isset($_POST['reg_mode'])) {
    checkemail();
} else {
    header("location:../");
}

function checkemail()
{
    try {
        require '../constants/db_config.php';
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $email = $_POST['email'];
        $account_type = $_POST['acctype'];

        $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $records = count($result);

        if ($account_type == "101") {
            $role = "Employee";
        } else {
            $role = "Employer";
        }

        if ($records > 0) {
            header("location:../register.php?p=$role&r=0927");
        } else {
            if ($account_type == "101") {
                register_as_employee();
            } else {
                register_as_employer();
            }
        }
    } catch (PDOException $e) {
        header("location:../register.php?p=$role&r=4568");
    }
}

function register_as_employee()
{
    try {
        require '../constants/db_config.php';
        require '../constants/uniques.php';
        $role = 'employee';
        $account_type = $_POST['acctype'];
        $last_login = date('d-m-Y h:m A [T P]');
        $member_no = 'EM' . get_rand_numbers(9) . '';
        $fname = ucwords($_POST['fname']);
        $lname = ucwords($_POST['lname']);
        $email = $_POST['email'];
        $login = md5($_POST['password']);

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare("INSERT INTO tbl_users (first_name, last_name, email, last_login, login, role, member_no) 
            VALUES (:fname, :lname, :email, :lastlogin, :login, :role, :memberno)");
        $stmt->bindParam(':fname', $fname);
        $stmt->bindParam(':lname', $lname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':lastlogin', $last_login);
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':memberno', $member_no);
        $stmt->execute();

        // Generate OTP code
        $otp = generateOTP();

        // Save OTP code to the database
        $stmt = $conn->prepare("UPDATE tbl_users SET otp = :otp WHERE email = :email");
        $stmt->bindParam(':otp', $otp);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Send OTP code to the user's email
        sendOTP($email, $otp);

        // Store the email in a session for verification in the next step
        $_SESSION['email'] = $email;

        // Display the OTP verification pop-up
        echo <<<HTML
        <script>
            function verifyOTP() {
                var otp = document.getElementById("otp").value;
                if (otp !== "") {
                    // Submit the OTP form
                    document.getElementById("otpForm").submit();
                } else {
                    alert("Please enter the OTP code.");
                }
            }
            window.onload = function () {
                document.getElementById("otpModal").style.display = "block";
            }
        </script>

        <div id="otpModal" style="display: none; position: fixed; z-index: 1; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.4);">
            <div style="background-color: #f9f9f9; margin: auto; padding: 20px; border: 1px solid #888; width: 40%; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2); border-radius: 5px;">
            <h2 style="font-family: 'Arial', sans-serif; text-align: center; font-weight: bold; color: #333;">OTP Verification</h2>
            <p style="font-family: 'Arial', sans-serif; text-align: center; font-size: 16px; color: #555;">Please enter the OTP code sent to your email:</p>
            <form id="otpForm" method="post" action="">
            <input type="text" id="otp" name="otp" placeholder="Enter OTP" style="padding: 10px; width: 100%; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px; font-family: 'Arial', sans-serif; font-size: 14px;">
            <br>
            <br>
            <input type="button" value="Verify" onclick="verifyOTP()" style="padding: 10px 20px; background-color: #333; color: #fff; border: none; border-radius: 5px; font-family: 'Arial', sans-serif; font-size: 16px; cursor: pointer;">
             </form>
         </div>
        </div>

HTML;
    } catch (PDOException $e) {
        header("location:../register.php?p=Employee&r=4568");
        exit(); // Stop further script execution
    }
}

function register_as_employer()
{
    try {
        require '../constants/db_config.php';
        require '../constants/uniques.php';
        $role = 'employer';
        $account_type = $_POST['acctype'];
        $last_login = date('d-m-Y h:m A [T P]');
        $comp_no = 'CM' . get_rand_numbers(9) . '';
        $cname = ucwords($_POST['company']);
        $ctype = ucwords($_POST['type']);
        $email = $_POST['email'];
        $login = md5($_POST['password']);

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare("INSERT INTO tbl_users (first_name, title, email, last_login, login, role, member_no) 
            VALUES (:fname, :title, :email, :lastlogin, :login, :role, :memberno)");
        $stmt->bindParam(':fname', $cname);
        $stmt->bindParam(':title', $ctype);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':lastlogin', $last_login);
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':memberno', $comp_no);
        $stmt->execute();

        // Generate OTP code
        $otp = generateOTP();

        // Save OTP code to the database
        $stmt = $conn->prepare("UPDATE tbl_users SET otp = :otp WHERE email = :email");
        $stmt->bindParam(':otp', $otp);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Send OTP code to the user's email
        sendOTP($email, $otp);

        // Store the email in a session for verification in the next step
        $_SESSION['email'] = $email;

        // Display the OTP verification pop-up
        echo <<<HTML
        <script>
            function verifyOTP() {
                var otp = document.getElementById("otp").value;
                if (otp !== "") {
                    // Submit the OTP form
                    document.getElementById("otpForm").submit();
                } else {
                    alert("Please enter the OTP code.");
                }
            }
            window.onload = function () {
                document.getElementById("otpModal").style.display = "block";
            }
        </script>

        <div id="otpModal" style="display: none; position: fixed; z-index: 1; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.4);">
            <div style="background-color: #f9f9f9; margin: auto; padding: 20px; border: 1px solid #888; width: 40%; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2); border-radius: 5px;">
            <h2 style="font-family: 'Arial', sans-serif; text-align: center; font-weight: bold; color: #333;">OTP Verification</h2>
            <p style="font-family: 'Arial', sans-serif; text-align: center; font-size: 16px; color: #555;">Please enter the OTP code sent to your email:</p>
            <form id="otpForm" method="post" action="">
            <input type="text" id="otp" name="otp" placeholder="Enter OTP" style="padding: 10px; width: 100%; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px; font-family: 'Arial', sans-serif; font-size: 14px;">
            <br>
            <br>
            <input type="button" value="Verify" onclick="verifyOTP()" style="padding: 10px 20px; background-color: #333; color: #fff; border: none; border-radius: 5px; font-family: 'Arial', sans-serif; font-size: 16px; cursor: pointer;">
             </form>
         </div>
        </div>
HTML;
    } catch (PDOException $e) {
        header("location:../register.php?p=Employer&r=4568");
        exit(); // Stop further script execution
    }
}

function generateOTP()
{
    // Generate a random 6-digit OTP
    $otp = mt_rand(100000, 999999);
    return $otp;
}

function sendOTP($email, $otp)
{
    $mail = new PHPMailer\PHPMailer\PHPMailer();

    // Configure SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Your SMTP host
    $mail->SMTPAuth = true;
    $mail->Username = 'divaega2@gmail.com'; // Your SMTP username
    $mail->Password = 'jocweomwnhbvjycx'; // Your SMTP password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Set the email details
    $mail->setFrom('internseekers@no-reply.com', 'InternSeekers');
    $mail->addAddress($email);
    $mail->Subject = 'OTP Verification - InternSeekers';
    $mail->Body = 'Your OTP code for InternSeekers registration is: ' . $otp;

    // Send the email
    $mail->send();
}

?>
