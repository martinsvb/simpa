function clearResponse(responseId)
{
    const responseEl = document.getElementById(responseId);
    if (responseEl) {
        document.getElementById(responseId + '-wrapper').classList.add('hide');
        responseEl.innerHTML = "";
        document.getElementById(responseId + '-buttonClear').disabled = true;
    }
}
