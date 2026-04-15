<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Concierge Report - {{ $appointment->pet->name }}</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: 'DejaVu Sans', Arial, sans-serif;
      font-size: 11px;
      line-height: 1.5;
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
      margin-bottom: 5px;
      color: #4a5568;
      font-weight: bold;
    }
    .section {
      margin-bottom: 15px;
      padding: 10px;
      border-bottom: 1px solid #e2e8f0;
      page-break-inside: avoid;
    }
    .field {
      margin-bottom: 5px;
    }
    .field-label {
      font-weight: bold;
      display: inline-block;
      min-width: 120px;
    }
    .field-value {
      display: inline-block;
    }
    .checkbox {
      margin-right: 5px;
    }
    ul {
      margin-left: 20px;
      margin-bottom: 5px;
    }
    li {
      margin-bottom: 3px;
    }
    .indent {
      margin-left: 20px;
    }
    .inline-label {
      display: inline;
      font-weight: bold;
    }
    .inline-value {
      display: inline;
      margin-left: 5px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }
    table td {
      padding: 5px;
      border: 1px solid #e2e8f0;
    }
    .page-break {
      page-break-before: always;
    }
  </style>
</head>
<body>
  <h1>Concierge Report</h1>

  <div class="section">
    <div class="field">
      <span class="field-label">Pet Name:</span>
      <span class="field-value">{{ $appointment->pet->name }}</span>
    </div>
    <div class="field">
      <span class="field-label">Customer:</span>
      <span class="field-value">{{ $appointment->customer->profile->first_name }} {{ $appointment->customer->profile->last_name }}</span>
    </div>
    <div class="field">
      <span class="field-label">Date:</span>
      <span class="field-value">{{ \Carbon\Carbon::parse($date)->format('M j, Y') }}</span>
    </div>
    @if($appointment->service)
    <div class="field">
      <span class="field-label">Service:</span>
      <span class="field-value">{{ $appointment->service->name }}</span>
    </div>
    @endif
  </div>

  @php
    $flows = $process->flows ?? [];
    $formatTimeOrDatetime = function($t) {
      if (!$t || trim((string)$t) === '') return '—';
      $t = trim((string)$t);
      try {
        $dt = \Carbon\Carbon::parse($t);
        return $dt->format('g:i A');
      } catch (\Exception $e) {
        return $t;
      }
    };
  @endphp

  <div class="section">
    <h2>Concierge Reports</h2>
    
    {{-- 1. Nose --}}
    <div class="field">
      <h3>1. Nose</h3>
      <div class="indent">
        <div class="field">
          <span class="checkbox">{{ isset($flows['concierge_nose']) && $flows['concierge_nose'] === 'okay' ? '☑' : '☐' }}</span>
          <span>Okay</span>
          <span class="checkbox" style="margin-left: 10px;">{{ isset($flows['concierge_nose']) && $flows['concierge_nose'] === 'issue' ? '☑' : '☐' }}</span>
          <span>Issue</span>
          @if(isset($flows['concierge_nose']) && $flows['concierge_nose'] === 'issue')
            @if(isset($flows['concierge_nose_discharge']) && ($flows['concierge_nose_discharge'] === true || $flows['concierge_nose_discharge'] === 'true'))
              <span style="margin-left: 10px;">☑ Discharge</span>
            @endif
            @if(isset($flows['concierge_nose_dryness']) && ($flows['concierge_nose_dryness'] === true || $flows['concierge_nose_dryness'] === 'true'))
              <span style="margin-left: 10px;">☑ Dryness</span>
            @endif
            @if(isset($flows['concierge_nose_cracking']) && ($flows['concierge_nose_cracking'] === true || $flows['concierge_nose_cracking'] === 'true'))
              <span style="margin-left: 10px;">☑ Cracking</span>
            @endif
          @endif
        </div>
        @if(isset($flows['concierge_nose_notes']) && $flows['concierge_nose_notes'])
        <div class="field">
          <span class="inline-label">Notes:</span>
          <span class="inline-value">{{ $flows['concierge_nose_notes'] }}</span>
        </div>
        @endif
      </div>
    </div>

    {{-- 2. Eyes --}}
    <div class="field">
      <h3>2. Eyes</h3>
      <div class="indent">
        <div class="field">
          <span class="checkbox">{{ isset($flows['concierge_eyes']) && $flows['concierge_eyes'] === 'okay' ? '☑' : '☐' }}</span>
          <span>Okay</span>
          <span class="checkbox" style="margin-left: 10px;">{{ isset($flows['concierge_eyes']) && $flows['concierge_eyes'] === 'issue' ? '☑' : '☐' }}</span>
          <span>Issue</span>
          @if(isset($flows['concierge_eyes']) && $flows['concierge_eyes'] === 'issue')
            @if(isset($flows['concierge_eyes_redness']) && ($flows['concierge_eyes_redness'] === true || $flows['concierge_eyes_redness'] === 'true'))
              <span style="margin-left: 10px;">☑ Redness</span>
            @endif
            @if(isset($flows['concierge_eyes_cloudiness']) && ($flows['concierge_eyes_cloudiness'] === true || $flows['concierge_eyes_cloudiness'] === 'true'))
              <span style="margin-left: 10px;">☑ Cloudiness</span>
            @endif
            @if(isset($flows['concierge_eyes_discharge']) && ($flows['concierge_eyes_discharge'] === true || $flows['concierge_eyes_discharge'] === 'true'))
              <span style="margin-left: 10px;">☑ Unusual discharge</span>
            @endif
          @endif
        </div>
        @if(isset($flows['concierge_eyes_notes']) && $flows['concierge_eyes_notes'])
        <div class="field">
          <span class="inline-label">Notes:</span>
          <span class="inline-value">{{ $flows['concierge_eyes_notes'] }}</span>
        </div>
        @endif
      </div>
    </div>

    {{-- 3. Ears --}}
    <div class="field">
      <h3>3. Ears</h3>
      <div class="indent">
        <div class="field">
          <span class="checkbox">{{ isset($flows['concierge_ears']) && $flows['concierge_ears'] === 'okay' ? '☑' : '☐' }}</span>
          <span>Okay</span>
          <span class="checkbox" style="margin-left: 10px;">{{ isset($flows['concierge_ears']) && $flows['concierge_ears'] === 'issue' ? '☑' : '☐' }}</span>
          <span>Issue</span>
          @if(isset($flows['concierge_ears']) && $flows['concierge_ears'] === 'issue')
            @if(isset($flows['concierge_ears_odor']) && ($flows['concierge_ears_odor'] === true || $flows['concierge_ears_odor'] === 'true'))
              <span style="margin-left: 10px;">☑ Odor</span>
            @endif
            @if(isset($flows['concierge_ears_redness']) && ($flows['concierge_ears_redness'] === true || $flows['concierge_ears_redness'] === 'true'))
              <span style="margin-left: 10px;">☑ Redness</span>
            @endif
            @if(isset($flows['concierge_ears_swelling']) && ($flows['concierge_ears_swelling'] === true || $flows['concierge_ears_swelling'] === 'true'))
              <span style="margin-left: 10px;">☑ Swelling</span>
            @endif
            @if(isset($flows['concierge_ears_buildup']) && ($flows['concierge_ears_buildup'] === true || $flows['concierge_ears_buildup'] === 'true'))
              <span style="margin-left: 10px;">☑ Buildup</span>
            @endif
          @endif
        </div>
        @if(isset($flows['concierge_ears_notes']) && $flows['concierge_ears_notes'])
        <div class="field">
          <span class="inline-label">Notes:</span>
          <span class="inline-value">{{ $flows['concierge_ears_notes'] }}</span>
        </div>
        @endif
      </div>
    </div>

    {{-- 4. Mouth & Teeth --}}
    <div class="field">
      <h3>4. Mouth & Teeth</h3>
      <div class="indent">
        <div class="field">
          <span class="checkbox">{{ isset($flows['concierge_mouth']) && $flows['concierge_mouth'] === 'okay' ? '☑' : '☐' }}</span>
          <span>Okay</span>
          <span class="checkbox" style="margin-left: 10px;">{{ isset($flows['concierge_mouth']) && $flows['concierge_mouth'] === 'issue' ? '☑' : '☐' }}</span>
          <span>Issue</span>
          @if(isset($flows['concierge_mouth']) && $flows['concierge_mouth'] === 'issue')
            @if(isset($flows['concierge_mouth_tartar']) && ($flows['concierge_mouth_tartar'] === true || $flows['concierge_mouth_tartar'] === 'true'))
              <span style="margin-left: 10px;">☑ Tartar</span>
            @endif
            @if(isset($flows['concierge_mouth_broken_teeth']) && ($flows['concierge_mouth_broken_teeth'] === true || $flows['concierge_mouth_broken_teeth'] === 'true'))
              <span style="margin-left: 10px;">☑ Broken teeth</span>
            @endif
            @if(isset($flows['concierge_mouth_foul_breath']) && ($flows['concierge_mouth_foul_breath'] === true || $flows['concierge_mouth_foul_breath'] === 'true'))
              <span style="margin-left: 10px;">☑ Foul breath</span>
            @endif
          @endif
        </div>
        @if(isset($flows['concierge_mouth_notes']) && $flows['concierge_mouth_notes'])
        <div class="field">
          <span class="inline-label">Notes:</span>
          <span class="inline-value">{{ $flows['concierge_mouth_notes'] }}</span>
        </div>
        @endif
      </div>
    </div>

    {{-- 5. Body and Coat --}}
    <div class="field">
      <h3>5. Body and Coat</h3>
      <div class="indent">
        <div class="field">
          <span class="checkbox">{{ isset($flows['concierge_body']) && $flows['concierge_body'] === 'okay' ? '☑' : '☐' }}</span>
          <span>Okay</span>
          <span class="checkbox" style="margin-left: 10px;">{{ isset($flows['concierge_body']) && $flows['concierge_body'] === 'issue' ? '☑' : '☐' }}</span>
          <span>Issue</span>
          @if(isset($flows['concierge_body']) && $flows['concierge_body'] === 'issue')
            @if(isset($flows['concierge_body_lumps']) && ($flows['concierge_body_lumps'] === true || $flows['concierge_body_lumps'] === 'true'))
              <span style="margin-left: 10px;">☑ Lumps, wounds, or skin irritation</span>
            @endif
            @if(isset($flows['concierge_body_fleas']) && ($flows['concierge_body_fleas'] === true || $flows['concierge_body_fleas'] === 'true'))
              <span style="margin-left: 10px;">☑ Fleas or ticks</span>
            @endif
            @if(isset($flows['concierge_body_matted']) && ($flows['concierge_body_matted'] === true || $flows['concierge_body_matted'] === 'true'))
              <span style="margin-left: 10px;">☑ Matted fur or bald spots</span>
            @endif
          @endif
        </div>
        @if(isset($flows['concierge_body_notes']) && $flows['concierge_body_notes'])
        <div class="field">
          <span class="inline-label">Notes:</span>
          <span class="inline-value">{{ $flows['concierge_body_notes'] }}</span>
        </div>
        @endif
      </div>
    </div>

    {{-- 6. Paws and Nails --}}
    <div class="field">
      <h3>6. Paws and Nails</h3>
      <div class="indent">
        <div class="field">
          <span class="checkbox">{{ isset($flows['concierge_paws']) && $flows['concierge_paws'] === 'okay' ? '☑' : '☐' }}</span>
          <span>Okay</span>
          <span class="checkbox" style="margin-left: 10px;">{{ isset($flows['concierge_paws']) && $flows['concierge_paws'] === 'issue' ? '☑' : '☐' }}</span>
          <span>Issue</span>
          @if(isset($flows['concierge_paws']) && $flows['concierge_paws'] === 'issue')
            @if(isset($flows['concierge_paws_debris']) && ($flows['concierge_paws_debris'] === true || $flows['concierge_paws_debris'] === 'true'))
              <span style="margin-left: 10px;">☑ Debris between toes</span>
            @endif
            @if(isset($flows['concierge_paws_swelling']) && ($flows['concierge_paws_swelling'] === true || $flows['concierge_paws_swelling'] === 'true'))
              <span style="margin-left: 10px;">☑ Swelling</span>
            @endif
            @if(isset($flows['concierge_paws_injury']) && ($flows['concierge_paws_injury'] === true || $flows['concierge_paws_injury'] === 'true'))
              <span style="margin-left: 10px;">☑ Injury</span>
            @endif
            @if(isset($flows['concierge_paws_overgrown']) && ($flows['concierge_paws_overgrown'] === true || $flows['concierge_paws_overgrown'] === 'true'))
              <span style="margin-left: 10px;">☑ Overgrown or cracked nails</span>
            @endif
          @endif
        </div>
        @if(isset($flows['concierge_paws_notes']) && $flows['concierge_paws_notes'])
        <div class="field">
          <span class="inline-label">Notes:</span>
          <span class="inline-value">{{ $flows['concierge_paws_notes'] }}</span>
        </div>
        @endif
      </div>
    </div>

    {{-- 7. Abdomen --}}
    <div class="field">
      <h3>7. Abdomen</h3>
      <div class="indent">
        <div class="field">
          <span class="checkbox">{{ isset($flows['concierge_abdomen']) && $flows['concierge_abdomen'] === 'okay' ? '☑' : '☐' }}</span>
          <span>Okay</span>
          <span class="checkbox" style="margin-left: 10px;">{{ isset($flows['concierge_abdomen']) && $flows['concierge_abdomen'] === 'issue' ? '☑' : '☐' }}</span>
          <span>Issue</span>
          @if(isset($flows['concierge_abdomen']) && $flows['concierge_abdomen'] === 'issue')
            @if(isset($flows['concierge_abdomen_bloating']) && ($flows['concierge_abdomen_bloating'] === true || $flows['concierge_abdomen_bloating'] === 'true'))
              <span style="margin-left: 10px;">☑ Bloating</span>
            @endif
            @if(isset($flows['concierge_abdomen_tenderness']) && ($flows['concierge_abdomen_tenderness'] === true || $flows['concierge_abdomen_tenderness'] === 'true'))
              <span style="margin-left: 10px;">☑ Tenderness</span>
            @endif
            @if(isset($flows['concierge_abdomen_rashes']) && ($flows['concierge_abdomen_rashes'] === true || $flows['concierge_abdomen_rashes'] === 'true'))
              <span style="margin-left: 10px;">☑ Rashes or skin irritations</span>
            @endif
          @endif
        </div>
        @if(isset($flows['concierge_abdomen_notes']) && $flows['concierge_abdomen_notes'])
        <div class="field">
          <span class="inline-label">Notes:</span>
          <span class="inline-value">{{ $flows['concierge_abdomen_notes'] }}</span>
        </div>
        @endif
      </div>
    </div>

    {{-- 8. Rear and Tail --}}
    <div class="field">
      <h3>8. Rear and Tail</h3>
      <div class="indent">
        <div class="field">
          <span class="checkbox">{{ isset($flows['concierge_rear']) && $flows['concierge_rear'] === 'okay' ? '☑' : '☐' }}</span>
          <span>Okay</span>
          <span class="checkbox" style="margin-left: 10px;">{{ isset($flows['concierge_rear']) && $flows['concierge_rear'] === 'issue' ? '☑' : '☐' }}</span>
          <span>Issue</span>
          @if(isset($flows['concierge_rear']) && $flows['concierge_rear'] === 'issue')
            @if(isset($flows['concierge_rear_irritation']) && ($flows['concierge_rear_irritation'] === true || $flows['concierge_rear_irritation'] === 'true'))
              <span style="margin-left: 10px;">☑ Irritation</span>
            @endif
            @if(isset($flows['concierge_rear_swelling']) && ($flows['concierge_rear_swelling'] === true || $flows['concierge_rear_swelling'] === 'true'))
              <span style="margin-left: 10px;">☑ Swelling</span>
            @endif
          @endif
        </div>
        @if(isset($flows['concierge_rear_notes']) && $flows['concierge_rear_notes'])
        <div class="field">
          <span class="inline-label">Notes:</span>
          <span class="inline-value">{{ $flows['concierge_rear_notes'] }}</span>
        </div>
        @endif
      </div>
    </div>

    {{-- 9. Digestive --}}
    <div class="field">
      <h3>9. Digestive</h3>
      <div class="indent">
        <div class="field">
          <span class="checkbox">{{ isset($flows['concierge_digestive']) && $flows['concierge_digestive'] === 'okay' ? '☑' : '☐' }}</span>
          <span>Okay</span>
          <span class="checkbox" style="margin-left: 10px;">{{ isset($flows['concierge_digestive']) && $flows['concierge_digestive'] === 'issue' ? '☑' : '☐' }}</span>
          <span>Issue</span>
          @if(isset($flows['concierge_digestive']) && $flows['concierge_digestive'] === 'issue')
            @if(isset($flows['concierge_digestive_vomit']) && ($flows['concierge_digestive_vomit'] === true || $flows['concierge_digestive_vomit'] === 'true'))
              <span style="margin-left: 10px;">☑ Vomit</span>
            @endif
            @if(isset($flows['concierge_digestive_diarrhea']) && ($flows['concierge_digestive_diarrhea'] === true || $flows['concierge_digestive_diarrhea'] === 'true'))
              <span style="margin-left: 10px;">☑ Diarrhea</span>
            @endif
          @endif
        </div>
        @if(isset($flows['concierge_digestive_notes']) && $flows['concierge_digestive_notes'])
        <div class="field">
          <span class="inline-label">Notes:</span>
          <span class="inline-value">{{ $flows['concierge_digestive_notes'] }}</span>
        </div>
        @endif
      </div>
    </div>

    {{-- 10. Other --}}
    <div class="field">
      <h3>10. Other – Any issues with behavior or overall health of the pet.</h3>
      <div class="indent">
        <div class="field">
          <span class="checkbox">{{ isset($flows['concierge_other']) && $flows['concierge_other'] === 'okay' ? '☑' : '☐' }}</span>
          <span>Okay</span>
          <span class="checkbox" style="margin-left: 10px;">{{ isset($flows['concierge_other']) && $flows['concierge_other'] === 'issue' ? '☑' : '☐' }}</span>
          <span>Issue</span>
        </div>
        @if(isset($flows['concierge_other_notes']) && $flows['concierge_other_notes'])
        <div class="field">
          <span class="inline-label">Notes:</span>
          <span class="inline-value">{{ $flows['concierge_other_notes'] }}</span>
        </div>
        @endif
      </div>
    </div>
  </div>

  @if(isset($flows['nose_tail_treatment']) && $flows['nose_tail_treatment'])
  <div class="section">
    <h2>Treatment</h2>
    <div class="field">
      <span class="field-value">{{ $flows['nose_tail_treatment'] }}</span>
    </div>
  </div>
  @endif

  <div class="section">
    <h2>AM Feeding/Meds</h2>
    @if(isset($flows['am_meal_prep_time']) || isset($flows['am_meal_preparation']) || isset($flows['am_meal_foods']))
    <div class="field">
      <h3>1) Meal Prep</h3>
      <div class="indent">
        @if(isset($flows['am_meal_prep_time']) && $flows['am_meal_prep_time'])
        <div class="field">
          <span class="inline-label">Time:</span>
          <span class="inline-value">{{ $formatTimeOrDatetime($flows['am_meal_prep_time']) }}</span>
        </div>
        @endif
        @if(isset($flows['am_meal_preparation']) && $flows['am_meal_preparation'])
        <div class="field">
          <span class="inline-label">Meal preparation:</span>
          <span class="inline-value">
            @if($flows['am_meal_preparation'] === 'dry_food_only')
              Dry food only
            @elseif($flows['am_meal_preparation'] === 'dry_with_wet')
              Dry with wet food or additional enticements
            @elseif($flows['am_meal_preparation'] === 'wet_food_only')
              Wet food only
            @endif
          </span>
        </div>
        @endif
        @if(isset($flows['am_meal_foods']) && $flows['am_meal_foods'])
        <div class="field">
          <span class="inline-label">Foods:</span>
          <span class="inline-value">{{ $flows['am_meal_foods'] }}</span>
        </div>
        @endif
      </div>
    </div>
    @endif

    @if(isset($flows['am_med_prep_time']) || isset($flows['am_med_prep_notes']))
    <div class="field">
      <h3>2) Med Prep</h3>
      <div class="indent">
        @if(isset($flows['am_med_prep_time']) && $flows['am_med_prep_time'])
        <div class="field">
          <span class="inline-label">Time:</span>
          <span class="inline-value">{{ $formatTimeOrDatetime($flows['am_med_prep_time']) }}</span>
        </div>
        @endif
        @if(isset($flows['am_med_prep_notes']) && $flows['am_med_prep_notes'])
        <div class="field">
          <span class="inline-label">Medication preparation:</span>
          <span class="inline-value">{{ $flows['am_med_prep_notes'] }}</span>
        </div>
        @endif
      </div>
    </div>
    @endif

    @if(isset($flows['am_meal_dispense_time']) || isset($flows['am_meal_dispense_hand_feed']) || isset($flows['am_meal_dispense_food_aggressive']) || isset($flows['am_meal_dispense_quiet_spot']) || isset($flows['am_meal_dispense_must_eat']) || isset($flows['am_meal_dispense_not_eating']))
    <div class="field">
      <h3>3) Meal Dispense</h3>
      <div class="indent">
        @if(isset($flows['am_meal_dispense_time']) && $flows['am_meal_dispense_time'])
        <div class="field">
          <span class="inline-label">Time:</span>
          <span class="inline-value">{{ $formatTimeOrDatetime($flows['am_meal_dispense_time']) }}</span>
        </div>
        @endif
        @if((isset($flows['am_meal_dispense_hand_feed']) && ($flows['am_meal_dispense_hand_feed'] === true || $flows['am_meal_dispense_hand_feed'] === 'true')) || (isset($flows['am_meal_dispense_food_aggressive']) && ($flows['am_meal_dispense_food_aggressive'] === true || $flows['am_meal_dispense_food_aggressive'] === 'true')) || (isset($flows['am_meal_dispense_quiet_spot']) && ($flows['am_meal_dispense_quiet_spot'] === true || $flows['am_meal_dispense_quiet_spot'] === 'true')))
        <div class="field">
          <span class="inline-label">Special instructions for handlers:</span>
          <span class="inline-value">
            @if(isset($flows['am_meal_dispense_hand_feed']) && ($flows['am_meal_dispense_hand_feed'] === true || $flows['am_meal_dispense_hand_feed'] === 'true'))
              ☑ Hand feed
            @endif
            @if(isset($flows['am_meal_dispense_food_aggressive']) && ($flows['am_meal_dispense_food_aggressive'] === true || $flows['am_meal_dispense_food_aggressive'] === 'true'))
              ☑ Food aggressive
            @endif
            @if(isset($flows['am_meal_dispense_quiet_spot']) && ($flows['am_meal_dispense_quiet_spot'] === true || $flows['am_meal_dispense_quiet_spot'] === 'true'))
              ☑ Quiet spot
            @endif
          </span>
        </div>
        @endif
        @if(isset($flows['am_meal_dispense_must_eat']) && $flows['am_meal_dispense_must_eat'])
        <div class="field">
          <span class="inline-label">"Must eat" list (has missed two consecutive meals):</span>
          <span class="inline-value">{{ $flows['am_meal_dispense_must_eat'] }}</span>
        </div>
        @endif
        @if(isset($flows['am_meal_dispense_not_eating']) && ($flows['am_meal_dispense_not_eating'] === true || $flows['am_meal_dispense_not_eating'] === 'true'))
        <div class="field">
          <span>☑ Identify if this dog does not eat its meal</span>
        </div>
        @endif
      </div>
    </div>
    @endif

    @if(isset($flows['am_med_dispense_time']) || isset($flows['am_med_dispense_instructions']) || isset($flows['am_med_dispense_must_receive']))
    <div class="field">
      <h3>4) Med Dispense</h3>
      <div class="indent">
        @if(isset($flows['am_med_dispense_time']) && $flows['am_med_dispense_time'])
        <div class="field">
          <span class="inline-label">Time:</span>
          <span class="inline-value">{{ $formatTimeOrDatetime($flows['am_med_dispense_time']) }}</span>
        </div>
        @endif
        @if(isset($flows['am_med_dispense_instructions']) && $flows['am_med_dispense_instructions'])
        <div class="field">
          <span class="inline-label">Special instructions for handlers:</span>
          <span class="inline-value">{{ $flows['am_med_dispense_instructions'] }}</span>
        </div>
        @endif
        @if(isset($flows['am_med_dispense_must_receive']) && ($flows['am_med_dispense_must_receive'] === true || $flows['am_med_dispense_must_receive'] === 'true'))
        <div class="field">
          <span>☑ This dog must receive its medications, no exceptions</span>
        </div>
        @endif
      </div>
    </div>
    @endif
  </div>

  <div class="section">
    <h2>PM Feeding/Meds</h2>
    @if(isset($flows['pm_meal_prep_time']) || isset($flows['pm_meal_preparation']) || isset($flows['pm_meal_foods']))
    <div class="field">
      <h3>1) Meal Prep</h3>
      <div class="indent">
        @if(isset($flows['pm_meal_prep_time']) && $flows['pm_meal_prep_time'])
        <div class="field">
          <span class="inline-label">Time:</span>
          <span class="inline-value">{{ $formatTimeOrDatetime($flows['pm_meal_prep_time']) }}</span>
        </div>
        @endif
        @if(isset($flows['pm_meal_preparation']) && $flows['pm_meal_preparation'])
        <div class="field">
          <span class="inline-label">Meal preparation:</span>
          <span class="inline-value">
            @if($flows['pm_meal_preparation'] === 'dry_food_only')
              Dry food only
            @elseif($flows['pm_meal_preparation'] === 'dry_with_wet')
              Dry with wet food or additional enticements
            @elseif($flows['pm_meal_preparation'] === 'wet_food_only')
              Wet food only
            @endif
          </span>
        </div>
        @endif
        @if(isset($flows['pm_meal_foods']) && $flows['pm_meal_foods'])
        <div class="field">
          <span class="inline-label">Foods:</span>
          <span class="inline-value">{{ $flows['pm_meal_foods'] }}</span>
        </div>
        @endif
      </div>
    </div>
    @endif

    @if(isset($flows['pm_med_prep_time']) || isset($flows['pm_med_prep_notes']))
    <div class="field">
      <h3>2) Med Prep</h3>
      <div class="indent">
        @if(isset($flows['pm_med_prep_time']) && $flows['pm_med_prep_time'])
        <div class="field">
          <span class="inline-label">Time:</span>
          <span class="inline-value">{{ $formatTimeOrDatetime($flows['pm_med_prep_time']) }}</span>
        </div>
        @endif
        @if(isset($flows['pm_med_prep_notes']) && $flows['pm_med_prep_notes'])
        <div class="field">
          <span class="inline-label">Medication preparation:</span>
          <span class="inline-value">{{ $flows['pm_med_prep_notes'] }}</span>
        </div>
        @endif
      </div>
    </div>
    @endif

    @if(isset($flows['pm_meal_dispense_time']) || isset($flows['pm_meal_dispense_hand_feed']) || isset($flows['pm_meal_dispense_food_aggressive']) || isset($flows['pm_meal_dispense_quiet_spot']) || isset($flows['pm_meal_dispense_must_eat']) || isset($flows['pm_meal_dispense_not_eating']))
    <div class="field">
      <h3>3) Meal Dispense</h3>
      <div class="indent">
        @if(isset($flows['pm_meal_dispense_time']) && $flows['pm_meal_dispense_time'])
        <div class="field">
          <span class="inline-label">Time:</span>
          <span class="inline-value">{{ $formatTimeOrDatetime($flows['pm_meal_dispense_time']) }}</span>
        </div>
        @endif
        @if((isset($flows['pm_meal_dispense_hand_feed']) && ($flows['pm_meal_dispense_hand_feed'] === true || $flows['pm_meal_dispense_hand_feed'] === 'true')) || (isset($flows['pm_meal_dispense_food_aggressive']) && ($flows['pm_meal_dispense_food_aggressive'] === true || $flows['pm_meal_dispense_food_aggressive'] === 'true')) || (isset($flows['pm_meal_dispense_quiet_spot']) && ($flows['pm_meal_dispense_quiet_spot'] === true || $flows['pm_meal_dispense_quiet_spot'] === 'true')))
        <div class="field">
          <span class="inline-label">Special instructions for handlers:</span>
          <span class="inline-value">
            @if(isset($flows['pm_meal_dispense_hand_feed']) && ($flows['pm_meal_dispense_hand_feed'] === true || $flows['pm_meal_dispense_hand_feed'] === 'true'))
              ☑ Hand feed
            @endif
            @if(isset($flows['pm_meal_dispense_food_aggressive']) && ($flows['pm_meal_dispense_food_aggressive'] === true || $flows['pm_meal_dispense_food_aggressive'] === 'true'))
              ☑ Food aggressive
            @endif
            @if(isset($flows['pm_meal_dispense_quiet_spot']) && ($flows['pm_meal_dispense_quiet_spot'] === true || $flows['pm_meal_dispense_quiet_spot'] === 'true'))
              ☑ Quiet spot
            @endif
          </span>
        </div>
        @endif
        @if(isset($flows['pm_meal_dispense_must_eat']) && $flows['pm_meal_dispense_must_eat'])
        <div class="field">
          <span class="inline-label">"Must eat" list (has missed two consecutive meals):</span>
          <span class="inline-value">{{ $flows['pm_meal_dispense_must_eat'] }}</span>
        </div>
        @endif
        @if(isset($flows['pm_meal_dispense_not_eating']) && ($flows['pm_meal_dispense_not_eating'] === true || $flows['pm_meal_dispense_not_eating'] === 'true'))
        <div class="field">
          <span>☑ Identify if the dog does not eat its meal</span>
        </div>
        @endif
      </div>
    </div>
    @endif

    @if(isset($flows['pm_med_dispense_time']) || isset($flows['pm_med_dispense_instructions']) || isset($flows['pm_med_dispense_must_receive']))
    <div class="field">
      <h3>4) Med Dispense</h3>
      <div class="indent">
        @if(isset($flows['pm_med_dispense_time']) && $flows['pm_med_dispense_time'])
        <div class="field">
          <span class="inline-label">Time:</span>
          <span class="inline-value">{{ $formatTimeOrDatetime($flows['pm_med_dispense_time']) }}</span>
        </div>
        @endif
        @if(isset($flows['pm_med_dispense_instructions']) && $flows['pm_med_dispense_instructions'])
        <div class="field">
          <span class="inline-label">Special instructions for handlers:</span>
          <span class="inline-value">{{ $flows['pm_med_dispense_instructions'] }}</span>
        </div>
        @endif
        @if(isset($flows['pm_med_dispense_must_receive']) && ($flows['pm_med_dispense_must_receive'] === true || $flows['pm_med_dispense_must_receive'] === 'true'))
        <div class="field">
          <span>☑ This dog must receive its medications, no exceptions</span>
        </div>
        @endif
      </div>
    </div>
    @endif
  </div>

  @if(isset($flows['rest_1200_time']) || (isset($flows['treatment_issues']) && is_array($flows['treatment_issues']) && count($flows['treatment_issues']) > 0))
  <div class="section">
    <h2>Rest/treatment/lunch</h2>
    @if(isset($flows['rest_1200_time']) && $flows['rest_1200_time'])
    <div class="field">
      <span class="inline-label">Time:</span>
      <span class="inline-value">{{ $formatTimeOrDatetime($flows['rest_1200_time']) }}</span>
    </div>
    @endif
    @if(isset($flows['treatment_issues']) && is_array($flows['treatment_issues']) && count($flows['treatment_issues']) > 0)
    <div class="field">
      <h3>Issues</h3>
      @foreach($flows['treatment_issues'] as $index => $issue)
      <div class="indent" style="margin-bottom: 10px;">
        <div class="field">
          <strong>Issue #{{ $index + 1 }}</strong>
        </div>
        @if(isset($issue['issue']))
        <div class="field">
          <span class="inline-label">Issue:</span>
          <span class="inline-value">{{ ucfirst(str_replace('_', ' ', $issue['issue'])) }}</span>
        </div>
        @endif
        @if(isset($issue['inhouse']) && $issue['inhouse'])
        <div class="field">
          <span class="inline-label">In-house treatment:</span>
          <span class="inline-value">{{ $issue['inhouse'] }}</span>
        </div>
        @endif
        @if(isset($issue['vet']) && $issue['vet'])
        <div class="field">
          <span class="inline-label">Vet treatment:</span>
          <span class="inline-value">{{ $issue['vet'] }}</span>
        </div>
        @endif
      </div>
      @endforeach
    </div>
    @endif
  </div>
  @endif

</body>
</html>

