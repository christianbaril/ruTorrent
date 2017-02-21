
$(document).ready(function () {
    var rTorrentStubStart = rTorrentStub.prototype.start;

    rTorrentStub.prototype.start = function () {

        rTorrentStubStart.call(this);

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
    };
});
