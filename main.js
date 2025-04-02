$(document).ready(function() {
    // Load user profile information
    loadUserProfile();
    
    // Load initial data and update statistics
    loadStudents();
    
    // Update system time every minute
    setInterval(updateSystemTime, 60000);
    updateSystemTime();
    
    function updateSystemTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
        $('#systemTime').text(timeString);
    }
    
    function loadUserProfile() {
        $.ajax({
            url: 'check_session.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.user_id) {
                    // Load user profile information
                    $.ajax({
                        url: 'get_user.php',
                        type: 'GET',
                        data: { student_id: data.user_id },
                        dataType: 'json',
                        success: function(userData) {
                            if (userData && userData.length > 0) {
                                const user = userData[0];
                                $('#userName').text(user.first_name + ' ' + user.last_name);
                                if (user.profile_image) {
                                    $('#userProfilePic').html('<img src="' + user.profile_image + '" class="profile-pic" alt="Profile Picture">');
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading user profile:', error);
                        }
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error checking session:', error);
            }
        });
    }
    
    function loadStudents() {
        $.ajax({
            url: 'get_user.php',
            type: 'GET',
            dataType: 'json',
            cache: false, // 🚀 Prevent caching issues
            success: function(data) {
                console.log('Data fetched:', data);
                updateTable(data);
                updateStatistics(data);
            },
            error: function(xhr, status, error) {
                console.error('Error loading students:', error);
                alert('Error loading students. Please try again.');
            }
        });
    }
    
    function updateTable(data) {
        const tbody = $('#tableBody');
        tbody.empty();
        
        if (!Array.isArray(data)) {
            tbody.html('<tr><td colspan="6" class="text-center">' + (data.error || 'No students found') + '</td></tr>');
            return;
        }
        
        data.forEach(function(item) {
            const fullName = item.first_name + ' ' + item.last_name;
            const status = item.is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
            const verification = item.is_verified 
                ? '<span class="badge badge-success">Verified</span>'
                : '<span class="badge badge-warning">Not Verified</span>';
            
            const row = `
                <tr>
                    <td>${item.student_id}</td>
                    <td>${fullName}</td>
                    <td>${item.email}</td>
                    <td>${status}</td>
                    <td>${verification}</td>
                    <td class="action-buttons">
                        <button class="btn btn-sm btn-info edit-btn" data-toggle="modal" data-target="#exampleModal"
                            data-id="${item.student_id}" 
                            data-first-name="${item.first_name}" 
                            data-last-name="${item.last_name}" 
                            data-email="${item.email}"
                            data-address="${item.user_address || ''}"
                            data-profile-image="${item.profile_image || ''}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${item.student_id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            tbody.append(row);
        });

        addEventListeners();
    }
    
    // Edit user button click
    function addEventListeners() {
        $(".edit-btn").on("click", function() {
            let studentId = $(this).data('id');
            let firstName = $(this).data('first-name');
            let lastName = $(this).data('last-name');
            let email = $(this).data('email');
            let address = $(this).data('address');
            let profileImage = $(this).data('profile-image');
            
            $("#exampleModalLabel").text("Edit Student");
            $("#student_id").val(studentId);
            $("#firstName").val(firstName);
            $("#lastName").val(lastName);
            $("#Email").val(email);
            $("#userAddress").val(address);
            
            if (profileImage) {
                $("#currentImageContainer").show();
                $("#currentImage").attr("src", profileImage);
            } else {
                $("#currentImageContainer").hide();
            }

            $("#ProfileImage").val('');
            $("#imagePreviewContainer").hide();
        });
        
        $(".delete-btn").on("click", function() {
            let studentId = $(this).data('id');
            $("#delete_student_id").val(studentId);
            $("#deleteModal").modal('show');
        });
    }
    
    // Save or Update Student
    $("#btnSaveUser").on("click", function() {
        console.log("btnSaveUser clicked");
    
        if (!$("#firstName").val() || !$("#lastName").val() || !$("#Email").val()) {
            alert("Please fill in all required fields");
            return;
        }

        let studentId = $("#student_id").val();
        let isNewStudent = !studentId;
        
        if (isNewStudent && !$("#ProfileImage")[0].files[0]) {
            alert("Please select a profile image");
            return;
        }
        
        let url = isNewStudent ? 'users_create.php' : 'users_update.php';
        let formData = new FormData($("#newUserForm")[0]);

        console.log("Submitting form data to:", url);
        
        $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
            data: formData,
            processData: false,
            contentType: false,
            success: function(result) {
                console.log("AJAX response:", result);
                if (result.res === "success") {
                    alert("Student saved successfully!");
                    $("#exampleModal").modal("hide");
                    loadStudents();
                } else {
                    alert(result.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX error:", error);
                console.error("Response:", xhr.responseText);
                alert("Server error occurred. Check console for details.");
            }
        });
    });

    $("#confirmDelete").on("click", function() {
        let studentId = $("#delete_student_id").val();
        $.ajax({
            url: 'users_delete.php',
            type: "POST",
            dataType: "json",
            data: { student_id: studentId },
            success: function(result) {
                if (result.res === "success") {
                    alert("Student deleted successfully!");
                    $("#deleteModal").modal("hide");
                    loadStudents();
                } else {
                    alert(result.error || "Unknown error occurred");
                }
            },
            error: function(xhr, status, error) {
                console.error('Delete error:', error);
                console.error("Response:", xhr.responseText);
                alert("Server error occurred. Check console for details.");
            }
        });
    });

    addEventListeners();
});
