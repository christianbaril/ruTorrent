/**
 * Created with JetBrains PhpStorm.
 * User: chris
 * Date: 13-09-17
 * Time: 07:03
 * To change this template use File | Settings | File Templates.
 */
theWebUI.perform = function (cmd) {

    if (cmd == "pause") {

        var h = this.getHashes("unpause");
        if (h != "") {
            var theHash = h.split('&hash=');
            var canStart = true;

            $.each(theHash, function (k, v) {
                if ($.trim(v).length > 0) {
                    if ($.trim(theWebUI.torrents[v].label).length == 0) {
                        canStart = false;
                        noty('Error, need label : ' + theWebUI.torrents[v].name, 'error');
                    }
                }
            });

            if (!canStart)
                return;

            this.request("?action=unpause" + h);
        }
    }

    var h = this.getHashes(cmd);

    if (cmd == "start") {
        var theHash = h.split('&hash=');
        var canStart = true;
        $.each(theHash, function (k, v) {
            if ($.trim(v).length > 0) {
                if ($.trim(theWebUI.torrents[v].label).length == 0) {
                    canStart = false;
                    noty('Need label : ' + theWebUI.torrents[v].name, 'error');
                }
            }
        });
        if (!canStart)
            return;
    }

    if (h != "") {
        if ((cmd.indexOf("remove") == 0) && (h.indexOf(this.dID) > -1)) {
            this.dID = "";
            this.clearDetails();
        }
        this.getTorrents(cmd + h + "&list=1");
    }

}
