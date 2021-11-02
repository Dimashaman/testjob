console.log('works');

var bookRows = document.querySelectorAll(".bookRow");

bookRows.forEach(row => {
    var inlineEditLink = row.querySelector('.inlineEditLink');
    inlineEditLink.addEventListener("click", function () {
        let bookInputs = row.querySelectorAll(".bookInput");
        bookInputs.forEach(input => {
            input.toggleAttribute('readonly');
            input.classList.toggle('table-warning');
        })
        inlineEditLink.toggleAttribute('hidden');
        var cancelLink = row.querySelector('.inlineEditCancel');
        cancelLink.toggleAttribute('hidden');
        var saveLink = row.querySelector('.inlineEditSave');
        saveLink.toggleAttribute('hidden');

        cancelLink.addEventListener("click", function () {

        })

        saveLink.addEventListener('click', saveEL = function () {
            data = {
                id: row.querySelector('[name="bookId"]').value,
                title: row.querySelector('[name="bookTitle"]').value,
                cover: '',
                description: row.querySelector('[name="bookDescription"]').value,
                publishYear: row.querySelector('[name="bookPublishYear"]').value
            }
            postData('/book/' + data.id + '/inlineEdit', data)
                .then((data) => {
                    console.log(data);
                    saveLink.toggleAttribute('hidden');
                    cancelLink.toggleAttribute('hidden');
                    bookInputs.forEach(input => {
                        input.toggleAttribute('readonly');
                        input.classList.toggle('table-warning');
                    })
                    inlineEditLink.toggleAttribute('hidden');
                    saveLink.removeEventListener('click', saveEL);
                });
        })
    });
});

async function postData(url = '', data = {}) {
    // Default options are marked with *
    const response = await fetch(url, {
        method: 'POST', // *GET, POST, PUT, DELETE, etc.
        mode: 'cors', // no-cors, *cors, same-origin
        cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
        credentials: 'same-origin', // include, *same-origin, omit
        headers: {
            // 'Content-Type': 'application/json'
            'Content-Type': 'application/x-www-form-urlencoded',
        },  
        redirect: 'follow', // manual, *follow, error
        referrerPolicy: 'no-referrer', // no-referrer, *client
        body: new URLSearchParams(data)
    });
    return await response; // parses JSON response into native JavaScript objects
}