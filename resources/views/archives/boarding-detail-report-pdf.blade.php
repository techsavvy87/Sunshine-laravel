<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Boarding Report - {{ $familyPets->pluck('name')->join(', ') }}</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: 'DejaVu Sans', Arial, sans-serif;
      font-size: 14px;
      line-height: 1.3;
      color: #333;
      padding: 8px;
    }
    h1 {
      font-size: 16px;
      margin: 0 0 8px 0;
      color: #1a1a1a;
      border-bottom: 2px solid #4a5568;
      padding-bottom: 4px;
    }
    h2 {
      font-size: 14px;
      margin: 6px 0 3px 0;
      color: #2d3748;
      font-weight: bold;
    }
    h3 {
      font-size: 14px;
      margin: 3px 0 2px 0;
      color: #4a5568;
      font-weight: bold;
    }
    .section {
      margin-bottom: 6px;
      padding: 4px;
      page-break-inside: avoid;
    }
    .field {
      margin-bottom: 2px;
    }
    .field-label {
      font-weight: bold;
      color: #4a5568;
      display: inline-block;
      min-width: 100px;
      vertical-align: middle;
      line-height: 1.2;
    }
    .field-value {
      color: #1a1a1a;
      display: inline-block;
      vertical-align: middle;
      line-height: 1.2;
      flex-wrap: wrap;
    }
    .grid {
      display: table;
      width: 100%;
      margin-bottom: 4px;
    }
    .grid-row {
      display: table-row;
    }
    .grid-cell {
      display: table-cell;
      padding: 1px 6px 1px 0;
      vertical-align: top;
    }
    .subsection {
      margin-left: 6px;
      margin-bottom: 4px;
      padding-left: 6px;
      border-left: 2px solid #cbd5e0;
    }
    .item-list {
      margin-bottom: 3px;
    }
    .item {
      margin-bottom: 2px;
      padding-left: 8px;
    }
    .empty-message {
      color: #718096;
      font-style: italic;
    }
    .footer {
      margin-top: 8px;
      padding-top: 6px;
      border-top: 2px solid #4a5568;
      text-align: center;
      font-size: 9px;
      color: #718096;
    }
    .pets-container {
      display: table;
      width: 100%;
      border-collapse: collapse;
      gap: 0;
    }
    .pet-column {
      display: table-cell;
      border: 1px solid #999;
      padding: 6px;
      vertical-align: top;
      width: 50%;
    }
    .pet-column-3 {
      display: table-cell;
      border: 1px solid #999;
      padding: 6px;
      vertical-align: top;
      width: 33.33%;
    }
    .pet-status {
      margin-bottom: 3px;
    }
    .status-badge {
      background: #eef2f7;
      padding: 1px 3px;
      margin-right: 2px;
      display: inline-block;
    }
    .pet-meta,
    .pet-entry,
    .pet-note {
      margin-bottom: 1px;
    }
    .rating-stars {
      letter-spacing: 0.5px;
      font-size: 16px;
    }
    .behavior-list {
      margin-top: 1px;
    }
    .behavior-item {
      margin-bottom: 5px;
      display: block;
    }
    .behavior-icon {
      display: inline-block;
      width: 24px;
      height: 24px;
      margin-right: 4px;
      vertical-align: middle;
      color: #1a1a1a;
    }
    .behavior-icon svg,
    .behavior-icon .iconify,
    .behavior-icon img {
      width: 24px;
      height: 24px;
      display: inline-block;
      vertical-align: middle;
    }
    .behavior-label {
      line-height: 1.2;
      display: inline-block;
      vertical-align: middle;
    }
    .care-notes-row {
      margin-top: 3px;
      padding-top: 2px;
    }
    .care-notes-label {
      font-weight: bold;
      margin-right: 4px;
      display: block;
      margin-bottom: 1px;
    }
    .care-notes-content {
      display: block;
      white-space: pre-line;
    }
    @media print {
      body {
        padding: 6px;
      }
      .section {
        page-break-inside: avoid;
        margin-bottom: 4px;
      }
    }
    @page {
      margin: 10px;
    }
  </style>
