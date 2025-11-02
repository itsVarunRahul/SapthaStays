<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection settings for XAMPP
$host = 'localhost';
$dbname = 'test';
$user = 'root';
$pass = '';
$port = '3306';

// Build the DSN
$dsn = "mysql:host=$host;port=$port;dbname=$dbname";

try {
    // Create PDO instance
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to get MySQL version
    $stmt = $db->query("SELECT VERSION()");
    echo "MySQL Server Version: " . $stmt->fetchColumn() . "<br>";

    // Prepare and execute insert operation
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $db->prepare("INSERT INTO reservations (name, cardnumber, checkindate, checkoutdate, phonenumber, roomtype, accommodation, acknowledgment, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Bind parameters
        $stmt->bindParam(1, $name);
        $stmt->bindParam(2, $cardnumber);
        $stmt->bindParam(3, $checkindate);
        $stmt->bindParam(4, $checkoutdate);
        $stmt->bindParam(5, $phonenumber);
        $stmt->bindParam(6, $roomtype);
        $stmt->bindParam(7, $accommodation);
        $stmt->bindParam(8, $acknowledgment);
        $stmt->bindParam(9, $email);

        // Set parameters and execute
        $name = htmlspecialchars($_POST['name']); // Sanitize input
        $cardnumber = htmlspecialchars($_POST['cardnumber']); // Sanitize input
        $checkindate = $_POST['checkindate'];
        $checkoutdate = $_POST['checkoutdate'];
        $phonenumber = htmlspecialchars($_POST['phonenumber']); // Sanitize input
        $roomtype = $_POST['roomtype'];
        $accommodation = (int)$_POST['accommodation']; // Cast to integer
        $acknowledgment = isset($_POST['acknowledgment']) ? 1 : 0;
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // Sanitize and validate email

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt->execute();
            echo "New records created successfully. Confirmation email sent.<br>";

            // Email sending
            $to = $email;
            $subject = "Reservation Confirmation";
            $message = "
            <html>
            <head>
                <title>Reservation Confirmation</title>
            </head>
            <body>
                <h1>Thank You for Your Reservation</h1>
                <p>Dear $name,</p>
                <p>Your reservation has been successfully received. Here are the details:</p>
                <ul>
                    <li>Card Number: $cardnumber</li>
                    <li>Check-In Date: $checkindate</li>
                    <li>Check-Out Date: $checkoutdate</li>
                    <li>Phone Number: $phonenumber</li>
                    <li>Room Type: $roomtype</li>
                    <li>Number of Accommodations: $accommodation</li>
                    <li>Acknowledgment: " . ($acknowledgment ? "Yes" : "No") . "</li>
                </ul>
                <p>We look forward to welcoming you!</p>
                <p>Best regards,<br>Your Hotel Team</p>
            </body>
            </html>";

            // To send HTML mail, the Content-type header must be set
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

            // Additional headers
            $headers .= 'From: noreply@yourdomain.com' . "\r\n";

            if (mail($to, $subject, $message, $headers)) {
                echo "Confirmation email sent to $email.<br>";
            } else {
                echo "Failed to send confirmation email.<br>";
            }
        } else {
            echo "Invalid email address.<br>";
        }
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
