<?php
// Function to check if current page is active
function isActivePage($page) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    return $currentPage === $page ? 'active' : '';
}

// Alternative method: You can also pass the active page as a variable
// For example: $activePage = 'detail.php'; (set this in your parent PHP file)
// Then use: ($activePage === $page) ? 'active' : '';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shamadora</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <!-- Mobile menu toggle -->
    <button class="menu-toggle" id="menuToggle">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- Sidebar Navigation -->
    <nav class="sidebar" id="sidebar">
        <ul class="nav-menu">
            <li><a href="index.php" class="nav-link <?php echo isActivePage('index.php'); ?>">
                    <span class="icon">🏠</span>
                    <span class="text">الرئيسية</span>
                </a></li>
            <li><a href="detail.php" class="nav-link <?php echo isActivePage('detail.php'); ?>">
                    <span class="icon">📋</span>
                    <span class="text">التفاصيل</span>
                </a></li>
            <li><a href="edit.php" class="nav-link <?php echo isActivePage('edit.php'); ?>">
                    <span class="icon">✏️</span>
                    <span class="text">تعديل</span>
                </a></li>
            <li><a href="dashboard.php" class="nav-link <?php echo isActivePage('dashboard.php'); ?>">
                    <span class="icon">📊</span>
                    <span class="text">لوحة التحكم</span>
                </a></li>
            <li><a href="scan.php" class="nav-link <?php echo isActivePage('scan.php'); ?>">
                    <span class="icon">📷</span>
                    <span class="text">مسح</span>
                </a></li>

            <li><a href="attendance.php" class="nav-link <?php echo isActivePage('attendance.php'); ?>">
                    <span class="icon">👥</span>
                    <span class="text">حضور</span>
                </a></li>
            <li><a href="logout.php" class="nav-link <?php echo isActivePage('logout.php'); ?>">
                    <span class="icon">🚪</span>
                    <span class="text">تسجيل الخروج</span>
                </a></li>
        </ul>
    </nav>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="content-wrapper">
            <?php
            // 🔹 This is where the page content will appear
            if (isset($pageContent)) {
                // If $pageContent is a file name, include it
                if (is_file($pageContent)) {
                    include $pageContent;
                } else {
                    // Otherwise, echo it as HTML/text
                    echo $pageContent;
                }
            }
            ?>
        </div>
    </main>

    <!-- Overlay for mobile -->
    <div class="overlay" id="overlay"></div>

    <script>
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const body = document.body;

    menuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        menuToggle.classList.toggle('active');
        body.classList.toggle('menu-open');
    });

    overlay.addEventListener('click', function() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        menuToggle.classList.remove('active');
        body.classList.remove('menu-open');
    });

    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                menuToggle.classList.remove('active');
                body.classList.remove('menu-open');
            }
        });
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            menuToggle.classList.remove('active');
            body.classList.remove('menu-open');
        }
    });
    </script>
</body>

</html>