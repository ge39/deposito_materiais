// public/js/produto.js
function previewImage(event, previewId = 'imagemPreview') {
    const input = event.target;
    const preview = document.getElementById(previewId);

    if(input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
