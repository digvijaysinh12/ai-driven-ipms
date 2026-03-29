<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function showToast(icon, message) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}
</script>
@if(session('success'))
    <script>showToast('success', "{{ addslashes(session('success')) }}");</script>
@endif
@if(session('error'))
    <script>showToast('error', "{{ addslashes(session('error')) }}");</script>
@endif
@if($errors->any())
    <script>showToast('error', "{{ addslashes($errors->first()) }}");</script>
@endif