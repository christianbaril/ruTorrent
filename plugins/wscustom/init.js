plugin.ownersLabels = {};

plugin.start = rTorrentStub.prototype.start;
setTimeout(function(){

rTorrentStub.prototype.start = function () {
    noty('calling startcmd')
    plugin.start.call(this);

    for (var i = 0; i < this.hashes.length; i++) {
        //            var owner = theWebUI.getTable('trt').getValueById(this.hashes[i], 'owner');
        var owner = theWebUI.torrents[this.hashes[i]].owner;

        if (owner == '') {

            // Make sure that content and datatype are ok
            this.contentType = "text/xml; charset=UTF-8";
            this.dataType = "xml";

            // Create new command for custom information
            var cmd = new rXMLRPCCommand('d.set_custom');

            // Add owner for the torrent that matches the hashes
            cmd.addParameter('string', this.hashes[i]);
            cmd.addParameter('string', 'owner');
            cmd.addParameter('string', userinfo.username);

            // push command to request
            this.commands.push(cmd);

            // Create new command to start torrent
            var cmd = new rXMLRPCCommand('d.start');
            cmd.addParameter('string', this.hashes[i]);

            // push command to request
            this.commands.push(cmd);

        }
    }
}

})

plugin.preventautodelete = rTorrentStub.prototype.preventautodelete;
rTorrentStub.prototype.preventautodelete = function () {
    for (var i = 0; i < this.hashes.length; i++) {
        // Create new command for custom information
        var cmd = new rXMLRPCCommand('d.set_custom');

        cmd.addParameter("string", this.hashes[i]);
        cmd.addParameter("string", 'keep');

        if (theWebUI.torrents[this.hashes[i]].keep) {
            cmd.addParameter("string", "");
        } else {
            cmd.addParameter("string", "yes");
        }
        this.commands.push(cmd);
    }
};

theWebUI.setPreventAutoDelete = function () {
    theWebUI.perform("preventautodelete");
};

plugin.filterByLabel = theWebUI.filterByLabel;
theWebUI.filterByLabel = function (hash) {
    if (plugin.enabled && theWebUI.actLbl && $($$(theWebUI.actLbl)).hasClass('owner'))
        theWebUI.filterByOwner(theWebUI.actLbl.replace(/^i/, ''));
    else
        plugin.filterByLabel.call(theWebUI, hash);
};

theWebUI.filterByOwner = function (owner) {
    var table = this.getTable("trt");
    var torrents = theWebUI.torrents;
    for (hash in torrents) {
        if (torrents[hash].owner == owner)
            table.unhideRow(hash);
        else
            table.hideRow(hash);
    }
};

plugin.config = theWebUI.config;
theWebUI.config = function (data) {
    // Owner part
    this.tables.trt.columns.push({
        text: 'Owner',
        width: '100px',
        id: 'owner',
        type: TYPE_STRING
    });
    // Superseed
    this.tables.trt.columns.push({
        text: 'Keep ?',
        width: '100px',
        id: 'keep',
        type: TYPE_STRING
    });
    plugin.config.call(this, data);

    plugin.reqOwner = theRequestManager.addRequest('trt', theRequestManager
        .map('d.get_custom=')
        + 'owner', function (hash, torrent, value) {
            torrent.owner = value;
        });

    plugin.reqKeep = theRequestManager.addRequest('trt', theRequestManager
        .map('d.get_custom=')
        + 'keep', function (hash, torrent, value) {
            torrent.keep = value;
        });

};


plugin.onRemove = function () {
    theWebUI.getTable('trt').removeColumnById('owner');
    theRequestManager.removeRequest('trt', plugin.reqOwner);

    theWebUI.getTable('trt').removeColumnById('seedmore');
    theRequestManager.removeRequest('trt', plugin.reqKeep);
};


plugin.isActualLabel = function (lbl) {
    return (theWebUI.actLbl && $($$(theWebUI.actLbl)).hasClass('owner') && ('i' + lbl == theWebUI.actLbl));
};

