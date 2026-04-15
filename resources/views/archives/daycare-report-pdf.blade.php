<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daycare Report - {{ $appointment->pet->name }}</title>
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
  <h1>Daycare Report</h1>
  
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
      <div class="field">
        <span class="field-label">Pickup Time:</span>
        <span class="field-value">{{ $appointment->end_time ? \Carbon\Carbon::createFromFormat('H:i:s', $appointment->end_time)->format('h:i A') : 'Not set' }}</span>
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

  <!-- Process Info -->
  @if(isset($process) && $process)
  <div class="section">
    <h2 class="section-title">2. Process</h2>
    <div class="grid">
      <div class="field">
        <span class="field-label">Date:</span>
        <span class="field-value">{{ $process->date ? \Carbon\Carbon::parse($process->date)->format('M j, Y') : 'Not set' }}</span>
      </div>
      <div class="field">
        <span class="field-label">Start Time:</span>
        <span class="field-value">{{ $process->start_time ? \Carbon\Carbon::createFromFormat('H:i:s', $process->start_time)->format('h:i A') : 'Not set' }}</span>
      </div>
      <div class="field">
        <span class="field-label">Pickup Time:</span>
        <span class="field-value">{{ $process->pickup_time ? \Carbon\Carbon::createFromFormat('H:i:s', $process->pickup_time)->format('h:i A') : 'Not set' }}</span>
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
        <span class="field-label">Start Time:</span>
        <span class="field-value">{{ $checkout->start_time ? \Carbon\Carbon::createFromFormat('H:i:s', $checkout->start_time)->format('h:i A') : 'Not set' }}</span>
      </div>
      <div class="field">
        <span class="field-label">Pickup Time:</span>
        <span class="field-value">{{ $checkout->pickup_time ? \Carbon\Carbon::createFromFormat('H:i:s', $checkout->pickup_time)->format('h:i A') : 'Not set' }}</span>
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

  <!-- First Day Evaluation -->
  @if(isset($process) && $process && $process->flows && is_array($process->flows))
  <div class="section">
    <h2 class="section-title">First Day Evaluation</h2>
    <div class="field">
      @if(isset($process->flows['daycare_evaluation_date']))
      <div style="margin-bottom: 8px;">
        <span class="field-label">Date:</span>
        <span class="field-value">{{ \Carbon\Carbon::parse($process->flows['daycare_evaluation_date'])->format('M j, Y') }}</span>
      </div>
      @endif

      @if(isset($process->flows['daycare_evaluation_result']))
      <div style="margin-bottom: 8px;">
        <span class="field-label">Result:</span>
        <span class="field-value">
          @if($process->flows['daycare_evaluation_result'] === 'passed_no_concerns')
            <span class="badge badge-success">Passed (no concerns)</span>
          @elseif($process->flows['daycare_evaluation_result'] === 'passed_management_needed')
            <span class="badge badge-warning">Passed (management needed)</span>
          @elseif($process->flows['daycare_evaluation_result'] === 'reintroduction')
            <span class="badge badge-info">Reintroduction</span>
          @elseif($process->flows['daycare_evaluation_result'] === 'refer_to_trainer')
            <span class="badge badge-error">Refer to trainer</span>
          @endif
        </span>
      </div>
      @endif

      <div style="margin-top: 10px;">
        <span class="field-label">Socialization evaluation:</span>
        <div style="margin-top: 5px; margin-left: 10px;">
          @if(isset($process->flows['new_person_evaluation']))
          <div style="margin-bottom: 5px;">
            <strong>New person:</strong>
            @if($process->flows['new_person_evaluation'] === 'accepted')
              <span class="badge badge-success">Accepted</span>
            @elseif($process->flows['new_person_evaluation'] === 'issue_concern')
              <span class="badge badge-error">Issue/concern</span>
            @endif
          </div>
          @endif

          @if(isset($process->flows['new_dog_evaluation']))
          <div style="margin-bottom: 5px;">
            <strong>New dog:</strong>
            @if($process->flows['new_dog_evaluation'] === 'accepted')
              <span class="badge badge-success">Accepted</span>
            @elseif($process->flows['new_dog_evaluation'] === 'issue_concern')
              <span class="badge badge-error">Issue/concern</span>
            @endif
          </div>
          @endif

          @if(isset($process->flows['small_group_evaluation']))
          <div style="margin-bottom: 5px;">
            <strong>Small group of dogs:</strong>
            @if($process->flows['small_group_evaluation'] === 'accepted')
              <span class="badge badge-success">Accepted</span>
            @elseif($process->flows['small_group_evaluation'] === 'issue_concern')
              <span class="badge badge-error">Issue/concern</span>
            @endif
          </div>
          @endif

          @if(isset($process->flows['large_group_evaluation']))
          <div style="margin-bottom: 5px;">
            <strong>Large group of dogs:</strong>
            @if($process->flows['large_group_evaluation'] === 'accepted')
              <span class="badge badge-success">Accepted</span>
            @elseif($process->flows['large_group_evaluation'] === 'issue_concern')
              <span class="badge badge-error">Issue/concern</span>
            @endif
          </div>
          @endif
        </div>
      </div>

      @if(isset($process->flows['daycare_evaluation_notes']) && $process->flows['daycare_evaluation_notes'])
      <div style="margin-top: 10px;">
        <span class="field-label">Notes:</span>
        <div class="notes">{{ $process->flows['daycare_evaluation_notes'] }}</div>
      </div>
      @endif
    </div>
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
          <span style="margin-left: 5px;">(mild reaction to daycare)</span>
          @if(isset($checkout->flows['rating_yellow_detail']))
          <div class="notes" style="margin-top: 5px;">{{ $checkout->flows['rating_yellow_detail'] }}</div>
          @endif
        @elseif($checkout->flows['rating'] === 'purple')
          <span class="badge badge-error">Purple</span>
          <span style="margin-left: 5px;">(reacts to daycare)</span>
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

