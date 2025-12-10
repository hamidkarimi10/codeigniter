<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title : 'مدیریت مالی' ?></title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Persian Datepicker -->
    <link href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css" rel="stylesheet">

    <!-- ایران سنس -->
    <!-- <link href="https://cdn.jsdelivr.net/gh/rastikerdar/iransans-font@5.0.1/dist/font-face.css" rel="stylesheet"> -->
    <style>
        body {
            font-family: 'IRANSans', Tahoma, sans-serif;
            background-color: #f6f7fb;
        }

        /* کارت‌های داشبورد */
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .stat-card.income { border-right: 5px solid #28a745; }
        .stat-card.expense { border-right: 5px solid #dc3545; }
        .stat-card.balance { border-right: 5px solid #0d6efd; }

        .main-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .transaction-income { background: #e9f8ef !important; }
        .transaction-expense { background: #fdecec !important; }
    </style>
</head>

<body>

<?php if (!empty($user)): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= base_url('dashboard') ?>">
            <i class="bi bi-wallet2"></i> مدیریت مالی
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuBar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuBar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= base_url('dashboard') ?>"><i class="bi bi-speedometer2"></i> داشبورد</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('transactions') ?>"><i class="bi bi-arrow-left-right"></i> تراکنش‌ها</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('transactions/create') ?>"><i class="bi bi-plus-circle"></i> اضافه کردن</a></li>
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-bold d-flex align-items-center gap-2" 
                href="#" data-bs-toggle="dropdown">

            <img src="<?= base_url($user->avatar ?: 'assets/img/default-avatar.png') ?>" class="rounded-circle" width="40" height="40">

            <?= $user->first_name . " " . $user->last_name ?>
            </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="<?= base_url('user/profile') ?>">پروفایل من</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('auth/logout') ?>">خروج</a></li>
                    </ul>
                </li>
            </ul>

            <div>تاریخ امروز : </div>
            <?= jdate("Y/m/d" , time() , '' , "Asia/Tehran") ?>

        </div>
    </div>
</nav>
<?php endif; ?>

<div class="container mt-4">
    <?php if (isset($content)): ?>
        <?php $this->load->view($content); ?>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



<!-- jQuery -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script> -->
<!-- <script>
$(document).ready(function () {
    $('input[name="from"], input[name="to"], input[name="transaction_date"]').persianDatepicker({
        format: 'YYYY/MM/DD',
        initialValue: false
    });
});
</script> -->

</body>
</html>
