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
    }
    .field-value {
      color: #1a1a1a;
      display: inline-block;
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

  @if(!empty($showPetStayInfo))
  <div class="section">
    <h2>Pet Stay Information</h2>
    <div class="grid">
      <div class="grid-row">
        <div class="grid-cell">
          <span class="field-label">Pet Name:</span>
          <span class="field-value">{{ $appointment->pet->name ?? 'Not set' }}</span>
        </div>
      </div>
      <div class="grid-row">
        <div class="grid-cell">
          <span class="field-label">Owner Name:</span>
          <span class="field-value">{{ $ownerName ?? 'Not set' }}</span>
        </div>
      </div>
      <div class="grid-row">
        <div class="grid-cell">
          <span class="field-label">Stay Duration:</span>
          <span class="field-value">{{ $stayDuration ?? 'Not set' }}</span>
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
            <span class="field-label">Behavior:</span>
            <span class="field-value">{{ !empty($behaviorLabels) ? implode(', ', $behaviorLabels) : 'None' }}</span>
          </div>
        </div>
      @endif
    </div>
  </div>
  @endif
  <div class="footer">
    <p>Generated on {{ \Carbon\Carbon::now()->format('F j, Y \a\t h:i A') }}</p>
    <p>PawPrints Boarding Report</p>
  </div>
</body>
</html>
