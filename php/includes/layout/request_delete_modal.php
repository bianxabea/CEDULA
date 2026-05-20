<!-- Request Delete Modal (Admin) - AMORA-style -->
<div id="requestDeleteModal" class="request-delete-modal">
    <div class="request-delete-modal-content">
        <div class="request-delete-header">
            <div class="request-delete-icon">
                <i class="fa-solid fa-clipboard-question"></i>
            </div>
            <h2 class="request-delete-title" id="requestDeleteTitle">Request deletion</h2>
        </div>
        <div class="request-delete-body">
            <p class="request-delete-message" id="requestDeleteMessage">
                Please provide a reason for this request. A superadmin will review it.
            </p>
            <div class="request-delete-form-group">
                <label for="requestDeleteReason" class="request-delete-label">
                    Reason <span class="required">*</span>
                </label>
                <textarea id="requestDeleteReason" class="request-delete-textarea" placeholder="Enter the reason..." rows="4" maxlength="500" required></textarea>
                <span class="request-delete-char-count" id="requestDeleteCharCount">0 / 500</span>
            </div>
        </div>
        <div class="request-delete-footer">
            <button type="button" class="request-delete-btn request-delete-cancel" id="requestDeleteCancel">
                <i class="fa-solid fa-times"></i> Cancel
            </button>
            <button type="button" class="request-delete-btn request-delete-submit" id="requestDeleteSubmit">
                <i class="fa-solid fa-paper-plane"></i> Submit request
            </button>
        </div>
    </div>
</div>
