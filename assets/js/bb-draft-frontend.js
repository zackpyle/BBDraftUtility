jQuery(document).ready(function ($) {
    // Check if Beaver Builder is active
    if (typeof FLBuilder !== 'undefined' && bbDraftUtility.hasDraft) {

        // Skip showing the modal if the user came from the previous draft modal to edit the draft
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('fl_saved_draft')) {
            return;
        }

        // Function to show a notification
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

            // Initialize the dialog with the "bb-draft-modal" class for consistent styling
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
                open: function(event, ui) {
                    $(".ui-dialog-titlebar-close", ui.dialog | ui).hide(); // Hide the close button
                },
                draggable: false,
                resizable: false,
                width: 500,
                dialogClass: "bb-draft-modal"
            });
        }

        // Display the modal if there's a saved draft
        let modalContent = '<p>This page has a saved draft.';

        // Add information about who saved the draft and when
        if (bbDraftUtility.showSavedInfo && bbDraftUtility.draftSavedBy && bbDraftUtility.draftSavedAt) {
            modalContent += `<p>Draft saved by <strong>${bbDraftUtility.draftSavedBy}</strong> on <strong>${bbDraftUtility.draftSavedAt}</strong>.</p>`;
        }

        // If there's a scheduled time, inform the user
        if (bbDraftUtility.scheduledTime) {
            modalContent += ` It is scheduled to be published on <strong>${bbDraftUtility.scheduledTime}</strong>.`;
        }

        modalContent += '</p><p>Would you like to continue editing this saved draft OR delete the saved draft and start editing the currently published version?</p>';

        const modal = $('<div id="bb-draft-modal" title="Saved Draft Available"></div>');
        modal.append(modalContent);

        // Add buttons for the options
        modal.dialog({
            modal: true,
            width: 515,
            closeOnEscape: false,
            position: { my: "center", at: "center", of: window },
            open: function(event, ui) {
                $(".ui-dialog-titlebar-close", ui.dialog | ui).hide(); // Hide the close button
            },
            buttons: {
                "Edit Saved Draft": function() {
                    $(this).dialog("close");
                },
                "Delete Saved Draft": function() {
                    $.post(bbDraftUtility.ajaxUrl, {
                        action: 'fl_delete_draft',
                        post_id: bbDraftUtility.postId,
                        nonce: bbDraftUtility.nonce
                    }, function(response) {
                        if (response.success) {
                            showNotification('Your draft has been deleted successfully!', 'Saved Draft', function() {
                                parent.window.location.reload(); // Refresh the entire parent window after closing the notification
                            }, modal);
                        } else {
                            showNotification('Failed to delete the draft.', 'Error', null, modal);
                        }
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        showNotification('AJAX Request Failed: ' + textStatus, 'Error', null, modal);
                    });
                }
            },
            dialogClass: "bb-draft-modal",
            draggable: false,
            resizable: false,
            create: function() {
                // Add custom classes to buttons
                $(".ui-dialog-buttonpane button:contains('Edit Saved Draft')").addClass("continue-editing-btn");
                $(".ui-dialog-buttonpane button:contains('Delete Saved Draft')").addClass("delete-draft-btn");
            }
        });

        // Recenter the modal on window resize
        $(window).on('resize', function() {
            modal.dialog("option", "position", { my: "center", at: "center", of: window });
        });
    }
});
