
<?php
session_start();

$timeout_duration = 10;

if (isset($_SESSION['LAST_ACTIVITY'])) {
    $elapsed_time = time() - $_SESSION['LAST_ACTIVITY'];
    if ($elapsed_time > $timeout_duration * 60) {
        session_unset();
        session_destroy();
        header("Location: signin.php");
        exit();
    }
}

$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['student_number'])) {
    $_SESSION['user_id'] = 'guest';
    $_SESSION['role'] = 'guest';
    $_SESSION['first_name'] = 'Guest';
    $_SESSION['last_name'] = 'Guest';
    $_SESSION['email'] = 'guest@example.com';
}

if ($_SESSION['role'] !== 'student' && $_SESSION['role'] !== 'admin') {
    $_SESSION['role'] = 'guest';
    $_SESSION['first_name'] = 'Guest';
    $_SESSION['last_name'] = 'Guest';
    $_SESSION['email'] = 'guest@example.com';
}

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$email = $_SESSION['email'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UJ Stabus - Real-Time Campus Shuttle Tracking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.3/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/leaflet@1.9.3/dist/leaflet.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-orange-500 text-white shadow-md">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 5h8m-4 5v-3m-8 3h16a2 2 0 002-2V6a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <h1 class="text-2xl font-bold">UJ Stabus</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="notificationBtn" class="relative p-2 rounded-full hover:bg-orange-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-1 right-1 bg-red-500 rounded-full w-2 h-2"></span>
                    </button>
                    <?php
if ($_SESSION['role'] != 'student') {
    echo '<button id="profileBtn" class="bg-white text-orange-500 px-3 py-1 rounded-full font-medium hover:bg-orange-100 transition">Sign In</button>';
}
?>

                </div>
            </div>
        </header>

        <!-- Notification Alert -->
        <div id="notification" class="notification fixed top-20 left-1/2 transform -translate-x-1/2 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center space-x-2 hidden">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span id="notificationText">Bus A1 is approaching APK Library in 2 minutes</span>
        </div>

        <!-- Main Content -->
        <main class="flex-grow container mx-auto px-4 py-6">
             <nav class="sidebar" id="sidebar">
        <h2>Dashboard</h2>
              <ul>
            <li><button onclick="window.location.href='dashboard.php'"><i class="fas fa-home"></i> Home</button></li>
            <li><button onclick="window.location.href='profile.php'" <?= $role == 'guest' ? 'style="opacity: 0.5;display:none; pointer-events: none;"' : '' ?>><i class="fas fa-user"></i> Profile</button></li>
            <li><button onclick="window.location.href='bus-tracking.php'" <?= $role == 'guest' ? 'style="opacity: 0.5; pointer-events: none;display:none;"' : '' ?>><i class="fas fa-bus"></i> Live Tracking</button></li>
            <li><button onclick="window.location.href='schedule.php'"><i class="fas fa-calendar-alt"></i> Schedule</button></li>
            <li><button onclick="window.location.href='chart.php'" <?= $role == 'guest' ? 'style="opacity: 0.5;display:none; pointer-events: none;"' : '' ?>><i class="fas fa-chart-bar"></i> Stats & Analysis</button></li>
            <li><button onclick="window.location.href='reservation.php'" <?= $role == 'guest' ? 'style="opacity: 0.5;display:none; pointer-events: none;"' : '' ?>><i class="fas fa-clipboard-check"></i> My Reservations</button></li>
            <li><button onclick="window.location.href='cancel-booking.php'" <?= $role == 'guest' ? 'style="opacity:0.5;display:none;pointer-events:none;"' : '' ?>><i class="fas fa-calendar-check"></i> My Booking</button></li>
            <li><button onclick="window.location.href='notifications.php'" <?= $role == 'guest' ? 'style="opacity: 0.5;display:none; pointer-events: none;"' : '' ?>><i class="fas fa-bell"></i> Notifications</button></li>
            <li><button onclick="window.location.href='settings.php'" <?= $role == 'guest' ? 'style="opacity: 0.5; pointer-events: none;display:none;"' : '' ?>><i class="fas fa-cogs"></i> Settings</button></li>
            <li><button onclick="window.location.href='weather.php'"><i class="fas fa-cloud-sun"></i> Weather</button></li>
            <li><button onclick="window.location.href='emergency-alert.php'"><i class="fas fa-exclamation-circle"></i> Emergency Alert</button></li>
            <li><button onclick="window.location.href='support.php'"><i class="fas fa-headset"></i> Help & Support</button></li>
            <li><button onclick="window.location.href='termsandconditions.php'"><i class="fas fa-file-contract"></i> Terms & Conditions</button></li>
            <li><button onclick="window.location.href='maintainance.php'"><i class="fas fa-tools"></i> Maintenance</button></li>
            <li><button onclick="window.location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button></li>
        </ul>
    </nav>
            <!-- Map Section -->
            <section class="mb-8">
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Live Shuttle Map</h2>
                    </div>
                    <div id="map" class="map-container"></div>
                </div>
            </section>

            <!-- Routes and Buses Section -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Routes Panel -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden md:col-span-1">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800">Campus Routes</h2>
                        <div class="flex items-center space-x-1 text-sm">
                            <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                            <span class="text-gray-500">Bus Stop</span>
                            <span class="w-3 h-3 bg-orange-500 rounded-full ml-2"></span>
                            <span class="text-gray-500">Bus</span>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <button class="route-btn bg-orange-500 text-white px-3 py-1 rounded-full text-sm font-medium" data-route="route1">Route A</button>
                                <button class="route-btn bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm font-medium" data-route="route2">Route B</button>
                                <button class="route-btn bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm font-medium" data-route="route3">Route C</button>
                            </div>
                            
                            <div id="routeInfo" class="border border-gray-200 rounded-lg p-3">
                                <h3 class="font-medium text-gray-800 mb-2">Route A: APK - APB - SWC</h3>
                                <div class="space-y-3">
                                    <div class="flex items-start">
                                        <div class="flex flex-col items-center mr-3">
                                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                            <div class="w-0.5 h-full bg-gray-300 my-1"></div>
                                        </div>
                                        <div>
                                            <p class="font-medium">APK Student Centre</p>
                                            <p class="text-sm text-gray-500">Next bus: 5 min</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex flex-col items-center mr-3">
                                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                            <div class="w-0.5 h-full bg-gray-300 my-1"></div>
                                        </div>
                                        <div>
                                            <p class="font-medium">APK Library</p>
                                            <p class="text-sm text-gray-500">Next bus: 8 min</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex flex-col items-center mr-3">
                                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                            <div class="w-0.5 h-full bg-gray-300 my-1"></div>
                                        </div>
                                        <div>
                                            <p class="font-medium">APB Main Entrance</p>
                                            <p class="text-sm text-gray-500">Next bus: 15 min</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex flex-col items-center mr-3">
                                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                        </div>
                                        <div>
                                            <p class="font-medium">SWC Campus</p>
                                            <p class="text-sm text-gray-500">Next bus: 25 min</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Buses Panel -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden md:col-span-2">
                    <div class="p-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Active Shuttles</h2>
                    </div>
                    <div class="p-4">
                        <div class="space-y-4">
                            <!-- Bus Card -->
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition cursor-pointer" onclick="focusBus('bus1')">
                                <div class="flex justify-between items-center mb-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="bg-orange-500 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold">A1</div>
                                        <div>
                                            <h3 class="font-medium">Bus A1</h3>
                                            <p class="text-sm text-gray-500">Route A: APK - APB - SWC</p>
                                        </div>
                                    </div>
                                    <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">On Time</div>
                                </div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-500">Current Location:</span>
                                    <span class="font-medium">APK Student Centre</span>
                                </div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-500">Next Stop:</span>
                                    <span class="font-medium">APK Library (2 min)</span>
                                </div>
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Capacity:</span>
                                    <span class="text-sm font-medium text-amber-600">65% Full</span>
                                </div>
                                <div class="capacity-indicator">
                                    <div class="capacity-fill bg-amber-500" style="width: 65%"></div>
                                </div>
                            </div>

                            <!-- Bus Card -->
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition cursor-pointer" onclick="focusBus('bus2')">
                                <div class="flex justify-between items-center mb-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="bg-orange-500 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold">A2</div>
                                        <div>
                                            <h3 class="font-medium">Bus A2</h3>
                                            <p class="text-sm text-gray-500">Route A: APK - APB - SWC</p>
                                        </div>
                                    </div>
                                    <div class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">Delayed</div>
                                </div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-500">Current Location:</span>
                                    <span class="font-medium">APB Main Entrance</span>
                                </div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-500">Next Stop:</span>
                                    <span class="font-medium">SWC Campus (10 min)</span>
                                </div>
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Capacity:</span>
                                    <span class="text-sm font-medium text-green-600">30% Full</span>
                                </div>
                                <div class="capacity-indicator">
                                    <div class="capacity-fill bg-green-500" style="width: 30%"></div>
                                </div>
                            </div>

                            <!-- Bus Card -->
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition cursor-pointer" onclick="focusBus('bus3')">
                                <div class="flex justify-between items-center mb-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="bg-orange-500 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold">B1</div>
                                        <div>
                                            <h3 class="font-medium">Bus B1</h3>
                                            <p class="text-sm text-gray-500">Route B: DFC - APK</p>
                                        </div>
                                    </div>
                                    <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">On Time</div>
                                </div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-500">Current Location:</span>
                                    <span class="font-medium">DFC Campus</span>
                                </div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-500">Next Stop:</span>
                                    <span class="font-medium">APK Student Centre (15 min)</span>
                                </div>
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Capacity:</span>
                                    <span class="text-sm font-medium text-red-600">90% Full</span>
                                </div>
                                <div class="capacity-indicator">
                                    <div class="capacity-fill bg-red-500" style="width: 90%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Peak Times Section -->
            <section class="mt-8">
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Peak Time Insights</h2>
                    </div>
                    <div class="p-4">
                        <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
                            <div class="flex-1 border border-gray-200 rounded-lg p-4">
                                <h3 class="font-medium text-gray-800 mb-3">Today's Busiest Times</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm">7:30 AM - 8:30 AM</span>
                                        <div class="w-2/3 bg-gray-200 h-4 rounded-full overflow-hidden">
                                            <div class="bg-red-500 h-full rounded-full" style="width: 90%"></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm">12:00 PM - 1:00 PM</span>
                                        <div class="w-2/3 bg-gray-200 h-4 rounded-full overflow-hidden">
                                            <div class="bg-amber-500 h-full rounded-full" style="width: 75%"></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm">4:30 PM - 5:30 PM</span>
                                        <div class="w-2/3 bg-gray-200 h-4 rounded-full overflow-hidden">
                                            <div class="bg-red-500 h-full rounded-full" style="width: 95%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-1 border border-gray-200 rounded-lg p-4">
                                <h3 class="font-medium text-gray-800 mb-3">Recommended Travel Times</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span>9:00 AM - 11:00 AM</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span>2:00 PM - 4:00 PM</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span>After 6:00 PM</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-6">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-4 md:mb-0">
                        <h2 class="text-xl font-bold flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 5h8m-4 5v-3m-8 3h16a2 2 0 002-2V6a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            UJ Stabus
                        </h2>
                        <p class="text-gray-400 text-sm mt-1">Real-time campus shuttle tracking</p>
                    </div>
                    <div class="flex space-x-4">
                        <a href="#" class="hover:text-orange-400 transition">Help</a>
                        <a href="#" class="hover:text-orange-400 transition">Privacy</a>
                        <a href="#" class="hover:text-orange-400 transition">Terms</a>
                        <a href="#" class="hover:text-orange-400 transition">Contact</a>
                    </div>
                </div>
                <div class="mt-6 text-center text-gray-400 text-sm">
                    &copy; 2023 UJ Stabus System. All rights reserved.
                </div>
            </div>
        </footer>
    </div>


<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'94690b0c805b4eb2',t:'MTc0ODM4NTkzMi4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>