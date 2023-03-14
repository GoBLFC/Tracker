function debounce(func, wait, immediate) {
    var timeout;
    return function () {
        var context = this, args = arguments;
        var later = function () {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
};

function addRow(key, elem, data) {
    let innerTable = '';
    for (let i = 0; i < data.length; i++) {
        const t = (key && i === 0) ? 'th' : 'td';
        innerTable += '<' + t + '>' + data[i] + '</' + t + '>';
    }
    $(elem).append('<tr>' + innerTable + '</tr>');
}

function getButtonInput(object) {
    return $(object).parent().parent().find('.form-control').val();
}

function getButtonSelect(object) {
    return $(object).parent().parent().find('.custom-select').val();
}

function getTableKey(object) {
    return $('th:first', $(object).parents('tr')).text();
}

function postAction(url, data, callback) {
    console.log('3: ' + data);

    $.post(url, data)
        .done(function (data) {
            if (data.code === 0) alert('Error: ' + data.msg);
            if (callback) callback(data);
            if (data.msg !== undefined) console.log(data.msg);
        }).fail(function (data) {
        alert('Internal error, please contact a staff member for assistance.');
        console.log(data.msg);
    });
}

function toastNotify(message, type, delay) {
    $.notify({
        message: message
    }, {
        type: type,
        delay: delay,
        animate: {
            enter: 'animated bounceInRight',
            exit: 'animated bounceOutRight'
        },
    });
}