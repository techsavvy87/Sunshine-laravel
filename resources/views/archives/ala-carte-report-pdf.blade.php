<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>A la Carte Report - {{ $appointment->pet->name }}</title>
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
    .grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
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
      background-color: #fef5c3;
      color: #744210;
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
    .checkbox-item {
      display: flex;
      align-items: flex-start;
      margin-bottom: 5px;
      padding-left: 10px;
    }
    .checkbox {
      display: inline-block;
      width: 14px;
      height: 14px;
      border: 2px solid #4a5568;
      margin-right: 8px;
      position: relative;
      top: 2px;
      vertical-align: middle;
      background-color: white;
    }
    .checkbox.checked::before {
      content: '';
      position: absolute;
      left: 4px;
      top: 1px;
      width: 3px;
      height: 7px;
      border: solid #4a5568;
      border-width: 0 2px 2px 0;
      transform: rotate(45deg);
    }
    .secondary-service {
      border: 1px solid #e2e8f0;
      border-radius: 5px;
      padding: 15px;
      margin-bottom: 15px;
      background-color: #f9fafb;
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
  <h1>A la Carte Report</h1>
  
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

  <!-- Process - Secondary Services -->
  @if(isAlaCarteService($appointment->service) && $appointment->metadata && isset($appointment->metadata['secondary_service_ids']))
    @php
      $secondaryServiceIds = explode(',', $appointment->metadata['secondary_service_ids']);
      $secondaryServices = \App\Models\Service::whereIn('id', $secondaryServiceIds)->get();
    @endphp
    @if($secondaryServices->count() > 0)
    <div class="section">
      <h2 class="section-title">2. Process - Secondary Services</h2>
      <div style="margin-top: 10px;">
        @foreach($secondaryServices as $secondaryService)
          @php
            $serviceProcess = isset($alaCarteProcesses[$secondaryService->id]) ? $alaCarteProcesses[$secondaryService->id] : null;
          @endphp
          <div class="secondary-service">
            <h3 style="margin-bottom: 10px; color: #2d3748;">{{ $secondaryService->name }}</h3>
            @if($serviceProcess)
              <div class="grid-2">
                <div class="field">
                  <span class="field-label">Date:</span>
                  <span class="field-value">{{ $serviceProcess->date ? \Carbon\Carbon::parse($serviceProcess->date)->format('M j, Y') : 'Not set' }}</span>
                </div>
                <div class="field">
                  <span class="field-label">Assigned Staff:</span>
                  <span class="field-value">
                    @if($serviceProcess->staff)
                      @if($serviceProcess->staff->profile)
                        {{ $serviceProcess->staff->profile->first_name }} {{ $serviceProcess->staff->profile->last_name }}
                      @else
                        {{ $serviceProcess->staff->name }}
                      @endif
                    @else
                      Not assigned
                    @endif
                  </span>
                </div>
                <div class="field">
                  <span class="field-label">Start Time:</span>
                  <span class="field-value">{{ $serviceProcess->start_time ? \Carbon\Carbon::createFromFormat('H:i:s', $serviceProcess->start_time)->format('h:i A') : 'Not set' }}</span>
                </div>
                <div class="field">
                  <span class="field-label">Pickup Time:</span>
                  <span class="field-value">{{ $serviceProcess->pickup_time ? \Carbon\Carbon::createFromFormat('H:i:s', $serviceProcess->pickup_time)->format('h:i A') : 'Not set' }}</span>
                </div>
              </div>
              @if($serviceProcess->notes)
              <div class="field" style="margin-top: 10px;">
                <span class="field-label">Notes:</span>
                <div class="notes">{{ $serviceProcess->notes }}</div>
              </div>
              @endif
            @else
              <div class="field">
                <span class="field-value">No process information available for this service.</span>
              </div>
            @endif
          </div>
        @endforeach
      </div>
    </div>
    @endif
  @endif

  <!-- Initial Temperament Assessment -->
  @if(isset($checkin) && $checkin)
    @php
      $checkinFlows = [];
      if ($checkin->flows) {
        if (is_array($checkin->flows)) {
          $checkinFlows = $checkin->flows;
        } elseif (is_string($checkin->flows)) {
          $decoded = json_decode($checkin->flows, true);
          $checkinFlows = is_array($decoded) ? $decoded : [];
        }
      }
    @endphp
    @if(is_array($checkinFlows) && !empty($checkinFlows))
    <div class="section">
      <h2 class="section-title">Initial Temperament Assessment</h2>
      
      <h3>Initial Greeting:</h3>
      <div class="checkbox-item">
        <span class="checkbox {{ isset($checkinFlows['initial_greeting']) && $checkinFlows['initial_greeting'] === 'approachable' ? 'checked' : '' }}"></span>
        <span>Approachable (allows contact, loose body posture, will accept treats)</span>
      </div>
      <div class="checkbox-item">
        <span class="checkbox {{ isset($checkinFlows['initial_greeting']) && $checkinFlows['initial_greeting'] === 'shy' ? 'checked' : '' }}"></span>
        <span>Shy (cautious, tail tucked, whale eye, does not want to be petted)</span>
      </div>
      <div class="checkbox-item">
        <span class="checkbox {{ isset($checkinFlows['initial_greeting']) && $checkinFlows['initial_greeting'] === 'uncomfortable' ? 'checked' : '' }}"></span>
        <span>Uncomfortable (moves away, shows teeth, barks or snaps)</span>
      </div>

      <div class="field" style="margin-bottom: 10px;">
        <div class="field-label" style="margin-bottom: 5px;">Body Touch:</div>
        <div style="margin-left: 10px;">
          <span class="checkbox {{ isset($checkinFlows['touch_body']) && $checkinFlows['touch_body'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
          <span class="checkbox {{ isset($checkinFlows['touch_body']) && $checkinFlows['touch_body'] === 'react' ? 'checked' : '' }}"></span> Reacts
        </div>
      </div>
      <div class="field" style="margin-bottom: 10px;">
        <div class="field-label" style="margin-bottom: 5px;">Legs Touch:</div>
        <div style="margin-left: 10px;">
          <span class="checkbox {{ isset($checkinFlows['touch_legs']) && $checkinFlows['touch_legs'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
          <span class="checkbox {{ isset($checkinFlows['touch_legs']) && $checkinFlows['touch_legs'] === 'react' ? 'checked' : '' }}"></span> Reacts
        </div>
      </div>
      <div class="field" style="margin-bottom: 10px;">
        <div class="field-label" style="margin-bottom: 5px;">Feet Touch:</div>
        <div style="margin-left: 10px;">
          <span class="checkbox {{ isset($checkinFlows['touch_feet']) && $checkinFlows['touch_feet'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
          <span class="checkbox {{ isset($checkinFlows['touch_feet']) && $checkinFlows['touch_feet'] === 'react' ? 'checked' : '' }}"></span> Reacts
        </div>
      </div>
      <div class="field" style="margin-bottom: 10px;">
        <div class="field-label" style="margin-bottom: 5px;">Tail Touch:</div>
        <div style="margin-left: 10px;">
          <span class="checkbox {{ isset($checkinFlows['touch_tail']) && $checkinFlows['touch_tail'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
          <span class="checkbox {{ isset($checkinFlows['touch_tail']) && $checkinFlows['touch_tail'] === 'react' ? 'checked' : '' }}"></span> Reacts
        </div>
      </div>
      <div class="field" style="margin-bottom: 10px;">
        <div class="field-label" style="margin-bottom: 5px;">Face Touch:</div>
        <div style="margin-left: 10px;">
          <span class="checkbox {{ isset($checkinFlows['touch_face']) && $checkinFlows['touch_face'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
          <span class="checkbox {{ isset($checkinFlows['touch_face']) && $checkinFlows['touch_face'] === 'react' ? 'checked' : '' }}"></span> Reacts
        </div>
      </div>
      <div class="field" style="margin-bottom: 10px;">
        <div class="field-label" style="margin-bottom: 5px;">Nails Touch:</div>
        <div style="margin-left: 10px;">
          <span class="checkbox {{ isset($checkinFlows['touch_nails']) && $checkinFlows['touch_nails'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
          <span class="checkbox {{ isset($checkinFlows['touch_nails']) && $checkinFlows['touch_nails'] === 'react' ? 'checked' : '' }}"></span> Reacts
        </div>
      </div>
    </div>
    @endif
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

  <!-- Final Assessment -->
  @if(isset($checkout) && $checkout)
    @php
      $checkoutFlows = [];
      if ($checkout->flows) {
        if (is_array($checkout->flows)) {
          $checkoutFlows = $checkout->flows;
        } elseif (is_string($checkout->flows)) {
          $decoded = json_decode($checkout->flows, true);
          $checkoutFlows = is_array($decoded) ? $decoded : [];
        }
      }
    @endphp
    @if(is_array($checkoutFlows) && !empty($checkoutFlows))
    <div class="section">
      <h2 class="section-title">Final Assessment</h2>
      @if(isset($checkoutFlows['rating']))
      <div class="field">
        <span class="field-label">Rating:</span>
        <span class="field-value">
          @if($checkoutFlows['rating'] === 'green')
            <span class="badge badge-success">Green</span>
            <span style="margin-left: 5px;">(no issues)</span>
          @elseif($checkoutFlows['rating'] === 'yellow')
            <span class="badge badge-warning">Yellow</span>
            <span style="margin-left: 5px;">(mild reaction to grooming)</span>
            @if(isset($checkoutFlows['rating_yellow_detail']))
            <div class="notes" style="margin-top: 5px;">{{ $checkoutFlows['rating_yellow_detail'] }}</div>
            @endif
          @elseif($checkoutFlows['rating'] === 'purple')
            <span class="badge badge-error">Purple</span>
            <span style="margin-left: 5px;">(reacts to grooming)</span>
            @if(isset($checkoutFlows['rating_purple_detail']))
            <div class="notes" style="margin-top: 5px;">{{ $checkoutFlows['rating_purple_detail'] }}</div>
            @endif
          @endif
        </span>
      </div>
      @endif

      @if(isset($checkoutFlows['pictures']) && is_array($checkoutFlows['pictures']) && count($checkoutFlows['pictures']) > 0)
      <div class="field">
        <div class="field-label">Checkout Pictures:</div>
        <div style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 10px;">
          @foreach($checkoutFlows['pictures'] as $picture)
            <div style="margin-bottom: 10px;">
              <img src="{{ public_path('storage/checkouts/' . $picture) }}" style="width: 150px; height: 150px; object-fit: cover; border: 1px solid #e2e8f0; border-radius: 5px;" alt="Checkout Picture">
            </div>
          @endforeach
        </div>
      </div>
      @endif
    </div>
    @endif
  @endif

  <div class="footer">
    <p>Generated on {{ \Carbon\Carbon::now()->format('F j, Y \a\t g:i A') }}</p>
  </div>
</body>
</html>

