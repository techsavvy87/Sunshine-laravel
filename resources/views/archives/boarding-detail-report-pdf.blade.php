<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Boarding Report - {{ $appointment->pet->name }}</title>
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
      font-size: 20px;
      margin-bottom: 15px;
      color: #1a1a1a;
      border-bottom: 2px solid #4a5568;
      padding-bottom: 8px;
    }
    h2 {
      font-size: 14px;
      margin-top: 15px;
      margin-bottom: 8px;
      color: #2d3748;
      font-weight: bold;
    }
    h3 {
      font-size: 12px;
      margin-top: 10px;
      margin-bottom: 6px;
      color: #4a5568;
      font-weight: bold;
    }
    .section {
      margin-bottom: 15px;
      padding: 10px;
      page-break-inside: avoid;
    }
    .field {
      margin-bottom: 5px;
    }
    .field-label {
      font-weight: bold;
      color: #4a5568;
      display: inline-block;
      min-width: 120px;
      vertical-align: middle;
      line-height: 1.3;
    }
    .field-value {
      color: #1a1a1a;
      display: inline-block;
      vertical-align: middle;
      line-height: 1.3;
    }
    .grid {
      display: table;
      width: 100%;
      margin-bottom: 10px;
    }
    .grid-row {
      display: table-row;
    }
    .grid-cell {
      display: table-cell;
      padding: 3px 10px 3px 0;
      vertical-align: top;
    }
    .subsection {
      margin-left: 10px;
      margin-bottom: 10px;
      padding-left: 10px;
      border-left: 2px solid #cbd5e0;
    }
    .item-list {
      margin-bottom: 8px;
    }
    .item {
      margin-bottom: 6px;
      padding-left: 15px;
    }
    .empty-message {
      color: #718096;
      font-style: italic;
    }
    .footer {
      margin-top: 30px;
      padding-top: 15px;
      border-top: 2px solid #4a5568;
      text-align: center;
      font-size: 10px;
      color: #718096;
    }
    @media print {
      .section {
        page-break-inside: avoid;
      }
    }
    @page {
      margin: 20px;
    }
  </style>
