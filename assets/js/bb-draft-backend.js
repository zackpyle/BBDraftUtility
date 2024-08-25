jQuery(document).ready(function ($) {
    // Function to initialize tooltips
    function initializeTooltips() {
        $(".dashicons-calendar-alt").tooltip({
            show: { effect: "fadeIn", duration: 200 },
            hide: { effect: "fadeOut", duration: 200 }
        });
    }
    initializeTooltips();

    function showNotification(message, title = 'Notification', callback = null, modalToClose = null) {
        // Check if the notification modal exists, if not, create it
        if (!$('#bb-draft-notification').length) {
            $('body').append('<div id="bb-draft-notification" style="display:none;"></div>');
        }
        
        const notificationDialog = $('#bb-draft-notification');

        // Close the specified modal before showing the notification
        if (modalToClose) {
            modalToClose.dialog("close");
        }

        // Set the title and message
        notificationDialog.attr('title', title);
        notificationDialog.text(message);

        // Initialize the dialog
        notificationDialog.dialog({
            modal: true,
            buttons: {
                Ok: function () {
                    $(this).dialog("close");

                    // If there's a callback, execute it after the dialog is closed
                    if (callback) {
                        callback();
                    }
                }
            },
            closeOnEscape: true,
            draggable: false,
            resizable: false,
            width: '40%',   // Set to 40% width
            dialogClass: "fl-saved-draft-modal",
            create: function() {
                // Apply min-width and max-width to the actual dialog wrapper
                $(this).closest('.ui-dialog').css({
                    'min-width': '300px',
                    'max-width': '600px'
                });
                // Replace the dialog title with an <h1> tag
                const titleBar = $(this).closest('.ui-dialog').find('.ui-dialog-title');
                titleBar.replaceWith(`<h1 class="ui-dialog-title">${titleBar.text()}</h1>`);
            }
        });
    }
	// Function to show confirmation modal
    function showConfirmation(message, title = 'Confirmation', onConfirm = null, onCancel = null, modalToClose = null) {
        // Check if the confirmation modal exists, if not, create it
        if (!$('#bb-draft-confirmation').length) {
            $('body').append('<div id="bb-draft-confirmation" style="display:none;"></div>');
        }
        
        const confirmationDialog = $('#bb-draft-confirmation');

        // Close the specified modal before showing the confirmation
        if (modalToClose) {
            modalToClose.dialog("close");
        }

        // Set the title and message
        confirmationDialog.attr('title', title);
        confirmationDialog.text(message);

        // Initialize the dialog
        confirmationDialog.dialog({
            modal: true,
            buttons: {
                "Yes": function () {
                    $(this).dialog("close");

                    // Execute the confirm callback if provided
                    if (onConfirm) {
                        onConfirm();
                    }
                },
                "No": function () {
                    $(this).dialog("close");

                    // Execute the cancel callback if provided
                    if (onCancel) {
                        onCancel();
                    }
                }
            },
            closeOnEscape: true,
            draggable: false,
            resizable: false,
            width: '40%',
            dialogClass: "fl-saved-draft-modal",
            create: function() {
                // Apply min-width and max-width to the actual dialog wrapper
                $(this).closest('.ui-dialog').css({
                    'min-width': '270px',
                    'max-width': '450px'
                });

                // Replace the dialog title with an <h1> tag
                const titleBar = $(this).closest('.ui-dialog').find('.ui-dialog-title');
                titleBar.replaceWith(`<h1 class="ui-dialog-title">${titleBar.text()}</h1>`);
            }
        });
    }

    // Loop through each element with the class '.fl-saved-draft' and add the 'has-saved-draft' class
    $('.fl-saved-draft').each(function () {
        const postId = $(this).data('post-id');
        if (postId) {
            $(`#post-${postId}`).addClass('has-saved-draft');
        }
    });

    // Declare modal variable globally within the script
    let modal;

    // Open the modal when clicking the saved draft link
    $(document).on('click', '.fl-saved-draft', function(e) {
        e.preventDefault();
        const postId = $(this).data('post-id');
        const savedBy = $(this).data('draft-saved-by');
        const savedAt = $(this).data('draft-saved-at');
        const scheduledTime = $(this).data('scheduled-time');
        const builderName = bbDraftUtility.builderName;
        
        let builderEditUrl = $(`tr#post-${postId} .fl-builder a`).attr('href');
            builderEditUrl += '&fl_saved_draft'; // Append the fl_saved_draft param to the URL


        // Initialize the modal globally
        modal = $('<div id="fl-saved-draft-modal-content"></div>').dialog({
            autoOpen: false,
            minHeight: '150',
            width: '40%',
            modal: true,
            title: 'Saved Draft',
            draggable: false,
            resizable: false,
            autoFocus: false,
            closeOnEscape: true,
            dialogClass: "fl-saved-draft-modal",
            create: function() {
                // Apply min-width and max-width to the dialog wrapper
                $(this).closest('.ui-dialog').css({
                    'min-width': '300px',
                    'max-width': '600px'
                });
                // Replace the dialog title with an <h1> tag
                const titleBar = $(this).closest('.ui-dialog').find('.ui-dialog-title');
                titleBar.replaceWith(`<h1 class="ui-dialog-title">${titleBar.text()}</h1>`);
            },
            open: function() {
                $(this).dialog("widget").find('#edit-saved-draft').blur();
                $('.fl-saved-draft-modal').focus();
                // Bind the overlay click to close the dialog
                $('.ui-widget-overlay').on('click', function() {
                    modal.dialog('close');
                });
                // Set the scheduled time in the input field if it exists after the modal opens
                if (scheduledTime) {
                    const scheduleInput = $('#fl-schedule-time');
                    scheduleInput.val(scheduledTime);	
                }
            },
            close: function() {
                // Unbind the overlay click event when closing the dialog
                $('.ui-widget-overlay').off('click');
            }
        });

        // Build the modal content
        let modalContent = `<p>This page has a ${builderName} Saved Draft.</p>`;
        if (bbDraftUtility.showSavedInfo && savedBy && savedAt) {
            modalContent += `<p>Draft saved by <strong>${savedBy}</strong> on <strong>${savedAt}</strong>.</p>`;
        }

        modalContent += '<div class="draft-buttons">';
        // Add the "Edit Saved Draft" button if the URL is available
        if (builderEditUrl) {
            modalContent += `<a href="${builderEditUrl}" class="bb-saved-draft-btn" id="edit-saved-draft">Edit Saved Draft</a>`;
        }

        // Delete Draft button
        modalContent += '<button id="delete-saved-draft" class="bb-saved-draft-btn">Delete Draft</button>';
        modalContent += '</div>';
        

        // Conditionally show scheduling options if scheduling is enabled
        if (bbDraftUtility.enableScheduling) {

            // Add a horizontal rule
        	modalContent += '<div class="schedule-draft-section"><hr>';
            modalContent += '<h2>Schedule Draft</h2>';


            // Input and Schedule button
            modalContent += `
                <div class="schedule-draft-section">
                    <input type="datetime-local" id="fl-schedule-time" />
                    <input type="submit" class="bb-saved-draft-btn" value="${scheduledTime ? 'Update Schedule' : 'Schedule'}">
                </div>
            `;

            // If there is a scheduled time, show it with unschedule button and format it
            if (scheduledTime) {
                const formattedScheduledTime = new Date(scheduledTime).toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });

                modalContent += `<br><p>Currently scheduled to be published on <strong>${formattedScheduledTime}</strong>.</p>`;
                modalContent += '<button id="unschedule-saved-draft" class="bb-saved-draft-btn">Unschedule</button>';
            }
            modalContent += '</div>';
        }

        modal.html(modalContent);
        modal.dialog('open');

        // Handle scheduling if enabled
        if (bbDraftUtility.enableScheduling) {
            modal.find('input[type="submit"]').on('click', function(e) {
                e.preventDefault();
                const scheduledTimeString = modal.find('#fl-schedule-time').val();
                const scheduledTime = new Date(scheduledTimeString);

                if (scheduledTime) {
                    if (scheduledTime <= new Date(bbDraftUtility.serverTime)) {
                        showNotification('Please select a time that is after the current server time.', 'Error', null, modal);
                        return;
                    }

                    const nonce = bbDraftUtility.nonce;
                    jQuery.ajax({
                        url: bbDraftUtility.ajaxUrl,
                        method: 'POST',
                        data: {
                            action: 'fl_schedule_changes',
                            nonce: nonce,
                            post_id: postId,
                            scheduled_time: scheduledTimeString
                        },
                        success: function(response) {
                            if (response.success) {
                                showNotification('Scheduled successfully!', 'Success', function() {
                                    location.reload(); // Refresh the page after user clicks Ok
                                }, modal);
                            } else {
                                showNotification('Error: ' + response.data, 'Error', null, modal);
                            }
                        },
                        error: function() {
                            showNotification('An error occurred while scheduling changes.', 'Error', null, modal);
                        }
                    });
                } else {
                    showNotification('Please select a valid date and time.', 'Error', null, modal);
                }
            });

            // Remove scheduled publishing
            modal.find('#unschedule-saved-draft').on('click', function(e) {
                e.preventDefault();

                const nonce = bbDraftUtility.nonce;
                jQuery.ajax({
                    url: bbDraftUtility.ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'fl_remove_schedule',
                        nonce: nonce,
                        post_id: postId
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Scheduled publishing removed.', 'Success', function() {
                                location.reload(); // Refresh the page after user clicks Ok
                            }, modal);
                        } else {
                            showNotification('Error: ' + response.data, 'Error', null, modal);
                        }
                    },
                    error: function() {
                        showNotification('An error occurred while removing scheduled publishing.', 'Error', null, modal);
                    }
                });
            });
        }

        // Delete saved draft with confirmation
        modal.find('#delete-saved-draft').on('click', function(e) {
            e.preventDefault();

            // Show confirmation dialog before proceeding
            showConfirmation('Are you sure you want to delete this Saved Draft? This action cannot be undone.', 'Confirm Delete', function() {
                // If confirmed, proceed with draft deletion
                const nonce = bbDraftUtility.nonce;
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
                            showNotification('Saved draft deleted.', 'Success', function() {
                                location.reload(); // Refresh the page after user clicks Ok
                            }, modal);
                        } else {
                            showNotification('Error: ' + response.data, 'Error', null, modal);
                        }
                    },
                    error: function() {
                        showNotification('An error occurred while deleting the Saved Draft.', 'Error', null, modal);
                    }
                });
            });
        });
    });

    // Recenter the modal on window resize
    $(window).on('resize', function() {
        if (modal) {
            modal.dialog("option", "position", { my: "center", at: "center", of: window });
        }
    });
});
