<?php function sidebar($dashboard, $group, $history, $bills, $logout) { ?>
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
    <h2>Kas App</h2>
    
    <ul>
        <li><a href="<?= $dashboard ?>">Dashboard</a></li>
        <li><a href="<?= $group ?>">Group</a></li>
        <li><a href="<?= $history ?>">Riwayat</a></li>
        <li><a href="<?= $bills ?>">Tagihan</a></li>
    </ul>

    <div class="logout">
        <a href="<?= $logout ?>">Logout</a>
    </div>
</div>
</body>
</html>
<?php } ?>