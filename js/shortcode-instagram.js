/**
 * Created by mak on 22/12/16.
 */
jQuery(document).ready(function( $ ) {

    var url_update = $('[maks-json-config]').text();
    $.getJSON( url_update+'/update.php?instagram=true',function(data) {
        console.log(data);
    });

    var findAttrAndRemove = function(attr){
        var tag = $('['+attr+']');
        tag.removeAttr(attr);
        return tag;
    };

    $('[maks-json-header]').each(function(){
        var json = $(this).text();
        if(json) {
            $(this).remove();
            var header = $.parseJSON(json);

            var profile_picture = findAttrAndRemove('maks-profile-picture');
            profile_picture.attr({
                alt: header['full_name'],
                height: 152,
                src: header['profile_picture'],
                width: 152
            });
            findAttrAndRemove('maks_username').text(header['username']);
            findAttrAndRemove('maks-counts-media').text(header['counts']['media']);
            findAttrAndRemove('maks-counts-followed_by').text(header['counts']['followed_by']);
            findAttrAndRemove('maks-counts-follows').text(header['counts']['follows']);
            findAttrAndRemove('maks-full_name').text(header['full_name']);
            findAttrAndRemove('maks-bio').text(header['bio']);
            findAttrAndRemove('maks-website').attr('href', header['website']).text(header['website'].replace(/^https?\:\/\//, ''));

            profile_picture.on('load', function () {
                findAttrAndRemove('maks-header').removeAttr('style');
            }).error(function () {
                console.log('profile picture not load');
            });
        }
    });

    var media_template = findAttrAndRemove('maks-media-template');
    var media_html = '';

    $('[maks-json-media]').each(function(index){

        if(index % 3 == 0 || index == 0){
            media_html += '<div class="maks-row">';
        }

        var json = $(this).text();

        if(json) {
            var data = $.parseJSON(json);
            var template = media_template.clone();

            var caption_text = data['caption']['text'];
            var caption_text_tag = template.find('[maks-caption-text]');
            if(caption_text && caption_text_tag) {
                caption_text_tag.text(caption_text);
                caption_text_tag.removeAttr('maks-caption-text');
            } else {
                caption_text_tag.remove();
            }

            var created_time = new Date(data['created_time'] * 1000);
            var created_time_tag = template.find('[maks-created-time]');
            created_time_tag.text(created_time);
            created_time_tag.removeAttr('maks-created-time');

            if(data['type'] == 'image') {

                template.find('[images]').attr({
                    alt: data['caption']['text'],
                    height: data['images']['standard_resolution']['height'],
                    src: data['images']['standard_resolution']['url'],
                    width: data['images']['standard_resolution']['width']

                });
                template.find('[images]').removeAttr('images');
                template.find('[videos]').remove();

            } else if(data['type'] == 'video') {

                template.find('[videos]').attr({
                    height: data['videos']['standard_resolution']['height'],
                    poster: data['images']['standard_resolution']['url'],
                    src: data['videos']['standard_resolution']['url'],
                    width: data['videos']['standard_resolution']['width']

                });
                template.find('[videos]').removeAttr('videos');
                template.find('[images]').remove();

            }

            var likes_count = data['likes']['count'];
            var likes_count_tag = template.find('[maks-likes-count]');
            likes_count_tag.text(likes_count);
            likes_count_tag.removeAttr('maks-likes-count');

            var comments_count = data['comments']['count'];
            var comments_count_tag = template.find('[maks-comments-count]');
            comments_count_tag.text(comments_count);
            comments_count_tag.removeAttr('maks-comments-count');

            media_html += template[0].outerHTML;
        }

        if((index + 1) % 3 == 0){
            media_html += '</div>';
        }
    });

    findAttrAndRemove('maks-media').html(media_html).removeAttr('style');
});



