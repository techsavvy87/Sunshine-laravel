<div class="space-y-4 text-sm text-base-content/70">
  <h4 class="text-xl font-semibold mb-4">System Settings</h4>

  <div class="help-subsection">
    <h4 class="help-subsection-title">Permissions</h4>
    <p>Permissions are the building blocks of access control in PawPrints. Each permission represents a specific feature or area of the system that can be controlled. Permissions are assigned to roles, and roles are assigned to users, creating a flexible access control system.</p>
    <p class="mt-2">Each permission can have four types of access: <strong>Create</strong> (can add new records), <strong>Read</strong> (can view records), <strong>Update</strong> (can edit records), and <strong>Delete</strong> (can remove records).</p>

    <p class="mt-3 mb-2"><strong>Available Permissions in the System:</strong></p>
    <div class="space-y-3 mt-3">
      <div><h6 class="font-semibold mb-1">1. Owner Records</h6><p>Controls access to customer/owner information management.</p></div>
      <div><h6 class="font-semibold mb-1">2. Pet Records</h6><p>Controls access to pet profile management.</p></div>
      <div><h6 class="font-semibold mb-1">3. Appointments</h6><p>Controls access to appointment scheduling and management.</p></div>
      <div><h6 class="font-semibold mb-1">4. Inventory</h6><p>Controls access to inventory management including categories and items.</p></div>
      <div><h6 class="font-semibold mb-1">5. Employee Records</h6><p>Controls access to staff/employee user account management.</p></div>
      <div><h6 class="font-semibold mb-1">6. Roles and Permissions</h6><p>Controls access to the role-based access control system.</p></div>
      <div><h6 class="font-semibold mb-1">7. Holidays</h6><p>Controls access to holiday and special date management.</p></div>
      <div><h6 class="font-semibold mb-1">8. Weight Ranges</h6><p>Controls access to pet weight range definitions.</p></div>
      <div><h6 class="font-semibold mb-1">9. Capacity</h6><p>Controls access to service capacity management.</p></div>
      <div><h6 class="font-semibold mb-1">10. Credit Types</h6><p>Controls access to credit type definitions.</p></div>
      <div><h6 class="font-semibold mb-1">11. Service Categories</h6><p>Controls access to service category management.</p></div>
      <div><h6 class="font-semibold mb-1">12. Services</h6><p>Controls access to service definition and management.</p></div>
      <div><h6 class="font-semibold mb-1">13. Time Slots</h6><p>Controls access to time slot generation and management.</p></div>
      <div><h6 class="font-semibold mb-1">14. Accept Payment</h6><p>Controls the ability to process payments and record payment transactions.</p></div>
      <div><h6 class="font-semibold mb-1">15. Send/Receive Messages</h6><p>Controls access to messaging and communication features.</p></div>
      <div><h6 class="font-semibold mb-1">16. Boardings</h6><p>Controls access to boarding service management and reports.</p></div>
      <div><h6 class="font-semibold mb-1">17. Daycares</h6><p>Controls access to daycare service management and reports.</p></div>
      <div><h6 class="font-semibold mb-1">18. Groomings</h6><p>Controls access to grooming service management and reports.</p></div>
      <div><h6 class="font-semibold mb-1">19. Private Trainings</h6><p>Controls access to private training service management and reports.</p></div>
      <div><h6 class="font-semibold mb-1">20. Group Classes</h6><p>Controls access to group class management and reports.</p></div>
      <div><h6 class="font-semibold mb-1">21. A La Carte</h6><p>Controls access to ala carte service management and reports.</p></div>
      <div><h6 class="font-semibold mb-1">22. Packages</h6><p>Controls access to package service management and reports.</p></div>
    </div>
  </div>

  <div class="help-subsection">
    <h4 class="help-subsection-title">Roles</h4>
    <p>Roles are collections of permissions that define what users can do in the system. Instead of assigning individual permissions to each user, you create roles (like "Manager", "Staff", "Receptionist") and assign permissions to those roles. Then you assign roles to users, making access control much more manageable.</p>

    <p class="mt-2"><strong>Understanding Roles:</strong></p>
    <ul class="list-disc list-inside space-y-1 ml-4">
      <li>Roles group multiple permissions together</li>
      <li>Each role can have different combinations of permissions</li>
      <li>Users are assigned one or more roles</li>
      <li>Users inherit all permissions from their assigned roles</li>
    </ul>

    <p class="mt-3 mb-2"><strong>How to Create a Role:</strong></p>
    <ol class="list-decimal list-inside space-y-1 ml-4">
      <li>Navigate to System Settings → Roles in the sidebar menu</li>
      <li>Click the "Add" button in the top right corner</li>
      <li>Enter a role title (e.g., "Manager", "Groomer", "Receptionist") - must be unique</li>
      <li>Add a description (optional) explaining the role's purpose</li>
      <li>Click "Save" to create the role</li>
      <li>The new role will appear in the list with no permissions assigned yet</li>
    </ol>

    <p class="mt-3 mb-2"><strong>How to Assign Permissions to a Role:</strong></p>
    <ol class="list-decimal list-inside space-y-1 ml-4">
      <li>Find the role in the roles list and click on it to expand the card</li>
      <li>Click the "+ Add Permission To This Role" button above the permissions table</li>
      <li>Select a permission from the dropdown (e.g., "Owner Records", "Appointments", "Services")</li>
      <li>Check the boxes for the actions you want to allow: Create, Read, Update, Delete</li>
      <li>Click "Save" to add the permission to the role</li>
      <li>Repeat steps 2-5 to add more permissions to the role</li>
    </ol>
  </div>

  <div class="help-subsection">
    <h4 class="help-subsection-title">Users</h4>
    <p>Manage staff members and their access to the system. Users are employees who can log into the system and perform tasks based on their assigned roles and permissions.</p>

    <p class="mt-3 mb-2"><strong>How to Create a User:</strong></p>
    <ol class="list-decimal list-inside space-y-1 ml-4">
      <li>Navigate to System Settings → Users</li>
      <li>Click the "New" or "Add" button</li>
      <li>Fill in Account Information: username, email, password, confirm password</li>
      <li>Fill in Basic Information (First Name, Last Name, Phone Number, etc.)</li>
      <li>Upload a profile picture (optional)</li>
      <li>Click "Save" to create the user</li>
    </ol>

    <p class="mt-3 mb-2"><strong>Managing User Details:</strong></p>
    <p class="font-semibold mb-1">Email Verified:</p>
    <p>This toggle indicates whether the user's email address has been verified. When enabled, the system marks the email as verified. This is useful for security and communication purposes.</p>

    <p class="mt-2 font-semibold mb-1">Is Active:</p>
    <p>This toggle controls whether the user account is active and can log into the system. When enabled, the user can log in. When disabled, the user cannot log in but all data is preserved.</p>

    <p class="mt-3 mb-2"><strong>How to Assign Roles to a User:</strong></p>
    <ol class="list-decimal list-inside space-y-1 ml-4">
      <li>When creating or editing a user, find the "Assign Roles" section</li>
      <li>You'll see a dropdown/select field that allows multiple selections</li>
      <li>Select one or more roles for the user</li>
      <li>The user will inherit all permissions from all assigned roles</li>
      <li>Click "Save" or "Update" to save the role assignments</li>
    </ol>
  </div>

  <div class="help-subsection">
    <h4 class="help-subsection-title">Holidays</h4>
    <p>Set up holidays and special dates that affect pricing and availability. You can restrict bookings on holidays and set service-specific capacity limits.</p>
    <p><strong>How to Use:</strong></p>
    <ul class="list-disc list-inside space-y-1 ml-4">
      <li>Create a new holiday with name, date, and percent increase</li>
      <li>Enable "Restrict Bookings" to prevent time slot generation on that date</li>
      <li>Set maximum values for specific services (excluding boarding, ala carte, group class, and package services)</li>
      <li>Edit or delete holidays as needed</li>
    </ul>
  </div>

  <div class="help-subsection">
    <h4 class="help-subsection-title">Credit Types</h4>
    <p>Define different types of credits that can be applied to customer accounts or packages.</p>
    <p><strong>How to Use:</strong> Create credit types with names and descriptions. These can be used when managing customer packages.</p>
  </div>

  <div class="help-subsection">
    <h4 class="help-subsection-title">Weight Ranges</h4>
    <p>Define weight ranges for pets (e.g., small, medium, large, xlarge). These are used for pricing and service categorization.</p>
    <p><strong>How to Use:</strong> Create weight ranges with minimum and maximum weight values.</p>
  </div>

  <div class="help-subsection">
    <h4 class="help-subsection-title">Capacities</h4>
    <p>Set the capacity (maximum number of bookings) for each service at different times.</p>
    <p><strong>How to Use:</strong> Define capacity values for services. This controls how many appointments can be scheduled for a service at the same time.</p>
  </div>

  <div class="mt-8 pt-6 border-t border-base-content/10 video-container">
    <video controls class="rounded-lg shadow-lg help-video" preload="metadata">
      <source src="{{ asset('videos/help/system-settings.mp4') }}" type="video/mp4">
      Your browser does not support the video tag.
    </video>
  </div>
</div>