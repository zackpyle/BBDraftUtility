jQuery(document).ready(function ($) {
    // Function to initialize tooltips
    function initializeTooltips() {
        $(".dashicons-calendar-alt").tooltip({
            show: { effect: "fadeIn", duration: 200 },
            hide: { effect: "fadeOut", duration: 200 }
        });
    }

    initializeTooltips();

	// Loop through each element with the class '.fl_schedule' and add the 'has-unpublished-changes' class
    $('.fl_schedule').each(function () {
        const postId = $(this).data('post-id');
        if (postId) {
            $(`#post-${postId}`).addClass('has-unpublished-changes');
        }
    });

	if (bbDraftUtility.enableScheduling) {
		const serverTimeString = $('#fl-schedule-changes').data('server-time');
		const serverTime = new Date(serverTimeString);
		$(document).on('click', '.fl_schedule', function(e) {
			e.preventDefault();
			const postId = $(this).data('post-id');
			const scheduledTime = $(this).find('.dashicons-calendar-alt').data('scheduled-time');

			const modal = $('#fl-schedule-changes').dialog({
				autoOpen: false,
				minHeight: '150',
				width: '40%',
				modal: true,
				title: 'Schedule Draft'
			});

			if (scheduledTime) {
				$('#fl-schedule-time').val(scheduledTime);
			} else {
				$('#fl-schedule-time').val('');
			}

			modal.dialog('open');

			modal.find('input[type="submit"]').on('click', function(e) {
				e.preventDefault();
				const scheduledTimeString = modal.find('#fl-schedule-time').val();
				const scheduledTime = new Date(scheduledTimeString);

				if (scheduledTime) {
					if (scheduledTime <= serverTime) {
						alert('Please select a time that is after the current server time.');
						return;
					}

					const nonce = modal.data('nonce');
					jQuery.ajax({
						url: ajaxurl,
						method: 'POST',
						data: {
							action: 'fl_schedule_changes',
							nonce: nonce,
							post_id: postId,
							scheduled_time: scheduledTimeString
						},
						success: function(response) {
							if (response.success) {
								alert('Changes scheduled successfully!');
								modal.dialog('close');

								// Refresh the page to reflect the changes
								location.reload();
							} else {
								alert('Error: ' + response.data);
							}
						},
						error: function() {
							alert('An error occurred while scheduling changes.');
						}
					});
				} else {
					alert('Please select a valid date and time.');
				}
			});

			// Remove scheduled publishing
			modal.find('#remove-scheduled-publishing').on('click', function(e) {
				e.preventDefault();

				const nonce = modal.data('nonce');
				jQuery.ajax({
					url: ajaxurl,
					method: 'POST',
					data: {
						action: 'fl_remove_schedule',
						nonce: nonce,
						post_id: postId
					},
					success: function(response) {
						if (response.success) {
							alert('Scheduled publishing removed.');
							modal.dialog('close');

							// Refresh the page to reflect the changes
							location.reload();
						} else {
							alert('Error: ' + response.data);
						}
					},
					error: function() {
						alert('An error occurred while removing scheduled publishing.');
					}
				});
			});

			// Delete unpublished draft with confirmation
			modal.find('#delete-unpublished-draft').on('click', function(e) {
				e.preventDefault();

				// Show confirmation dialog before proceeding
				const confirmation = confirm('Are you sure you want to delete this unpublished draft? This action cannot be undone.');
				if (!confirmation) {
					return; // Exit if the user cancels the confirmation
				}

				const nonce = modal.data('nonce');
				jQuery.ajax({
					url: ajaxurl,
					method: 'POST',
					data: {
						action: 'fl_delete_draft',
						nonce: nonce,
						post_id: postId
					},
					success: function(response) {
						if (response.success) {
							alert('Unpublished draft deleted.');
							modal.dialog('close');

							// Refresh the page to reflect the changes
							location.reload();
						} else {
							alert('Error: ' + response.data);
						}
					},
					error: function() {
						alert('An error occurred while deleting unpublished draft.');
					}
				});
			});
		});
	}
});
