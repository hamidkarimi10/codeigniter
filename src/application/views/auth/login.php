<div class="row justify-content-center align-items-center min-vh-100">
    <div class="col-md-5">
        <div class="card shadow-lg">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h3 class="card-title text-primary">ورود به سیستم</h3>
                    <p class="text-muted">لطفا اطلاعات حساب خود را وارد کنید</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <strong>خطا:</strong> <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <?= $success ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= base_url('auth/login') ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label">ایمیل</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= set_value('email') ?>" placeholder="ایمیل خود را وارد کنید" required>
                        <?= form_error('email', '<div class="text-danger small mt-1">', '</div>') ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">رمز عبور</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               value="" placeholder="رمز عبور خود را وارد کنید" required>
                        <?= form_error('password', '<div class="text-danger small mt-1">', '</div>') ?>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember_me" class="form-check-input" id="remember_me">
                        <label class="form-check-label" for="remember_me">مرا به خاطر بسپار</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">ورود به سیستم</button>
                </form>

                <div class="text-center mt-3">
                    <p class="mb-0">حساب کاربری ندارید؟ 
                        <a href="<?= base_url('auth/register') ?>" class="text-decoration-none">ثبت نام کنید</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
