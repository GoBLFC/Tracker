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

$(".nav-item").on('click', function (event) {
    $(".nav-item.active").removeClass('active');
    $(this).addClass('active');
    const sect = $.trim($(this).text());
    $(".card[data-section]:visible").hide();
    $('.card[data-section=' + sect + ']').show();
});