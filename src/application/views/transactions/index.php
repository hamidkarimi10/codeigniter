<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>تراکنش‌های من</h3>
    <a href="<?= base_url('transactions/create') ?>" class="btn btn-primary">تراکنش جدید</a>
</div>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
<?php endif; ?>


<!-- فیلترها -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-3">

            <div class="col-md-3">
                <label class="form-label">عنوان / توضیحات</label>
                <input type="text" name="search" class="form-control"
                    value="<?= $this->input->get('search') ?>" placeholder="مثلاً خرید، حقوق ...">
            </div>

            <div class="col-md-3">
                <label class="form-label">نوع تراکنش</label>
                <select name="type" class="form-select">
                    <option value="">همه</option>
                    <option value="income" <?= $this->input->get('type') == 'income' ? 'selected' : '' ?>>درآمد</option>
                    <option value="expense" <?= $this->input->get('type') == 'expense' ? 'selected' : '' ?>>هزینه</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">از تاریخ</label>
                <input type="text" name="from" class="form-control date-picker" 
                    value="<?=  $this->input->get('from') ?>" id="from_date" readonly>
            </div>

            <div class="col-md-3">
                <label class="form-label">تا تاریخ</label>
                <input type="text" name="to" class="form-control date-picker"
                    value="<?=  $this->input->get('to') ?>" id="form_data" readonly>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">جستجو</button>
                <a href="<?= base_url('transactions') ?>" class="btn btn-secondary">حذف فیلتر</a>
            </div>

        </form>
    </div>
</div>


<!-- جدول تراکنش‌ها -->
<?php if (!empty($transactions)): ?>
    <table class="table table-striped table-bordered">
        <thead class="table-primary">
            <tr>
                <th>تاریخ</th>
                <th>دسته‌بندی</th>
                <th>توضیحات</th>
                <th>مبلغ</th>
                <th>نوع</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($transactions as $tx): ?>
                <tr>
                    <?php $timestamp = strtotime($tx->transaction_date); ?>

                    <td>
                        <?= jdate("Y/m/d", $timestamp) ?>
                    </td>
                    <td><?= $tx->category_name ?></td>
                    <td><?= $tx->description ?: 'بدون توضیح' ?></td>

                    <td class="<?= $tx->type == 'income' ? 'text-success' : 'text-danger' ?>">
                        <?= $tx->type == 'income' ? '+' : '-' ?>
                        <?= number_format($tx->amount) ?> تومان
                    </td>

                    <td><?= $tx->type == 'income' ? 'درآمد' : 'هزینه' ?></td>

                    <td>
                        <a href="<?= base_url('transactions/edit/' . $tx->id) ?>" 
                           class="btn btn-sm btn-warning">ویرایش</a>

                        <a href="<?= base_url('transactions/delete/' . $tx->id) ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('آیا از حذف این تراکنش مطمئن هستید؟')">حذف</a>
                    </td>
                </tr>
            <?php endforeach; ?>

        </tbody>
    </table>

<?php else: ?>

    <div class="text-center py-4">
        <i class="bi bi-receipt display-4 text-muted"></i>
        <p class="text-muted mt-2">تراکنشی یافت نشد</p>
    </div>

<?php endif; ?>
<!-- نمایش لیست تراکنش‌ها -->
<?php if (!empty($transactions)): ?>
    <?php foreach ($transactions as $t): ?>
        <!-- ... -->
    <?php endforeach; ?>
<?php else: ?>
    <div class="alert alert-info">تراکنشی یافت نشد.</div>
<?php endif; ?>

<!-- نمایش pagination -->
<?php if (isset($pagination) && $pagination): ?>
    <?= $pagination ?>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>


<script>
    $(document).ready(function () {
    $('.date-picker').persianDatepicker({
        format: 'YYYY/MM/DD',
        initialValue: false,
        autoClose: true,
        calendar:{
        persian: {
            leapYearMode: 'astronomical'
        }
    }
    });
});

</script>



