<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Grooming Report - {{ $appointment->pet->name }}</title>
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
      min-width: 100px;
    }
    .field-value {
      color: #1a1a1a;
      display: inline-block;
    }
    .field-inline {
      margin-bottom: 3px;
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
    .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 3px;
      font-size: 11px;
      font-weight: bold;
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
      font-style: italic;
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
  <h1>Grooming Report</h1>
  
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
      <span class="info-value">{{ \Carbon\Carbon::createFromFormat('H:i:s', $appointment->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::createFromFormat('H:i:s', $appointment->end_time)->format('h:i A') }}</span>
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

  @if(isset($checkin) && $checkin)
  <div class="section">
    <div class="section-title">1. Check-in</div>
    <div class="field-inline">
      <span class="field-label">Date:</span>
      <span class="field-value">{{ $appointment->date ? \Carbon\Carbon::parse($appointment->date)->format('M j, Y') : 'Not set' }}</span>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <span class="field-label">Start Time:</span>
      <span class="field-value">{{ $appointment->start_time ? \Carbon\Carbon::createFromFormat('H:i:s', $appointment->start_time)->format('h:i A') : 'Not set' }}</span>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <span class="field-label">Pickup Time:</span>
      <span class="field-value">{{ $appointment->end_time ? \Carbon\Carbon::createFromFormat('H:i:s', $appointment->end_time)->format('h:i A') : 'Not set' }}</span>
    </div>
    @if($checkin->notes)
    <div class="field" style="margin-top: 8px;">
      <div class="field-label">Notes:</div>
      <div class="notes">{{ $checkin->notes }}</div>
    </div>
    @endif
  </div>
  @endif

  @if(isset($process) && $process)
  <div class="section">
    <div class="section-title">2. Process</div>
    <div class="field-inline">
      <span class="field-label">Date:</span>
      <span class="field-value">{{ $process->date ? \Carbon\Carbon::parse($process->date)->format('M j, Y') : 'Not set' }}</span>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <span class="field-label">Start Time:</span>
      <span class="field-value">{{ $process->start_time ? \Carbon\Carbon::createFromFormat('H:i:s', $process->start_time)->format('h:i A') : 'Not set' }}</span>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <span class="field-label">Pickup Time:</span>
      <span class="field-value">{{ $process->pickup_time ? \Carbon\Carbon::createFromFormat('H:i:s', $process->pickup_time)->format('h:i A') : 'Not set' }}</span>
    </div>
    @if($process->notes)
    <div class="field" style="margin-top: 8px;">
      <div class="field-label">Notes:</div>
      <div class="notes">{{ $process->notes }}</div>
    </div>
    @endif
  </div>
  @endif

  @if(isset($checkout) && $checkout)
  <div class="section">
    <div class="section-title">3. Checkout</div>
    <div class="field-inline">
      <span class="field-label">Date:</span>
      <span class="field-value">{{ $checkout->date ? \Carbon\Carbon::parse($checkout->date)->format('M j, Y') : 'Not set' }}</span>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <span class="field-label">Start Time:</span>
      <span class="field-value">{{ $checkout->start_time ? \Carbon\Carbon::createFromFormat('H:i:s', $checkout->start_time)->format('h:i A') : 'Not set' }}</span>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <span class="field-label">Pickup Time:</span>
      <span class="field-value">{{ $checkout->pickup_time ? \Carbon\Carbon::createFromFormat('H:i:s', $checkout->pickup_time)->format('h:i A') : 'Not set' }}</span>
    </div>
    @if($checkout->notes)
    <div class="field" style="margin-top: 8px;">
      <div class="field-label">Notes:</div>
      <div class="notes">{{ $checkout->notes }}</div>
    </div>
    @endif
  </div>
  @endif

  @if(isset($checkin) && $checkin && $checkin->flows && is_array($checkin->flows))
  <div class="section">
    <div class="section-title">Initial Temperament Assessment</div>
    
    <h3>Initial Greeting:</h3>
    <div class="checkbox-item">
      <span class="checkbox {{ isset($checkin->flows['initial_greeting']) && $checkin->flows['initial_greeting'] === 'approachable' ? 'checked' : '' }}"></span>
      <span>Approachable (allows contact, loose body posture, will accept treats)</span>
    </div>
    <div class="checkbox-item">
      <span class="checkbox {{ isset($checkin->flows['initial_greeting']) && $checkin->flows['initial_greeting'] === 'shy' ? 'checked' : '' }}"></span>
      <span>Shy (cautious, tail tucked, whale eye, does not want to be petted)</span>
    </div>
    <div class="checkbox-item">
      <span class="checkbox {{ isset($checkin->flows['initial_greeting']) && $checkin->flows['initial_greeting'] === 'uncomfortable' ? 'checked' : '' }}"></span>
      <span>Uncomfortable (moves away, shows teeth, barks or snaps)</span>
    </div>

    <h3>Table test, can you touch the following areas:</h3>
    <div class="field" style="margin-bottom: 10px;">
      <div class="field-label" style="margin-bottom: 5px;">Body:</div>
      <div style="margin-left: 10px;">
        <span class="checkbox {{ isset($checkin->flows['touch_body']) && $checkin->flows['touch_body'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
        <span class="checkbox {{ isset($checkin->flows['touch_body']) && $checkin->flows['touch_body'] === 'react' ? 'checked' : '' }}"></span> Reacts
      </div>
    </div>
    <div class="field" style="margin-bottom: 10px;">
      <div class="field-label" style="margin-bottom: 5px;">Legs:</div>
      <div style="margin-left: 10px;">
        <span class="checkbox {{ isset($checkin->flows['touch_legs']) && $checkin->flows['touch_legs'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
        <span class="checkbox {{ isset($checkin->flows['touch_legs']) && $checkin->flows['touch_legs'] === 'react' ? 'checked' : '' }}"></span> Reacts
      </div>
    </div>
    <div class="field" style="margin-bottom: 10px;">
      <div class="field-label" style="margin-bottom: 5px;">Feet:</div>
      <div style="margin-left: 10px;">
        <span class="checkbox {{ isset($checkin->flows['touch_feet']) && $checkin->flows['touch_feet'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
        <span class="checkbox {{ isset($checkin->flows['touch_feet']) && $checkin->flows['touch_feet'] === 'react' ? 'checked' : '' }}"></span> Reacts
      </div>
    </div>
    <div class="field" style="margin-bottom: 10px;">
      <div class="field-label" style="margin-bottom: 5px;">Tail:</div>
      <div style="margin-left: 10px;">
        <span class="checkbox {{ isset($checkin->flows['touch_tail']) && $checkin->flows['touch_tail'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
        <span class="checkbox {{ isset($checkin->flows['touch_tail']) && $checkin->flows['touch_tail'] === 'react' ? 'checked' : '' }}"></span> Reacts
      </div>
    </div>
    <div class="field" style="margin-bottom: 10px;">
      <div class="field-label" style="margin-bottom: 5px;">Face:</div>
      <div style="margin-left: 10px;">
        <span class="checkbox {{ isset($checkin->flows['touch_face']) && $checkin->flows['touch_face'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
        <span class="checkbox {{ isset($checkin->flows['touch_face']) && $checkin->flows['touch_face'] === 'react' ? 'checked' : '' }}"></span> Reacts
      </div>
    </div>
    <div class="field" style="margin-bottom: 10px;">
      <div class="field-label" style="margin-bottom: 5px;">Nails:</div>
      <div style="margin-left: 10px;">
        <span class="checkbox {{ isset($checkin->flows['touch_nails']) && $checkin->flows['touch_nails'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
        <span class="checkbox {{ isset($checkin->flows['touch_nails']) && $checkin->flows['touch_nails'] === 'react' ? 'checked' : '' }}"></span> Reacts
      </div>
    </div>
  </div>
  @endif

  @if(isset($process) && $process)
    @php
      $processFlows = [];
      if ($process->flows) {
        if (is_array($process->flows)) {
          $processFlows = $process->flows;
        } elseif (is_string($process->flows)) {
          $decoded = json_decode($process->flows, true);
          $processFlows = is_array($decoded) ? $decoded : [];
        }
      }
    @endphp
    <div class="section">
      <div class="section-title">Process Activities</div>

      <div class="field" style="margin-bottom: 10px;">
        <div class="field-label" style="margin-bottom: 5px;">Nail Trimming:</div>
        <div style="margin-left: 10px;">
          <span class="checkbox {{ isset($processFlows['nail_trimming']) && $processFlows['nail_trimming'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
          <span class="checkbox {{ isset($processFlows['nail_trimming']) && $processFlows['nail_trimming'] === 'react' ? 'checked' : '' }}"></span> Reacts
        </div>
      </div>

      <div class="field" style="margin-bottom: 10px;">
        <div class="field-label" style="margin-bottom: 5px;">Ear Cleaning:</div>
        <div style="margin-left: 10px;">
          <span class="checkbox {{ isset($processFlows['ear_cleaning']) && $processFlows['ear_cleaning'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
          <span class="checkbox {{ isset($processFlows['ear_cleaning']) && $processFlows['ear_cleaning'] === 'react' ? 'checked' : '' }}"></span> Reacts
        </div>
      </div>

      <div class="field" style="margin-bottom: 10px;">
        <div class="field-label" style="margin-bottom: 5px;">Wetting with Sprayer:</div>
        <div style="margin-left: 10px;">
          <span class="checkbox {{ isset($processFlows['wetting_sprayer']) && $processFlows['wetting_sprayer'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
          <span class="checkbox {{ isset($processFlows['wetting_sprayer']) && $processFlows['wetting_sprayer'] === 'react' ? 'checked' : '' }}"></span> Reacts
        </div>
      </div>

      <div class="field" style="margin-bottom: 10px;">
        <div class="field-label" style="margin-bottom: 5px;">Shampooing:</div>
        <div style="margin-left: 10px;">
          <span class="checkbox {{ isset($processFlows['shampooing']) && $processFlows['shampooing'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
          <span class="checkbox {{ isset($processFlows['shampooing']) && $processFlows['shampooing'] === 'react' ? 'checked' : '' }}"></span> Reacts
        </div>
      </div>

      <div class="field" style="margin-bottom: 10px;">
        <div class="field-label" style="margin-bottom: 5px;">Rinsing:</div>
        <div style="margin-left: 10px;">
          <span class="checkbox {{ isset($processFlows['rinsing']) && $processFlows['rinsing'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
          <span class="checkbox {{ isset($processFlows['rinsing']) && $processFlows['rinsing'] === 'react' ? 'checked' : '' }}"></span> Reacts
        </div>
      </div>

      <div class="field" style="margin-bottom: 10px;">
        <div class="field-label" style="margin-bottom: 5px;">Drying:</div>
        <div style="margin-left: 10px;">
          <span class="checkbox {{ isset($processFlows['drying']) && $processFlows['drying'] === 'accept' ? 'checked' : '' }}"></span> Accepts &nbsp;&nbsp;
          <span class="checkbox {{ isset($processFlows['drying']) && $processFlows['drying'] === 'react' ? 'checked' : '' }}"></span> Reacts
        </div>
      </div>

      <h3>Brushing/Combing:</h3>
      <div class="field-value">
        <span class="checkbox {{ isset($processFlows['brushing_body']) && ($processFlows['brushing_body'] === true || $processFlows['brushing_body'] === 'true') ? 'checked' : '' }}"></span> Body &nbsp;&nbsp;
        <span class="checkbox {{ isset($processFlows['brushing_legs']) && ($processFlows['brushing_legs'] === true || $processFlows['brushing_legs'] === 'true') ? 'checked' : '' }}"></span> Legs &nbsp;&nbsp;
        <span class="checkbox {{ isset($processFlows['brushing_feet']) && ($processFlows['brushing_feet'] === true || $processFlows['brushing_feet'] === 'true') ? 'checked' : '' }}"></span> Feet &nbsp;&nbsp;
        <span class="checkbox {{ isset($processFlows['brushing_tail']) && ($processFlows['brushing_tail'] === true || $processFlows['brushing_tail'] === 'true') ? 'checked' : '' }}"></span> Tail &nbsp;&nbsp;
        <span class="checkbox {{ isset($processFlows['brushing_face']) && ($processFlows['brushing_face'] === true || $processFlows['brushing_face'] === 'true') ? 'checked' : '' }}"></span> Face
      </div>

      <h3>Clippers/Scissors:</h3>
      <div class="field-value">
        <span class="checkbox {{ isset($processFlows['clippers_body']) && ($processFlows['clippers_body'] === true || $processFlows['clippers_body'] === 'true') ? 'checked' : '' }}"></span> Body &nbsp;&nbsp;
        <span class="checkbox {{ isset($processFlows['clippers_legs']) && ($processFlows['clippers_legs'] === true || $processFlows['clippers_legs'] === 'true') ? 'checked' : '' }}"></span> Legs &nbsp;&nbsp;
        <span class="checkbox {{ isset($processFlows['clippers_feet']) && ($processFlows['clippers_feet'] === true || $processFlows['clippers_feet'] === 'true') ? 'checked' : '' }}"></span> Feet &nbsp;&nbsp;
        <span class="checkbox {{ isset($processFlows['clippers_tail']) && ($processFlows['clippers_tail'] === true || $processFlows['clippers_tail'] === 'true') ? 'checked' : '' }}"></span> Tail &nbsp;&nbsp;
        <span class="checkbox {{ isset($processFlows['clippers_face']) && ($processFlows['clippers_face'] === true || $processFlows['clippers_face'] === 'true') ? 'checked' : '' }}"></span> Face
      </div>
    </div>
  @endif

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
    <div class="section">
      <div class="section-title">Final Assessment</div>
      
      @if(isset($checkoutFlows['rating']))
      <div class="field" style="margin-bottom: 10px;">
        <div class="field-label" style="margin-bottom: 5px;">Rating:</div>
        <div style="margin-left: 10px;">
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
        </div>
      </div>
      @endif

      @if(isset($checkoutFlows['service_notes']) && $checkoutFlows['service_notes'])
      <div class="field">
        <div class="field-label">Service Notes:</div>
        <div class="notes">{{ $checkoutFlows['service_notes'] }}</div>
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

  <div class="footer">
    <p>Generated on {{ \Carbon\Carbon::now()->format('F j, Y \a\t h:i A') }}</p>
    <p>PawPrints Grooming Report</p>
  </div>
</body>
</html>