</head>
<body>
  <h1>Boarding Report</h1>

  {{-- Section 1: Appointment Information --}}
  @if(!empty($showPetStayInfo))
  <div class="section">
    <h2>Appointment Information</h2>
    <div class="grid">
      <div class="grid-row">
        <div class="grid-cell">
          <span class="field-label">Assignment:</span>
          <span class="field-value">{{ $assignmentLabel ?? $kennelName ?? 'N/A' }}</span>
        </div>
      </div>
      <div class="grid-row">
        <div class="grid-cell">
          <span class="field-label">Pets:</span>
          <span class="field-value">{{ $familyPets->pluck('name')->join(', ') ?? 'N/A' }}</span>
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
    </div>
  </div>
  @endif

  {{-- Section 2-N: Per-Pet Care Information (Side by Side) --}}
  @if(!empty($petsCareData))
    <div class="section">
      <h2>Pet Care Information</h2>
      <div class="pets-container">
        @foreach($petsCareData as $petData)
          <div class="pet-column" style="@if(count($petsCareData) == 3) width: 33.33%; @else width: {{ 100 / count($petsCareData) }}%; @endif">
            {{-- Pet Name and Status --}}
            <h3 style="margin: 0 0 3px 0;">{{ $petData['pet']->name }}</h3>
            <div class="pet-status">
              @if(!empty($petData['isSenior']))
                <span class="status-badge">Senior</span>
              @endif
              @if(!empty($petData['medicationRequired']))
                <span class="status-badge">Meds</span>
              @endif
            </div>

            <div class="pet-meta">
              <strong>Rating:</strong>
              @if(!empty($petData['ratingStars']))
                <span class="rating-stars">{{ $petData['ratingStars'] }}</span>
              @else
                <span class="empty-message">N/A</span>
              @endif
            </div>

            <div class="pet-meta">
              <strong>Behavior:</strong>
              @if(!empty($petData['behaviorItems']))
                <div class="behavior-list">
                  @foreach($petData['behaviorItems'] as $behavior)
                    @php
                      $iconMarkup = trim((string) ($behavior['icon_markup'] ?? ''));

                      if ($iconMarkup !== '') {
                          // Remove unsupported/problematic SVG parts
                          $iconMarkup = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $iconMarkup);
                          $iconMarkup = preg_replace('/\sclass="[^"]*"/i', '', $iconMarkup);
                          $iconMarkup = preg_replace('/\sid="[^"]*"/i', '', $iconMarkup);
                          $iconMarkup = preg_replace('/\sversion="[^"]*"/i', '', $iconMarkup);
                          $iconMarkup = preg_replace('/\sxml:space="[^"]*"/i', '', $iconMarkup);
                          $iconMarkup = preg_replace('/\sxmlns:xlink="[^"]*"/i', '', $iconMarkup);

                          // Force SVG size for PDF
                          $iconMarkup = preg_replace('/\swidth="[^"]*"/i', ' width="32"', $iconMarkup, 1);
                          $iconMarkup = preg_replace('/\sheight="[^"]*"/i', ' height="32"', $iconMarkup, 1);

                          // If SVG has huge viewBox, scale paths down into 32x32 viewBox
                          if (preg_match('/viewBox="0 0 ([0-9.]+) ([0-9.]+)"/i', $iconMarkup, $matches)) {
                              $viewBoxWidth = (float) $matches[1];

                              if ($viewBoxWidth > 32) {
                                  $scale = 32 / $viewBoxWidth;

                                  $iconMarkup = preg_replace(
                                      '/viewBox="[^"]*"/i',
                                      'viewBox="0 0 32 32"',
                                      $iconMarkup,
                                      1
                                  );

                                  $iconMarkup = preg_replace(
                                      '/<svg\b([^>]*)>/i',
                                      '<svg$1><g transform="scale(' . $scale . ')">',
                                      $iconMarkup,
                                      1
                                  );

                                  $iconMarkup = preg_replace(
                                      '/<\/svg>/i',
                                      '</g></svg>',
                                      $iconMarkup,
                                      1
                                  );
                              }
                          }

                          $iconDataUri = 'data:image/svg+xml;base64,' . base64_encode($iconMarkup);
                      } else {
                          $iconDataUri = '';
                      }
                  @endphp
                    <div class="behavior-item">
                      @if($iconDataUri !== '')
                        <span class="behavior-icon"><img src="{{ $iconDataUri }}" alt="" /></span>
                      @endif
                      <span class="behavior-label">{{ $behavior['label'] ?: 'N/A' }}</span>
                    </div>
                  @endforeach
                </div>
              @else
                <span class="empty-message">N/A</span>
              @endif
            </div>

            {{-- Feeding --}}
            @if(!empty($petData['dryFoodList']) || !empty($petData['wetFoodList']) || !empty($petData['ownerFoodList']) || !empty($petData['ownerFood']))
              <h3 style="margin: 3px 0 2px 0;">Feeding</h3>
              @if(!empty($petData['dryFoodList']))
                @foreach($petData['dryFoodList'] as $food)
                  <div class="pet-entry">
                    <strong>{{ $food['brand'] ?? 'N/A' }}</strong>
                    @if(!empty($food['amount'])){{ $food['amount'] }} @endif
                    <br/>
                    {{ !empty($food['selected_times']) ? implode(', ', $food['selected_times']) : 'N/A' }}
                  </div>
                @endforeach
              @endif
              @if(!empty($petData['wetFoodList']))
                @foreach($petData['wetFoodList'] as $food)
                  <div class="pet-entry">
                    <strong>{{ $food['brand'] ?? 'N/A' }}</strong>
                    @if(!empty($food['amount'])){{ $food['amount'] }} @endif
                    <br/>
                    {{ !empty($food['selected_times']) ? implode(', ', $food['selected_times']) : 'N/A' }}
                  </div>
                @endforeach
              @endif
              @if(!empty($petData['ownerFoodList']))
                @foreach($petData['ownerFoodList'] as $ownerFoodItem)
                  <div class="pet-entry">
                    <strong>{{ $ownerFoodItem['value'] ?? 'N/A' }}</strong>
                    <br/>
                    {{ !empty($ownerFoodItem['selected_times']) ? implode(', ', $ownerFoodItem['selected_times']) : 'N/A' }}
                  </div>
                @endforeach
              @elseif(!empty($petData['ownerFood']))
                <div class="pet-entry">{{ is_string($petData['ownerFood']) ? $petData['ownerFood'] : 'N/A' }}</div>
              @endif
              @if(!empty($petData['feedingNotes']))
                <div class="pet-note empty-message">
                  {{ $petData['feedingNotes'] }}
                </div>
              @endif
            @endif

            {{-- Medication --}}
            @if(!empty($petData['medicationList']))
              <h3 style="margin: 3px 0 2px 0;">Medication</h3>
              @foreach($petData['medicationList'] as $med)
                <div class="pet-entry">
                  <strong>{{ $med['name'] ?? 'N/A' }}</strong>
                  @if(!empty($med['amount'])){{ $med['amount'] }} @endif
                  <br/>
                  {{ !empty($med['selected_times']) ? implode(', ', $med['selected_times']) : 'N/A' }}
                  @if(!empty($med['conditions_display']))
                    <br/>
                    <span>{{ implode(', ', $med['conditions_display']) }}</span>
                  @endif
                </div>
              @endforeach
              @if(!empty($petData['medicationNotes']))
                <div class="pet-note empty-message">
                  {{ $petData['medicationNotes'] }}
                </div>
              @endif
            @endif

            {{-- Rest --}}
            @if(!empty($petData['restRequired']) || !empty($petData['restNote']))
              <h3 style="margin: 3px 0 2px 0;">Rest</h3>
              <div class="pet-entry">
                <strong>Required:</strong> {{ !empty($petData['restRequired']) ? 'Yes' : 'No' }}
              </div>
              @if(!empty($petData['restNote']))
                <div class="pet-note empty-message">
                  {{ $petData['restNote'] }}
                </div>
              @endif
            @endif

            @if(!empty($petData['careNotes']))
              <div class="care-notes-row">
                <span class="care-notes-label">Care notes</span>
                <span class="care-notes-content">{{ $petData['careNotes'] }}</span>
              </div>
            @endif
          </div>
        @endforeach
      </div>
    </div>
  @endif

  <div class="footer">
    <p>Generated on {{ \Carbon\Carbon::now()->format('F j, Y \a\t h:i A') }}</p>
    <p>Sunshine Boarding Report</p>
  </div>
</body>
</html>
