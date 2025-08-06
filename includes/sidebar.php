

<!-- includes/sidebar.php 

/*
 * 🔒 Spond Manager - Created by Belli Dev
 * © 2025 Belli Dev. All rights reserved.
 * You are not allowed to copy, modify, redistribute, or sell this software
 * without explicit written permission from the author.
 * Violators will be prosecuted under applicable laws.
 */

 
 -->

<nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : ''; ?>" href="events.php">
                    <i class="fas fa-calendar"></i> Events
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'overview.php' ? 'active' : ''; ?>" href="overview.php">
                    <i class="fas fa-chart-bar"></i> Overview
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'members.php' ? 'active' : ''; ?>" href="members.php">
                    <i class="fas fa-users"></i> Members
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Tools</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="syncSpondEvents()">
                    <i class="fas fa-sync-alt"></i> Sync Events
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="sync_logs.php">
                    <i class="fas fa-history"></i> Sync Logs
                </a>
            </li>
        </ul>

        <div class="mt-auto p-3">
            <div class="alert alert-info alert-sm">
                <small>
                    <i class="fas fa-info-circle"></i>
                    <strong>Quick Tip:</strong> Use the sync button to update events from Spond.
                </small>
            </div>
        </div>
    </div>
</nav>

<style>
.sidebar {
    position: fixed;
    top: 48px;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 48px 0 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
}

.sidebar-heading {
    font-size: .75rem;
    text-transform: uppercase;
}

.sidebar .nav-link {
    font-weight: 500;
    color: #333;
}

.sidebar .nav-link .fas {
    margin-right: 4px;
    color: #999;
}

.sidebar .nav-link.active {
    color: #007bff;
    background-color: rgba(0, 123, 255, .1);
}

.sidebar .nav-link:hover .fas,
.sidebar .nav-link.active .fas {
    color: inherit;
}

.alert-sm {
    padding: 0.5rem;
    font-size: 0.875rem;
}

@media (max-width: 767.98px) {
    .sidebar {
        top: 5rem;
    }
}
</style>