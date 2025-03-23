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
$email = isset($_GET['email']) ? $_GET['email'] : ""; // Get email from URL parameter
$password = isset($_GET['password']) ? $_GET['password'] : "";




// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email']; // Set email when the form is submitted
    $password = $_POST['password'];

    // Fetch user from database
    $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $db_email, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $db_email;

            header("Location: index.php"); // Redirect to home page
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
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
    <title>Login - ParkEase</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- daisy UI -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
        <?php if ($error): ?>
            <div class="mb-4 p-2 bg-red-100 text-red-700 rounded">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4 relative">
                <label class="block text-gray-700">Password</label>
                <input type="password" name="password" id="password" value="<?php echo htmlspecialchars($password); ?>"
                    required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    autocomplete="current-password">
                <!-- Eye icon to toggle password visibility -->
                <i class="fas fa-eye absolute right-3 top-10 cursor-pointer" onclick="togglePassword('password')"></i>
            </div>
            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Login
            </button>
        </form>
        <p class="mt-4 text-center">Don't have an account? <a href="register.php" class="text-blue-600">Register</a></p>
    </div>

    <script>
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