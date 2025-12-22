
<div id="dashboardApp" class="container mt-4">
    <!-- خوش آمدگویی -->
    <div class="row mb-4">
        <div class="col-12">
            <h2>خوش آمدید، {{ user.first_name }}! 👋</h2>
            <p class="text-muted">خلاصه وضعیت مالی شما</p>
        </div>
    </div>

    <!-- کارت‌های آماری -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card income p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">کل درآمد</h6>
                        <h4 class="mb-0">{{ formatNumber(financial_summary.total_income) }} تومان</h4>
                    </div>
                    <i class="bi bi-arrow-up-circle display-4 opacity-75"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card expense p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">کل هزینه</h6>
                        <h4 class="mb-0">{{ formatNumber(financial_summary.total_expense) }} تومان</h4>
                    </div>
                    <i class="bi bi-arrow-down-circle display-4 opacity-75"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card balance p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">موجودی</h6>
                        <h4 class="mb-0">{{ formatNumber(user.balance) }} تومان</h4>
                    </div>
                    <i class="bi bi-wallet display-4 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- نمودار و تراکنش‌های اخیر -->
    <div class="row">
        <!-- نمودار -->
        <div class="col-lg-8 mb-3 mb-lg-0">
            <div class="card main-card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">📊 روند ماهانه درآمد و هزینه</h5>
                </div>
                <div class="card-body">
                    <canvas id="financialChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- تراکنش‌های اخیر -->
        <div class="col-lg-4">
            <div class="card main-card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">🔄 تراکنش‌های اخیر</h5>
                </div>
                <div class="card-body p-0">
                    <div v-if="recent_transactions.length" class="list-group list-group-flush">
                        <div v-for="tx in recent_transactions" :key="tx.id" 
                             :class="['list-group-item', tx.type === 'income' ? 'transaction-income' : 'transaction-expense']">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ tx.category_name }}</h6>
                                    <p class="mb-1 text-muted small">{{ tx.description || 'بدون توضیح'}}</p>
                                    <small class="text-muted">{{ formatDate(tx.transaction_date) }}</small>
                                </div>
                                <div class="text-end text-nowrap">
                                    <span :class="tx.type === 'income' ? 'text-success fw-bold' : 'text-danger fw-bold'">
                                        {{ tx.type === 'income' ? '+' : '-' }}{{ formatNumber(tx.amount) }}
                                    </span>
                                    <small class="text-muted">تومان</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-4"> 
                        <i class="bi bi-receipt display-4 text-muted"></i>
                        <p class="text-muted mt-2">تراکنشی یافت نشد</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>


<script>
// تعریف داده‌ها (همانند قبل)
window.DASHBOARD_DATA = {
    user: <?= json_encode($user) ?>,
    financial_summary: <?= json_encode($financial_summary) ?>,
    recent_transactions: <?= json_encode($recent_transactions) ?>,
    monthly_data: <?= json_encode($monthly_data) ?>
};

// تبدیل امن monthly_data
if (typeof window.DASHBOARD_DATA.monthly_data === 'string') {
    try {
        window.DASHBOARD_DATA.monthly_data = JSON.parse(window.DASHBOARD_DATA.monthly_data);
    } catch(e) {
        window.DASHBOARD_DATA.monthly_data = [];
        console.error('monthly_data parse error:', e);
    }
}

const { createApp, ref, onMounted, nextTick } = Vue;

createApp({
  setup() {
    // Stateها
    const user = ref({});
    const financial_summary = ref({ total_income: 0, total_expense: 0 });
    const recent_transactions = ref([]);
    const monthly_data = ref([]);

    // تبدیل اعداد به فارسی
    const toPersianDigits = (str) => {
      const en = '0123456789';
      const fa = '۰۱۲۳۴۵۶۷۸۹';
      return String(str).replace(/[0-9]/g, d => fa[en.indexOf(d)]);
    };

    const formatNumber = (num) => {
      if (num == null || num === '') return toPersianDigits('0');
      const n = parseFloat(num);
      if (isNaN(n)) return toPersianDigits('0');
      return n.toLocaleString('fa-IR');
    };

    const formatDate = (dateStr) => {
      if (!dateStr) return '';
      try {
        const j = new jDate(dateStr);
        return toPersianDigits(j.format('Y/m/d'));
      } catch (e) {
        console.warn('Date parse failed:', dateStr);
        return toPersianDigits('');
      }
    };

    const initChart = () => {
      if (!Array.isArray(monthly_data.value)  || !monthly_data.value.length) return;

      const labels = monthly_data.value.map(item => toPersianDigits(item.month || ''));
      const incomeData = monthly_data.value.map(item => parseFloat(item.income) || 0);
      const expenseData = monthly_data.value.map(item => parseFloat(item.expense) || 0);

      new Chart(document.getElementById('financialChart'), {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: 'درآمد',
              borderColor: 'rgba(40, 167, 69, 1)',
              backgroundColor: 'rgba(40, 167, 69, 0.2)',
              data: incomeData,
              fill: false,
              borderWidth: 2
            },
            {
              label: 'هزینه',
              borderColor: 'rgba(220, 53, 69, 1)',
              backgroundColor: 'rgba(220, 53, 69, 0.2)',
              data: expenseData,
              fill: false,
              borderWidth: 2
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          legend: { display: true, rtl: true },
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero: true,
                callback: (value) => toPersianDigits(value.toLocaleString('fa-IR')) + ' ت'
              }
            }],
            xAxes: [{ ticks: { autoSkip: false } }]
          },
          tooltips: {
            rtl: true,
            callbacks: {
              label: (tooltipItem, data) => {
                const dataset = data.datasets[tooltipItem.datasetIndex];
                const value = dataset.data[tooltipItem.index];
                const formatted = toPersianDigits(value.toLocaleString('fa-IR'));
                return `${dataset.label}: ${formatted} تومان`;
              }
            }
          }
        }
      });
    };

    onMounted(() => {
      // داده‌ها را از window بخوان
      const data = window.DASHBOARD_DATA || {};
      user.value = data.user || {};
      financial_summary.value = data.financial_summary || { total_income: 0, total_expense: 0 };
      recent_transactions.value = data.recent_transactions || [];
      monthly_data.value = Array.isArray(data.monthly_data) ? data.monthly_data : [];

      nextTick(() => {
        initChart();
      });
    });

    return {
      user,
      financial_summary,
      recent_transactions,
      formatNumber,
      formatDate
    };
  }
}).mount('#dashboardApp');
</script>