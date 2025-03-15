/**
 * Admin panel JavaScript functionality
 */

$(document).ready(function() {
    // Toggle sidebar on mobile
    $('#sidebarToggle').on('click', function() {
        $('.sidebar').toggleClass('show');
    });

    // User management - delete user confirmation
    $('.delete-user-btn').on('click', function(e) {
        e.preventDefault();
        const userId = $(this).data('user-id');
        const username = $(this).data('username');
        
        $('#deleteUserModal .user-name').text(username);
        $('#confirmDeleteUser').data('user-id', userId);
        $('#deleteUserModal').modal('show');
    });

    // Confirm user deletion
    $('#confirmDeleteUser').on('click', function() {
        const userId = $(this).data('user-id');
        window.location.href = 'users.php?action=delete&id=' + userId;
    });

    // Pending changes approval/rejection
    $('.approve-change-btn').on('click', function() {
        const changeId = $(this).data('change-id');
        $('#approveChangeModal').data('change-id', changeId);
        $('#approveChangeModal').modal('show');
    });

    $('#confirmApproveChange').on('click', function() {
        const changeId = $('#approveChangeModal').data('change-id');
        window.location.href = 'pending_changes.php?action=approve&id=' + changeId;
    });

    $('.reject-change-btn').on('click', function() {
        const changeId = $(this).data('change-id');
        $('#rejectChangeModal').data('change-id', changeId);
        $('#rejectChangeModal').modal('show');
    });

    $('#confirmRejectChange').on('click', function() {
        const changeId = $('#rejectChangeModal').data('change-id');
        const reason = $('#rejectionReason').val();
        window.location.href = 'pending_changes.php?action=reject&id=' + changeId + '&reason=' + encodeURIComponent(reason);
    });

    // Date range picker for logs
    if ($('#log-date-range').length) {
        $('#log-date-range').daterangepicker({
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            alwaysShowCalendars: true,
            startDate: moment().subtract(29, 'days'),
            endDate: moment()
        }, function(start, end) {
            window.location.href = 'logs.php?start=' + start.format('YYYY-MM-DD') + '&end=' + end.format('YYYY-MM-DD');
        });
    }

    // Settings form validation
    if ($('#settingsForm').length) {
        $('#settingsForm').submit(function(e) {
            let hasError = false;
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').hide();

            // Validate form fields
            if ($('#siteName').val().trim() === '') {
                $('#siteName').addClass('is-invalid');
                $('#siteNameFeedback').show();
                hasError = true;
            }

            if ($('#adminEmail').val().trim() === '') {
                $('#adminEmail').addClass('is-invalid');
                $('#adminEmailFeedback').show();
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
            }
        });
    }

    // Initialize datatables
    if ($.fn.DataTable && $('.data-table').length) {
        $('.data-table').DataTable({
            responsive: true,
            order: [[0, 'desc']]
        });
    }
}); 