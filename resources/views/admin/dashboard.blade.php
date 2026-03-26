@extends('admin.layouts.app')

@section('title', 'Analytics Dashboard')

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}">
    <style>
        /* Main Layout */
        .content-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .container-xxl {
            flex: 1;
            padding: 2rem 2.5rem;
            width: 100%;
            max-width: 100%;
        }
        
        /* Cards */
        .card {
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.1);
            border: none;
            border-radius: 0.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px 0 rgba(67, 89, 113, 0.15);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #566a7f;
        }
        
        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.1);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px 0 rgba(67, 89, 113, 0.15);
        }
        
        .stat-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 0.5rem;
            margin-right: 1rem;
            font-size: 1.5rem;
            color: #fff;
        }
        
        .stat-icon.bg-label-primary { background-color: rgba(105, 108, 255, 0.1); color: #696cff; }
        .stat-icon.bg-label-success { background-color: rgba(40, 199, 111, 0.1); color: #28c76f; }
        .stat-icon.bg-label-info { background-color: rgba(32, 201, 243, 0.1); color: #20c9f3; }
        .stat-icon.bg-label-warning { background-color: rgba(255, 171, 0, 0.1); color: #ffab00; }
        
        .stat-info h6 {
            color: #6f6b7d;
            margin-bottom: 0.25rem;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .stat-info h4 {
            margin-bottom: 0;
            font-weight: 600;
            color: #5d596c;
            font-size: 1.5rem;
        }
        
        /* Charts */
        .chart-container {
            margin-bottom: 2rem;
        }
        
        .chart-card {
            height: 100%;
        }
        
        .chart-card .card-body {
            padding: 1.5rem 1.5rem 0.5rem;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 1200px) {
            .container-xxl {
                padding: 1.5rem 1.5rem;
            }
        }
        
        @media (max-width: 992px) {
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .container-xxl {
                padding: 1.25rem 1rem;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Stats Cards -->
    <div class="stats-cards row d-flex justify-content-center">
    <!-- Total Users -->
    <div class="stat-card col-6 col-md-3">
        <div class="stat-icon bg-label-primary">
            <i class='bx bx-user'></i>
        </div>
        <div class="stat-info">
            <h6>Total Users</h6>
            <h4>{{ number_format($totalUsers ?? 0) }}</h4>
        </div>
    </div>

    <!-- Active Sessions -->
    <div class="stat-card col-6 col-md-3">
        <div class="stat-icon bg-label-success">
            <i class='bx bx-chart'></i>
        </div>
        <div class="stat-info">
            <h6>Active Sessions</h6>
            <h4>{{ number_format($activeSessions ?? 0) }}</h4>
        </div>
    </div>

    <!-- Total Revenue -->
    <div class="stat-card col-6 col-md-3">
        <div class="stat-icon bg-label-info">
            <i class='bx bx-dollar-circle'></i>
        </div>
        <div class="stat-info">
            <h6>Total Revenue</h6>
            <h4>${{ number_format($totalRevenue ?? 0, 2) }}</h4>
        </div>
    </div>
    </div>

    <!-- New Messages -->
    <div class="stat-card col-6 col-md-3">
        <div class="stat-icon bg-label-warning">
            <i class='bx bx-message-dots'></i>
        </div>
        <div class="stat-info">
            <h6>New Messages</h6>
            <h4>{{ number_format($newMessages ?? 0) }}</h4>
        </div>
    </div>
</div>


    <!-- Main Content -->
    <div class="row">
        <!-- Revenue Chart -->
        <div class="col-12 col-lg-8 mb-4">
            <div class="card chart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Revenue Overview</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="revenueReport" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="revenueReport">
                            <li><a class="dropdown-item" href="javascript:void(0);">Last 7 Days</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);">Last Month</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);">Last Year</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div id="revenueChart" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>

        <!-- Sales Analytics -->
        <div class="col-12 col-lg-4 mb-4">
            <div class="card chart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Sales Analytics</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="salesAnalytics" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="salesAnalytics">
                            <li><a class="dropdown-item" href="javascript:void(0);">Last 7 Days</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);">Last Month</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);">Last Year</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div id="salesAnalyticsChart" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Transactions</h5>
                    <a href="javascript:void(0)" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            <tr>
                                <td>#4567</td>
                                <td>John Doe</td>
                                <td>2023-06-01</td>
                                <td>$120.00</td>
                                <td><span class="badge bg-label-success">Completed</span></td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                            <a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-trash me-1"></i> Delete</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <!-- More rows... -->
                            <tr>
                                <td>#4566</td>
                                <td>Jane Smith</td>
                                <td>2023-05-30</td>
                                <td>$85.50</td>
                                <td><span class="badge bg-label-warning">Pending</span></td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                            <a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-trash me-1"></i> Delete</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
        
        <div class="col-lg-4 col-md-4 order-1">
            <div class="row">
                <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <img src="{{ asset('assets/img/icons/unicons/chart-success.png') }}" alt="chart success" class="rounded">
                                </div>
                                <div class="dropdown">
                                    <button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3">
                                        <a class="dropdown-item" href="javascript:void(0);">View More</a>
                                        <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                    </div>
                                </div>
                            </div>
                            <span class="fw-semibold d-block mb-1">Total Users</span>
                            <h3 class="card-title mb-2">{{ $totalUsers ?? 0 }}</h3>
                            <small class="text-success fw-semibold"><i class='bx bx-up-arrow-alt'></i> +72.80%</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="Credit Card" class="rounded">
                                </div>
                                <div class="dropdown">
                                    <button class="btn p-0" type="button" id="cardOpt6" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt6">
                                        <a class="dropdown-item" href="javascript:void(0);">View More</a>
                                        <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                    </div>
                                </div>
                            </div>
                            <span>Revenue</span>
                            <h3 class="card-title text-nowrap mb-1">${{ number_format($totalRevenue ?? 0) }}</h3>
                            <small class="text-success fw-semibold"><i class='bx bx-up-arrow-alt'></i> +28.14%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Total Revenue -->
        <div class="col-12 col-lg-8 order-2 order-md-3 order-lg-2 mb-4">
            <div class="card">
                <div class="row row-bordered g-0">
                    <div class="col-md-8">
                        <h5 class="card-header m-0 me-2 pb-3">Total Revenue</h5>
                        <div id="totalRevenueChart" class="px-2"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-body">
                            <div class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="growthReportId" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        2023
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="growthReportId">
                                        <a class="dropdown-item" href="javascript:void(0);">2022</a>
                                        <a class="dropdown-item" href="javascript:void(0);">2021</a>
                                        <a class="dropdown-item" href="javascript:void(0);">2020</a>
                                    </div>
                                </div>
                            </div>
                            <div id="growthChart"></div>
                            <div class="text-center fw-semibold pt-3 mb-2">62% Company Growth</div>
                            <div class="d-flex px-xxl-4 px-lg-2 p-4 gap-xxl-3 gap-lg-1 gap-3 justify-content-between">
                                <div class="d-flex">
                                    <div class="me-2">
                                        <span class="badge bg-label-primary p-2"><i class="bx bx-dollar text-primary"></i></span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <small>2022</small>
                                        <h6 class="mb-0">$32.5k</h6>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="me-2">
                                        <span class="badge bg-label-info p-2"><i class="bx bx-wallet text-info"></i></span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <small>2023</small>
                                        <h6 class="mb-0">$41.2k</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Total Revenue -->
        
        <!-- Order Statistics -->
        <div class="col-12 col-md-8 col-lg-4 order-3 order-md-2">
            <div class="row">
                <div class="col-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="Credit Card" class="rounded">
                                </div>
                                <div class="dropdown">
                                    <button class="btn p-0" type="button" id="cardOpt4" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt4">
                                        <a class="dropdown-item" href="javascript:void(0);">View More</a>
                                        <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                    </div>
                                </div>
                            </div>
                            <span class="d-block mb-1">Payments</span>
                            <h3 class="card-title text-nowrap mb-2">$2,456</h3>
                            <small class="text-danger fw-semibold"><i class='bx bx-down-arrow-alt'></i> -14.82%</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <img src="{{ asset('assets/img/icons/unicons/cc-primary.png') }}" alt="Credit Card" class="rounded">
                                </div>
                                <div class="dropdown">
                                    <button class="btn p-0" type="button" id="cardOpt1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="cardOpt1">
                                        <a class="dropdown-item" href="javascript:void(0);">View More</a>
                                        <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                    </div>
                                </div>
                            </div>
                            <span class="fw-semibold d-block mb-1">Transactions</span>
                            <h3 class="card-title mb-2">$14,857</h3>
                            <small class="text-success fw-semibold"><i class='bx bx-up-arrow-alt'></i> +28.14%</small>
                        </div>
                    </div>
                </div>
                <!-- </div>
                <div class="row"> -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between flex-sm-row flex-column gap-3">
                                <div class="d-flex flex-sm-column flex-row align-items-start justify-content-between">
                                    <div class="card-title">
                                        <h5 class="text-nowrap mb-2">Profile Report</h5>
                                        <span class="badge bg-label-warning rounded-pill">Year 2023</span>
                                    </div>
                                    <div class="mt-sm-auto">
                                        <small class="text-success text-nowrap fw-semibold"><i class='bx bx-chevron-up'></i> 68.2%</small>
                                        <h3 class="mb-0">$84,686k</h3>
                                    </div>
                                </div>
                                <div id="profileReportChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Order Statistics -->
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- User Registration Chart -->
        <div class="col-12 col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">User Registration Trend</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="userChartDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userChartDropdown">
                            <a class="dropdown-item" href="javascript:void(0);">Last 7 Days</a>
                            <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
                            <a class="dropdown-item" href="javascript:void(0);">Last Year</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="userChart" class="chartjs" style="min-height: 300px; height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-12 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Activity</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="activityDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="activityDropdown">
                            <a class="dropdown-item" href="javascript:void(0);">View All</a>
                            <a class="dropdown-item" href="javascript:void(0);">Mark all as read</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="timeline mt-3 mb-0">
                        <li class="timeline-item timeline-item-transparent">
                            <span class="timeline-point timeline-point-primary"></span>
                            <div class="timeline-event">
                                <div class="timeline-header mb-1">
                                    <h6 class="mb-0">New user registered</h6>
                                    <small class="text-muted">2 hours ago</small>
                                </div>
                                <p class="mb-0">A new user has joined the platform</p>
                            </div>
                        </li>
                        <li class="timeline-item timeline-item-transparent">
                            <span class="timeline-point timeline-point-success"></span>
                            <div class="timeline-event">
                                <div class="timeline-header mb-1">
                                    <h6 class="mb-0">New match created</h6>
                                    <small class="text-muted">5 hours ago</small>
                                </div>
                                <p class="mb-0">Two users have matched with each other</p>
                            </div>
                        </li>
                        <li class="timeline-item timeline-item-transparent">
                            <span class="timeline-point timeline-point-danger"></span>
                            <div class="timeline-event">
                                <div class="timeline-header mb-1">
                                    <h6 class="mb-0">New message received</h6>
                                    <small class="text-muted">1 day ago</small>
                                </div>
                                <p class="mb-0">A new message has been sent between users</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script>
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Revenue Chart
        var revenueChartEl = document.getElementById('revenueChart');
        if (revenueChartEl) {
            var revenueChart = new ApexCharts(revenueChartEl, {
                chart: {
                    height: 350,
                    type: 'area',
                    fontFamily: 'inherit',
                    parentHeightOffset: 0,
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true
                    },
                    zoom: {
                        enabled: false
                    },
                    foreColor: '#6f6b7d'
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                series: [{
                    name: 'Revenue',
                    data: [0, 20000, 35000, 25000, 42000, 38000, 50000]
                }],
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                },
                colors: ['#696cff'],
                grid: {
                    borderColor: '#e7e7e7',
                    strokeDashArray: 3,
                    xaxis: {
                        lines: {
                            show: false
                        }
                    },
                    padding: {
                        top: 0,
                        right: 0,
                        bottom: 0,
                        left: 0
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            });
            revenueChart.render();
        }

        // Sales Analytics Chart (Donut)
        var salesAnalyticsChartEl = document.getElementById('salesAnalyticsChart');
        if (salesAnalyticsChartEl) {
            var salesAnalyticsChart = new ApexCharts(salesAnalyticsChartEl, {
                chart: {
                    height: 350,
                    type: 'donut',
                    fontFamily: 'inherit',
                    parentHeightOffset: 0,
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true
                    }
                },
                dataLabels: {
                    enabled: false
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '14px',
                                    fontFamily: 'inherit',
                                    color: '#6f6b7d',
                                    offsetY: 15
                                },
                                value: {
                                    show: true,
                                    fontSize: '24px',
                                    fontFamily: 'inherit',
                                    color: '#5d596c',
                                    offsetY: -15,
                                    formatter: function(val) {
                                        return '$' + parseInt(val).toLocaleString();
                                    }
                                },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Weekly Sales',
                                    fontSize: '14px',
                                    fontFamily: 'inherit',
                                    color: '#6f6b7d',
                                    formatter: function(w) {
                                        return w.globals.seriesTotals.reduce((a, b) => {
                                            return a + b;
                                        }, 0).toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                },
                colors: ['#696cff', '#8592a3', '#71dd37', '#ff3e1d', '#ffab00'],
                series: [12500, 5000, 7500, 10000, 15000],
                labels: ['Electronics', 'Fashion', 'Home & Living', 'Beauty', 'Sports'],
                legend: {
                    show: true,
                    position: 'bottom',
                    horizontalAlign: 'center',
                    fontSize: '13px',
                    itemMargin: {
                        horizontal: 10,
                        vertical: 5
                    },
                    onItemClick: {
                        toggleDataSeries: false
                    },
                    onItemHover: {
                        highlightDataSeries: true
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            });
            salesAnalyticsChart.render();
        }
    });
  // Total Revenue Chart
  let cardColor, headingColor, axisColor, shadeColor, borderColor;

  cardColor = config.colors.white;
  headingColor = config.colors.headingColor;
  axisColor = config.colors.axisColor;
  borderColor = config.colors.borderColor;

  // Total Revenue Report Chart - Bar Chart
  // --------------------------------------------------------------------
  const totalRevenueChartEl = document.querySelector('#totalRevenueChart'),
    totalRevenueChartOptions = {
      series: [
        {
          name: '2023',
          data: [18000, 22000, 26000, 21000, 28000, 32000, 36000, 38000, 42000, 38000, 45000, 50000]
        },
        {
          name: '2022',
          data: [15000, 19000, 22000, 18000, 24000, 28000, 30000, 32000, 36000, 34000, 40000, 45000]
        }
      ],
      chart: {
        height: 300,
        stacked: true,
        type: 'bar',
        toolbar: { show: false }
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '33%',
          borderRadius: 12,
          startingShape: 'rounded',
          endingShape: 'rounded'
        },
      },
      colors: [config.colors.primary, config.colors.info],
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'smooth',
        width: 6,
        lineCap: 'round',
        colors: [cardColor]
      },
      legend: {
        show: true,
        horizontalAlign: 'left',
        position: 'top',
        markers: {
          height: 8,
          width: 8,
          radius: 12,
          offsetX: -3
        },
        labels: {
          colors: axisColor
        },
        itemMargin: {
          horizontal: 10
        }
      },
      grid: {
        borderColor: borderColor,
        padding: {
          top: 0,
          bottom: -8,
          left: 20,
          right: 20
        }
      },
      xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        labels: {
          style: {
            fontSize: '13px',
            colors: axisColor
          }
        },
        axisTicks: {
          show: false
        },
        axisBorder: {
          show: false
        }
      },
      yaxis: {
        labels: {
          style: {
            fontSize: '13px',
            colors: axisColor
          }
        }
      },
      responsive: [
        {
          breakpoint: 1700,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '32%'
              }
            }
          }
        },
        {
          breakpoint: 1580,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '35%'
              }
            }
          }
        },
        {
          breakpoint: 1440,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '42%'
              }
            }
          }
        },
        {
          breakpoint: 1300,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '48%'
              }
            }
          }
        },
        {
          breakpoint: 1200,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '40%'
              }
            }
          }
        },
        {
          breakpoint: 1040,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 11,
                columnWidth: '48%'
              }
            }
          }
        },
        {
          breakpoint: 991,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '30%'
              }
            }
          }
        },
        {
          breakpoint: 840,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '35%'
              }
            }
          }
        },
        {
          breakpoint: 768,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '28%'
              }
            }
          }
        },
        {
          breakpoint: 640,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '32%'
              }
            }
          }
        },
        {
          breakpoint: 576,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '37%'
              }
            }
          }
        },
        {
          breakpoint: 480,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '45%'
              }
            }
          }
        },
        {
          breakpoint: 420,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '52%'
              }
            }
          }
        },
        {
          breakpoint: 380,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '60%'
              }
            }
          }
        }
      ],
      states: {
        hover: {
          filter: { type: 'none' }
        },
        active: {
          filter: { type: 'none' }
        }
      }
    };
  if (typeof totalRevenueChartEl !== undefined && totalRevenueChartEl !== null) {
    const totalRevenueChart = new ApexCharts(totalRevenueChartEl, totalRevenueChartOptions);
    totalRevenueChart.render();
  }

  // Growth Chart - Radial Bar Chart
  // --------------------------------------------------------------------
  const growthChartEl = document.querySelector('#growthChart'),
    growthChartOptions = {
      series: [62],
      labels: ['Growth'],
      chart: {
        height: 150,
        type: 'radialBar',
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        radialBar: {
          hollow: {
            size: '70%'
          },
          track: {
            background: borderColor,
            strokeWidth: '50%',
            margin: 15
          },
          dataLabels: {
            name: {
              offsetY: 25,
              color: headingColor,
              fontSize: '13px',
              fontWeight: '500',
              fontFamily: 'Public Sans'
            },
            value: {
              offsetY: -15,
              color: headingColor,
              fontSize: '22px',
              fontWeight: '500',
              fontFamily: 'Public Sans'
            }
          },
          fill: {
            type: 'gradient',
            gradient: {
              shade: 'dark',
              shadeIntensity: 0.5,
              gradientToColors: [config.colors.primary],
              inverseColors: true,
              opacityFrom: 1,
              opacityTo: 0.6,
              stops: [30, 70, 100]
            }
          }
        }
      },
      stroke: {
        dashArray: 5
      },
      colors: [config.colors.primary],
      grid: {
        padding: {
          top: -35,
          bottom: -10
        }
      },
      states: {
        hover: {
          filter: { type: 'none' }
        },
        active: {
          filter: { type: 'none' }
        }
      }
    };
  if (typeof growthChartEl !== undefined && growthChartEl !== null) {
    const growthChart = new ApexCharts(growthChartEl, growthChartOptions);
    growthChart.render();
  }

  // Profile Report Chart - Radial Bar Chart
  // --------------------------------------------------------------------
  const profileReportChartEl = document.querySelector('#profileReportChart'),
    profileReportChartConfig = {
      chart: {
        height: 80,
        // width: 175,
        type: 'radialBar',
        sparkline: {
          enabled: true
        },
        parentHeightOffset: 0,
        offsetY: 0
      },
      grid: {
        padding: {
          top: 12,
          bottom: 12
        }
      },
      plotOptions: {
        radialBar: {
          offsetY: 0,
          startAngle: 90,
          endAngle: 90,
          hollow: {
            size: '60%'
          },
          track: {
            background: borderColor
          },
          dataLabels: {
            name: {
              show: false
            },
            value: {
              show: false
            }
          }
        }
      },
      stroke: {
        lineCap: 'round'
      },
      colors: [config.colors.warning],
      series: [68],
      labels: ['Progress']
    };
  if (typeof profileReportChartEl !== undefined && profileReportChartEl !== null) {
    const profileReportChart = new ApexCharts(profileReportChartEl, profileReportChartConfig);
    profileReportChart.render();
  }
</script>
@endpush
<script>
    // User registration chart
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('userChart');
        if (ctx) {
            const userChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'New Users',
                        data: [12, 19, 3, 5, 2, 3],
                        backgroundColor: 'rgba(105, 108, 255, 0.1)',
                        borderColor: 'rgba(105, 108, 255, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#fff',
                            titleColor: '#697a8d',
                            bodyColor: '#697a8d',
                            borderColor: 'rgba(67, 89, 113, 0.1)',
                            borderWidth: 1,
                            padding: 10
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: 'rgba(67, 89, 113, 0.1)'
                            },
                            ticks: {
                                color: '#697a8d'
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: '#697a8d'
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
