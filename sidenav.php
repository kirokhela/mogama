<?php
// Determine current page dynamically
$current_page = basename($_SERVER['PHP_SELF']);
$menu_items = [
    'index.php' => ['label' => 'الرئيسية', 'icon' => '🏠'],
    'detail.php' => ['label' => 'التفاصيل', 'icon' => '📋'],
    'edit.php' => ['label' => 'تعديل', 'icon' => '✏️'],
    'dashboard.php' => ['label' => 'لوحة التحكم', 'icon' => '📊'],
    'logout.php' => ['label' => 'تسجيل الخروج', 'icon' => '🚪'],
    'scan.php' => ['label' => 'مسح', 'icon' => '📷'],
'attendance.php' => ['label' => 'حضور', 'icon' => '👥'],
];
?>
<div class="side-nav">
    <ul>
        <?php foreach ($menu_items as $file => $item): ?>
        <li>
            <a href="<?php echo $file; ?>" class="<?php echo $current_page == $file ? 'active' : ''; ?>">
                <span class="icon"><?php echo $item['icon']; ?></span>
                <span class="label"><?php echo $item['label']; ?></span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

<style>
/* Sidebar container */
.side-nav {
    width: 220px;
    background-color: #2c3e50;
    height: 100vh;
    padding-top: 20px;
    position: fixed;
    right: 0;
    /* Position sidebar on the right */
    transition: width 0.3s;
    direction: rtl;
    /* RTL direction */
}

/* Menu list */
.side-nav ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
}

.side-nav ul li {
    margin-bottom: 5px;
}

/* Menu links */
.side-nav ul li a {
    color: #ecf0f1;
    text-decoration: none;
    display: flex;
    align-items: center;
    padding: 12px 20px;
    font-weight: 500;
    border-radius: 6px;
    transition: background 0.3s, padding-right 0.3s;
    /* Changed to padding-right for RTL */
    text-align: right;
    /* Right align text for Arabic */
}

.side-nav ul li a:hover {
    background-color: #34495e;
    padding-right: 25px;
    /* Changed to padding-right for RTL */
}

/* Active page highlighting */
.side-nav ul li a.active {
    background-color: #1abc9c;
    color: #fff;
}

/* Icon style */
.side-nav ul li a .icon {
    margin-left: 10px;
    /* Changed from margin-right to margin-left for RTL */
    font-size: 18px;
}

/* Responsive: collapse sidebar */
@media (max-width: 768px) {
    .side-nav {
        width: 60px;
        padding-top: 10px;
    }

    .side-nav ul li a {
        padding: 10px 12px;
        justify-content: center;
    }

    .side-nav ul li a .label {
        display: none;
    }

    .side-nav ul li a .icon {
        margin: 0;
        font-size: 20px;
    }
}
</style>