</head>
<body>
  <h1>Boarding Report</h1>

  {{-- Section 1: Pet Stay Information --}}
  @if(!empty($showPetStayInfo))
  <div class="section">
    <h2>Pet & Appointment Information</h2>
    <div class="grid">
      <div class="grid-row">
        <div class="grid-cell">
          <span class="field-label">Kennel:</span>
          <span class="field-value">{{ $kennelName ?? 'N/A' }}</span>
        </div>
      </div>
      <div class="grid-row">
        <div class="grid-cell">
          <span class="field-label">Pet Name:</span>
          <span class="field-value">{{ $appointment->pet->name ?? 'N/A' }}</span>
        </div>
      </div>
      <div class="grid-row">
        <div class="grid-cell">
          <span class="field-label">Owner Name:</span>
          <span class="field-value">{{ $ownerName ?? 'N/A' }}</span>
        </div>
      </div>
      <div class="grid-row">
        <div class="grid-cell">
          <span class="field-label">Stay Duration:</span>
          <span class="field-value">{{ $stayDuration ?? 'N/A' }}</span>
        </div>
      </div>
      <div class="grid-row">
        <div class="grid-cell">
          <span class="field-label">Check-in:</span>
          <span class="field-value">{{ $checkinDateTime ?? 'N/A' }}</span>
        </div>
      </div>
      <div class="grid-row">
        <div class="grid-cell">
          <span class="field-label">Pickup Date:</span>
          <span class="field-value">{{ $pickupDateTime ?? 'N/A' }}</span>
        </div>
      </div>
      <div class="grid-row">
        <div class="grid-cell">
          <span class="field-label">Senior:</span>
          <span class="field-value">{{ !empty($isSenior) ? 'Yes' : 'No' }}</span>
        </div>
      </div>
      <div class="grid-row">
        <div class="grid-cell">
          <span class="field-label">Medication Required:</span>
          <span class="field-value">{{ !empty($medicationRequired) ? 'Yes' : 'No' }}</span>
        </div>
      </div>
      @if(!empty($behaviorLabels))
        <div class="grid-row">
          <div class="grid-cell">
            <span class="field-label">Behavior Notes:</span>
            <span class="field-value">{{ implode(', ', $behaviorLabels) }}</span>
          </div>
        </div>
      @endif
    </div>
  </div>
  @endif

  {{-- Section 2: Feeding Information --}}
  <div class="section">
    <h2>Feeding Information</h2>
    @if(!empty($dryFoodList) || !empty($wetFoodList) || !empty($ownerFoodList) || !empty($ownerFood))
      {{-- Dry Food --}}
      @if(!empty($dryFoodList))
        <h3>Dry Food</h3>
        <div class="item-list">
          @foreach($dryFoodList as $food)
            <div class="item">
              <strong>{{ $food['brand'] ?? 'N/A' }}</strong>
              @if(!empty($food['amount']))
                — {{ $food['amount'] }}
              @endif
              <br/>
              <span class="field-label">Feeding Time:</span>
              <span class="field-value">{{ !empty($food['selected_times']) ? implode(', ', $food['selected_times']) : 'N/A' }}</span>
            </div>
          @endforeach
        </div>
      @endif

      {{-- Wet Food --}}
      @if(!empty($wetFoodList))
        <h3>Wet Food</h3>
        <div class="item-list">
          @foreach($wetFoodList as $food)
            <div class="item">
              <strong>{{ $food['brand'] ?? 'N/A' }}</strong>
              @if(!empty($food['amount']))
                — {{ $food['amount'] }}
              @endif
              <br/>
              <span class="field-label">Feeding Time:</span>
              <span class="field-value">{{ !empty($food['selected_times']) ? implode(', ', $food['selected_times']) : 'N/A' }}</span>
            </div>
          @endforeach
        </div>
      @endif

      {{-- Owner Food --}}
      @if(!empty($ownerFoodList))
        <h3>Owner-Provided Food</h3>
        <div class="item-list">
          @foreach($ownerFoodList as $ownerFoodItem)
            <div class="item">
              <strong>{{ $ownerFoodItem['value'] ?? 'N/A' }}</strong>
              <br/>
              <span class="field-label">Feeding Time:</span>
              <span class="field-value">{{ !empty($ownerFoodItem['selected_times']) ? implode(', ', $ownerFoodItem['selected_times']) : 'N/A' }}</span>
            </div>
          @endforeach
        </div>
      @elseif(!empty($ownerFood))
        <h3>Owner-Provided Food</h3>
        <div class="item">{{ is_string($ownerFood) ? $ownerFood : 'N/A' }}</div>
      @endif

      {{-- Feeding Notes --}}
      @if(!empty($feedingNotes))
        <h3>Feeding Notes</h3>
        <div class="item">
          {{ $feedingNotes }}
        </div>
      @endif
    @else
      <span class="empty-message">No feeding information recorded.</span>
    @endif
  </div>

  {{-- Section 3: Medication Information --}}
  <div class="section">
    <h2>Medication Information</h2>
    @if(!empty($medicationList))
      <div class="item-list">
        @foreach($medicationList as $med)
          <div class="item">
            <strong>{{ $med['name'] ?? 'N/A' }}</strong>
            @if(!empty($med['amount']))
              — {{ $med['amount'] }}
            @endif
            
            <br/>
            <span class="field-label">Medication Time:</span>
            <span class="field-value">{{ !empty($med['selected_times']) ? implode(', ', $med['selected_times']) : 'N/A' }}</span>

            @if(!empty($med['conditions_display']))
              <br/>
              <span class="field-label">Condition:</span>
              <span class="field-value">{{ implode(', ', $med['conditions_display']) }}</span>
            @endif
          </div>
        @endforeach
      </div>

      {{-- Medication Notes --}}
      @if(!empty($medicationNotes))
        <h3>Medication Notes</h3>
        <div class="item">
          {{ $medicationNotes }}
        </div>
      @endif
    @else
      <span class="empty-message">No medications recorded.</span>
    @endif
  </div>

  {{-- Section 4: Rest Information --}}
  <div class="section">
    <h2>Rest Information</h2>
    @if(!empty($restRequired) || !empty($restNote))
      <div class="grid">
        <div class="grid-row">
          <div class="grid-cell">
            <span class="field-label">Rest Required:</span>
            <span class="field-value">{{ !empty($restRequired) ? 'Yes' : 'No' }}</span>
          </div>
        </div>
        @if(!empty($restNote))
          <div class="grid-row">
            <div class="grid-cell">
              <span class="field-label">Rest Note:</span>
              <span class="field-value">{{ $restNote }}</span>
            </div>
          </div>
        @endif
      </div>
    @else
      <span class="empty-message">No rest assigned for this pet.</span>
    @endif
  </div>

  <div class="footer">
    <p>Generated on {{ \Carbon\Carbon::now()->format('F j, Y \a\t h:i A') }}</p>
    <p>Sunshine Boarding Report</p>
  </div>
</body>
</html>
