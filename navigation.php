<?php
// Get the current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Navigation Bar -->
<nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="index.php" class="text-2xl font-bold text-blue-600 hover:text-blue-700 transition-colors duration-200">
                    <i class="fas fa-parking mr-2"></i>ParkEase
                </a>
            </div>

            <!-- Desktop Navigation - Centered -->
            <div class="hidden sm:flex sm:items-center sm:justify-center flex-1">
                <div class="flex space-x-8">
                    <a href="index.php" 
                       class="<?php echo $current_page === 'index.php' ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> 
                              inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium transition-colors duration-200 hover:bg-blue-50 rounded-t-lg">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                    <a href="booking_slot.php" 
                       class="<?php echo $current_page === 'booking_slot.php' ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> 
                              inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium transition-colors duration-200 hover:bg-blue-50 rounded-t-lg">
                        <i class="fas fa-history mr-2"></i>Booking History
                    </a>
                    <a href="profile.php" 
                       class="<?php echo $current_page === 'profile.php' ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> 
                              inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium transition-colors duration-200 hover:bg-blue-50 rounded-t-lg">
                        <i class="fas fa-user mr-2"></i>Profile
                    </a>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="flex items-center sm:hidden">
                <button type="button" id="mobile-menu-button" 
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 transition-colors duration-200" 
                        aria-controls="mobile-menu" 
                        aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>

            <!-- Desktop Profile Menu -->
            <div class="hidden sm:flex sm:items-center">
                <div class="relative group">
                    <button class="flex items-center space-x-2 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-full p-1 hover:bg-gray-100 transition-colors duration-200">
                        <img class="h-8 w-8 rounded-full" src="https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.webp" alt="Profile">
                        <span class="text-sm text-gray-700"><?php echo $_SESSION['email']; ?></span>
                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                    </button>
                    <div class="absolute right-0 w-48 mt-2 py-2 bg-white rounded-lg shadow-xl hidden group-hover:block transform origin-top-right transition-all duration-200 ease-out">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm text-gray-700 font-medium"><?php echo $_SESSION['email']; ?></p>
                        </div>
                        <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200">
                            <i class="fas fa-user-circle mr-2"></i>Profile
                        </a>
                        <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div class="sm:hidden hidden" id="mobile-menu">
        <div class="pt-2 pb-3 space-y-1">
            <a href="index.php" 
               class="<?php echo $current_page === 'index.php' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'; ?> 
                      block pl-3 pr-4 py-3 border-l-4 text-base font-medium transition-colors duration-200">
                <i class="fas fa-home mr-2"></i>Home
            </a>
            <a href="booking_slot.php" 
               class="<?php echo $current_page === 'booking_slot.php' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'; ?> 
                      block pl-3 pr-4 py-3 border-l-4 text-base font-medium transition-colors duration-200">
                <i class="fas fa-history mr-2"></i>Booking History
            </a>
            <a href="profile.php" 
               class="<?php echo $current_page === 'profile.php' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'; ?> 
                      block pl-3 pr-4 py-3 border-l-4 text-base font-medium transition-colors duration-200">
                <i class="fas fa-user mr-2"></i>Profile
            </a>
        </div>
        <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="flex items-center px-4">
                <div class="flex-shrink-0">
                    <img class="h-10 w-10 rounded-full" src="https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.webp" alt="Profile">
                </div>
                <div class="ml-3">
                    <div class="text-base font-medium text-gray-800"><?php echo $_SESSION['email']; ?></div>
                </div>
            </div>
            <div class="mt-3 space-y-1">
                <a href="profile.php" class="block px-4 py-3 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-colors duration-200">
                    <i class="fas fa-user-circle mr-2"></i>Profile
                </a>
                <a href="logout.php" class="block px-4 py-3 text-base font-medium text-red-600 hover:text-red-800 hover:bg-red-50 transition-colors duration-200">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
// Mobile menu toggle with animation
document.getElementById('mobile-menu-button').addEventListener('click', function() {
    const mobileMenu = document.getElementById('mobile-menu');
    const isExpanded = this.getAttribute('aria-expanded') === 'true';
    
    this.setAttribute('aria-expanded', !isExpanded);
    mobileMenu.classList.toggle('hidden');
    
    // Toggle icon with animation
    const icon = this.querySelector('i');
    if (isExpanded) {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
        mobileMenu.style.transform = 'translateY(-10px)';
        mobileMenu.style.opacity = '0';
    } else {
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
        mobileMenu.style.transform = 'translateY(0)';
        mobileMenu.style.opacity = '1';
    }
});

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    
    if (!mobileMenu.contains(event.target) && !mobileMenuButton.contains(event.target)) {
        mobileMenu.classList.add('hidden');
        mobileMenuButton.setAttribute('aria-expanded', 'false');
        const icon = mobileMenuButton.querySelector('i');
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    }
});

// Add hover effect to profile menu
const profileMenu = document.querySelector('.group');
if (profileMenu) {
    profileMenu.addEventListener('mouseenter', function() {
        const dropdown = this.querySelector('.group-hover\\:block');
        dropdown.style.display = 'block';
        dropdown.style.transform = 'translateY(0)';
        dropdown.style.opacity = '1';
    });

    profileMenu.addEventListener('mouseleave', function() {
        const dropdown = this.querySelector('.group-hover\\:block');
        dropdown.style.display = 'none';
        dropdown.style.transform = 'translateY(-10px)';
        dropdown.style.opacity = '0';
    });
}
</script> 