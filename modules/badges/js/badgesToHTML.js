var badgesToHTML = function(data, messages, withRewardLabel) {
    console.log(withRewardLabel);
    if ('undefined' === typeof withRewardLabel) withRewardLabel = true;
    // messages MUST HAVE the following properties: unrewarded, rewarded, nobadges, error
    var retval = messages.error;
    if ('id' in data && 'badges' in data) {
        if (data.badges.length>0) {
            var instanceId = data.id;
            var badgesdiv = $j('<div></div>');
            var badgesCont = $j('<div id="badgesContainer-'+instanceId+'" class="ui stackable items"></div>');
            badgesCont.appendTo(badgesdiv);
            for (var i in data.badges) {
                if (data.badges.hasOwnProperty(i)) {
                    var badge = data.badges[i];
                    var item = $j('<div class="item"></div>');
                    var imgdiv = $j('<div class="image"></div>');
                    var img = $j('<img class="ui small image" src="'+badge.imageurl+'"></img>');
                    img.addClass((withRewardLabel && badge.issuedOn == null ? 'un' : '')+'rewarded');
                    img.appendTo(imgdiv);
                    imgdiv.appendTo(item);
                    var content = $j('<div class="content"></div>');
                    $j('<div class="name">'+
                        badge.name.charAt(0).toUpperCase() + badge.name.toLowerCase().slice(1)
                    +'</div>').appendTo(content);
                    $j('<span class="description">'+
                        // replace will be a kind of nl2br
                        (badge.description + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br/>' + '$2')
                    +'</span>').appendTo(content);
                    if (withRewardLabel) {
                        $j('<span class="extra">'+
                            (badge.issuedOn == null ? messages.unrewarded : messages.rewarded +
                            new Date(parseInt(badge.issuedOn)*1000).toLocaleDateString())
                        +'</span>').appendTo(content);
                    }
                    content.appendTo(item);
                    item.appendTo(badgesCont);
                }
            }
            retval = badgesdiv.html();
        } else {
            retval = messages.nobadges;
        }
    }
    return retval;
}