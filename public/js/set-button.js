function enableButton(submitBtn, buttonText){
    submitBtn.setAttribute('data-submitting', 'false');
    submitBtn.disabled = false;
    submitBtn.style.cursor = 'pointer';
    submitBtn.innerHTML = buttonText;
}

function disableButton(submitBtn){
    if (submitBtn.getAttribute('data-submitting') === 'true') return false;
    submitBtn.setAttribute('data-submitting', 'true');
    submitBtn.disabled = true;
    submitBtn.style.cursor = 'not-allowed';
    submitBtn.innerHTML = '<i class=\'fas fa-spinner fa-spin\'></i> Memproses...';
}