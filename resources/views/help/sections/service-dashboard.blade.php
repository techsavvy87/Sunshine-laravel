<div class="space-y-4 text-sm text-base-content/70">
  <h4 class="text-xl font-semibold mb-4">Service Dashboard</h4>
  <p>Each service type has its own dedicated dashboard accessible from the "Service Dashboard" menu in the sidebar. This provides a focused view for managing appointments specific to that service.</p>

  <div>
    <h5 class="font-semibold mb-2">Accessing Service Dashboard</h5>
    <p>Click "Service Dashboard" in the sidebar, then select the service you want to view. The dashboard displays all appointments for that service in a Kanban board format.</p>
  </div>

  <div>
    <h5 class="font-semibold mb-2">Kanban Board View</h5>
    <p>The default view shows appointments in four status columns. Click any appointment card to view details and manage it.</p>
    <ul class="list-disc list-inside space-y-1 ml-4 mt-2">
      <li><strong>Scheduled:</strong> Shows pet photo, name, customer name, assigned staff, and pickup time. Includes a "+" button to add new appointments. Pet rating indicators (green/yellow/red stars) are displayed.</li>
      <li><strong>In Progress / On Property:</strong> For grooming and training: "In Progress" shows when the service started, assigned staff, and pet/customer information. For boarding and daycare: "On Property" indicates the pet is at the facility.</li>
      <li><strong>Completed:</strong> Shows completion time. Ready for checkout and payment processing.</li>
      <li><strong>Issue:</strong> Marked with a warning icon. Requires attention and follow-up, usually related to incident reports.</li>
    </ul>
    <p class="mt-2">Use the "View List" button to switch to a detailed table format organized by status.</p>
  </div>

  <div>
    <h5 class="font-semibold mb-2">Search and Filter</h5>
    <ul class="list-disc list-inside space-y-1 ml-4">
      <li><strong>Customer/Pet Search:</strong> Search by customer name, email, or pet name</li>
      <li><strong>Staff Filter:</strong> Filter by assigned staff member or select "All Staffs"</li>
      <li><strong>Incident Reports:</strong> Direct link to view and manage incident reports</li>
    </ul>
  </div>

  <div>
    <h5 class="font-semibold mb-2">Appointment Statuses and Workflow</h5>
    <p>Appointments progress through several statuses. Click any appointment card to view details and manage it.</p>

    <p class="mt-3 mb-1"><strong>1. Scheduled</strong></p>
    <p>When a customer and pet arrive, record the check-in time, assign staff, set estimated price and pickup time, and add check-in notes. For boarding services, also collect pickup date/time, trip location, alternate contacts, pet items, food/medication details, and assignment location. Click "Confirm Check-In" to move to "In Progress" (or "On Property" for boarding/daycare).</p>

    <p class="mt-3 mb-1"><strong>2. In Progress / On Property</strong></p>
    <p>The status automatically changes when check-in is confirmed. Staff can record process information specific to the service type. For grooming, track reactions to nail trimming, ear cleaning, shampooing, etc. For training, record progress and commands practiced. For daycare, monitor activities and behavior. Add process notes as needed to document the service.</p>

    <p class="mt-3 mb-1"><strong>3. Completed</strong></p>
    <p>When the service finishes, record the completion date, add service notes, and upload photos (optional). Assign a behavior rating: <strong>Green</strong> (well-behaved), <strong>Yellow</strong> (some concerns with notes), or <strong>Purple</strong> (significant issues with notes). For training services, use a 5-star rating system for obedience commands. Click "Confirm Completed" to finalize.</p>

    <p class="mt-3 mb-1"><strong>4. Checkout</strong></p>
    <p>The system records checkout time automatically. Add checkout notes and upload final photos (optional). An invoice is automatically created with all service charges. Process payment (cash, card, etc.) and update invoice status to "paid". After successful payment, the status changes to "Finished".</p>

    <p class="mt-3 mb-1"><strong>5. Finished</strong></p>
    <p>Appointments are marked "Finished" when checkout and payment are complete. For packages, all services must be completed. For group classes, the session must be finished. Finished appointments are archived and viewable in the Archives section.</p>

    <p class="mt-3 mb-1"><strong>6. Issue</strong></p>
    <p>Appointments are marked "Issue" when an incident report is created. This indicates a problem occurred and requires immediate attention. Review and resolve these appointments before finalizing.</p>
  </div>

  <div>
    <h5 class="font-semibold mb-2">Incident Reports</h5>
    <p>Click the "Incident Reports" button to document problems, injuries, or concerns that occur during service delivery.</p>
    <p class="mt-2">When creating a report, you'll need to: select involved pets and staff, describe the incident in detail, upload photos if available, specify injury type and location, indicate treatment needs and emergency status, record owner contact and conversation notes, document treatment details (in-house or vet), and add a conclusion summarizing the incident.</p>
    <p class="mt-2">Related appointments are automatically marked as "Issue" status when an incident report is created.</p>
  </div>

  <div>
    <h5 class="font-semibold mb-2">Notes and Documentation</h5>
    <p>Record notes throughout the appointment lifecycle at different stages:</p>
    <ul class="list-disc list-inside space-y-1 ml-4 mt-1">
      <li><strong>Check-In Notes:</strong> Pet condition, special instructions, customer requests</li>
      <li><strong>Process Notes:</strong> Service delivery tracking and observations</li>
      <li><strong>Service Notes:</strong> Observations and service details</li>
      <li><strong>Checkout Notes:</strong> Customer feedback and final observations</li>
    </ul>
    <p class="mt-2">All notes are saved and viewable in appointment details and archived records.</p>
  </div>

  <div>
    <h5 class="font-semibold mb-2">Ratings</h5>
    <p>Pet behavior ratings are assigned during service completion:</p>
    <ul class="list-disc list-inside space-y-1 ml-4 mt-1">
      <li><strong>Green:</strong> Well-behaved and cooperative</li>
      <li><strong>Yellow:</strong> Some concerns (requires detail notes)</li>
      <li><strong>Purple:</strong> Significant issues (requires detail notes)</li>
    </ul>
    <p class="mt-2">For training services, use a 5-star rating system for basic obedience commands (sit, stay, come, down, heel). Ratings appear as colored stars on appointment cards. Rating history from previous appointments is shown when completing new appointments.</p>
  </div>

  <div>
    <h5 class="font-semibold mb-2">Invoices and Payment</h5>
    <p>An invoice is automatically created at checkout with service charges, additional services, taxes/fees, and total amount.</p>
    <p class="mt-2">Process payment (cash, card, etc.), record the transaction, and update invoice status to "paid". Invoices can be viewed, printed, or downloaded from the appointment detail page.</p>
    <p class="mt-2">Once payment is processed and the invoice is marked as paid, the appointment status changes to "Finished".</p>
  </div>

  <div>
    <h5 class="font-semibold mb-2">Special Handling</h5>
    <p class="mb-1"><strong>Group Classes:</strong> Only the next upcoming appointment per pet shows in "Scheduled". Additional sessions appear after completion.</p>
    <p class="mt-2 mb-1"><strong>Package Appointments:</strong> Each service in the package is tracked separately. The appointment is marked "Finished" when all services are complete.</p>
    <p class="mt-2 mb-1"><strong>Boarding Services:</strong> Extended check-in forms collect detailed stay information including feeding schedules, medications, and location assignment.</p>
  </div>

  <div>
    <h5 class="font-semibold mb-2">Best Practices</h5>
    <ul class="list-disc list-inside space-y-1 ml-4">
      <li>Check dashboards regularly to monitor workflow</li>
      <li>Complete check-in information thoroughly</li>
      <li>Record process notes during service delivery</li>
      <li>Assign accurate behavior ratings</li>
      <li>Add detailed notes at each stage</li>
      <li>Upload photos to document service quality</li>
      <li>Create incident reports immediately when issues occur</li>
      <li>Process payments promptly after completion</li>
      <li>Review and resolve "Issue" status appointments quickly</li>
      <li>Use search and filter to find specific appointments</li>
    </ul>
  </div>

  <div class="mt-8 pt-6 border-t border-base-content/10 video-container">
    <video controls class="rounded-lg shadow-lg help-video" preload="metadata">
      <source src="{{ asset('videos/help/service-dashboard.mp4') }}" type="video/mp4">
      Your browser does not support the video tag.
    </video>
  </div>
</div>