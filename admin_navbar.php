<?php
// Get the current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="sidebar">
    <h4 class="mb-4">Admin Panel</h4>
    <ul class="sidebar-menu">
        <li>
            <a href="admin_dashboard.php" class="<?php echo $current_page === 'admin_dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="parking_slots.php" class="<?php echo $current_page === 'parking_slots.php' ? 'active' : ''; ?>">
                <i class="fas fa-car"></i> Parking Slots
            </a>
        </li>
        <li>
            <a href="users.php" class="<?php echo $current_page === 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Users
            </a>
        </li>
        <li>
            <a href="bookings.php" class="<?php echo $current_page === 'bookings.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar"></i> Bookings
            </a>
        </li>
        <li>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>

<style>
.sidebar {
    width: 250px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: #2c3e50;
    color: white;
    padding: 20px;
    z-index: 1000;
}

.main-content {
    margin-left: 250px;
    padding: 20px;
}

.sidebar-menu {
    list-style: none;
    padding: 0;
}

.sidebar-menu li {
    margin-bottom: 10px;
}

.sidebar-menu a {
    color: white;
    text-decoration: none;
    display: block;
    padding: 10px;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.sidebar-menu a:hover {
    background: rgba(255,255,255,0.1);
    transform: translateX(5px);
}

.sidebar-menu a.active {
    background: rgba(255,255,255,0.2);
    font-weight: bold;
}

.sidebar-menu i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}
</style> 