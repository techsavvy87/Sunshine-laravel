@extends('layouts.main')
@section('title', 'Dashboard')

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Business Overview</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="./dashboards-ecommerce.html">PawPrints</a></li>
      <li>Dashboards</li>
    </ul>
  </div>
</div>
<div class="mt-6">
  <!--  Start: Dashboard Stats Widget -->

    <div class="grid gap-5 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
      <div class="card bg-base-100 shadow">
          <div class="card-body gap-2">
              <div class="flex items-start justify-between gap-2 text-sm">
                  <div>
                      <p class="text-base-content/80 font-medium">
                          Today's Revenue
                      </p>
                      <div class="mt-3 flex items-center gap-2">
                          <p class="inline text-2xl font-semibold">
                              ${{ number_format($todayRevenue, 2) }}
                          </p>
                          @if($percentageChange != 0)
                          <div
                              class="badge badge-soft {{ $percentageChange > 0 ? 'badge-success' : 'badge-error' }} badge-sm gap-0.5 px-1 font-medium">
                              <span
                                  class="iconify lucide--arrow-{{ $percentageChange > 0 ? 'up' : 'down' }} size-3.5"></span>
                              {{ abs($percentageChange) }}%
                          </div>
                          @endif
                      </div>
                  </div>
                  <div
                      class="bg-base-200 rounded-box flex items-center p-2">
                      <span
                          class="iconify lucide--circle-dollar-sign size-5"></span>
                  </div>
              </div>
              <p class="text-base-content/60 text-sm">
                  vs.
                  <span class="mx-1">${{ number_format($yesterdayRevenue, 2) }}</span>
                  yesterday
              </p>
          </div>
      </div>
      <div class="card bg-base-100 shadow">
          <div class="card-body gap-2">
              <div class="flex items-start justify-between gap-2 text-sm">
                  <div>
                      <p class="text-base-content/80 font-medium">
                          Today's Appointments
                      </p>
                      <div class="mt-3 flex items-center gap-2">
                          <p class="inline text-2xl font-semibold">
                              {{ number_format($todayAppointments) }}
                          </p>
                          @if($appointmentPercentageChange != 0)
                          <div
                              class="badge badge-soft {{ $appointmentPercentageChange > 0 ? 'badge-success' : 'badge-error' }} badge-sm gap-0.5 px-1 font-medium">
                              <span
                                  class="iconify lucide--arrow-{{ $appointmentPercentageChange > 0 ? 'up' : 'down' }} size-3.5"></span>
                              {{ abs($appointmentPercentageChange) }}%
                          </div>
                          @endif
                      </div>
                  </div>
                  <div
                      class="bg-base-200 rounded-box flex items-center p-2">
                      <span class="iconify lucide--calendar size-5"></span>
                  </div>
              </div>
              <p class="text-base-content/60 text-sm">
                  vs.
                  <span class="mx-1">{{ number_format($yesterdayAppointments) }}</span>
                  yesterday
              </p>
          </div>
      </div>
      <div class="card bg-base-100 shadow">
          <div class="card-body gap-2">
              <div class="flex items-start justify-between gap-2 text-sm">
                  <div>
                      <p class="text-base-content/80 font-medium">
                          Customers
                      </p>
                      <div class="mt-3 flex items-center gap-2">
                          <p class="inline text-2xl font-semibold">
                              {{ number_format($totalCustomers) }}
                          </p>
                          @if($customerPercentageChange != 0)
                          <div
                              class="badge badge-soft {{ $customerPercentageChange > 0 ? 'badge-success' : 'badge-error' }} badge-sm gap-0.5 px-1 font-medium">
                              <span
                                  class="iconify lucide--arrow-{{ $customerPercentageChange > 0 ? 'up' : 'down' }} size-3.5"></span>
                              {{ abs($customerPercentageChange) }}%
                          </div>
                          @endif
                      </div>
                  </div>
                  <div
                      class="bg-base-200 rounded-box flex items-center p-2">
                      <span class="iconify lucide--users size-5"></span>
                  </div>
              </div>
              <p class="text-base-content/60 text-sm">
                  @if($todayNewCustomers > 0 || $yesterdayCustomers > 0)
                  {{ $todayNewCustomers }} new today
                  @if($yesterdayCustomers > 0)
                  vs. {{ $yesterdayCustomers }} yesterday
                  @endif
                  @else
                  Total registered customers
                  @endif
              </p>
          </div>
      </div>
      <div class="card bg-base-100 shadow">
          <div class="card-body gap-2">
              <div class="flex items-start justify-between gap-2 text-sm">
                  <div>
                      <p class="text-base-content/80 font-medium">
                          Pets
                      </p>
                      <div class="mt-3 flex items-center gap-2">
                          <p class="inline text-2xl font-semibold">
                              {{ number_format($totalPets) }}
                          </p>
                      </div>
                  </div>
                  <div
                      class="bg-base-200 rounded-box flex items-center p-2">
                      <span class="iconify lucide--brain-circuit size-5"></span>
                  </div>
              </div>
              <p class="text-base-content/60 text-sm">
                  Total registered pets
              </p>
          </div>
      </div>
      <div class="card bg-base-100 shadow">
          <div class="card-body gap-2">
              <div class="flex items-start justify-between gap-2 text-sm">
                  <div>
                      <p class="text-base-content/80 font-medium">
                          Check-Ins
                      </p>
                      <div class="mt-3 flex items-center gap-2">
                          <p class="inline text-2xl font-semibold">
                              {{ number_format($todayBoardingCheckins) }}
                          </p>
                      </div>
                  </div>
                  <div class="bg-base-200 rounded-box flex items-center p-2">
                      <span class="iconify lucide--log-in size-5"></span>
                  </div>
              </div>
              <p class="text-base-content/60 text-sm">
                  Today: {{ number_format($todayBoardingCheckins) }} check-ins
              </p>
          </div>
      </div>
      <div class="card bg-base-100 shadow">
          <div class="card-body gap-2">
              <div class="flex items-start justify-between gap-2 text-sm">
                  <div>
                      <p class="text-base-content/80 font-medium">
                          Check-Outs
                      </p>
                      <div class="mt-3 flex items-center gap-2">
                          <p class="inline text-2xl font-semibold">
                              {{ number_format($todayBoardingCheckouts) }}
                          </p>
                      </div>
                  </div>
                  <div class="bg-base-200 rounded-box flex items-center p-2">
                      <span class="iconify lucide--log-out size-5"></span>
                  </div>
              </div>
              <p class="text-base-content/60 text-sm">
                  Today: {{ number_format($todayBoardingCheckouts) }} check-outs
              </p>
          </div>
      </div>
  </div>

  <!--  End: Dashboard Stats Widget -->

  <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-12">
      <div class="xl:col-span-7">
          <!--  Start: Revenue Statistic -->

          <div class="card bg-base-100 shadow">
              <div class="card-body px-0 pb-0">
                  <div class="px-6">
                      <div class="flex items-start justify-between">
                          <span class="font-medium">
                              Revenue Statistics
                          </span>
                          <div
                              class="tabs tabs-box tabs-xs hidden sm:block" id="revenue-period-tabs">
                              <a href="?period=day" class="tab {{ $period === 'day' ? 'tab-active' : '' }} px-3">Day</a>
                              <a href="?period=week" class="tab {{ $period === 'week' ? 'tab-active' : '' }} px-3">Week</a>
                              <a href="?period=month" class="tab {{ $period === 'month' ? 'tab-active' : '' }} px-3">Month</a>
                          </div>
                      </div>
                      <div class="mt-3">
                          <div class="flex items-center gap-3">
                              <span class="text-4xl font-semibold">
                                  ${{ number_format($revenueData['total'] / 1000, 2) }}K
                              </span>
                              @if($revenueData['percentageChange'] != 0)
                              <span class="{{ $revenueData['percentageChange'] > 0 ? 'text-success' : 'text-error' }} font-medium">
                                  {{ $revenueData['percentageChange'] > 0 ? '+' : '' }}{{ number_format($revenueData['percentageChange'], 2) }}%
                              </span>
                              @endif
                          </div>
                          <span class="text-base-content/60 text-sm">
                              Total income in this {{ $period === 'day' ? 'week' : ($period === 'week' ? 'period' : 'year') }}
                          </span>
                      </div>
                  </div>
                  <div id="revenue-statics-chart"></div>
              </div>
          </div>

          <!--  End: Revenue Statistic -->
      </div>
      <div class="xl:col-span-5">

        <!--  Start: Recent Orders -->

          <div aria-label="Card" class="card bg-base-100 shadow">
              <div class="card-body p-0">
                  <div class="flex items-center gap-3 px-5 pt-5">
                      <span
                          class="iconify lucide--calendar size-4.5"></span>
                      <span class="font-medium">Recent Appointments</span>
                      <a href="{{ route('appointments') }}"
                          class="btn btn-outline border-base-300 btn-sm ms-auto">
                          <span
                              class="iconify lucide--arrow-right size-3.5"></span>
                          View All
                      </a>
                  </div>
                  <div class="mt-2 overflow-auto">
                      <table class="table *:text-nowrap">
                          <thead>
                              <tr>
                                  <th>Pet</th>
                                  <th>Service</th>
                                  <th>Price</th>
                                  <th>Date</th>
                                  <th>Status</th>
                                  <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>
                              @forelse($recentAppointments as $appointment)
                              <tr>
                                  <td
                                      class="flex items-center space-x-3 truncate">
                                      @if($appointment->pet && $appointment->pet->pet_img)
                                      <img
                                          alt="pet image"
                                          class="mask mask-squircle bg-base-200 size-7.5"
                                          src="{{ empty($appointment->pet->pet_img) ? asset('images/no_image.jpg') : asset('storage/pets/'. $appointment->pet->pet_img) }}" />
                                      @else
                                      <div class="mask mask-squircle bg-base-200 size-7.5 flex items-center justify-center">
                                          <span class="iconify lucide--brain-circuit size-4 text-base-content/60"></span>
                                      </div>
                                      @endif
                                      <p>{{ $appointment->pet->name ?? 'N/A' }}</p>
                                  </td>
                                  <td class="font-medium">{{ $appointment->service->name ?? 'N/A' }}</td>
                                  <td class="font-medium">${{ number_format($appointment->total_price ?? 0, 2) }}</td>
                                  <td class="text-xs">{{ $appointment->date ? \Carbon\Carbon::parse($appointment->date)->format('M j, Y') : 'N/A' }}</td>
                                  <td>
                                      @php
                                          $status = $appointment->status ?? '';
                                          $badgeClass = 'badge-secondary';
                                          if ($status === 'completed') {
                                              $badgeClass = 'badge-success';
                                          } elseif ($status === 'checked_in' || $status === 'in_progress') {
                                              $badgeClass = 'badge-info';
                                          } elseif ($status === 'cancelled' || $status === 'canceled') {
                                              $badgeClass = 'badge-error';
                                          } elseif ($status === 'confirmed') {
                                              $badgeClass = 'badge-primary';
                                          }
                                      @endphp
                                      <div
                                          class="badge {{ $badgeClass }} badge-sm badge-soft">
                                          {{ ucfirst(str_replace('_', ' ', $status)) }}
                                      </div>
                                  </td>
                                  <td>
                                      <div
                                          class="flex items-center gap-1">
                                          <a href="{{ route('appointment-dashboard', $appointment->id) }}"
                                              aria-label="View appointment"
                                              class="btn btn-square btn-ghost btn-xs">
                                              <span
                                                  class="iconify lucide--eye text-base-content/60 size-4"></span>
                                          </a>
                                      </div>
                                  </td>
                              </tr>
                              @empty
                              <tr>
                                  <td colspan="6" class="text-center py-8 text-base-content/60">
                                      No recent appointments found.
                                  </td>
                              </tr>
                              @endforelse
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>

          <!--  End: Recent Orders -->

      </div>
  </div>

