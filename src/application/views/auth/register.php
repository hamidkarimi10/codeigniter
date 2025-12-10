<div class="row justify-content-center align-items-center min-vh-100">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h3 class="card-title text-primary">ثبت نام در سیستم</h3>
                    <p class="text-muted">فرم زیر را برای ایجاد حساب کاربری پر کنید</p>
                </div>

                <form method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">نام</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?= set_value('first_name') ?>" required>
                                <?= form_error('first_name', '<div class="text-danger small mt-1">', '</div>') ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="last_name" class="form-label">نام خانوادگی</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?= set_value('last_name') ?>" required>
                                <?= form_error('last_name', '<div class="text-danger small mt-1">', '</div>') ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">ایمیل</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= set_value('email') ?>" required>
                        <?= form_error('email', '<div class="text-danger small mt-1">', '</div>') ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">رمز عبور</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <?= form_error('password', '<div class="text-danger small mt-1">', '</div>') ?>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">تکرار رمز عبور</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <?= form_error('confirm_password', '<div class="text-danger small mt-1">', '</div>') ?>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2">ثبت نام</button>
                </form>

                <div class="text-center mt-3">
                    <p class="mb-0">حساب کاربری دارید؟ 
                        <a href="<?= base_url('auth/login') ?>" class="text-decoration-none">وارد شوید</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>