// update labels ..
plugin.updateLabels = theWebUI.updateLabels;
theWebUI.updateLabels = function (wasRemoved) {
    plugin.updateLabels.call(theWebUI, wasRemoved);
    if (plugin.enabled) {
        if (wasRemoved) {
            theWebUI.rebuildOwnersLabels();
        }
    }
};

plugin.addTrackers = theWebUI.addTrackers;
theWebUI.addTrackers = function (data) {
    plugin.addTrackers.call(theWebUI, data);
    if (plugin.enabled)
        theWebUI.rebuildOwnersLabels();
};

theWebUI.rebuildOwnersLabels = function () {

    if (!userinfo.admin)
        return;


    if (!plugin.allStuffLoaded)
        setTimeout('theWebUI.rebuildOwnersLabels()', 1000);
    else {

        var table = this.getTable('trt');
        var ownersLabels = {};

        var tArray = [];

        for (hash in this.torrents) {
            if (tArray.indexOf(this.torrents[hash].owner) == -1) {
                tArray.push(this.torrents[hash].owner);
            }
        }
        tArray.sort();

        ownersLabelsSize = {};

        for (t in tArray) {
            ownersLabels[tArray[t]] = 0;
            ownersLabelsSize[tArray[t]] = 0;
        }

        for (hash in this.torrents) {
            //            var size = (parseInt(this.torrents[hash].size));
            var size = (parseInt(this.torrents[hash].downloaded));
            ownersLabels[this.torrents[hash].owner] += 1;
            ownersLabelsSize[this.torrents[hash].owner] += size;
        }

        for (i in ownersLabelsSize) {
            ownersLabelsSize[i] = theConverter.bytes(ownersLabelsSize[i], 2) + " / " + ownersLabels[i];
        }

        var keys = [];

        for (var lbl in ownersLabels) {
            keys.push(lbl);
        }
        keys.sort();

        plugin.ownersLabels = [];

        var ul = $("#ownrl");

        $(ul).children().remove();


        for (var i = 0; i < keys.length; i++) {
            var lbl = keys[i];
            var li = null;

            if (lbl in plugin.ownersLabels) {
                li = $($$('i' + lbl));
                li.children("span").text(ownersLabelsSize[lbl]);
            } else {

                li = $('<li>').attr("id", 'i' + lbl).html(escapeHTML(lbl) + '&nbsp;(<span id="-' + lbl + '_c">' + ownersLabelsSize[lbl] + '</span>)').mouseclick(theWebUI.ownersLabelContextMenu).addClass("cat owner").attr("title", lbl + " (" + ownersLabels[lbl] + ")");
                ul.append(li);

            }

            if (plugin.isActualLabel(lbl))
                li.addClass("sel");
        }

        var needSwitch = false;
        for (var lbl in plugin.ownersLabels)
            if (!(lbl in ownersLabels)) {
                $($$('i' + lbl)).remove();
                if (plugin.isActualLabel(lbl))
                    needSwitch = true;
            }

        plugin.ownersLabels = ownersLabels;
        if (needSwitch)
            theWebUI.switchLabel($$("-_-_-all-_-_-"));
    }
};

theWebUI.ownersLabelContextMenu = function (e) {
    var ownerid = this.id.replace(/^i/, '');
    if (e.which == 3) {
        var table = theWebUI.getTable("trt");
        table.clearSelection();
        table.clearSelection();
        theWebUI.switchLabel(this);
        table.fillSelection();
        var id = table.getFirstSelected();

        if (id && plugin.canChangeMenu()) {
            theWebUI.createMenu(null, id);
            theContextMenu.show();
        }
        else
            theContextMenu.hide();
    }
    else
        theWebUI.switchLabel(this);
    return (false);
};


theWebUI.initOwnersLabels = function () {
    if (userinfo.admin) {
        plugin.addPaneToCategory("pown", 'Owners').append($("<ul></ul>").attr("id", "ownrl"));
    }
    plugin.markLoaded();
};

theWebUI.initOwnersLabels();

log('plugin initialized');
