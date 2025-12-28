<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title : 'مدیریت مالی' ?></title>

    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">

    <!--css -->
    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">

    <!-- jquery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- <link href="fontfont.ir/fonts/iraniansans/iraniansans.ttf" rel="stylesheet">  -->
     <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@30.1.0/dist/font-face.css" rel="stylesheet" type="text/css">
     

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
</head>

<body>
<?php if (isset($user)): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm py-3">
  <div class="container d-flex align-items-center">
    
    <!-- <a class="navbar-brand fw-bold" href="<?= base_url('dashboard') ?>">
      <i class="bi bi-wallet2"></i> مدیریت مالی
    </a> -->
    

    <div class="navbar-nav me-auto order-1 order-lg-2">
    </div>

    <ul class="navbar-nav d-flex flex-row gap-3 order-2 order-lg-1">
      <li class="nav-item">
        <a class="nav-link" href="<?= base_url('dashboard') ?>">
          <i class="bi bi-speedometer2"></i> داشبورد
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?= base_url('transactions') ?>">
          <i class="bi bi-arrow-left-right"></i> تراکنش‌ها
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?= base_url('transactions/create') ?>">
          <i class="bi bi-plus-circle"></i> اضافه کردن
        </a>
      </li>
      <li class="nav-item">
    <a class="nav-link" href="<?= base_url('category') ?>">
      <i class="bi bi-tag"></i> دسته‌بندی‌ها
    </a>
  </li>
   <li class="nav-item">
    <button id="theme-toggle" class="btn btn-outline-secondary">
      🌙
    </button>
   </li>
    </ul>
   


    <div class="d-flex align-items-center gap-4 order-3">
      <div class="bi bi-calendar-event text-white d-none d-lg-block">
          <?= jdate('Y/m/d') ?>
      </div>

      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle fw-bold d-flex align-items-center gap-2"
             href="#" data-bs-toggle="dropdown">
            <img src="<?= $user->avatar ? base_url($user->avatar) : base_url('/uploads/avatars/images.jpeg') ?>"
                 class="rounded-circle" width="40" height="40">
            <?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?>
          </a>
          
          <ul class="dropdown-menu dropdown-menu-end shadow" style="direction: rtl; text-align: right;">
            <li>
                <a class="dropdown-item d-flex align-items-center justify-content-between gap-2" 
                href="<?= base_url('user/profile') ?>">
                <span>پروفایل من</span>
                <i class="bi bi-person"></i> 
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center justify-content-between gap-2" 
                href="<?= base_url('auth/logout') ?>">
                <span>خروج</span>
                <i class="bi bi-box-arrow-left"></i> 
                </a>
            </li>  
        </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<?php endif; ?>

<div id="theme-wrapper" class="theme light">
<div id="app">     
    <div class="container">
           <div class="container">
        <?php if (isset($content)): ?>
            <?php $this->load->view($content); ?>
        <?php endif; ?>
    </div>
    </div> 
    </div>
</div>
<script>
    window.SERVER_DATA = {
        user: <?= isset($user) ? json_encode($user) : 'null' ?>,
        baseUrl: "<?= base_url() ?>",
        today: "<?= jdate('Y/m/d', time(), '', 'Asia/Tehran') ?>"
    }

    const Application = {
        user: <?= isset($user) ? json_encode($user) : 'null' ?>,

        baseUrl: "<?= base_url() ?>",
        
        today: "<?= jdate('Y/m/d', time(), '', 'Asia/Tehran') ?>",

        alert(message) {
            alert("this send from Application: " + message)
        },
    };
    const themeWrapper = document.getElementById('theme-wrapper');
const toggleBtn = document.getElementById('theme-toggle');

const savedTheme = localStorage.getItem('theme') || 'light';
themeWrapper.classList.remove('light', 'dark');
themeWrapper.classList.add(savedTheme);
toggleBtn.textContent = savedTheme === 'dark' ? '☀️' : '🌙';

// کلیک روی دکمه
toggleBtn.addEventListener('click', () => {
  const isDark = themeWrapper.classList.contains('dark');
  
  themeWrapper.classList.toggle('dark', !isDark);
  themeWrapper.classList.toggle('light', isDark);

  localStorage.setItem('theme', isDark ? 'light' : 'dark');
  toggleBtn.textContent = isDark ? '🌙' : '☀️';
});

function showAlert(title, text, icon) {
    const isDark = document.body.classList.contains('dark'); 
    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        background: isDark ? '#1e1e1e' : '#fff',  
        color: isDark ? '#fff' : '#000',          
        iconColor: icon === 'success' ? '#28a745' : (icon === 'error' ? '#dc3545' : '#007bff'),
        confirmButtonColor: isDark ? '#444' : '#0d6efd',
    });
}

</script>

<!-- <script>
    const app = Vue.createApp({
    data() {
        return {
            user: SERVER_DATA.user,
            baseUrl: SERVER_DATA.baseUrl,
            today: SERVER_DATA.today
        }
    }
}).mount('#app');
</script> -->

</body>
</html>
