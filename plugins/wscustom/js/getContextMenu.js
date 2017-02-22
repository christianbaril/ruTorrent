$(document).ready(function () {

    plugin.createMenu = theWebUI.createMenu;

    theWebUI.createMenu = function (e, id) {

        var table = this.getTable('trt');

        theContextMenu.clear();

        if (table.selCount > 1) {
            theContextMenu.add([theUILang.Start, 'theWebUI.start()']);
            theContextMenu.add([theUILang.Pause, 'theWebUI.pause()']);
            theContextMenu.add([theUILang.Stop, 'theWebUI.stop()']);
        } else {
            var canStart = $.trim(this.torrents[id].label).length > 0;
            theContextMenu.add([theUILang.Start, (this.isTorrentCommandEnabled('start', id) && canStart) ? 'theWebUI.start()' : null]);
            theContextMenu.add([theUILang.Pause, (this.isTorrentCommandEnabled('pause', id) || this.isTorrentCommandEnabled('unpause', id)) ? 'theWebUI.pause()' : null]);
            theContextMenu.add([theUILang.Stop, this.isTorrentCommandEnabled('stop', id) ? 'theWebUI.stop()' : null]);
        }
        if (theWebUI.systemInfo.rTorrent.iVersion >= 0x809)
            theContextMenu.add([theUILang.updateTracker, this.isTorrentCommandEnabled('updateTracker', id) ? 'theWebUI.updateTracker()' : null]);
        theContextMenu.add([CMENU_SEP]);
        var _bf = [];

        for (v in allowedLabels) {
            k = allowedLabels[v];
            if (theWebUI.torrents[id].label == k) {
                _bf.push([CMENU_SEL, k + " ", 'theWebUI.setLabel(\'' + addslashes(k) + '\')']);
            } else {
                _bf.push([k + " ", 'theWebUI.setLabel(\'' + addslashes(k) + '\')']);
            }
        }

        theContextMenu.add([CMENU_CHILD, theUILang.Labels, _bf]);

        if (userinfo.admin) {
            theContextMenu.add([CMENU_SEP]);
            theContextMenu.add([('Toggle keep torrent'), 'theWebUI.setPreventAutoDelete()']);
            theContextMenu.add([CMENU_SEP]);
            theContextMenu.add([('Remove and delete data'), (table.selCount > 1) || this.isTorrentCommandEnabled('remove', id) ? 'theWebUI.removeWithData()' : null]);
        }
    };
});
