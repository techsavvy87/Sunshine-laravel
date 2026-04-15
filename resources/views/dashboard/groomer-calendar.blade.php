@extends('layouts.main')
@section('title', 'Appointments')

@section('page-css')
<style>
.fc .fc-button {
    padding: 0.25rem 0.6rem;
    font-size: 0.875rem;
}

.fc .fc-button .fc-icon {
    font-size: 1rem;
    line-height: 0.8;
}

.fc .fc-scrollgrid-section-sticky>* {
    background-color: var(--bc-base-100);
}

#calendar .fc-groomer-event-pet {
    font-weight: 600;
    line-height: 1.1;
}

#calendar .fc-groomer-event-wrap {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 0;
    flex-grow: 1;
}

#calendar .fc-groomer-event-avatar {
    width: 50px;
    height: 50px;
    border-radius: 9999px;
    object-fit: cover;
    flex-shrink: 0;
    background: #e5e7eb;
}

#calendar .fc-groomer-event-body {
    min-width: 0;
}

#calendar .fc-groomer-event-time {
    margin-top: 0.15rem;
    font-size: 0.75rem;
    opacity: 0.85;
}

#calendar .fc-groomer-event-services {
    margin-top: 0.15rem;
    font-size: 0.75rem;
    white-space: normal;
    line-height: 1.2;
}

#calendar .fc-datagrid-cell-main {
    font-weight: 600;
}

#calendar .fc-groomer-label {
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

#calendar .fc-groomer-label-avatar {
    width: 50px;
    height: 50px;
    border-radius: 9999px;
    object-fit: cover;
    flex-shrink: 0;
    background: #e5e7eb;
}

#calendar .fc-groomer-label-name {
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#calendar .fc-event {
    border-radius: 0.5rem;
}

.fc .fc-toolbar.fc-header-toolbar div:last-child {
    visibility: hidden;
}

.fc-timegrid-event .fc-event-main {
    display: flex;
    align-items: center;
    padding-left: 0.25rem;
}

.fc-timegrid-event .fc-event-main {
    cursor: pointer;
}
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
    <div class="inline-flex items-center gap-3">
        <h3 class="text-lg font-medium">Groomer Calendar</h3>
        <a class="btn btn-primary btn-sm max-sm:btn-square w-36" href="{{ route('service-dashboard', $id) }}  ">
            <span class="iconify lucide--calendar-days size-4"></span>
            <span class="hidden sm:inline">View Step</span>
        </a>
    </div>
    <div class="breadcrumbs hidden p-0 text-sm sm:inline">
        <ul>
            <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
            <li>Groomer Calendar</li>
        </ul>
    </div>
</div>
<div class="mt-3">
    <div class="card bg-base-100 shadow mt-3">
        <div class="card-body p-4">
            <form method="GET" class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center"
                action="{{ route('groomer-calendar', $id) }}">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                    <label class="text-sm font-medium" for="groomer_id">Groomer</label>
                    <div class="flex items-center gap-2">
                        <select id="groomer_id" name="groomer_id"
                            class="select select-bordered select-sm w-auto min-w-[200px]" onchange="this.form.submit()">
                            <option value="">All groomers</option>
                            @foreach($groomers as $groomer)
                            @php
                            $firstName = trim((string) optional($groomer->profile)->first_name);
                            $displayName = $firstName !== '' ? $firstName : ($groomer->name ?: $groomer->email);
                            @endphp
                            <option value="{{ $groomer->id }}"
                                {{ (string) $groomerId === (string) $groomer->id ? 'selected' : '' }}>{{ $displayName }}
                            </option>
                            @endforeach
                        </select>

                        <label class="text-sm font-medium" for="date">Date</label>
                        <input id="date" name="date" type="date" value="{{ $date }}"
                            class="input input-bordered input-sm" onchange="this.form.submit()">

                        <a href="{{ route('groomer-calendar', $id) }}" class="btn btn-ghost btn-sm"
                            {{ request('groomer_id') || request('date') ? '' : 'disabled' }}>Reset</a>
                    </div>
                </div>
            </form>

            <div id="calendar"></div>
        </div>
    </div>
</div>
@endsection

