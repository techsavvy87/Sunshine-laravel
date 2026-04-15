<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Training Report - {{ $appointment->pet->name }}</title>
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
    .badge-primary {
      background-color: #bee3f8;
      color: #2c5282;
    }
    .notes {
      background-color: #f7fafc;
      padding: 10px;
      border-radius: 3px;
      margin-top: 5px;
      white-space: pre-wrap;
    }
    .star-rating {
      display: inline-block;
      margin-left: 5px;
    }
    .star {
      color: #fbbf24;
      font-size: 14px;
    }
    .star-empty {
      color: #d1d5db;
      font-size: 14px;
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
  <h1>Training Report</h1>
  
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

  <!-- Training Check-in Info -->
  @if(isset($checkin) && $checkin)
  <div class="section">
    <h2 class="section-title">1. Training Check-in Info</h2>
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
    @if($checkin->flows && is_array($checkin->flows))
      @php
        $location = isset($checkin->flows['location']) ? $checkin->flows['location'] : '';
        $pickupDateTime = '';
        if (isset($checkin->flows['pickup_datetime_onsite']) && $checkin->flows['pickup_datetime_onsite']) {
          try {
            $dt = \Carbon\Carbon::parse($checkin->flows['pickup_datetime_onsite']);
            $pickupDateTime = $dt->format('M j, Y g:i A');
          } catch (\Exception $e) {
            $pickupDateTime = $checkin->flows['pickup_datetime_onsite'];
          }
        } else {
          $pickupDate = isset($checkin->flows['pickup_date_onsite']) ? $checkin->flows['pickup_date_onsite'] : '';
          $pickupTime = isset($checkin->flows['pickup_time_onsite']) ? $checkin->flows['pickup_time_onsite'] : '';
          if ($pickupDate && $pickupTime) {
            try {
              $dt = \Carbon\Carbon::parse($pickupDate . ' ' . $pickupTime);
              $pickupDateTime = $dt->format('M j, Y g:i A');
            } catch (\Exception $e) {
              $pickupDateTime = $pickupDate . ' ' . $pickupTime;
            }
          } elseif ($pickupDate) {
            try {
              $dt = \Carbon\Carbon::parse($pickupDate);
              $pickupDateTime = $dt->format('M j, Y');
            } catch (\Exception $e) {
              $pickupDateTime = $pickupDate;
            }
          }
        }
        $locationAddress = isset($checkin->flows['location_address']) ? $checkin->flows['location_address'] : '';
        $descriptionNeeds = isset($checkin->flows['description_needs']) ? $checkin->flows['description_needs'] : '';
        $trainingFocus = isset($checkin->flows['training_focus']) && is_array($checkin->flows['training_focus']) ? $checkin->flows['training_focus'] : [];
        $additionalServicesLink = isset($checkin->flows['additional_services_link']) ? (is_array($checkin->flows['additional_services_link']) ? $checkin->flows['additional_services_link'] : []) : [];
      @endphp
      @if($location)
      <div class="field">
        <span class="field-label">Location:</span>
        <span class="field-value">{{ ucfirst($location) }}</span>
      </div>
      @endif
      @if($location === 'onsite' && $pickupDateTime)
      <div class="field">
        <span class="field-label">Pick up time/date:</span>
        <span class="field-value">{{ $pickupDateTime }}</span>
      </div>
      @endif
      @if($location === 'offsite' && $locationAddress)
      <div class="field">
        <span class="field-label">Location/address:</span>
        <div class="notes">{{ $locationAddress }}</div>
      </div>
      @endif
      @if(!empty($additionalServicesLink))
      <div class="field">
        <span class="field-label">Additional Services:</span>
        <span class="field-value">
          @php
            $services = \App\Models\Service::whereIn('id', $additionalServicesLink)->get();
          @endphp
          {{ $services->pluck('name')->join(', ') }}
        </span>
      </div>
      @endif
      @if($descriptionNeeds)
      <div class="field">
        <span class="field-label">Goals/owner needs:</span>
        <div class="notes">{{ $descriptionNeeds }}</div>
      </div>
      @endif
      @if(!empty($trainingFocus))
      <div class="field">
        <span class="field-label">Training Focus:</span>
        <span class="field-value">
          @foreach($trainingFocus as $focus)
            @if($focus === 'basic_obedience')
              <span class="badge badge-primary">Basic obedience/management</span>
            @elseif($focus === 'behavior_modification')
              <span class="badge badge-primary">Behavior modification/aggression</span>
            @elseif($focus === 'reactivity')
              <span class="badge badge-primary">Reactivity/socialization</span>
            @endif
          @endforeach
        </span>
      </div>
      @endif
    @endif
  </div>
  @endif

  <!-- Checkout Info -->
  @if(isset($checkout) && $checkout)
  <div class="section">
    <h2 class="section-title">2. Checkout Info</h2>
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
    @php
      $descriptionNeeds = '';
      if (isset($checkin) && $checkin && $checkin->flows && is_array($checkin->flows) && isset($checkin->flows['description_needs'])) {
        $descriptionNeeds = $checkin->flows['description_needs'];
      }
    @endphp
    @if($descriptionNeeds)
    <div class="field">
      <span class="field-label">Customer Goal:</span>
      <div class="notes">{{ $descriptionNeeds }}</div>
    </div>
    @endif
    @if(!empty($lastAppointmentRatings))
    <div class="field">
      <span class="field-label">Star Rating from Last Appointment:</span>
      <div style="margin-top: 5px;">
        @php
          $obedienceCommands = ['sit', 'down', 'stay', 'come', 'loose_leash_walking'];
        @endphp
        @foreach($obedienceCommands as $command)
          @php
            $commandLabel = ucwords(str_replace('_', ' ', $command));
            $lastRating = isset($lastAppointmentRatings[$command]) ? (int)$lastAppointmentRatings[$command] : 0;
          @endphp
          <div style="margin-bottom: 5px;">
            <strong>{{ $commandLabel }}:</strong>
            <span class="star-rating">
              @for($i = 0; $i <= 5; $i++)
                @if($i <= $lastRating)
                  <span class="star">★</span>
                @else
                  <span class="star-empty">☆</span>
                @endif
              @endfor
            </span>
            <span style="margin-left: 5px;">({{ $lastRating }} star{{ $lastRating != 1 ? 's' : '' }})</span>
          </div>
        @endforeach
      </div>
    </div>
    @endif
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
      @php
        $obedienceRatings = isset($checkoutFlows['obedience_ratings']) ? $checkoutFlows['obedience_ratings'] : [];
        $trainingCurrentRatings = isset($checkoutFlows['training_current_ratings']) ? $checkoutFlows['training_current_ratings'] : '';
        $trainingTargets = isset($checkoutFlows['training_targets']) ? $checkoutFlows['training_targets'] : '';
        $trainingHomework = isset($checkoutFlows['training_homework']) ? $checkoutFlows['training_homework'] : '';
        $obedienceCommands = ['sit', 'down', 'stay', 'come', 'loose_leash_walking'];
      @endphp
      @if(!empty($obedienceRatings))
      <div class="field">
        <span class="field-label">Basic obedience (5-star rating):</span>
        <div style="margin-top: 5px;">
          @foreach($obedienceCommands as $command)
            @php
              $commandLabel = ucwords(str_replace('_', ' ', $command));
              $currentRating = isset($obedienceRatings[$command]) ? (int)$obedienceRatings[$command] : 0;
            @endphp
            <div style="margin-bottom: 5px;">
              <strong>{{ $commandLabel }}:</strong>
              <span class="star-rating">
                @for($i = 0; $i <= 5; $i++)
                  @if($i <= $currentRating)
                    <span class="star">★</span>
                  @else
                    <span class="star-empty">☆</span>
                  @endif
                @endfor
              </span>
              <span style="margin-left: 5px;">({{ $currentRating }} star{{ $currentRating != 1 ? 's' : '' }})</span>
            </div>
          @endforeach
        </div>
      </div>
      @endif
      @if($trainingCurrentRatings)
      <div class="field">
        <span class="field-label">Current ratings:</span>
        <div class="notes">{{ $trainingCurrentRatings }}</div>
      </div>
      @endif
      @if($trainingTargets)
      <div class="field">
        <span class="field-label">Goal for next lesson:</span>
        <span class="field-value">{{ $trainingTargets }}</span>
      </div>
      @endif
      @if($trainingHomework)
      <div class="field">
        <span class="field-label">Homework for owner:</span>
        <div class="notes">{{ $trainingHomework }}</div>
      </div>
      @endif
      @if(isset($checkoutFlows['pictures']) && is_array($checkoutFlows['pictures']) && count($checkoutFlows['pictures']) > 0)
      <div class="field">
        <span class="field-label">Checkout Pictures:</span>
        <div style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 10px;">
          @foreach($checkoutFlows['pictures'] as $picture)
            <div style="margin-bottom: 10px;">
              <img src="{{ public_path('storage/checkouts/' . $picture) }}" style="width: 150px; height: 150px; object-fit: cover; border: 1px solid #e2e8f0; border-radius: 5px;" alt="Checkout Picture">
            </div>
          @endforeach
        </div>
      </div>
      @endif
    @endif
  </div>
  @endif

  <div class="footer">
    <p>Generated on {{ \Carbon\Carbon::now()->format('F j, Y \a\t g:i A') }}</p>
  </div>
</body>
</html>

