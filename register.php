<?php
session_start();



// Database connection
$host = 'localhost';
$db = 'online_parking';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";


// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();


    if ($stmt->num_rows > 0) {
        $error = "Email already exists!";
    } else {
        // Insert user into database
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
            // header("Location: login.php"); // Redirect to login page
            header("Location: login.php?email=" . urlencode($email) . "&password=" . urlencode($_POST['password']));
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ParkEase</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">


</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold mb-6 text-center">Register</h2>
        <?php if ($error): ?>
            <div class="mb-4 p-2 bg-red-100 text-red-700 rounded">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="" onsubmit="return validateForm()">
            <div class="mb-4">
                <label class="block text-gray-700">Username</label>
                <input type="text" name="username" id="username" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    autocomplete="username">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Email</label>
                <input type="email" name="email" id="email" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    autocomplete="email">
                <span id="email-error" class="text-red-500 text-sm hidden">Please enter a valid email address (e.g.,
                    example@example.com).</span>
            </div>
            <div class="mb-4 relative">
                <label class="block text-gray-700">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    autocomplete="new-password">
                <!-- Eye icon to toggle password visibility -->
                <i class="fas fa-eye absolute right-3 top-10 cursor-pointer" onclick="togglePassword('password')"></i>
            </div>
            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Register
            </button>
        </form>

        <p class="mt-4 text-center">Already have an account? <a href="login.php" class="text-blue-600">Login</a></p>
    </div>
    <script>
        // Function to validate email format
        function validateEmail(email) {
            // Regex to check for a valid email format and ensure the domain ends with a fully written TLD
            const regex = /^[^\s@]+@[^\s@]+\.[a-zA-Z]{3,}$/;
            return regex.test(email);
        }

        // Function to validate the form
        function validateForm() {
            const emailInput = document.getElementById('email');
            const emailError = document.getElementById('email-error');
            const email = emailInput.value.trim();

            if (!validateEmail(email)) {
                emailError.textContent = 'Please enter a valid email address (e.g., example@example.com).';
                emailError.classList.remove('hidden'); // Show error message
                emailInput.focus(); // Focus on the email field
                return false; // Prevent form submission
            } else {
                emailError.classList.add('hidden'); // Hide error message
                return true; // Allow form submission
            }
        }

        // Function to toggle password visibility
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const eyeIcon = passwordField.nextElementSibling;

            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }
    </script>
</body>

</html>