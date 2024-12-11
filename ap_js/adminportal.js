function Confirm(title, message, callback, modalclass) {
    if (modalclass == undefined)
        modalclass = 'primary'
    bootbox.confirm({
                    title: title,
                    message: message,
                    className: 'modal modal-' + modalclass + ' fade',
                    callback: callback
                    });
}

function Alert(title, message, callback, modalclass) {
    if (modalclass == undefined)
        modalclass = 'primary'
    bootbox.alert({
                    title: title,
                    message: message,
                    className: 'modal modal-' + modalclass + ' fade',
                    callback: callback
                    });
}