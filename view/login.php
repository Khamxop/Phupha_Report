<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Preclinic</title>
    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Phetsarath:wght@400;700&display=swap"
        rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>

    <div class="login-container">
        <div class="login-header">
            <img src="../img/Phupha.png" alt="Logo" class="logo" width="100" height="100">
            <h2>ໂຮງງານ ນ້ຳດື່ມພູຜາ</h2>
            <p>ເຂົ້າສູ່ລະບົບ</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>ຊື່ຜູ້ໃຊ້ ຫຼື ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ!</span>
            </div>
        <?php endif; ?>

        <form action="../api/auth.php" method="POST">
            <div class="form-group">
                <label for="username">ຊື່ຜູ້ໃຊ້ (Username)</label>
                <div class="input-group">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" id="username" name="username" class="form-control" placeholder="ປ້ອນຊື່ຜູ້ໃຊ້..."
                        required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">ລະຫັດຜ່ານ (Password)</label>
                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="ປ້ອນລະຫັດຜ່ານ..." required>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <span>ເຂົ້າສູ່ລະບົບ</span>
            </button>
        </form>
    </div>

</body>

</html>