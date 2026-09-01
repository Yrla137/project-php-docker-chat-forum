<div id="delete-modal" class="delete-modal">
    <div class="delete-modal-content">
        <h3>Are you sure?</h3>

        <p id="delete-modal-message">
            This action cannot be undone.
        </p>

        <div class="delete-modal-actions">
            <button type="button" id="delete-confirm">Yes</button>
            <button type="button" id="delete-cancel">No</button>
        </div>
    </div>
</div>

<script>
    const deleteModal = document.getElementById('delete-modal');
    const deleteModalMessage = document.getElementById('delete-modal-message');
    const deleteCancel = document.getElementById('delete-cancel');
    const deleteConfirm = document.getElementById('delete-confirm');

    const deleteForms = document.querySelectorAll('.delete-form');

    let activeDeleteForm = null;

    deleteForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            activeDeleteForm = form;

            const message = form.dataset.deleteMessage;

            if (message) {
                deleteModalMessage.textContent = message;
            }

            deleteModal.classList.add('show');
        });
    });

    deleteCancel.addEventListener('click', function() {
        deleteModal.classList.remove('show');
        activeDeleteForm = null;
    });

    deleteConfirm.addEventListener('click', function() {
        if (activeDeleteForm) {
            activeDeleteForm.submit();
        }
    });
</script>