</div>
@endsection

@section('page-js')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/4.3.0/apexcharts.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/4.3.0/apexcharts.min.js"></script>
  <script>
    // Revenue Statistics Chart Data
    const revenueChartData = @json($revenueData);

    const initRevenueStatisticsChart = () => {
        const chartOptions = {
            chart: {
                height: 288,
                type: "bar",
                stacked: false,
                background: "transparent",
                toolbar: {
                    show: false,
                },
            },
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    borderRadiusApplication: "end",
                    colors: {
                        backgroundBarColors: ["rgba(150,150,150,0.07)"],
                        backgroundBarRadius: 8,
                    },
                    columnWidth: "45%",
                    barHeight: "100%",
                },
            },
            dataLabels: {
                enabled: false,
            },
            colors: ["#6c74f8"],
            series: [
                {
                    name: "Revenue",
                    data: revenueChartData.data,
                },
            ],
            xaxis: {
                categories: revenueChartData.categories,
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false,
                },
                labels: {
                    formatter: (val) => {
                        return val;
                    },
                },
            },
            yaxis: {
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false,
                },
                labels: {
                    formatter: (val) => {
                        return '$' + (val / 1000).toFixed(0) + 'K';
                    },
                },
            },
            tooltip: {
                enabled: true,
                y: {
                    formatter: (val) => {
                        return '$' + val.toFixed(2);
                    },
                },
            },
            grid: {
                show: false,
            },
            responsive: [
                {
                    breakpoint: 450,
                    options: {
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                            },
                        },
                        xaxis: {
                            tickAmount: 3,
                        },
                    },
                },
            ],
        };

        if (document.getElementById("revenue-statics-chart")) {
            new ApexCharts(document.getElementById("revenue-statics-chart"), chartOptions).render();
        }
    };

    // Initialize chart when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initRevenueStatisticsChart();
    });
  </script>
  <script src="{{ asset('src/js/pages/dashboards/ecommerce.js') }}"></script>
@endsection