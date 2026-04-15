<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Package Report - {{ $appointment->pet->name }}</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: 'DejaVu Sans', Arial, sans-serif;
      font-size: 12px;
      line-height: 1.6;
      color: #333;
      padding: 20px;
    }
    h1 {
      font-size: 24px;
      margin-bottom: 20px;
      color: #1a1a1a;
      border-bottom: 2px solid #4a5568;
      padding-bottom: 10px;
    }
    h2 {
      font-size: 16px;
      margin-top: 20px;
      margin-bottom: 10px;
      color: #2d3748;
      font-weight: bold;
    }
    h3 {
      font-size: 14px;
      margin-top: 15px;
      margin-bottom: 8px;
      color: #4a5568;
      font-weight: bold;
    }
    .header-info {
      background-color: #f7fafc;
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
    }
    .info-row {
      display: flex;
      margin-bottom: 8px;
    }
    .info-label {
      font-weight: bold;
      width: 140px;
      color: #4a5568;
    }
    .info-value {
      color: #1a1a1a;
    }
    .section {
      margin-bottom: 20px;
      padding: 15px;
      border: 1px solid #e2e8f0;
      border-radius: 5px;
      page-break-inside: avoid;
    }
    .section-title {
      font-size: 14px;
      font-weight: bold;
      margin-bottom: 10px;
      color: #2d3748;
      border-bottom: 1px solid #cbd5e0;
      padding-bottom: 5px;
    }
    .grid {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 15px;
      margin-bottom: 10px;
    }
    .field {
      margin-bottom: 5px;
    }
    .field-label {
      font-weight: bold;
      color: #4a5568;
      display: inline-block;
      min-width: 120px;
    }
    .field-value {
      color: #1a1a1a;
      display: inline-block;
    }
    .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 3px;
      font-size: 11px;
      font-weight: bold;
      margin-right: 5px;
    }
    .badge-success {
      background-color: #c6f6d5;
      color: #22543d;
    }
    .badge-warning {
      background-color: #fefcbf;
      color: #744210;
    }
    .badge-info {
      background-color: #bee3f8;
      color: #2c5282;
    }
    .badge-error {
      background-color: #fed7d7;
      color: #742a2a;
    }
    .notes {
      background-color: #f7fafc;
      padding: 10px;
      border-radius: 3px;
      margin-top: 5px;
      white-space: pre-wrap;
    }
    hr {
      border: none;
      border-top: 1px solid #e2e8f0;
      margin: 15px 0;
    }
    .footer {
      margin-top: 30px;
      padding-top: 15px;
      border-top: 2px solid #4a5568;
      text-align: center;
      font-size: 10px;
      color: #718096;
    }
    @page {
      margin: 20px;
    }
  </style>
