/*
    Add class 'table-sortable' to any table.

    Requires that table is set up like this:

    <table class="table-sortable">
        <thead>
            <tr>
                <th>Column 1</th>
                <th>Column 2</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Data 1</td>
                <td>Data 2</td>
            </tr>
            <tr>
                <td>Data 3</td>
                <td>Data 4</td>
            </tr>
        </tbody>
    </table>
 */

$(function(){

    initSortableTable();

    $('table.table-sortable').on('click', 'th', function(){

        var index = $(this).data('index');
        var title = $(this).data('title');
        var direction = $(this).data('direction');

        if (direction == '' || direction == 'desc') {
            direction = 'asc';
            $(this).html(title+' <i class="fa fa-arrow-circle-down"></i>');
        } else {
            direction = 'desc';
            $(this).html(title+' <i class="fa fa-arrow-circle-up"></i>');
        }


        $(this).data('direction', direction);

        if ( current_sort_index !== index && current_sort_index !== '' ) {
            var old_element = $('.table-sortable-'+current_sort_index);
            old_element.find('i').remove();
            old_element.data('direction', '');
        }

        current_sort_index = index;
        var is_numeric = true;
        var rows = [];
        var tbody = $('table.table-sortable').find('tbody');

        tbody.find('tr').each(function(){
            var html = $(this).html();
            $(this).find('td').each(function(i){
                if ( i === index ){
                    var content = $(this).text().replace(/\s/g, '');
                    var original_content = content;
                    content = content.replace(/,/g, '').replace(/$/g, '').replace(/%/, '');
                    if (!$.isNumeric(content)){
                        is_numeric = false;
                        content = original_content;
                    }
                    rows.push({
                        sort: content,
                        html: html
                    });
                }
            });
        });

        if (is_numeric){
            for(var i=0; i<rows.length; i++) {
                rows[i].sort = parseFloat(rows[i].sort);
            }
        }

        rows.sort(function(a, b){
            if (is_numeric) {
                return b.sort - a.sort;
            } else {
                return b.sort.localeCompare(a.sort);
            }
        });

        if (direction == 'desc') {
            rows = rows.reverse();
        }

        tbody.html('');
        for(i=0; i<rows.length; i++) {
            tbody.append('<tr>'+rows[i].html+'</tr>');
        }

    });

});

var current_sort_index = '';

function initSortableTable(){
    $('table.table-sortable').find('thead').find('th').each(function(index){
        $(this)
            .data('index', index)
            .data('title', $.trim($(this).text()))
            .data('direction', '')
            .addClass('table-sortable-'+index)
            .css('cursor', 'pointer');
    });
}