function callApi(endpoint, method, responseId)
{
    document.getElementById(responseId + '-wrapper').classList.remove('hide');

    const endpointValue = document.getElementById(responseId + '-endpoint')?.value;
    const payloadValue = method !== 'GET'
        ? document.getElementById(responseId + '-payload')?.value.replaceAll('\n', '')
        : undefined;
    const responseEl = document.getElementById(responseId);

    if (endpointValue && endpointValue.includes(endpoint)) {
        document.getElementById(responseId + '-button').disabled = true;
        responseEl.innerHTML = "Loading...";

        const callApiController = new AbortController();

        const callApiTimeout = setTimeout(() => {
            callApiController.abort()
        }, 5 * 1000);
    
        fetch(
            endpointValue,
            {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                method,
                body: payloadValue,
                signal: callApiController.signal,
            }
        )
            .then((response) => {
                console.log({endpoint, method, responseId, response});
                return response.ok
                    ? response.json()
                    : {endpoint, method, status: response.status, statusText: response.statusText}
                ;
            })
            .then((result) => {
                responseEl.innerHTML = JSON.stringify(result, null, 4);
            })
            .finally(() => {
                document.getElementById(responseId + '-button').disabled = false;
                document.getElementById(responseId + '-buttonClear').disabled = false;
                if (responseEl.innerHTML === "Loading...") {
                    responseEl.innerHTML = "";
                }
                clearTimeout(callApiTimeout);
            });
    }
    else {
        responseEl.innerHTML = "Invalid endpoint value.";
    }
}
