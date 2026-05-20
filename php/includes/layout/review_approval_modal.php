<!-- Review Approval Modal (Superadmin) - AMORA-style -->
<div id="reviewApprovalModal" class="request-delete-modal">
    <div class="request-delete-modal-content">
        <div class="request-delete-header" id="reviewApprovalHeader">
            <div class="request-delete-icon" id="reviewApprovalIcon">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
            <h2 class="request-delete-title" id="reviewApprovalTitle">Review request</h2>
        </div>
        <div class="request-delete-body">
            <p class="request-delete-message" id="reviewApprovalMessage">Process this request?</p>
            <div class="request-delete-form-group">
                <label for="reviewApprovalNotes" class="request-delete-label">Review notes <span class="optional">(optional)</span></label>
                <textarea id="reviewApprovalNotes" class="request-delete-textarea" placeholder="Add notes about your decision..." rows="4" maxlength="500"></textarea>
                <span class="request-delete-char-count" id="reviewApprovalCharCount">0 / 500</span>
            </div>
        </div>
        <div class="request-delete-footer">
            <button type="button" class="request-delete-btn request-delete-cancel" id="reviewApprovalCancel">
                <i class="fa-solid fa-times"></i> Cancel
            </button>
            <button type="button" class="request-delete-btn request-delete-submit" id="reviewApprovalReject">
                <i class="fa-solid fa-times-circle"></i> Reject
            </button>
            <button type="button" class="request-delete-btn request-delete-submit" id="reviewApprovalApprove">
                <i class="fa-solid fa-check"></i> Approve
            </button>
        </div>
    </div>
</div>
