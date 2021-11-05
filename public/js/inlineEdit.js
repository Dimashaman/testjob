//console.log('works');

const table = document.querySelector('.table');
const model = table.dataset.model;
table.addEventListener('click', _inlineEdit = function (e) {
    if (e.target.classList.contains('inlineEditLink')) {
        var targetTableRow = e.target.parentNode.parentNode;
        const id = targetTableRow.querySelector('[name="bookId"]').innerText;
        getInlineForm('/' + model + '/' + id + '/inlineEdit')
            .then(function (response) {
                response.text().then(function (text) {
                    var rowCopy = targetTableRow.cloneNode(true);
                    targetTableRow.innerHTML = text;
                    let form = targetTableRow.querySelector('[name=' + model + ']');
                    saveLink = targetTableRow.querySelector('.inlineEditSave');
                    saveLink.addEventListener('click', () => { postInlineFormAndReplaceView(form, targetTableRow) })
                    cancelLink = targetTableRow.querySelector('.inlineEditCancel');
                    cancelLink.addEventListener('click', () => {
                        targetTableRow.outerHTML = rowCopy.outerHTML;
                        table.addEventListener('click', _inlineEdit);
                    })
                })
                table.removeEventListener('click', _inlineEdit);
            })
    }
})

function postInlineFormAndReplaceView(form, targetTableRow) {
    this.targetTableRow = targetTableRow;
    fetch(form.action, {
        method: 'POST',
        body: new URLSearchParams(new FormData(form))
    }).then((resp) => {
        resp.text().then(text => {
            this.targetTableRow.outerHTML = text;
            table.addEventListener('click', _inlineEdit);
        })
    })
}

async function getInlineForm(url = '') {
    const response = await fetch(url, {
        method: 'GET'
    });
    return await response;
}
