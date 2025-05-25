<section>
    <h5 class="mb-4 text-center"><i class="fas fa-user-edit"></i> Edit Profile</h5>
    <form id="editProfileForm" method="POST" action="/NEW-PM-JI-RESERVIFY/pages/customer/profile/update_profile.php">
        <div class="form-group">
            <label for="edit_first_name">First Name</label>
            <input type="text" class="form-control" id="edit_first_name" name="first_name"
                value="<?= htmlspecialchars($user['first_name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="edit_middle_name">Middle Name</label>
            <input type="text" class="form-control" id="edit_middle_name" name="middle_name"
                value="<?= htmlspecialchars($user['middle_name']) ?>">
        </div>
        <div class="form-group">
            <label for="edit_last_name">Last Name</label>
            <input type="text" class="form-control" id="edit_last_name" name="last_name"
                value="<?= htmlspecialchars($user['last_name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="edit_contact_no">Contact No</label>
            <input type="text" class="form-control" id="edit_contact_no" name="contact_no"
                value="<?= htmlspecialchars($user['contact_no']) ?>" required>
        </div>
        <div class="form-group">
            <label for="edit_email">Email</label>
            <input type="email" class="form-control" id="edit_email" name="email"
                value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</section>