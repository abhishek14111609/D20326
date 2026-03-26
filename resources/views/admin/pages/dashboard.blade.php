@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <!-- Welcome -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body py-4">
                        <h4 class="text-primary mb-2">👋 Welcome back, {{ Auth::user()->name }}!</h4>
                        <p class="text-muted mb-0">Here’s an overview of your platform performance today.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-4">
            <!-- Users -->
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Total Users</small>
                                <h3 class="fw-bold mb-0">{{ number_format($stats['total_users'] ?? 0) }}</h3>
                            </div>
                            <span class="stat-icon avatar bg-label-primary rounded-circle p-3">
                                <i class="bx bx-user fs-4"></i>
                            </span>
                        </div>
                        <p class="mt-2 text-muted small">
                            +{{ $stats['new_users_today'] ?? 0 }} new today
                        </p>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Active Users</small>
                                <h3 class="fw-bold mb-0">{{ number_format($stats['active_users'] ?? 0) }}</h3>
                            </div>
                            <span class="stat-icon avatar bg-label-success rounded-circle p-3">
                                <i class="bx bx-user-check fs-4"></i>
                            </span>
                        </div>
                        <ul class="list-unstyled mt-3 mb-0 small text-muted">
                            <li><i class="bx bx-user-plus text-success"></i> {{ number_format($stats['new_active_users_this_month'] ?? 0) }} this month</li>
                            <li><i class="bx bx-user-x text-danger"></i> {{ number_format($stats['inactive_users'] ?? 0) }} inactive</li>
                            <li><i class="bx bx-credit-card text-primary"></i> {{ number_format($stats['paying_users'] ?? 0) }} paying</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Revenue -->
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Total Revenue</small>
                                <h3 class="fw-bold mb-0">${{ number_format($totalRevenue ?? 0, 2) }}</h3>
                            </div>
                            <span class="stat-icon avatar bg-label-warning rounded-circle p-3">
                                <i class="bx bx-dollar fs-4"></i>
                            </span>
                        </div>
                        <p class="mt-2 text-muted small">This month: ${{ number_format($monthlyRevenue ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Transactions -->
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Transactions</small>
                                <h3 class="fw-bold mb-0">{{ number_format($stats['total_transactions'] ?? 0) }}</h3>
                            </div>
                            <span class="stat-icon avatar bg-label-info rounded-circle p-3">
                                <i class="bx bx-credit-card fs-4"></i>
                            </span>
                        </div>
                        <p class="mt-2 small">
                            <span class="text-success">✓ {{ $stats['successful_payments'] ?? 0 }}</span> 
                            <span class="mx-1">|</span>
                            <span class="text-warning">⏳ {{ $stats['pending_payments'] ?? 0 }}</span> 
                            <span class="mx-1">|</span>
                            <span class="text-danger">✗ {{ $stats['failed_payments'] ?? 0 }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mt-4">
            <!-- User Growth -->
            <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                <h6 class="mb-0">User Growth</h6>
                </div>
                <div class="card-body">
                <div id="userGrowthChart"></div>
                </div>
            </div>
            </div>

            <!-- Gender Distribution -->
            <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                <h6 class="mb-0">Gender Distribution</h6>
                </div>
                <div class="card-body">
                <div id="genderChart"></div>
                </div>
            </div>
            </div>
        </div>

        <!-- Revenue -->
        <div class="row mt-4">
         <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                <h6 class="mb-0">Revenue</h6>
                </div>
                <div class="card-body">
                <div id="revenueChart"></div>
            </div>
        </div>
        </div>
         </div>
        

        <!-- Recent Transactions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">💳 Recent Transactions</h6>
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['recent_payments'] as $payment)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="stat-icon avatar bg-label-primary rounded-circle me-2">{{ substr($payment->user->name ?? 'U', 0, 1) }}</span>
                                            <div>
                                                <h6 class="mb-0">{{ $payment->user->name ?? 'Guest' }}</h6>
                                                <small class="text-muted">{{ $payment->transaction_id ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>${{ number_format($payment->amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-label-{{ $statusClass = ['succeeded'=>'success','pending'=>'warning','failed'=>'danger'][$payment->status] ?? 'secondary' }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No recent transactions found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@php
    // Prepare chart data
    $chartData = [
        'months' => json_encode(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']),
        'userGrowth' => json_encode(array_column($monthlyUserData, 'new_users')),
        'revenue' => json_encode(array_column($monthlyData, 'income')),
        'genderData' => [
            'male' => $stats['male_users'] ?? 0,
            'female' => $stats['female_users'] ?? 0,
            'other' => $stats['other_gender'] ?? 0
        ]
    ];
@endphp

@push('page-js')
<script>
document.addEventListener("DOMContentLoaded", function () {
    // User Growth (line chart)
    var userGrowthOptions = {
        chart: { 
            type: 'line', 
            height: 350,
            toolbar: { show: true },
            zoom: { enabled: true }
        },
        series: [{ 
            name: 'New Users', 
            data: {!! $chartData['userGrowth'] !!}
        }],
        xaxis: { 
            categories: {!! $chartData['months'] !!},
            title: { text: 'Months' }
        },
        yaxis: {
            title: { text: 'Number of Users' },
            min: 0
        },
        stroke: {
            width: 3,
            curve: 'smooth'
        },
        markers: {
            size: 5,
            hover: {
                size: 7
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + ' users';
                }
            }
        }
    };
    new ApexCharts(document.querySelector("#userGrowthChart"), userGrowthOptions).render();

    // Revenue (bar chart) with conditional coloring
    var revenueData = {!! $chartData['revenue'] !!};
    var maxRevenue = Math.max(...revenueData);
    var colors = revenueData.map(value => {
        if (value >= maxRevenue * 0.7) {
            return '#10B981'; // Green for high values (top 30%)
        } else if (value >= maxRevenue * 0.3) {
            return '#F59E0B'; // Yellow for medium values (30-70%)
        } else {
            return '#EF4444'; // Red for low values (bottom 30%)
        }
    });

    var revenueOptions = {
        chart: { 
            type: 'bar', 
            height: 350,
            toolbar: { show: true },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800,
                animateGradually: {
                    enabled: true,
                    delay: 150
                },
                dynamicAnimation: {
                    enabled: true,
                    speed: 350
                }
            }
        },
        plotOptions: {
            bar: {
                distributed: true,
                borderRadius: 4,
                horizontal: false,
                columnWidth: '55%',
                endingShape: 'rounded'
            },
        },
        colors: colors,
        dataLabels: {
            enabled: false
        },
        series: [{ 
            name: 'Revenue', 
            data: revenueData
        }],
        xaxis: { 
            categories: {!! $chartData['months'] !!},
            title: { text: 'Months' },
            labels: {
                style: {
                    colors: '#6B7280',
                    fontSize: '12px',
                    fontFamily: 'Inter, sans-serif',
                }
            },
            axisBorder: {
                show: true,
                color: '#E5E7EB',
                height: 1,
                width: '100%',
                offsetX: 0,
                offsetY: 0
            },
            axisTicks: {
                show: true,
                borderType: 'solid',
                color: '#E5E7EB',
                height: 6,
                offsetX: 0,
                offsetY: 0
            },
        },
        yaxis: {
            title: { 
                text: 'Amount ($)',
                style: {
                    color: '#6B7280',
                    fontSize: '12px',
                    fontFamily: 'Inter, sans-serif',
                }
            },
            labels: {
                style: {
                    colors: '#6B7280',
                    fontSize: '12px',
                    fontFamily: 'Inter, sans-serif',
                },
                formatter: function(val) {
                    return '$' + val.toLocaleString();
                }
            },
        },
        grid: {
            borderColor: '#E5E7EB',
            strokeDashArray: 4,
            yaxis: {
                lines: {
                    show: true
                }
            },
            xaxis: {
                lines: {
                    show: false
                }
            },
            padding: {
                top: 0,
                right: 20,
                bottom: 0,
                left: 20
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return '$' + val.toLocaleString();
                }
            },
            style: {
                fontSize: '14px',
                fontFamily: 'Inter, sans-serif',
            },
            theme: 'light',
            fillSeriesColor: false
        },
        states: {
            hover: {
                filter: {
                    type: 'darken',
                    value: 0.1,
                }
            },
            active: {
                filter: {
                    type: 'darken',
                    value: 0.2,
                }
            },
        }
    };
    new ApexCharts(document.querySelector("#revenueChart"), revenueOptions).render();

    // Gender Distribution (donut chart)
    var genderOptions = {
        chart: {
            type: 'donut',
            height: 350,
        },
        series: [
            {{ $chartData['genderData']['male'] }}, 
            {{ $chartData['genderData']['female'] }},
            {{ $chartData['genderData']['other'] ?? 0 }}
        ],
        labels: ['Male', 'Female', 'Other'],
        colors: ['#3B82F6', '#EC4899', '#10B981'],
        legend: {
            position: 'bottom',
            horizontalAlign: 'center'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total Users',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                            }
                        }
                    }
                }
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 300
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };
    new ApexCharts(document.querySelector("#genderChart"), genderOptions).render();
});
</script>
@endpush
@endsection