</head>
<body>
  <h1>Package Report</h1>
  
  <div class="header-info">
    <div class="info-row">
      <span class="info-label">Pet Name:</span>
      <span class="info-value">{{ $appointment->pet->name }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Owner:</span>
      <span class="info-value">{{ $appointment->customer->profile->first_name }} {{ $appointment->customer->profile->last_name }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Service:</span>
      <span class="info-value">{{ $appointment->service->name }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Date:</span>
      <span class="info-value">{{ \Carbon\Carbon::parse($appointment->date)->format('F j, Y') }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Time:</span>
      <span class="info-value">
        @if($appointment->start_time && $appointment->end_time)
          {{ \Carbon\Carbon::createFromFormat('H:i:s', $appointment->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::createFromFormat('H:i:s', $appointment->end_time)->format('h:i A') }}
        @else
          N/A
        @endif
      </span>
    </div>
    @if ($appointment->staff)
    <div class="info-row">
      <span class="info-label">Staff:</span>
      <span class="info-value">
        @if ($appointment->staff->profile)
          {{ $appointment->staff->profile->first_name }} {{ $appointment->staff->profile->last_name }}
        @else
          {{ $appointment->staff->name }}
        @endif
      </span>
    </div>
    @endif
  </div>

  <!-- Check-in Info -->
  @if(isset($checkin) && $checkin)
  <div class="section">
    <h2 class="section-title">1. Check-in</h2>
    <div class="grid">
      <div class="field">
        <span class="field-label">Date:</span>
        <span class="field-value">{{ $checkin->date ? \Carbon\Carbon::parse($checkin->date)->format('M j, Y') : 'Not set' }}</span>
      </div>
      <div class="field">
        <span class="field-label">Start Time:</span>
        <span class="field-value">{{ $appointment->start_time ? \Carbon\Carbon::createFromFormat('H:i:s', $appointment->start_time)->format('h:i A') : 'Not set' }}</span>
      </div>
    </div>
    @if($checkin->notes)
    <div class="field">
      <span class="field-label">Notes:</span>
      <div class="notes">{{ $checkin->notes }}</div>
    </div>
    @endif
  </div>
  @endif

  <!-- Process Info (Multiple) -->
  @if(isset($packageProcesses) && $packageProcesses->count() > 0)
  <div class="section">
    <h2 class="section-title">2. Process</h2>
    @foreach($packageProcesses as $processItem)
    <div style="margin-bottom: 20px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 5px; {{ !$loop->last ? 'margin-bottom: 15px;' : '' }}">
      @if($processItem->detail_id)
        @php
          $service = \App\Models\Service::find($processItem->detail_id);
        @endphp
        @if($service)
          <h3 style="margin-bottom: 10px; color: #4a5568;">{{ $service->name }}</h3>
        @else
          <h3 style="margin-bottom: 10px; color: #4a5568;">Service ID: {{ $processItem->detail_id }}</h3>
        @endif
      @else
        <h3 style="margin-bottom: 10px; color: #4a5568;">Main Process</h3>
      @endif
      <div class="grid">
        <div class="field">
          <span class="field-label">Date:</span>
          <span class="field-value">{{ $processItem->date ? \Carbon\Carbon::parse($processItem->date)->format('M j, Y') : 'Not set' }}</span>
        </div>
        <div class="field">
          <span class="field-label">Start Time:</span>
          <span class="field-value">
            @if($processItem->start_time)
              @php
                try {
                  $time = \Carbon\Carbon::createFromFormat('H:i:s', $processItem->start_time)->format('h:i A');
                  echo $time;
                } catch (\Exception $e) {
                  echo $processItem->start_time;
                }
              @endphp
            @else
              Not set
            @endif
          </span>
        </div>
        <div class="field">
          <span class="field-label">Pickup Time:</span>
          <span class="field-value">
            @if($processItem->pickup_time)
              @php
                try {
                  $time = \Carbon\Carbon::createFromFormat('H:i:s', $processItem->pickup_time)->format('h:i A');
                  echo $time;
                } catch (\Exception $e) {
                  echo $processItem->pickup_time;
                }
              @endphp
            @else
              Not set
            @endif
          </span>
        </div>
      </div>
      @if($processItem->notes)
      <div class="field">
        <span class="field-label">Notes:</span>
        <div class="notes">{{ $processItem->notes }}</div>
      </div>
      @endif
    </div>
    @endforeach
  </div>
  @elseif(isset($process) && $process)
  <div class="section">
    <h2 class="section-title">2. Process</h2>
    <div class="grid">
      <div class="field">
        <span class="field-label">Date:</span>
        <span class="field-value">{{ $process->date ? \Carbon\Carbon::parse($process->date)->format('M j, Y') : 'Not set' }}</span>
      </div>
      <div class="field">
        <span class="field-label">Start Time:</span>
        <span class="field-value">
          @if($process->start_time)
            @php
              try {
                $time = \Carbon\Carbon::createFromFormat('H:i:s', $process->start_time)->format('h:i A');
                echo $time;
              } catch (\Exception $e) {
                echo $process->start_time;
              }
            @endphp
          @else
            Not set
          @endif
        </span>
      </div>
      <div class="field">
        <span class="field-label">Pickup Time:</span>
        <span class="field-value">
          @if($process->pickup_time)
            @php
              try {
                $time = \Carbon\Carbon::createFromFormat('H:i:s', $process->pickup_time)->format('h:i A');
                echo $time;
              } catch (\Exception $e) {
                echo $process->pickup_time;
              }
            @endphp
          @else
            Not set
          @endif
        </span>
      </div>
    </div>
    @if($process->notes)
    <div class="field">
      <span class="field-label">Notes:</span>
      <div class="notes">{{ $process->notes }}</div>
    </div>
    @endif
  </div>
  @endif

  <!-- Checkout Info -->
  @if(isset($checkout) && $checkout)
  <div class="section">
    <h2 class="section-title">3. Checkout</h2>
    <div class="grid">
      <div class="field">
        <span class="field-label">Date:</span>
        <span class="field-value">{{ $checkout->date ? \Carbon\Carbon::parse($checkout->date)->format('M j, Y') : 'Not set' }}</span>
      </div>
      <div class="field">
        <span class="field-label">Pickup Time:</span>
        <span class="field-value">{{ $appointment->end_time ? \Carbon\Carbon::createFromFormat('H:i:s', $appointment->end_time)->format('h:i A') : 'Not set' }}</span>
      </div>
    </div>
    @if($checkout->notes)
    <div class="field">
      <span class="field-label">Notes:</span>
      <div class="notes">{{ $checkout->notes }}</div>
    </div>
    @endif
  </div>
  @endif

  <!-- Final Assessment -->
  @if(isset($checkout) && $checkout && $checkout->flows && is_array($checkout->flows))
  <div class="section">
    <h2 class="section-title">Final Assessment</h2>
    @if(isset($checkout->flows['rating']))
    <div class="field">
      <span class="field-label">Rating:</span>
      <span class="field-value">
        @if($checkout->flows['rating'] === 'green')
          <span class="badge badge-success">Green</span>
          <span style="margin-left: 5px;">(no issues)</span>
        @elseif($checkout->flows['rating'] === 'yellow')
          <span class="badge badge-warning">Yellow</span>
          <span style="margin-left: 5px;">(mild reaction)</span>
          @if(isset($checkout->flows['rating_yellow_detail']))
          <div class="notes" style="margin-top: 5px;">{{ $checkout->flows['rating_yellow_detail'] }}</div>
          @endif
        @elseif($checkout->flows['rating'] === 'purple')
          <span class="badge badge-error">Purple</span>
          <span style="margin-left: 5px;">(reacts to service)</span>
          @if(isset($checkout->flows['rating_purple_detail']))
          <div class="notes" style="margin-top: 5px;">{{ $checkout->flows['rating_purple_detail'] }}</div>
          @endif
        @endif
      </span>
    </div>
    @endif

    @if(isset($checkout->flows['pictures']) && is_array($checkout->flows['pictures']) && count($checkout->flows['pictures']) > 0)
    <div class="field">
      <div class="field-label">Checkout Pictures:</div>
      <div style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 10px;">
        @foreach($checkout->flows['pictures'] as $picture)
          <div style="margin-bottom: 10px;">
            <img src="{{ public_path('storage/checkouts/' . $picture) }}" style="width: 150px; height: 150px; object-fit: cover; border: 1px solid #e2e8f0; border-radius: 5px;" alt="Checkout Picture">
          </div>
        @endforeach
      </div>
    </div>
    @endif
  </div>
  @endif

  <div class="footer">
    <p>Generated on {{ \Carbon\Carbon::now()->format('F j, Y \a\t g:i A') }}</p>
  </div>
</body>
</html>

