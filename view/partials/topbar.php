<header class="topbar">
    <div class="topbar-left">
        <button class="toggle-btn" id="toggleSidebarBtn">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="search-bar">
            <i class="fa-solid fa-search"></i>
            <input type="text" placeholder="Search">
            <i class="fa-solid fa-expand" style="cursor:pointer; color: var(--text-muted);"></i>
        </div>

    </div>
    <div class="topbar-center">
        <form method="GET" action="" class="topbar-filter-form">
            <div class="filter-group">
                <label>Start</label>
                <input type="date" name="start" value="<?php echo htmlspecialchars($reportFilterStart); ?>"
                    onchange="document.getElementById('loading-overlay')?.classList.remove('hidden'); this.form.submit()">
            </div>
            <div class="filter-group">
                <label>End</label>
                <input type="date" name="end" value="<?php echo htmlspecialchars($reportFilterEnd); ?>"
                    onchange="document.getElementById('loading-overlay')?.classList.remove('hidden'); this.form.submit()">
            </div>
        </form>
    </div>

    <div class="topbar-right">
        <div class="topbar-icons">
            <button><i class="fa-solid fa-gear"></i></button>
            <button><i class="fa-regular fa-moon"></i></button>
        </div>
        <a href="index.php?page=account" class="user-profile" style="display:flex; align-items:center; gap:10px; text-decoration: none; color: inherit; cursor: pointer;">
            <div class="user-info" style="text-align: right;">
                <span class="username"><?php echo htmlspecialchars($loggedInUser['username']); ?></span>
                <span class="role"><?php echo htmlspecialchars($loggedInUser['Role']); ?></span>
            </div>
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($loggedInUser['username']); ?>&background=random"
                alt="User">
        </a>
    </div>
</header>