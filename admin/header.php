<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<div class="topbar">
    <div class="topbar-left">
        <h3 class="welcome-text">Halo, <?= htmlspecialchars($_SESSION['nama']) ?></h3>
    </div>
</div>