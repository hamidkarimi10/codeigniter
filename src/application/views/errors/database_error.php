<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>خطای سیستم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-exclamation-triangle"></i> 
                            خطای سیستم
                        </h4>
                    </div>
                    <div class="card-body">
                        <h5 class="text-danger"><?= $message ?></h5>
                        <p class="card-text mt-3">
                            لطفاً مراحل زیر را بررسی کنید:
                        </p>
                        <ol>
                            <li>دیتابیس ایجاد شده باشد</li>
                            <li>تنظیمات دیتابیس درست باشد</li>
                            <li>Migration اجرا شده باشد</li>
                            <li>جداول در دیتابیس وجود داشته باشند</li>
                        </ol>
                        
                        <div class="mt-4">
                            <a href="<?= base_url('test_db') ?>" class="btn btn-info">تست دیتابیس</a>
                            <a href="<?= base_url('migrate') ?>" class="btn btn-primary">اجرای Migration</a>
                            <a href="<?= base_url() ?>" class="btn btn-secondary">بازگشت</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>