@section('page-js')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.19/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var dataEndpoint = @json(route('groomer-calendar-data', ['id' => $id]));
    var fallbackAvatarUrl = @json(asset('images/no_image.jpg'));
    var selectedGroomerId = @json($groomerId);
    var selectedDate = @json($date);

    if (!window.FullCalendar || !window.FullCalendar.Calendar) {
        calendarEl.innerHTML = '<div class="alert alert-error">Calendar library failed to load.</div>';
        return;
    }

    var calendar;

    try {
        calendar = new FullCalendar.Calendar(calendarEl, {
            schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
            initialView: 'resourceTimeGridDay',
            initialDate: selectedDate || undefined,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            resourceAreaHeaderContent: 'Groomer',
            resourceAreaWidth: '220px',
            resourceLabelContent: function(arg) {
                var wrap = document.createElement('div');
                wrap.className = 'fc-groomer-label';

                var imgUrl = arg.resource.extendedProps.img_url || '';
                var avatar = document.createElement('img');
                avatar.className = 'fc-groomer-label-avatar';
                avatar.src = imgUrl || fallbackAvatarUrl;
                avatar.alt = 'Groomer';
                avatar.loading = 'lazy';
                avatar.onerror = function() {
                    this.onerror = null;
                    this.src = fallbackAvatarUrl;
                };
                wrap.appendChild(avatar);

                var name = document.createElement('span');
                name.className = 'fc-groomer-label-name';
                name.textContent = arg.resource.title || '';
                wrap.appendChild(name);

                return {
                    domNodes: [wrap]
                };
            },
            resources: [],
            events: [],
            nowIndicator: true,
            allDaySlot: false,
            slotMinTime: '08:00:00',
            slotMaxTime: '19:00:00',
            slotDuration: '00:30:00',
            slotLabelInterval: '01:00',
            eventMinHeight: 34,
            height: 'auto',
            datesSet: function(info) {
                loadCalendarData(info.startStr, info.endStr);
            },
            eventContent: function(arg) {
                var wrapper = document.createElement('div');
                wrapper.className = 'fc-groomer-event-wrap';
                var url = `/appointment/edit/${arg.event.id}`;
                wrapper.onclick = function() {
                    window.location.href = url;
                };

                var imgUrl = arg.event.extendedProps.img_url || '';
                var avatar = document.createElement('img');
                avatar.className = 'fc-groomer-event-avatar';
                avatar.src = imgUrl || fallbackAvatarUrl;
                avatar.alt = 'Pet';
                avatar.loading = 'lazy';
                avatar.onerror = function() {
                    this.onerror = null;
                    this.src = fallbackAvatarUrl;
                };
                wrapper.appendChild(avatar);

                var body = document.createElement('div');
                body.className = 'fc-groomer-event-body';

                var pet = document.createElement('div');
                pet.className = 'fc-groomer-event-pet';
                pet.textContent = arg.event.extendedProps.pet_name || arg.event.title ||
                    'Pet Booking';

                var time = document.createElement('div');
                time.className = 'fc-groomer-event-time';
                time.textContent = arg.timeText || '';

                var additionalServices = Array.isArray(arg.event.extendedProps
                        .additional_services) ?
                    arg.event.extendedProps.additional_services : [];

                if (additionalServices.length) {
                    var services = document.createElement('div');
                    services.className = 'fc-groomer-event-services';
                    services.textContent = additionalServices.join(', ');
                }

                body.appendChild(pet);
                if (additionalServices.length) {
                    body.appendChild(services);
                }
                body.appendChild(time);
                wrapper.appendChild(body);

                return {
                    domNodes: [wrapper]
                };
            }
        });
    } catch (e) {
        console.error(e);
        calendarEl.innerHTML =
            '<div class="alert alert-error">Scheduler view failed to initialize. Please ensure Scheduler assets are accessible.</div>';
        return;
    }

    function loadCalendarData(startStr, endStr) {
        var url = new URL(dataEndpoint, window.location.origin);
        url.searchParams.set('start', startStr);
        url.searchParams.set('end', endStr);

        if (selectedGroomerId) {
            url.searchParams.set('groomer_id', selectedGroomerId);
        }

        if (selectedDate) {
            url.searchParams.set('date', selectedDate);
        }

        fetch(url.toString(), {
                headers: {
                    Accept: 'application/json'
                }
            })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Failed to load groomer calendar data.');
                }
                return response.json();
            })
            .then(function(payload) {
                var resources = Array.isArray(payload.resources) ? payload.resources : [];
                var events = Array.isArray(payload.events) ? payload.events : [];
                console.log('Payload==', payload);
                console.log('Parsed resources:', resources);
                console.log('Parsed events:', events);

                calendar.batchRendering(function() {
                    calendar.getResources().forEach(function(resource) {
                        resource.remove();
                    });

                    resources.forEach(function(resource) {
                        calendar.addResource(resource);
                    });

                    calendar.removeAllEvents();
                    events.forEach(function(event) {
                        calendar.addEvent(event);
                    });
                });

                console.log('Rendered calendar counts:', {
                    renderedResources: calendar.getResources(),
                    renderedEvents: calendar.getEvents()
                });
            })
            .catch(function(error) {
                console.error(error);
            });
    }

    calendar.render();
});
</script>
@endsection