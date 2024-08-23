jQuery(document).ready(function ($) {
    // Check if Beaver Builder is active
    if (typeof FLBuilder !== 'undefined' && bbDraftUtility.hasDraft) {
        // Display the modal if there's a saved draft
        let modalContent = '<p>This page has a saved draft.';

        // If there's a scheduled time, inform the user
        if (bbDraftUtility.scheduledTime) {
            modalContent += ` It is scheduled to be published on <strong>${bbDraftUtility.scheduledTime}</strong>.`;
        }

        modalContent += '</p><p>Would you like to continue editing this draft OR delete the draft and edit the currently published version?</p>';

        const modal = $('<div id="bb-draft-modal" title="Saved Draft Available"></div>');
        modal.append(modalContent);

        // Add buttons for the options
        modal.dialog({
            modal: true,
            width: 500,
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
                        console.log(response); // Log the response from the server
                        if (response.success) {
                            alert('Draft deleted successfully!');
                            parent.window.location.reload(); // Refresh the entire parent window
                        } else {
                            alert('Failed to delete the draft.');
                        }
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        console.error("AJAX Request Failed: ", textStatus, errorThrown);
                        console.error("Response Text: ", jqXHR.responseText);
                    });

                    $(this).dialog("close");
                }
            },
            dialogClass: "no-close",
            draggable: false,
            resizable: false,
			dialogClass: "bb-draft-modal",
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
