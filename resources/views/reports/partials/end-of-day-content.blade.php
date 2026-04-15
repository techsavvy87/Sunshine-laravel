{{-- End of Day report content (tables only, no layout). Used on standalone page and embedded in Boarding Workflow. --}}
<div class="end-of-day-content space-y-6">
  {{-- 1. Pets --}}
  <section class="space-y-4">
    <h2 class="text-lg font-semibold">1. Pets</h2>
    <div>
      <p class="font-medium text-sm mb-3">Daily Numbers</p>
    <div class="grid grid-cols-4 gap-4">
      <div class="card card-border bg-base-100">
        <div class="card-body p-3">
          <div class="flex items-center gap-2">
            <div class="rounded-box bg-primary/10 text-primary flex items-center p-1.5">
              <span class="iconify lucide--log-in size-5"></span>
            </div>
            <span class="text-sm font-medium">Checked-in</span>
          </div>
          <div class="text-base-content/70 mt-2 text-2xl font-semibold">{{ $petDailyNumbers['checked_in'] }}</div>
          <div class="text-base-content/60 text-xs">Scheduled appointments</div>
        </div>
      </div>
      <div class="card card-border bg-base-100">
        <div class="card-body p-3">
          <div class="flex items-center gap-2">
            <div class="rounded-box bg-warning/10 text-warning flex items-center p-1.5">
              <span class="iconify lucide--x-circle size-5"></span>
            </div>
            <span class="text-sm font-medium">No-show / Cancellation</span>
          </div>
          <div class="text-base-content/70 mt-2 text-2xl font-semibold">{{ $petDailyNumbers['no_show_cancelled'] }}</div>
        </div>
      </div>
      <div class="card card-border bg-base-100">
        <div class="card-body p-3">
          <div class="flex items-center gap-2">
            <div class="rounded-box bg-success/10 text-success flex items-center p-1.5">
              <span class="iconify lucide--log-out size-5"></span>
            </div>
            <span class="text-sm font-medium">Checkout completed</span>
          </div>
          <div class="text-base-content/70 mt-2 text-2xl font-semibold">{{ $petDailyNumbers['checkout_completed'] }}</div>
        </div>
      </div>
      <div class="card card-border bg-base-100">
        <div class="card-body p-3">
          <div class="flex items-center gap-2">
            <div class="rounded-box bg-info/10 text-info flex items-center p-1.5">
              <span class="iconify lucide--brain-circuit size-5"></span>
            </div>
            <span class="text-sm font-medium">Dogs on property</span>
          </div>
          <div class="text-base-content/70 mt-2 text-2xl font-semibold">{{ $petDailyNumbers['dogs_on_property'] }}</div>
          <div class="text-base-content/60 text-xs">Boarding</div>
        </div>
      </div>
    </div>
  </div>

  <div class="card card-border bg-base-100">
    <div class="card-body">
      <div class="card-title">Issues and Concerns</div>
      <div class="mt-3 overflow-x-auto">
        @if (empty($treatmentListRows) || count($treatmentListRows) === 0)
        <p class="text-base-content/70 text-sm">No pets with issues from nose to tail check or do-not-eat (AM/PM meals) for today.</p>
        @else
        <table class="table">
          <thead>
            <tr>
              <th>Pet</th>
              <th>Customer</th>
              <th>Issues</th>
              <th>Status</th>
              <th>Treatment</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($treatmentListRows as $row)
            <tr>
              <td>
                <div class="flex items-center gap-2">
                  @if (!empty($row['pet_img']))
                  <img src="{{ asset('storage/pets/'. $row['pet_img']) }}" alt="" class="mask mask-squircle bg-base-200 size-8" />
                  @else
                  <img src="{{ asset('images/no_image.jpg') }}" alt="" class="mask mask-squircle bg-base-200 size-8" />
                  @endif
                  <span>{{ $row['pet_name'] }}</span>
                </div>
              </td>
              <td>
                <div class="flex items-center gap-2">
                  @if (!empty($row['customer_avatar']))
                  <img src="{{ asset('storage/profiles/'. $row['customer_avatar']) }}" alt="" class="mask mask-squircle bg-base-200 size-8" />
                  @else
                  <img src="{{ asset('images/default-user-avatar.png') }}" alt="" class="mask mask-squircle bg-base-200 size-8" />
                  @endif
                  <span>{{ $row['customer_name'] }}</span>
                </div>
              </td>
              <td>{{ implode(', ', $row['issues']) ?: '—' }}</td>
              <td>{{ $row['status'] ?? '—' }}</td>
              <td class="max-w-xs">{{ Str::limit($row['treatment'] ?? '', 60) ?: '—' }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endif
      </div>
    </div>
  </div>

  <div class="card card-border bg-base-100">
    <div class="card-body">
      <div class="card-title">Incident reports</div>
      <div class="mt-3 overflow-x-auto">
        @if ($incidentReports->isEmpty())
        <p class="text-base-content/70 text-sm">No incidents reported for today.</p>
        @else
        <table class="table">
          <thead>
            <tr>
              <th>Pets</th>
              <th>Staff</th>
              <th>Injury type</th>
              @if (empty($embed))
              <th>Action</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @foreach ($incidentReports as $report)
            <tr>
              <td>
                <div class="flex items-center gap-2">
                  @if ($report->pets->isNotEmpty())
                    @php $firstPet = $report->pets->first(); @endphp
                    @if ($firstPet->pet_img)
                    <img src="{{ asset('storage/pets/'. $firstPet->pet_img) }}" alt="" class="mask mask-squircle bg-base-200 size-8" />
                    @else
                    <img src="{{ asset('images/no_image.jpg') }}" alt="" class="mask mask-squircle bg-base-200 size-8" />
                    @endif
                  @endif
                  <a href="{{ route('edit-incident-report', ['id' => $report->id]) }}" class="text-blue-600 underline hover:text-blue-800">{{ $report->pets->pluck('name')->implode(', ') ?: '—' }}</a>
                </div>
              </td>
              <td>
                <div class="flex items-center gap-2">
                  @if ($report->staffs->isNotEmpty())
                    @php $firstStaff = $report->staffs->first(); @endphp
                    @if ($firstStaff->profile && $firstStaff->profile->avatar_img)
                    <img src="{{ asset('storage/profiles/'. $firstStaff->profile->avatar_img) }}" alt="" class="mask mask-squircle bg-base-200 size-8" />
                    @else
                    <img src="{{ asset('images/default-user-avatar.png') }}" alt="" class="mask mask-squircle bg-base-200 size-8" />
                    @endif
                  @endif
                  <span>{{ $report->staffs->map(fn($s) => $s->profile ? $s->profile->first_name . ' ' . $s->profile->last_name : $s->name)->implode(', ') ?: '—' }}</span>
                </div>
              </td>
              <td>{{ $report->injury_type ?? '—' }}</td>
              @if (empty($embed))
              <td><a href="{{ route('edit-incident-report', ['id' => $report->id]) }}" class="btn btn-ghost btn-xs">View</a></td>
              @endif
            </tr>
            @endforeach
          </tbody>
        </table>
        @endif
      </div>
    </div>
  </div>
  </section>

  {{-- 2. People --}}
  <section class="space-y-4">
    <h2 class="text-lg font-semibold">2. People</h2>
    <div class="card card-border bg-base-100">
    <div class="card-body">
      <div class="card-title">Customers — Complaints / Issues (today)</div>
      <div class="mt-3 overflow-x-auto">
        @if ($complaintsToday->isEmpty())
        <p class="text-base-content/70 text-sm">None.</p>
        @else
        <table class="table">
          <thead>
            <tr>
              <th>Customer</th>
              <th>Description</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($complaintsToday as $c)
            <tr>
              <td>
                <div class="flex items-center gap-2">
                  @if ($c->customer && $c->customer->profile && $c->customer->profile->avatar_img)
                  <img src="{{ asset('storage/profiles/'. $c->customer->profile->avatar_img) }}" alt="" class="mask mask-squircle bg-base-200 size-8" />
                  @else
                  <img src="{{ asset('images/default-user-avatar.png') }}" alt="" class="mask mask-squircle bg-base-200 size-8" />
                  @endif
                  <span>{{ $c->customer && $c->customer->profile ? $c->customer->profile->first_name . ' ' . $c->customer->profile->last_name : $c->customer->name ?? '—' }}</span>
                </div>
              </td>
              <td class="max-w-xs">{{ Str::limit($c->description, 80) }}</td>
              <td>{{ $c->date ? $c->date->format('Y-m-d') : '—' }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endif
      </div>
    </div>
  </div>

  <div class="card card-border bg-base-100">
    <div class="card-body">
      <div class="card-title">Invoices created today</div>
      <div class="mt-3 overflow-x-auto">
        @if ($invoicesToday->isEmpty())
        <p class="text-base-content/70 text-sm">None.</p>
        @else
        <table class="table">
          <thead>
            <tr>
              <th>Invoice #</th>
              <th>Status</th>
              <th>Created</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($invoicesToday as $inv)
            <tr>
              <td>{{ $inv->invoice_number }}</td>
              <td><span class="badge badge-ghost badge-sm">{{ $inv->status }}</span></td>
              <td>{{ $inv->created_at->format('Y-m-d H:i') }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endif
      </div>
    </div>
  </div>

  <div class="card card-border bg-base-100">
    <div class="card-body">
      <div class="card-title">Employees — Attendance (today)</div>
      <div class="mt-3 overflow-x-auto">
        @if ($attendanceRecords->isEmpty())
        <p class="text-base-content/70 text-sm">None.</p>
        @else
        <table class="table">
          <thead>
            <tr>
              <th>Employee</th>
              <th class="text-center">Present</th>
              <th class="text-center">Injury / Sickness</th>
              <th>Issue</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($attendanceRecords as $att)
            <tr>
              <td>
                <div class="flex items-center gap-2">
                  @if ($att->user && $att->user->profile && $att->user->profile->avatar_img)
                  <img src="{{ asset('storage/profiles/'. $att->user->profile->avatar_img) }}" alt="" class="mask mask-squircle bg-base-200 size-8" />
                  @else
                  <img src="{{ asset('images/default-user-avatar.png') }}" alt="" class="mask mask-squircle bg-base-200 size-8" />
                  @endif
                  <span>{{ $att->user && $att->user->profile ? $att->user->profile->first_name . ' ' . $att->user->profile->last_name : $att->user->name ?? '—' }}</span>
                </div>
              </td>
              <td class="text-center">
                <span class="badge badge-sm {{ $att->present ? 'badge-success' : 'badge-ghost' }}">{{ $att->present ? 'Yes' : 'No' }}</span>
              </td>
              <td class="text-center">
                <span class="badge badge-sm {{ $att->injury_sickness ? 'badge-warning' : 'badge-ghost' }}">{{ $att->injury_sickness ? 'Yes' : 'No' }}</span>
              </td>
              <td class="max-w-xs">{{ Str::limit($att->notes, 60) ?: '—' }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endif
      </div>
    </div>
  </div>

  <div class="card card-border bg-base-100">
    <div class="card-body">
      <div class="card-title">Involvement in incidents (today)</div>
      <div class="mt-3 overflow-x-auto">
        @if ($incidentsToday->isEmpty())
        <p class="text-base-content/70 text-sm">None.</p>
        @else
        <table class="table">
          <thead>
            <tr>
              <th>Staff</th>
              <th>Pets</th>
              <th>Injury type</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($incidentsToday as $inc)
            <tr>
              <td>
                <div class="flex items-center gap-2">
                  @if ($inc->staffs->isNotEmpty())
                    @php $firstStaff = $inc->staffs->first(); @endphp
                    @if ($firstStaff->profile && $firstStaff->profile->avatar_img)
                    <img src="{{ asset('storage/profiles/'. $firstStaff->profile->avatar_img) }}" alt="" class="mask mask-squircle bg-base-200 size-8" />
                    @else
                    <img src="{{ asset('images/default-user-avatar.png') }}" alt="" class="mask mask-squircle bg-base-200 size-8" />
                    @endif
                  @endif
                  <span>{{ $inc->staffs->map(fn($s) => $s->profile ? $s->profile->first_name . ' ' . $s->profile->last_name : $s->name)->implode(', ') ?: '—' }}</span>
                </div>
              </td>
              <td>
                <div class="flex items-center gap-2">
                  @if ($inc->pets->isNotEmpty())
                    @php $firstPet = $inc->pets->first(); @endphp
                    @if ($firstPet->pet_img)
                    <img src="{{ asset('storage/pets/'. $firstPet->pet_img) }}" alt="" class="mask mask-squircle bg-base-200 size-8" />
                    @else
                    <img src="{{ asset('images/no_image.jpg') }}" alt="" class="mask mask-squircle bg-base-200 size-8" />
                    @endif
                  @endif
                  <a href="{{ route('edit-incident-report', ['id' => $inc->id]) }}" class="text-blue-600 underline hover:text-blue-800">{{ $inc->pets->pluck('name')->implode(', ') ?: '—' }}</a>
                </div>
              </td>
              <td>{{ $inc->injury_type ?? '—' }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endif
      </div>
    </div>
  </div>
  </section>

  {{-- 3. Property --}}
  <section class="space-y-4">
    <h2 class="text-lg font-semibold">3. Property</h2>
    <div class="card card-border bg-base-100">
    <div class="card-body">
      <div class="card-title">Property — Maintenance</div>
      <div class="mt-3 overflow-x-auto">
        <table class="table">
          <thead>
            <tr>
              <th>Type</th>
              <th>Details</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($maintenanceIssues->groupBy('type') as $type => $issues)
            @foreach ($issues as $issue)
            <tr>
              <td>{{ $type }}</td>
              <td>{{ Str::limit($issue->description, 80) }}</td>
            </tr>
            @endforeach
            @empty
            <tr><td colspan="2" class="text-base-content/70 text-sm">None recorded for today.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  </section>
</div>
