<?php
// Security check: Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Determine which tab is currently active (defaults to 'courses')
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'courses';
?>

<!-- WordPress Admin Page Wrapper -->
<div class="wrap">
    <h1>Academy</h1>

    <!-- Navigation Tabs -->
    <h2 class="nav-tab-wrapper">
        <!-- "Cursussen" Tab -->
        <a href="?page=academy&tab=courses"
           class="nav-tab <?php echo $current_tab == 'courses' ? 'nav-tab-active' : ''; ?>">
            Cursussen
        </a>
        <!-- You can easily add more tabs here (e.g., Users, Settings, Stats) -->
    </h2>

    <!-- Tab Content Container -->
    <div class="tab-content">
        <?php
        // Conditionally load the tab content based on the active tab
        switch ($current_tab) {
            case 'courses':
                include ACADEMY_PLUGIN_DIR . 'admin/pages/tabs/tab-courses.php';
                break;

            // Example placeholder for future tabs:
            // case 'settings':
            //     include ACADEMY_PLUGIN_DIR . 'admin/pages/tabs/tab-settings.php';
            //     break;
        }
        ?>
    </div>
</div>
