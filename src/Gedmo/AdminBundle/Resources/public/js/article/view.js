$(document).ready(function(){
    SyntaxHighlighter.defaults['toolbar'] = false;
    SyntaxHighlighter.all();
    $('a[rel=on-reply]').click(function(){
        var replyBody = '<blockquote><p>' + $(this).parent().parent().next().next().html()
        + '</p></blockquote>';
        $('#comment_content').html(replyBody);
        var subject = 'Re: ' + $(this).parent().parent().children().filter('span.subject').html();
        $('#comment_subject').attr('value', subject);
        $('#comment_form').attr("tabindex", -1).focus();
        return false;
    });
    var status = $('#comment-header').attr('class');
    if (status != undefined) {
        if (status == 1) {
            $('#1').attr("tabindex", -1).focus();
        } else {
            $('#comment_form').attr("tabindex", -1).focus();
        }
    }
});