$(document).ready(function() {
    $('a.tree-reorder-link').click(function(){
        var id = $("select[name='select-root-category'] option:selected").attr('value');
        $(this).attr('href', $(this).attr('href')+id);
        return true;
    });
});