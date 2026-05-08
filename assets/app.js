import 'sweetalert2/dist/sweetalert2.min.css';
import Swal from 'sweetalert2';

// Confirmations de suppression avec SweetAlert2
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-delete-confirm').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const form = btn.closest('form') || document.getElementById(btn.dataset.formId);
            const result = await Swal.fire({
                title: btn.dataset.title || 'Supprimer ?',
                text: btn.dataset.text || 'Cette action est irréversible.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
            });
            if (result.isConfirmed && form) {
                form.submit();
            }
        });
    });

    // Favoris badge mise à jour dynamique
    const favBadge = document.getElementById('fav-badge');
    if (favBadge) {
        const count = parseInt(favBadge.dataset.count || '0');
        if (count > 0) {
            favBadge.textContent = count;
            favBadge.classList.remove('d-none');
        }
    }
});
