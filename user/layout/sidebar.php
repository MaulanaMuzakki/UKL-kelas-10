<?php function sidebar($dashboard, $group, $bills, $logout, $profil, $active) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DanaKita</title>
    <link rel="stylesheet" href="../../style/style.css">
</head>
<body>

<div class="sidebar">

    <div class="logo">
        <a href="<?= $dashboard ?>" style="text-decoration: none;">
            <h2>DanaKita</h2>
        </a>
    </div>

    <div class="menu">

        <a href="<?= $dashboard ?>" class="menu-item <?= $active == 'dashboard' ? 'active' : '' ?>">
            <span>📊</span>
            Dashboard
        </a>

        <a href="<?= $group ?>" class="menu-item <?= $active == 'group' ? 'active' : '' ?>">
            <span>👥</span>
            Group
        </a>

        <a href="<?= $bills ?>" class="menu-item <?= $active == 'bills' ? 'active' : '' ?>">
            <span>🧾</span>
            Bills
        </a>

    </div>

    <div class="bottom-menu">

        <a href="<?= $profil ?>" class="menu-item <?= $active == 'profil' ? 'active' : '' ?>">
            <span>⚙️</span>
            Account
        </a>

        <a href="<?= $logout ?>" class="menu-item">
            <span>↩️</span>
            Log out
        </a>

    </div>

</div>

</body>
<?php } ?>